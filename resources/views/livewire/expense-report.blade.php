<div class="px-0 py-8 md:px-6 space-y-8 max-w-7xl mx-auto flex-1 w-full">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-lg md:text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Laporan Pengeluaran</h1>
        <div class="flex items-center gap-3">
            @if(!empty($selected) && is_array($selected))
                <flux:button variant="danger" icon="trash" class="h-10 px-4" wire:click="confirmDeleteSelected">Terpilih ({{ count($selected) }})</flux:button>
            @endif
            <flux:button variant="primary" icon="arrow-down-tray" class="h-10 px-4" wire:click="exportExcel" wire:loading.attr="disabled">Export Excel</flux:button>
        </div>
    </div>

    @if(session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.opacity.duration.500ms class="fixed top-6 right-6 z-50 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-xl shadow-emerald-900/10 dark:border-emerald-900/50 dark:bg-emerald-900 dark:text-emerald-400 dark:shadow-black/50 flex items-center gap-3">
            <flux:icon name="check-circle" class="h-5 w-5" />
            {{ session('message') }}
            <button @click="show = false" class="ml-2 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400/70 dark:hover:text-emerald-300 transition-colors">
                <flux:icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <flux:input type="date" label="Dari Tanggal" class="h-10" wire:model.live="startDate" />
        <flux:input type="date" label="Sampai Tanggal" class="h-10" wire:model.live="endDate" />
        <flux:select label="Kategori" class="h-10" wire:model.live="filterCategoryId">
            <flux:select.option value="">Semua Kategori</flux:select.option>
            @foreach($categories as $cat)
                <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-3 text-zinc-500 dark:text-zinc-400 mb-2">
                <flux:icon name="document-text" class="h-5 w-5" />
                <h3 class="font-medium text-sm">jumlah Data Pengeluaran</h3>
            </div>
            <p class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ number_format($totalCount) }} <span class="text-sm font-semibold text-zinc-500">data</span></p>
        </div>
        <div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 p-6 shadow-sm dark:border-red-800/50 relative overflow-hidden">
            <div class="flex items-center gap-3 text-red-600 dark:text-red-400 mb-2 relative z-10">
                <flux:icon name="banknotes" class="h-5 w-5" />
                <h3 class="font-medium text-sm">Total Pengeluaran</h3>
            </div>
            <p class="text-2xl font-bold tracking-tight text-red-700 dark:text-red-300 relative z-10">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 border-b border-zinc-200 dark:bg-zinc-900/50 dark:border-zinc-800">
                    <tr>
                        <th class="px-4 py-4 text-center w-12">
                            <flux:checkbox wire:model.live="selectAll" />
                        </th>
                        <th class="px-6 py-4 text-left font-semibold text-zinc-600 dark:text-zinc-400">Tanggal</th>
                        <th class="px-6 py-4 text-left font-semibold text-zinc-600 dark:text-zinc-400">Kategori</th>
                        <th class="px-6 py-4 text-right font-semibold text-zinc-600 dark:text-zinc-400">Jumlah (Rp)</th>
                        <th class="px-6 py-4 text-left font-semibold text-zinc-600 dark:text-zinc-400">Deskripsi</th>
                        <th class="px-6 py-4 text-left font-semibold text-zinc-600 dark:text-zinc-400">Kuantitas</th>
                        <th class="px-6 py-4 text-left font-semibold text-zinc-600 dark:text-zinc-400">Satuan</th>
                        <th class="px-6 py-4 text-center font-semibold text-zinc-600 dark:text-zinc-400">Bukti</th>
                        <!-- <th class="px-6 py-4 text-left font-semibold text-zinc-600 dark:text-zinc-400 ">Dibuat oleh</th> -->
                        <th class="px-6 py-4 text-center font-semibold text-zinc-600 dark:text-zinc-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                    @forelse($expenses as $expense)
                        <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ in_array((string)$expense->id, $selected) ? 'bg-blue-50 dark:bg-blue-900/10' : '' }}">
                            <td class="px-4 py-4 text-center">
                                <flux:checkbox wire:model.live="selected" value="{{ $expense->id }}" />
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-zinc-900 dark:text-white">{{ $expense->expense_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-900/30 dark:text-indigo-400 dark:ring-indigo-900/50">
                                    {{ $expense->category->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class=" font-bold text-red-600 dark:text-red-400">{{ number_format($expense->amount, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-zinc-600 dark:text-zinc-400 truncate max-w-[200px]" title="{{ $expense->description }}">{{ $expense->description ? (mb_strlen($expense->description) > 50 ? mb_substr($expense->description, 0, 50) . '...' : $expense->description) : '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-zinc-600 dark:text-zinc-400 truncate max-w-[200px]" title="{{ $expense->quantity }}">{{ $expense->quantity ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-zinc-600 dark:text-zinc-400 truncate max-w-[200px]" title="{{ $expense->unit }}">{{ $expense->unit ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($expense->image_path)
                                    <button wire:click="openImagePreview('{{ Storage::url($expense->image_path) }}')" class="cursor-pointer inline-block">
                                        <img src="{{ Storage::url($expense->image_path) }}" alt="Bukti" class="h-10 w-10 rounded-lg object-cover ring-1 ring-zinc-200 dark:ring-zinc-700 hover:ring-2 hover:ring-indigo-400 transition-all" />
                                    </button>
                                @else
                                    <span class="text-zinc-400 text-xs">—</span>
                                @endif
                            </td>
                            <!-- <td class="px-6 py-4">
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $expense->creator->name ?? '-' }}</div>
                            </td> -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <flux:button size="sm" variant="primary" class="cursor-pointer h-9 px-3 bg-blue-600 hover:bg-blue-700 text-white border-blue-600 shadow-sm" wire:click="showDetail({{ $expense->id }})">Lihat</flux:button>
                                    <flux:button size="sm" variant="ghost" class="cursor-pointer h-9 w-9 px-0 [&_svg]:text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/20 dark:hover:text-red-400" icon="trash" wire:click="confirmDelete({{ $expense->id }}, '{{ $expense->description}}')" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-6 py-12 text-center text-zinc-500">
                            <flux:icon name="document-magnifying-glass" class="mx-auto mb-3 h-8 w-8 opacity-40" />
                            Tidak ada data pengeluaran pada periode ini.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">{{ $expenses->links() }}</div>
    </div>


    {{-- Detail Modal --}}
    <flux:modal wire:model="showDetailModal" class="max-w-sm md:max-w-lg p-0 overflow-y-auto bg-white dark:bg-zinc-900 rounded-xl shadow-xl">
        @if($detailExpense)
        <div class="p-1">
            <header class="border-b border-zinc-100 dark:border-zinc-800 pb-4 mb-5">
                <h2 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Detail Pengeluaran</h2>
            </header>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Tanggal</p>
                        <p class="font-medium text-zinc-900 dark:text-white mt-0.5">{{ $detailExpense->expense_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Kategori</p>
                        <p class="font-medium text-zinc-900 dark:text-white mt-0.5">{{ $detailExpense->category->name ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Jumlah</p>
                    <p class="text-xl font-bold text-red-600 dark:text-red-400 mt-0.5">Rp {{ number_format($detailExpense->amount, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Deskripsi</p>
                    <p class="font-medium text-zinc-900 dark:text-white mt-0.5">{{ $detailExpense->description ?: '-' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Kuantitas</p>
                        <p class="font-medium text-zinc-900 dark:text-white mt-0.5">{{ $detailExpense->quantity ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Satuan</p>
                        <p class="font-medium text-zinc-900 dark:text-white mt-0.5">{{ $detailExpense->unit ?: '-' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Dibuat oleh</p>
                    <p class="font-medium text-zinc-900 dark:text-white mt-0.5">{{ $detailExpense->creator->name ?? '-' }}</p>
                </div>
                @if($detailExpense->image_path)
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">Bukti Pembayaran</p>
                        <img src="{{ Storage::url($detailExpense->image_path) }}" alt="Bukti" class="max-h-64 w-auto rounded-lg object-contain ring-1 ring-zinc-200 dark:ring-zinc-700" />
                    </div>
                @endif
            </div>

            <footer class="cursor-pointer mb-2 flex justify-end mt-3 dark:border-zinc-800">
                <button type="button" class="cursor-pointer h-10 px-4 rounded-lg bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 font-medium text-sm transition-colors" wire:click="$set('showDetailModal', false)">Tutup</button>
            </footer>
        </div>
        @endif
    </flux:modal>

    {{-- Image Preview Modal --}}
    <flux:modal wire:model="showImageModal" class="max-w-sm md:max-w-2xl p-0 overflow-hidden bg-white dark:bg-zinc-900 rounded-lg shadow-lg">
        <div>
            @if($previewImageUrl)
                <img src="{{ $previewImageUrl }}" alt="Preview Bukti" class="w-full max-h-[70vh] object-contain rounded-md" />
            @endif
            <div class="mt-4 flex justify-end">
                <button type="button" class="cursor-pointer h-10 px-4 rounded-lg bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 font-medium text-sm transition-colors" wire:click="$set('showImageModal', false)">Tutup</button>
            </div>
        </div>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-sm md:max-w-md p-0 overflow-hidden bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl transition-all">
        <div class="p-1">
            <div class="flex flex-col items-center text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4 mx-auto">
                    <flux:icon name="exclamation-triangle" class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Konfirmasi Hapus
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Apakah Anda yakin ingin menghapus <span class="font-medium text-gray-700 dark:text-gray-300">{{ $itemToDeleteName ?: 'pengeluaran ini' }}</span>?
                </p>
            </div>

            <div class="mt-6 flex justify-end gap-3 w-full">
                <button type="button" wire:click="$set('showDeleteModal', false)" class="cursor-pointer h-10 px-4 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-gray-300 dark:hover:bg-zinc-700 font-medium text-sm transition-colors">
                    Batal
                </button>
                <button type="button" wire:click="processDelete" class="cursor-pointer h-10 px-4 rounded-lg bg-red-600 text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-sm transition-colors" wire:loading.attr="disabled" wire:target="processDelete">
                    <span wire:loading.remove wire:target="processDelete">Hapus</span>
                    <span wire:loading wire:target="processDelete">Memproses...</span>
                </button>
            </div>
        </div>
    </flux:modal>
</div>
