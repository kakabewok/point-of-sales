<div class="px-1 py-8 md:px-6 space-y-8 max-w-7xl mx-auto flex-1 w-full">
    <div class="flex flex-row items-center justify-between gap-4">
        <h1 class="text-lg md:text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Pengeluaran</h1>
        <div class="flex flex-col items-end md:flex-row md:items-center gap-3">
            @if(!empty($selected) && is_array($selected))
                <flux:button variant="danger" icon="trash" class="cursor-pointer h-10 px-4" wire:click="confirmDeleteSelected">Terpilih ({{ count($selected) }})</flux:button>
            @endif
            <flux:button variant="primary" icon="plus" class="cursor-pointer text-sm md:text-md h-10 px-4" wire:click="create">Pengeluaran</flux:button>
        </div>
    </div>

    {{-- Notifications --}}
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
                <h3 class="font-medium text-sm">Total Data Pengeluaran</h3>
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
                        <th class="px-6 py-4 text-left font-semibold text-zinc-600 dark:text-zinc-400">Dibuat oleh</th>
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
                                <span class="text-base font-bold text-red-600 dark:text-red-400">{{ number_format($expense->amount, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-zinc-600 dark:text-zinc-400 truncate max-w-[200px]" title="{{ $expense->description }}">{{ $expense->description ?: '-' }}</div>
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
                            <td class="px-6 py-4">
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $expense->creator->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- <flux:button size="sm" variant="ghost" class="cursor-pointer h-8 w-8 px-0" icon="eye" wire:click="showDetail({{ $expense->id }})" /> -->
                                    <flux:button size="sm" variant="ghost" class="cursor-pointer h-8 w-8 px-0" icon="pencil" wire:click="edit({{ $expense->id }})" />
                                    <flux:button size="sm" variant="ghost" class="cursor-pointer h-8 w-8 px-0 [&_svg]:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" icon="trash" wire:click="confirmDelete({{ $expense->id }}, 'Rp {{ number_format($expense->amount, 0, ',', '.') }}')" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-12 text-center text-zinc-500">
                            <flux:icon name="banknotes" class="mx-auto mb-3 h-8 w-8 opacity-40" />
                            Tidak ada data pengeluaran ditemukan.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">{{ $expenses->links() }}</div>
    </div>

    {{-- CRUD Modal --}}
    <flux:modal wire:model="showModal" class="max-w-sm md:max-w-lg p-0 overflow-hidden bg-white dark:bg-zinc-900 rounded-xl shadow-xl">
        <div>
            <header class="border-b border-zinc-100 dark:border-zinc-800 pb-4 mb-4">
                <h2 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $editingId ? 'Edit Pengeluaran' : 'Tambah Pengeluaran' }}</h2>
                <p class="text-sm text-zinc-500 mt-1">Lengkapi informasi pengeluaran di bawah ini.</p>
            </header>

            <div class="max-h-[60vh] overflow-y-auto pr-2 customized-scrollbar">
                <form wire:submit="save" id="expenseForm" class="space-y-4 px-3">
                    <flux:field>
                        <flux:label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Kategori <span class="text-red-500">*</span></flux:label>
                        <flux:select class="h-10 mt-1 rounded-lg border-zinc-300" wire:model="category_id">
                            <flux:select.option value="">Pilih Kategori</flux:select.option>
                            @foreach($categories as $cat)
                                <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="category_id" class="mt-1 text-sm text-red-500" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Jumlah (Rp) <span class="text-red-500">*</span></flux:label>
                        <flux:input type="number" class="h-10 mt-1 rounded-lg border-zinc-300" wire:model="amount" placeholder="0" min="1" />
                        <flux:error name="amount" class="mt-1 text-sm text-red-500" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Deskripsi</flux:label>
                        <flux:textarea class="mt-1 rounded-lg border-zinc-300" wire:model="description" rows="3" placeholder="Deskripsi pengeluaran..." />
                        <flux:error name="description" class="mt-1 text-sm text-red-500" />
                    </flux:field>

                    <div class="flex gap-2">
                        <flux:field class="flex-1">
                            <flux:label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Kuantitas <span class="text-red-500">*</span></flux:label>
                            <flux:input type="number" class="h-10 mt-1 rounded-lg border-zinc-300" wire:model="quantity" placeholder="0" min="1" />
                            <flux:error name="quantity" class="mt-1 text-sm text-red-500" />
                        </flux:field>

                        <flux:field class="flex-1">
                            <flux:label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Satuan <span class="text-red-500">*</span></flux:label>
                            <flux:input type="text" class="h-10 mt-1 rounded-lg border-zinc-300" wire:model="unit" placeholder="pcs/box/lusin" />
                            <flux:error name="unit" class="mt-1 text-sm text-red-500" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Tanggal <span class="text-red-500">*</span></flux:label>
                        <flux:input type="date" class="h-10 mt-1 rounded-lg border-zinc-300" wire:model="expense_date" />
                        <flux:error name="expense_date" class="mt-1 text-sm text-red-500" />
                    </flux:field>

                    <flux:field>
                        <flux:label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Bukti Pembayaran (JPG/PNG, Maks 2MB)</flux:label>
                        <div class="mt-1">
                            <input type="file" wire:key="expense-image-{{ $imageIteration }}" id="expense-image-{{ $imageIteration }}" wire:model="image" accept="image/jpeg,image/png" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 dark:file:bg-slate-900/30 dark:file:text-slate-400 dark:text-zinc-400 cursor-pointer" />
                            <div wire:loading wire:target="image" class="mt-2 text-sm text-slate-600 dark:text-slate-400">Mengupload gambar...</div>
                        </div>
                        <flux:error name="image" class="mt-1 text-sm text-red-500" />

                        @if ($image)
                            <div class="mt-3 relative inline-block group">
                                <p class="text-xs text-zinc-500 mb-1">Preview:</p>
                                <img src="{{ $image->temporaryUrl() }}" class="h-full w-full rounded-lg object-cover ring-1 ring-zinc-200 dark:ring-zinc-700" />
                                <button type="button" wire:click="removeUploadedImage" class="absolute top-2 -right-2 p-1 bg-red-500 text-white shadow-md cursor-pointer rounded-full" title="Hapus Gambar">
                                    <flux:icon name="x-mark" class="w-4 h-4 cursor-pointer" />
                                </button>
                            </div>
                        @elseif ($existingImagePath && !$removeImage)
                            <div class="mt-3 relative inline-block group">
                                <p class="text-xs text-zinc-500 mb-1">Gambar saat ini:</p>
                                <img src="{{ Storage::url($existingImagePath) }}" class="h-full w-full rounded-lg object-cover ring-1 ring-zinc-200 dark:ring-zinc-700" />
                                <button type="button" wire:click="removeUploadedImage" class="absolute top-2 -right-2 p-1 bg-red-500 text-white shadow-md cursor-pointer rounded-full" title="Hapus Gambar">
                                    <flux:icon name="x-mark" class="w-4 h-4 cursor-pointer" />
                                </button>
                            </div>
                        @endif
                    </flux:field>
                </form>
            </div>

            <footer class="mt-6 flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <button type="button" class="cursor-pointer h-10 px-4 rounded-lg bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 font-medium text-sm transition-colors" wire:click="$set('showModal', false)">Batal</button>
                <button type="submit" form="expenseForm" class="cursor-pointer h-10 px-6 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold text-sm transition-colors" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Perbarui' : 'Simpan' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </footer>
        </div>
    </flux:modal>

    {{-- Detail Modal --}}
    

    {{-- Image Preview Modal --}}
    <flux:modal wire:model="showImageModal" class="max-w-sm md:max-w-2xl p-0 overflow-hidden bg-white dark:bg-zinc-900 rounded-lg shadow-xl">
        <div>
            @if($previewImageUrl)
                <img src="{{ $previewImageUrl }}" alt="Preview Bukti" class="w-full max-h-[70vh] object-contain rounded-sm" />
            @endif
            <div class="mt-4 flex justify-end">
                <button type="button" class="cursor-pointer h-10 px-4 rounded-lg bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 font-medium text-sm transition-colors" wire:click="$set('showImageModal', false)">Tutup</button>
            </div>
        </div>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-sm md:max-w-md p-0 overflow-hidden bg-white dark:bg-zinc-900 rounded-lg shadow-lg transition-all">
        <div>
            <div class="flex flex-col items-center text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4 mx-auto">
                    <flux:icon name="exclamation-triangle" class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Konfirmasi Hapus
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Apakah Anda yakin ingin menghapus <span class="font-medium text-gray-700 dark:text-gray-300">{{ $itemToDeleteName ?: 'pengeluaran ini' }}</span>
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