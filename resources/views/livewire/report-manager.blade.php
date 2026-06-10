<div class="px-0 py-8 md:px-5 space-y-8 max-w-7xl mx-auto flex-1 w-full">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-lg md:text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Laporan Transaksi</h1>
        <div class="flex items-center gap-3">
            @if(!empty($selected) && is_array($selected))
                <flux:button variant="danger" icon="trash" class="h-10 px-4" wire:click="confirmDeleteSelected">Terpilih ({{ count($selected) }})</flux:button>
            @endif
            <flux:button variant="primary" icon="arrow-down-tray" class="cursor-pointer h-10 px-4" wire:click="exportExcel" wire:loading.attr="disabled">Export Excel</flux:button>
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
    @if(session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.opacity.duration.500ms class="fixed top-6 right-6 z-50 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 shadow-xl shadow-red-900/10 dark:border-red-900/50 dark:bg-red-900 dark:text-red-400 dark:shadow-black/50 flex items-center gap-3">
            <flux:icon name="exclamation-circle" class="h-5 w-5" />
            {{ session('error') }}
            <button @click="show = false" class="ml-2 text-red-600 hover:text-red-800 dark:text-red-400/70 dark:hover:text-red-300 transition-colors">
                <flux:icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <flux:input type="date" label="Dari Tanggal" class="h-10 cursor-pointer" wire:model.live="startDate" />
        <flux:input type="date" label="Sampai Tanggal" class="h-10 cursor-pointer" wire:model.live="endDate" />
        <flux:select label="Metode Pembayaran" class="h-10 cursor-pointer" wire:model.live="paymentMethod">
            <flux:select.option value="">Semua Metode</flux:select.option>
            <flux:select.option value="cash">Tunai</flux:select.option>
            <flux:select.option value="qris">QRIS</flux:select.option>
        </flux:select>
        <flux:select label="Kategori" class="h-10 cursor-pointer" wire:model.live="categoryId">
            <flux:select.option value="">Semua Kategori</flux:select.option>
            @foreach($categories as $category)
                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select label="Pengguna" class="h-10 cursor-pointer" wire:model.live="userId">
             <flux:select.option value="">Semua Pengguna</flux:select.option>
            @foreach($users as $user)
                <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-3 text-zinc-500 dark:text-zinc-400 mb-2">
                <flux:icon name="shopping-cart" class="h-5 w-5" />
                <h3 class="font-medium text-sm">Total Transaksi</h3>
            </div>
            <p class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ number_format($totalTransactions) }} <span class="text-sm font-semibold text-zinc-500">trx</span></p>
        </div>
        <div class="rounded-lg border bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 p-6 shadow-sm">
            <div class="flex items-center gap-3 text-blue-700 dark:text-blue-400 mb-2">
                <flux:icon name="receipt-percent" class="h-5 w-5" />
                <h3 class="font-medium text-sm">Total Diskon Diberikan</h3>
            </div>
            <p class="text-2xl font-bold tracking-tight text-blue-700 dark:text-blue-300">Rp {{ number_format($totalDiscounts, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg border bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 p-6 shadow-sm">
            <div class="flex items-center gap-3 text-amber-700 dark:text-amber-500 mb-2">
                <flux:icon name="document-text" class="h-5 w-5" />
                <h3 class="font-medium text-sm">Total Pajak</h3>
            </div>
            <p class="text-2xl font-bold tracking-tight text-amber-700 dark:text-amber-500">Rp {{ number_format($totalTax, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 p-6 shadow-sm dark:border-emerald-800/50 relative overflow-hidden">
            <div class="flex items-center gap-3 text-emerald-600 dark:text-emerald-400 mb-2 relative z-0">
                <flux:icon name="currency-dollar" class="h-5 w-5" />
                <h3 class="font-medium text-sm">Total Pendapatan</h3>
            </div>
            <p class="text-2xl font-bold tracking-tight text-emerald-700 dark:text-emerald-300 relative z-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
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
                        <th class="px-6 py-4 text-left font-semibold text-zinc-600 dark:text-zinc-400">Invoice/Waktu</th>
                        <th class="px-6 py-4 text-center font-semibold text-zinc-600 dark:text-zinc-400">Item</th>
                        <th class="px-3 py-4 text-right font-semibold text-zinc-600 dark:text-zinc-400">Subtotal (Rp)</th>
                        <th class="px-3 py-4 text-right font-semibold text-blue-600 dark:text-blue-500">Diskon (Rp)</th>
                        <th class="px-3 py-4 text-right font-semibold text-amber-700 dark:text-amber-500">Pajak (Rp)</th>
                        <th class="px-3 py-4 text-right font-semibold text-emerald-600 dark:text-emerald-500">Total (Rp)</th>
                        <th class="px-6 py-4 text-center font-semibold text-zinc-600 dark:text-zinc-400">Metode</th>
                        <th class="px-6 py-4 text-center font-semibold text-zinc-600 dark:text-zinc-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                    @forelse($transactions as $trx)
                        <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ in_array((string)$trx->id, $selected) ? 'bg-blue-50 dark:bg-blue-900/10' : '' }}">
                            <td class="px-4 py-4 text-center">
                                <flux:checkbox wire:model.live="selected" value="{{ $trx->id }}" />
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold font-mono text-zinc-900 dark:text-white">{{ $trx->invoice_number }}</div>
                                <div class="text-xs text-zinc-500 mt-1">{{ $trx->created_at->format('d/m/Y H:i') }} • {{ $trx->user->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-zinc-700 dark:text-zinc-300">{{ $trx->items->sum('quantity') }}</td>
                            <td class="px-6 py-4 text-right font-medium text-zinc-600 dark:text-zinc-400">{{ number_format($trx->subtotal, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-medium text-blue-600 dark:text-blue-400">-{{ number_format($trx->discount_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-medium text-amber-700 dark:text-amber-500">+{{ number_format($trx->tax_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-base font-bold text-emerald-600 dark:text-emerald-500">{{ number_format($trx->grand_total, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ match($trx->payment?->method) { 
                                    'cash' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-900/50', 
                                    'qris' => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-900/50', 
                                    'va' => 'bg-violet-50 text-violet-700 ring-violet-600/20 dark:bg-violet-900/30 dark:text-violet-400 dark:ring-violet-900/50', 
                                    default => 'bg-zinc-50 text-zinc-600 ring-zinc-500/20 dark:bg-zinc-800/50 dark:text-zinc-400 dark:ring-zinc-700' 
                                } }}">
                                    {{ strtoupper($trx->payment?->method ?? '-') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <flux:button size="sm" variant="ghost" class="cursor-pointer h-9 w-9 px-0 text-zinc-600 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-900/20 dark:hover:text-zinc-300" icon="pencil-square" wire:click="editTransaction({{ $trx->id }})" />
                                    <flux:button size="sm" variant="ghost" class="cursor-pointer h-9 w-9 px-0 text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800" icon="printer" as="a" href="{{ route('receipt.print', $trx->id) }}" target="_blank" />
                                    <flux:button size="sm" variant="ghost" class="cursor-pointer h-9 w-9 px-0 [&_svg]:text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/20 dark:hover:text-red-400" icon="trash" wire:click="confirmDelete({{ $trx->id }}, '{{ $trx->invoice_number }}')" />
                                    <flux:button size="sm" variant="primary" class="cursor-pointer h-9 px-3 bg-blue-500 hover:bg-blue-600 text-white border-blue-600 shadow-sm" as="a" href="{{ route('reports.detail', $trx->id) }}" wire:navigate>Lihat</flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-6 py-12 text-center text-zinc-500">
                            <flux:icon name="document-magnifying-glass" class="mx-auto mb-3 h-8 w-8 opacity-40" />
                            Tidak ada transaksi pada periode ini.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">{{ $transactions->links() }}</div>
    </div>

    <!-- Delete Confirmation Modal -->
    <flux:modal wire:model="showDeleteModal" class="max-w-sm md:max-w-md p-0 overflow-hidden bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl transition-all">
        <div>
            <div class="flex flex-col items-center text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4 mx-auto">
                    <flux:icon name="exclamation-triangle" class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Konfirmasi Hapus
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Apakah Anda yakin ingin menghapus <span class="font-medium text-gray-700 dark:text-gray-300">{{ $itemToDeleteName ?: 'item ini' }}</span>?
                </p>
            </div>
            
            <div class="mt-6 flex justify-end gap-3 w-full">
                <button type="button" wire:click="$set('showDeleteModal', false)" class="h-10 px-4 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-gray-300 dark:hover:bg-zinc-700 font-medium text-sm transition-colors">
                    Batal
                </button>
                <button type="button" wire:click="processDelete" class="h-10 px-4 rounded-lg bg-red-600 text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium text-sm transition-colors" wire:loading.attr="disabled" wire:target="processDelete">
                    <span wire:loading.remove wire:target="processDelete">Hapus</span>
                    <span wire:loading wire:target="processDelete">Memproses...</span>
                </button>
            </div>
        </div>
    </flux:modal>

    <!-- Edit Modal -->
    <flux:modal wire:model="showEditModal" class="max-w-sm md:max-w-2xl bg-white dark:bg-zinc-900 rounded-xl shadow-xl">
        <div class="flex flex-col max-h-[70vh] md:max-h-[90vh]">
            <div class="sticky top-0 -z-10 bg-white dark:bg-zinc-900 p-6 w-full">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Edit Transaksi</h2>
                        <p class="text-xs text-zinc-500 mt-0.5">Ubah detail item, diskon dan metode pembayaran</p>
                    </div>
                    <div wire:loading wire:target="saveTransaction" class="flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 mr-8">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </div>
                </div>
            </div>

            <div class="overflow-y-auto p-6">
                <form wire:submit="saveTransaction" class="space-y-6">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3 flex items-center gap-2">
                            <flux:icon name="shopping-bag" class="h-4 w-4" />
                            Item Transaksi
                        </h3>
                        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-800">
                            <table class="w-full text-sm">
                                <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-400">Produk</th>
                                        <th class="px-4 py-3 text-center font-semibold text-zinc-600 dark:text-zinc-400 w-28">Qty</th>
                                        <th class="px-4 py-3 text-right font-semibold text-zinc-600 dark:text-zinc-400 w-40">Harga (Rp)</th>
                                        <th class="px-4 py-3 text-right font-semibold text-zinc-600 dark:text-zinc-400 w-36">Subtotal (Rp)</th>
                                        <th class="px-4 py-3 text-center w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                    @foreach($editItems as $index => $item)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                            <td class="px-4 py-3">
                                                <span class="font-medium text-zinc-900 dark:text-white">{{ $item['product_name'] }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="number" min="1" wire:model.live.debounce.300ms="editItems.{{ $index }}.quantity" class="w-20 h-9 text-center rounded-md border border-zinc-300 bg-white text-sm font-medium text-zinc-900 px-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" />
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <input type="number" min="0" step="100" wire:model.live.debounce.300ms="editItems.{{ $index }}.product_price" class="w-32 h-9 text-left rounded-md border border-zinc-300 bg-white text-sm font-medium text-zinc-900 px-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" />
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-zinc-900 dark:text-white">
                                                {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if(count($editItems) > 1)
                                                    <button type="button" wire:click="removeEditItem({{ $index }})" class="cursor-pointer h-7 w-7 flex items-center justify-center rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/20 transition-colors">
                                                        <flux:icon name="x-mark" class="h-4 w-4" />
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @error('editItems') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        @error('editItems.*.quantity') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                            Tanggal Transaksi
                        </label>

                        <input
                            type="date"
                            wire:model.live.debounce.300ms="editTransactionDate"
                            class="w-full h-10 rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        />

                        @error('editTransactionDate')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Discount & Payment --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Tipe Diskon</label>
                            <select wire:model.live="editDiscountType" class="cursor-pointer w-full h-10 rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                <option value="">Tanpa Diskon</option>
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal (Rp)</option>
                            </select>
                            @error('editDiscountType') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nilai Diskon</label>
                            <input type="number" min="0" step="1" wire:model.live.debounce.300ms="editDiscountValue" class="w-full h-10 rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" placeholder="0" {{ !$editDiscountType ? 'disabled' : '' }} />
                            @error('editDiscountValue') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Metode Pembayaran</label>
                            <select wire:model="editPaymentMethod" class="cursor-pointer w-full h-10 rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                <option value="cash">Tunai</option>
                                <option value="qris">QRIS</option>
                            </select>
                            @error('editPaymentMethod') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Catatan</label>
                            <input type="text" wire:model="editNotes" class="w-full h-10 rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" placeholder="Opsional..." />
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/30 p-5 space-y-3">
                        <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400">
                            <span>Subtotal</span>
                            <span class="font-medium">Rp {{ number_format($editSubtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($editDiscountAmount > 0)
                            <div class="flex justify-between text-sm text-emerald-600 dark:text-emerald-400">
                                <span>Diskon {{ $editDiscountType === 'percentage' ? '('.$editDiscountValue.'%)' : '' }}</span>
                                <span class="font-medium">-Rp {{ number_format($editDiscountAmount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($editTaxAmount > 0)
                            <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400">
                                <span>Pajak ({{ $editTaxRate }}%)</span>
                                <span class="font-medium">+Rp {{ number_format($editTaxAmount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="pt-3 mt-1 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                            <span class="text-base font-bold text-zinc-900 dark:text-white">Total Akhir</span>
                            <span class="text-xl font-black text-zinc-900 dark:text-white">Rp {{ number_format($editGrandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    {{-- Footer Actions --}}
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showEditModal', false)" class="cursor-pointer h-10 px-5 rounded-lg bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 font-medium text-sm transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="cursor-pointer h-10 px-6 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="saveTransaction">
                            <span wire:loading.remove wire:target="saveTransaction">Simpan Perubahan</span>
                            <span wire:loading wire:target="saveTransaction">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </flux:modal>
</div>