<?php

namespace App\Livewire;

use App\Exports\TransactionReportExport;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('layouts.app')]
#[Title('Laporan Penjualan')]
class ReportManager extends Component
{
    use WithPagination;

    // filters
    public $startDate;
    public $endDate;
    public $paymentMethod = '';
    public $categoryId = '';
    public $userId = '';

    public $showDeleteModal = false;
    public $itemToDeleteId = null;
    public $itemToDeleteName = null;
    public $deleteType = 'single';

    public $selected = [];
    public $selectAll = false;

    // Edit Transaction Properties
    public $showEditModal = false;
    public $editingTransactionId = null;
    public $editItems = [];
    public $editPaymentMethod = '';
    public $editNotes = '';
    public $editDiscountType = '';
    public $editDiscountValue = 0;
    public $editTransactionDate = '';

    // Computed totals for the edit form
    public $editSubtotal = 0;
    public $editDiscountAmount = 0;
    public $editTaxRate = 0;
    public $editTaxAmount = 0;
    public $editGrandTotal = 0;

    public function mount()
    {
        // Default to this month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }
    public function updatingPaymentMethod() { $this->resetPage(); }
    public function updatingCategoryId() { $this->resetPage(); }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->buildQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function exportExcel()
    {
        $filename = 'laporan-transaksi-' . $this->startDate . '-to-' . $this->endDate . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new TransactionReportExport($this->startDate, $this->endDate), 
            $filename
        );
    }

    // Edit Methods

    public function editTransaction($id)
    {
        $transaction = Transaction::with(['items.product', 'payment'])->find($id);

        if (!$transaction) {
            session()->flash('error', 'Transaksi tidak ditemukan.');
            return;
        }

        $this->editTransactionDate = $transaction->created_at->format('Y-m-d');
        $this->editingTransactionId = $transaction->id;
        $this->editPaymentMethod = $transaction->payment?->method ?? 'cash';
        $this->editNotes = $transaction->notes ?? '';
        $this->editDiscountType = $transaction->discount_type ?? '';
        $this->editDiscountValue = (float) ($transaction->discount_value ?? 0);
        $this->editTaxRate = (float) ($transaction->tax_rate ?? 0);

        $this->editItems = [];
        foreach ($transaction->items as $item) {
            $this->editItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_price' => (float) $item->product_price,
                'quantity' => (int) $item->quantity,
                'subtotal' => (float) $item->subtotal,
                'original_quantity' => (int) $item->quantity,
            ];
        }

