@props([
    'grandTotal',
    'paymentMethod',
    'cashReceived',
    'changeAmount',
    'qrisNotConfigured',
    'enableVirtualKeypad'
])

<flux:modal wire:model="showPaymentModal" {{ $attributes->merge(['class' => 'max-w-sm md:max-w-2xl p-2 overflow-hidden bg-white dark:bg-zinc-900 rounded-md w-full']) }}>
    <div class="p-4 md:p-6 space-y-6 overflow-y-auto max-h-[85vh] md:max-h-[93vh]">
        <header class="flex justify-between items-end border-b border-zinc-100 dark:border-zinc-800 pb-5">
            <div>
                <h2 class="text-xl font-black uppercase tracking-tighter text-zinc-900 dark:text-white leading-none">Checkout</h2>
                <p class="text-[9px] font-bold text-zinc-400 uppercase tracking-widest mt-1">Konfirmasi Pembayaran</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black tracking-[0.2em] text-zinc-400 uppercase mb-1">Total Bayar</p>
                <p class="text-xl mdtext-3xl font-black text-green-600 tracking-wide leading-none">Rp{{ number_format($grandTotal, 0, ',', '.') }}</p>
            </div>
        </header>

        <div class="flex flex-col md:flex-row gap-6">
            {{-- Left side: Payment Method & Input --}}
            <div class="flex-1 flex flex-col space-y-6">
                {{-- Method Selection --}}
                <div class="grid grid-cols-2 gap-4 shrink-0">
                    @foreach(['cash' => 'Tunai', 'qris' => 'QRIS'] as $val => $label)
                        <label class="relative flex flex-col items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all {{ $paymentMethod === $val ? 'border-green-600 bg-green-50 dark:bg-green-950/20 shadow-sm' : 'border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 hover:bg-zinc-100' }}">
                            <input type="radio" wire:model.live="paymentMethod" value="{{ $val }}" class="sr-only">
                            <div class="h-8 w-8 rounded-xl flex items-center justify-center mb-2 {{ $paymentMethod === $val ? 'bg-green-600 text-white' : 'bg-white dark:bg-zinc-800 text-zinc-400' }}">
                                <flux:icon name="{{ $val === 'cash' ? 'banknotes' : 'qr-code' }}" class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-center {{ $paymentMethod === $val ? 'text-green-700 dark:text-green-400' : 'text-zinc-400' }}">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                {{-- Context Action Area --}}
                <div class="bg-zinc-50 dark:bg-zinc-950 p-3 rounded-lg border border-zinc-100 dark:border-zinc-800 min-h-[160px] flex flex-col justify-center flex-1">
                    @if($paymentMethod === 'cash')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-1">
                                <label class="text-[10px] font-black uppercase tracking-widest text-zinc-500">Uang Diterima</label>
                                <button type="button" wire:click="$set('cashReceived', {{ $grandTotal }})" class="text-[10px] bg-green-100 text-green-700 px-2 py-1 rounded-md font-black hover:bg-green-200 transition-colors cursor-pointer">Uang Pas</button>
                            </div>
                            
                            <div class="relative">
                                <input 
                                    type="number" 
                                    wire:model.live="cashReceived"
                                    class="w-full h-20 text-4xl font-black text-right pr-4 tracking-wide bg-white dark:bg-zinc-900 border-2 border-zinc-200 dark:border-zinc-700 rounded-lg focus:border-green-500 outline-none text-zinc-900 dark:text-white transition-all caret-green-600"
                                    placeholder="0"
                                    {{ $enableVirtualKeypad ? 'readonly' : '' }}
                                    autofocus
                                />
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400 font-bold text-lg select-none pointer-events-none">Rp</div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between items-center px-1 opacity-90">
                                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Total Format</span>
                                    <span class="text-sm font-bold text-zinc-900 dark:text-white">
                                        Rp {{ number_format((float)$cashReceived, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center px-1 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                                    <span class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Kembalian</span>
                                    <span class="text-lg font-black {{ $changeAmount > 0 ? 'text-emerald-500' : ($cashReceived >= $grandTotal ? 'text-zinc-400' : 'text-red-500') }}">
                                        Rp{{ number_format($changeAmount, 0, ',', '.') }}
                                    </span>
                                </div>
                                @if($cashReceived < $grandTotal && $cashReceived > 0)
                                    <p class="text-xs md:text-sm font-bold text-red-500 tracking-wide text-right px-1">Kurang Rp{{ number_format($grandTotal - $cashReceived, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- QRIS Payment Panel --}}
                        @if($qrisNotConfigured)
                            {{-- External QRIS flow (Not configured in system) --}}
                            <div class="flex flex-col items-center justify-center text-center h-full gap-4">
                                <div class="w-16 h-16 bg-blue-100 dark:bg-blue-950/40 border-2 border-blue-200 dark:border-blue-800 flex items-center justify-center rounded-2xl">
                                    <flux:icon name="qr-code" class="h-8 w-8 text-blue-500" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-zinc-700 dark:text-zinc-300 mb-1">QRIS Eksternal</p>
                                    <p class="text-[10px] font-medium text-zinc-400 uppercase tracking-wide leading-loose">
                                        Toko menggunakan QRIS Eksternal.<br>
                                        Pelanggan scan QR dari EDC atau QR Cetak.
                                    </p>
                                </div>
                                <div class="px-3 py-2 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                                    <p class="text-[12px] font-black text-blue-700 dark:text-blue-400 uppercase tracking-widest">Total Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @else
                            {{-- QRIS ready --}}
                            <div class="flex flex-col items-center justify-center text-center h-full gap-4">
                                <div class="w-20 h-20 bg-green-50 dark:bg-green-950/30 border-2 border-green-200 dark:border-green-800 flex items-center justify-center rounded-2xl">
                                    <flux:icon name="qr-code" class="h-10 w-10 text-green-600 dark:text-green-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-zinc-700 dark:text-zinc-300 mb-1">QRIS Siap Digunakan</p>
                                    <p class="text-[10px] font-medium text-zinc-400 uppercase tracking-wide leading-loose">
                                        QR code dinamis akan digenerate<br>
                                        setelah konfirmasi pembayaran
                                    </p>
                                </div>
                                <div class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 rounded-full">
                                    <p class="text-[9px] font-black text-green-700 dark:text-green-400 uppercase tracking-widest">Total: Rp{{ number_format($grandTotal, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Right side: Numeric Keypad (Cash only) --}}
            @if($enableVirtualKeypad && $paymentMethod === 'cash')
                <div class="md:w-64 shrink-0 flex flex-col justify-end">
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['1', '2', '3', '4', '5', '6', '7', '8', '9', '000', '00', '0'] as $key)
                            <button type="button" wire:click="appendKeypad('{{ $key }}')" class="cursor-pointer h-11 md:h-14 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-900 dark:text-white rounded-xl font-bold text-md md:text-xl transition-colors select-none active:scale-95 shadow-sm">
                                {{ $key }}
                            </button>
                        @endforeach
                        <button type="button" wire:click="removeKeypad" class="cursor-pointer col-span-3 h-14 bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/20 dark:hover:bg-red-900/40 dark:text-red-400 rounded-xl flex items-center justify-center transition-colors active:scale-95 shadow-sm">
                            <flux:icon name="backspace" class="h-6 w-6" />
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
            <button type="button" wire:click="$set('showPaymentModal', false)" class="cursor-pointer flex-1 h-14 rounded-xl border border-zinc-200 dark:border-zinc-700 text-[11px] font-black uppercase text-zinc-500 hover:bg-zinc-50 transition-colors">Batal</button>
            <button 
                type="button" 
                wire:click="processPayment" 
                @if($paymentMethod === 'cash' && $cashReceived < $grandTotal) disabled @endif
                class="cursor-pointer flex-2 h-14 rounded-xl bg-green-600 hover:bg-green-700 text-white font-black uppercase text-[12px] tracking-[0.2em] shadow-lg shadow-green-600/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 group transition-all"
            >
                Konfirmasi Bayar
            </button>
        </div>
    </div>
</flux:modal>
