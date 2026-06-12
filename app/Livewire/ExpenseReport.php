<?php

namespace App\Livewire;

use App\Exports\ExpenseReportExport;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\ActivityLogger;

#[Layout('layouts.app')]
#[Title('Laporan Pengeluaran')]
class ExpenseReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $filterCategoryId = '';

    public $showDeleteModal = false;
    public $itemToDeleteId = null;
    public $itemToDeleteName = null;
    public $deleteType = 'single';

    public $selected = [];
    public $selectAll = false;

    // Image Preview
    public $showImageModal = false;
    public $previewImageUrl = '';

    // Detail Modal
    public $showDetailModal = false;
    public $detailExpense = null;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }
    public function updatingFilterCategoryId() { $this->resetPage(); }

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
        $filename = 'laporan-pengeluaran-' . $this->startDate . '-to-' . $this->endDate . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new ExpenseReportExport($this->startDate, $this->endDate, $this->filterCategoryId),
            $filename
        );
    }

    // ─── Detail / Preview ──────────────────────────────────────

    public function showDetail(Expense $expense)
    {
        $expense->load(['category', 'creator']);
        $this->detailExpense = $expense;
        $this->showDetailModal = true;
    }

    public function openImagePreview($url)
    {
        $this->previewImageUrl = $url;
        $this->showImageModal = true;
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
        // Include soft-deleted expenses for historical record persistent
        // $query = Expense::withTrashed()
        //     ->with(['category', 'creator'])
        //     ->orderByDesc('expense_date')
        //     ->orderByDesc('created_at');
        
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

        return view('livewire.expense-report', [
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'totalCount' => $totalCount,
            'categories' => $categories,
        ]);
    }
}