        $this->recalculateEditTotals();
        $this->showEditModal = true;
    }

    public function updatedEditItems()
    {
        $this->recalculateEditTotals();
    }

    public function updatedEditDiscountType()
    {
        $this->recalculateEditTotals();
    }

    public function updatedEditDiscountValue()
    {
        $this->recalculateEditTotals();
    }

    public function removeEditItem($index)
    {
        if (count($this->editItems) <= 1) {
            session()->flash('error', 'Transaksi harus memiliki minimal 1 item.');
            return;
        }

        unset($this->editItems[$index]);
        $this->editItems = array_values($this->editItems);
        $this->recalculateEditTotals();
    }

    protected function recalculateEditTotals()
    {
        $subtotal = 0;
        foreach ($this->editItems as $key => $item) {
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $price = (float) ($item['product_price'] ?? 0);
            $itemSubtotal = $price * $qty;

            $this->editItems[$key]['quantity'] = $qty;
            $this->editItems[$key]['subtotal'] = $itemSubtotal;
            $subtotal += $itemSubtotal;
        }
        
        $this->editSubtotal = $subtotal;

        // Calculate discount
        $discountAmount = 0;

        if ($this->editDiscountType === 'percentage' && $this->editDiscountValue > 0) {
            $discountAmount = $subtotal * (min(100, $this->editDiscountValue) / 100);
        } elseif ($this->editDiscountType === 'fixed' && $this->editDiscountValue > 0) {
            $discountAmount = min($subtotal, $this->editDiscountValue);
        }
        $this->editDiscountAmount = round($discountAmount, 2);

        // Calculate tax
        $afterDiscount = $subtotal - $this->editDiscountAmount;
        $this->editTaxAmount = round($afterDiscount * ($this->editTaxRate / 100), 2);

        // Grand total
        $this->editGrandTotal = round($afterDiscount + $this->editTaxAmount, 2);
    }

    public function saveTransaction()
    {
        // Validation
        $this->validate([
            'editItems' => 'required|array|min:1',
            'editItems.*.quantity' => 'required|integer|min:1',
            'editItems.*.product_price' => 'required|numeric|min:0',
            'editPaymentMethod' => 'required|in:cash,qris,va',
            'editTransactionDate' => 'required|date',
        ], [
            'editItems.required' => 'Minimal 1 item diperlukan.',
            'editItems.*.quantity.min' => 'Jumlah minimal 1.',
            'editItems.*.product_price.min' => 'Harga tidak boleh negatif.',
            'editPaymentMethod.required' => 'Metode pembayaran harus dipilih.',
            'editTransactionDate.required' => 'Tanggal transaksi harus diisi.',
        ]);

        $this->recalculateEditTotals();

        try {
            DB::transaction(function () {
                $transaction = Transaction::with(['items', 'payment'])->findOrFail($this->editingTransactionId);

                // Build a map of old quantities for stock adjustment
                $oldQuantities = [];
                foreach ($transaction->items as $item) {
                    $oldQuantities[$item->id] = (int) $item->quantity;
                }

                // Determine which item IDs still exist
                $updatedItemIds = collect($this->editItems)->pluck('id')->filter()->toArray();

                // Delete removed items and restore their stock
                $removedItems = $transaction->items->whereNotIn('id', $updatedItemIds);
                foreach ($removedItems as $removedItem) {
                    if ($removedItem->product && $removedItem->product->type !== 'service') {
                        $removedItem->product->increment('stock', $removedItem->quantity);
                    }
                    $removedItem->delete();
                }

                // Update existing items and adjust stock
                foreach ($this->editItems as $editItem) {
                    if (!empty($editItem['id'])) {
                        $txItem = TransactionItem::find($editItem['id']);
                        if ($txItem) {
                            $oldQty = $oldQuantities[$txItem->id] ?? 0;
                            $newQty = (int) $editItem['quantity'];
                            $qtyDiff = $newQty - $oldQty;

                            // Adjust stock (only for products, not services)
                            if ($txItem->product && $txItem->product->type !== 'service' && $qtyDiff !== 0) {
                                $txItem->product->decrement('stock', $qtyDiff);
                            }

                            $txItem->update([
                                'quantity' => $newQty,
                                'product_price' => (float) $editItem['product_price'],
                                'subtotal' => (float) $editItem['subtotal'],
                            ]);
                        }
                    }
                }

                // Update the transaction itself
                $transaction->update([
                    'subtotal' => $this->editSubtotal,
                    'discount_type' => $this->editDiscountType ?: null,
                    'discount_value' => $this->editDiscountValue,
                    'discount_amount' => $this->editDiscountAmount,
                    'tax_amount' => $this->editTaxAmount,
                    'grand_total' => $this->editGrandTotal,
                    'notes' => $this->editNotes ?: null,
                    'created_at' => Carbon::parse($this->editTransactionDate)
                    ->startOfDay(),
                ]);

                // Update payment
                if ($transaction->payment) {
                    $transaction->payment->update([
                        'method' => $this->editPaymentMethod,
                        'amount' => $this->editGrandTotal,
                    ]);
                }

                ActivityLogger::crud('transaction_edited', 'transaction', $transaction->id, [
                    'invoice' => $transaction->invoice_number,
                    'new_grand_total' => $this->editGrandTotal,
                ]);
            });

            $this->showEditModal = false;
            $this->reset(['editTransactionDate', 'editingTransactionId', 'editItems', 'editPaymentMethod', 'editNotes', 'editDiscountType', 'editDiscountValue']);
            session()->flash('message', 'Transaksi berhasil diperbarui.');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }

    // ─── Delete Methods ────────────────────────────────────────

    public function confirmDelete($id, $invoice = '')
    {
        $this->itemToDeleteId = $id;
        $this->itemToDeleteName = $invoice;
        $this->deleteType = 'single';
        $this->showDeleteModal = true;
    }

    public function confirmDeleteSelected()
    {
        if (empty($this->selected)) return;

        $this->itemToDeleteName = count($this->selected) . ' transaksi terpilih';
        $this->deleteType = 'multiple';
        $this->showDeleteModal = true;
    }

    public function processDelete()
    {
        if ($this->deleteType === 'single' && $this->itemToDeleteId) {
            $transaction = Transaction::find($this->itemToDeleteId);
            if ($transaction) {
                ActivityLogger::crud('transaction_deleted', 'transaction', $transaction->id, ['invoice' => $transaction->invoice_number]);
                $transaction->delete(); // Soft delete
                session()->flash('message', 'Transaksi berhasil dihapus.');
            }
        } elseif ($this->deleteType === 'multiple') {
            $this->deleteSelected();
        }

        $this->showDeleteModal = false;
        $this->reset(['itemToDeleteId', 'itemToDeleteName', 'deleteType']);
    }

    public function deleteSelected()
    {
        if (empty($this->selected)) return;

        ActivityLogger::bulk('transaction_bulk_deleted', 'transaction', $this->selected);

        // Chunk deletion for safety with large selections
        collect($this->selected)->chunk(100)->each(function ($chunk) {
            Transaction::whereIn('id', $chunk)->delete();
        });

        $deletedCount = count($this->selected);
        $this->reset(['selected', 'selectAll']);
        session()->flash('message', "{$deletedCount} transaksi berhasil dihapus.");
    }

    // ─── Query Builder ─────────────────────────────────────────

    protected function buildQuery()
    {
        $query = Transaction::query()
            ->with(['user', 'payment', 'items.product.category'])
            ->where('status', 'completed')
            ->orderByDesc('created_at');

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        if ($this->paymentMethod) {
            $query->whereHas('payment', fn($q) => $q->where('method', $this->paymentMethod));
        }

        if ($this->categoryId) {
            $query->whereHas('items.product', fn($q) => $q->where('category_id', $this->categoryId));
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        return $query;
    }

    public function render()
    {
        $query = $this->buildQuery();

        // Summary calculations (without pagination limit)
        $summaryQuery = clone $query;
        $totalRevenue = $summaryQuery->sum('grand_total');
        $totalTransactions = $summaryQuery->count();
        $totalTax = $summaryQuery->sum('tax_amount');
        $totalDiscounts = $summaryQuery->sum('discount_amount');

        $transactions = $query->paginate(20);

        $categories = Category::active()->ordered()->get();
        $users = User::active()->get();

        return view('livewire.report-manager', [
            'transactions' => $transactions,
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'totalTax' => $totalTax,
            'totalDiscounts' => $totalDiscounts,
            'categories' => $categories,
            'users' => $users,
        ]);
    }
}
