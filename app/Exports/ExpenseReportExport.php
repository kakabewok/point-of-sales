<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        protected ?string $startDate = null,
        protected ?string $endDate = null,
        protected ?string $categoryId = null,
    ) {}

    public function query()
    {
        $query = Expense::query()
            ->with(['category', 'creator'])
            ->orderBy('expense_date', 'desc');

        if ($this->startDate) {
            $query->whereDate('expense_date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('expense_date', '<=', $this->endDate);
        }

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kategori',
            'Nama',
            'Jumlah',
            'Deskripsi',
            'Kuantitas',
            'Satuan',
            'Dibuat oleh',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->expense_date->format('d/m/Y'),
            $expense->category->name ?? '-',
            $expense->description ? mb_substr($expense->description, 0, 50) : '-',
            $expense->amount,
            $expense->description ?? '-',
            $expense->quantity ?? '-',
            $expense->unit ?? '-',
            $expense->creator->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Pengeluaran';
    }
}
