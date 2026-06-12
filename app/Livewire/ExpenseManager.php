<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Services\ActivityLogger;

#[Layout('layouts.app')]
#[Title('Pengeluaran')]
class ExpenseManager extends Component
{
    use WithPagination, WithFileUploads;

    // Filters
    public $startDate;
    public $endDate;
    public $filterCategoryId = '';

    // CRUD Modal
    public $showModal = false;
    public $editingId = null;
    public $category_id = '';
    public $amount = '';
    public $quantity = '';
    public $unit = '';
    public $description = '';
    public $expense_date = '';
    public $image;
    public $imageIteration = 0;
    public $existingImagePath = null;
    public $removeImage = false;

    // Detail Modal
    public $showDetailModal = false;
    public $detailExpense = null;

    // Image Preview Modal
    public $showImageModal = false;
    public $previewImageUrl = '';

    // Delete Modal
    public $showDeleteModal = false;
    public $itemToDeleteId = null;
    public $itemToDeleteName = null;
    public $deleteType = 'single';

    // Bulk
    public $selected = [];
    public $selectAll = false;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->expense_date = now()->format('Y-m-d');
    }

    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }
    public function updatingFilterCategoryId() { $this->resetPage(); }

    protected function rules()
    {
        return [
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'quantity' => 'required|numeric|min:1',
            'unit' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'expense_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    protected $messages = [
        'category_id.required' => 'Kategori wajib dipilih.',
        'amount.required' => 'Jumlah wajib diisi.',
        'amount.min' => 'Jumlah minimal 1.',
        'quantity.required' => 'Kuantitas wajib diisi.',
        'unit.required' => 'Satuan wajib diisi.',
        'expense_date.required' => 'Tanggal wajib diisi.',
        'image.image' => 'File harus berupa gambar.',
        'image.mimes' => 'Format gambar harus JPG atau PNG.',
        'image.max' => 'Ukuran gambar maksimal 2MB.',
    ];

    // ─── CRUD ──────────────────────────────────────────────────

    public function create()
    {
        $this->reset(['category_id', 'amount', 'quantity', 'unit', 'description', 'image', 'editingId', 'existingImagePath', 'removeImage']);
        $this->expense_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit(Expense $expense)
    {
        $this->editingId = $expense->id;
        $this->category_id = $expense->category_id;
        $this->amount = $expense->amount;
        $this->quantity = $expense->quantity;
        $this->unit = $expense->unit;
        $this->description = $expense->description;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->existingImagePath = $expense->image_path;
        $this->image = null;
        $this->removeImage = false;
        $this->showModal = true;
    }

    public function removeUploadedImage()
    {
        $this->image = null;
        $this->removeImage = true;
        $this->imageIteration++;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'category_id' => $this->category_id,
            'amount' => $this->amount,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'description' => $this->description,
            'expense_date' => $this->expense_date,
        ];

        if ($this->removeImage && $this->editingId && $this->existingImagePath && !$this->image) {
            Storage::disk('public')->delete($this->existingImagePath);
            $data['image_path'] = null;
        }

        // Handle image upload
        if ($this->image) {
            $path = $this->image->store('expenses', 'public');
            $data['image_path'] = $path;

            // Delete old image if editing
            if ($this->editingId && $this->existingImagePath) {
                Storage::disk('public')->delete($this->existingImagePath);
            }
        }

        if ($this->editingId) {
            $expense = Expense::findOrFail($this->editingId);
            $expense->update($data);
            ActivityLogger::crud('expense_updated', 'expense', $this->editingId, ['amount' => $this->amount, 'quantity' => $this->quantity, 'unit' => $this->unit]);
            session()->flash('message', 'Pengeluaran berhasil diperbarui.');
        } else {
            $data['created_by'] = auth()->id();
            $expense = Expense::create($data);
            ActivityLogger::crud('expense_created', 'expense', $expense->id, ['amount' => $expense->amount, 'quantity' => $expense->quantity, 'unit' => $expense->unit]);
            session()->flash('message', 'Pengeluaran berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['image']);
    }

    // ─── Detail ────────────────────────────────────────────────

    // public function showDetail(Expense $expense)
    // {
    //     $expense->load(['category', 'creator']);
    //     $this->detailExpense = $expense;
    //     $this->showDetailModal = true;
    // }

    public function openImagePreview($url)
    {
        $this->previewImageUrl = $url;
        $this->showImageModal = true;
    }

    // ─── Select / Bulk ─────────────────────────────────────────

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->buildQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    // ─── Delete Methods ────────────────────────────────────────

    public function confirmDelete($id, $label = '')
    {
        $this->itemToDeleteId = $id;
        $this->itemToDeleteName = $label;
        $this->deleteType = 'single';
        $this->showDeleteModal = true;
    }

    public function confirmDeleteSelected()
    {
        if (empty($this->selected)) return;

        $this->itemToDeleteName = count($this->selected) . ' pengeluaran terpilih';
        $this->deleteType = 'multiple';
        $this->showDeleteModal = true;
    }

    public function processDelete()
    {
        if ($this->deleteType === 'single' && $this->itemToDeleteId) {
            $expense = Expense::find($this->itemToDeleteId);
            if ($expense) {
                ActivityLogger::crud('expense_deleted', 'expense', $expense->id, ['amount' => $expense->amount]);
                $expense->delete();
                session()->flash('message', 'Pengeluaran berhasil dihapus.');
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

        ActivityLogger::bulk('expense_bulk_deleted', 'expense', $this->selected);

        collect($this->selected)->chunk(100)->each(function ($chunk) {
            Expense::whereIn('id', $chunk)->delete();
        });

        $deletedCount = count($this->selected);
        $this->reset(['selected', 'selectAll']);
        session()->flash('message', "{$deletedCount} pengeluaran berhasil dihapus.");
    }

    // ─── Query Builder ─────────────────────────────────────────

    protected function buildQuery()
    {
        $query = Expense::query()
            ->with(['category', 'creator'])
            ->orderByDesc('expense_date')
            ->orderByDesc('created_at');

        if ($this->startDate) {
            $query->whereDate('expense_date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('expense_date', '<=', $this->endDate);
        }

        if ($this->filterCategoryId) {
            $query->where('category_id', $this->filterCategoryId);
        }

        return $query;
    }

    public function render()
    {
        $query = $this->buildQuery();

        // Summary
        $summaryQuery = clone $query;
        $totalExpenses = $summaryQuery->sum('amount');
        $totalCount = $summaryQuery->count();

        $expenses = $query->paginate(20);
        $categories = ExpenseCategory::ordered()->get();

        return view('livewire.expense-manager', [
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'totalCount' => $totalCount,
            'categories' => $categories,
        ]);
    }
}
