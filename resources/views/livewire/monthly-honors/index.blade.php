<div>
    <x-slot name="header">
        Rekap Honor Bulanan
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-blue-100 text-sm">Keuangan Guru Multi Lembaga</p>
                    <h1 class="text-2xl lg:text-3xl font-extrabold">Rekap Honor Bulanan</h1>
                    <p class="text-blue-50 mt-2">
                        Rekap honor berdasarkan lembaga, paket honor, transport, tambahan, potongan, dan pembayaran.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button wire:click="generate"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-blue-700 font-bold shadow">
                        <i data-lucide="calculator" class="w-5 h-5"></i>
                        Generate Rekap
                    </button>

                    <button wire:click="exportExcel"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-emerald-500 text-white font-bold shadow">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                        Export Excel
                    </button>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-100 text-emerald-700 p-4 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-8 gap-4">
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Honor Paket</p>
                <h3 class="text-xl font-extrabold">
                    Rp{{ number_format($totalTeachingHonor ?? 0, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Transport</p>
                <h3 class="text-xl font-extrabold">
                    Rp{{ number_format($totalTransport ?? 0, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Tambahan</p>
                <h3 class="text-xl font-extrabold text-emerald-700">
                    Rp{{ number_format($totalAdditionalHonor ?? 0, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Potongan</p>
                <h3 class="text-xl font-black text-red-600">
                    Rp{{ number_format($totalDeduction ?? 0, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Sudah Dibayar</p>
                <h3 class="text-xl font-black text-emerald-700">
                    Rp{{ number_format($totalPaid ?? 0, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Sisa Bayar</p>
                <h3 class="text-xl font-black text-amber-600">
                    Rp{{ number_format($totalRemaining ?? 0, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Jumlah Rekap</p>
                <h3 class="text-xl font-black text-slate-900">
                    {{ $totalTeachers ?? 0 }}
                </h3>
            </div>

            <div class="bg-gradient-to-br from-blue-700 to-sky-500 rounded-3xl p-5 shadow-xl text-white">
                <p class="text-sm text-blue-100">Grand Total</p>
                <h3 class="text-xl font-extrabold">
                    Rp{{ number_format($totalGrand ?? 0, 0, ',', '.') }}
                </h3>
            </div>
        </section>

        <section class="grid lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2 bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="relative">
                    <i data-lucide="search" class="w-5 h-5 absolute left-4 top-3.5 text-slate-400"></i>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari guru atau lembaga..."
                        class="w-full pl-12 pr-4 py-3 rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>
                @if ($institutionId)
                    <section class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                        <a href="{{ route('honor-reports.institution.pdf', [$institutionId, $month, $year]) }}"
                            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-red-50 text-red-700 font-black">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                            Download PDF Rekap Lembaga
                        </a>
                    </section>
                @endif
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <select wire:model.live="institutionId"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Lembaga</option>
                    @foreach ($institutions as $institution)
                        <option value="{{ $institution->id }}">
                            {{ $institution->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <select wire:model.live="month"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}">
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <select wire:model.live="year"
                    class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    @foreach (range(now()->year - 3, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </section>

        <section class="grid gap-4">
            @forelse ($honors as $honor)
                @php
                    $paidTotal = $honor->payments->sum('amount');
                    $remaining = max(($honor->grand_total ?? 0) - $paidTotal, 0);
                @endphp

                <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                        <div>
                            <p class="text-sm text-slate-500">Guru</p>

                            <h3 class="text-xl font-black text-slate-900">
                                {{ $honor->teacher?->name ?? '-' }}
                            </h3>

                            <p class="mt-1 text-xs font-black text-blue-600">
                                {{ $honor->institution?->name ?? 'Tanpa Lembaga' }}
                            </p>

                            <div class="mt-3">
                                @if ($honor->payment_status === 'paid')
                                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-black">
                                        Lunas
                                    </span>
                                @elseif ($honor->payment_status === 'partial')
                                    <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-black">
                                        Dibayar Sebagian
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-black">
                                        Belum Dibayar
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button wire:click="openPaymentModal({{ $honor->id }})"
                                class="px-4 py-2.5 rounded-2xl bg-emerald-50 text-emerald-700 font-black">
                                Bayar
                            </button>

                            @if ($honor->payments->count())
                                <button wire:click="markAsUnpaid({{ $honor->id }})"
                                    wire:confirm="Yakin reset semua riwayat pembayaran honor ini?"
                                    class="px-4 py-2.5 rounded-2xl bg-slate-100 text-slate-700 font-black">
                                    Reset Bayar
                                </button>
                            @endif

                            <a href="{{ route('monthly-honors.pdf', $honor->id) }}"
                                class="px-4 py-2.5 rounded-2xl bg-red-50 text-red-700 font-black">
                                PDF
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3 mt-5">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs text-slate-500">Total JP</p>
                            <p class="font-black text-slate-900">
                                {{ $honor->total_teaching_hours ?? 0 }} JP
                            </p>
                        </div>

                        <div class="rounded-2xl bg-blue-50 p-4">
                            <p class="text-xs text-blue-500">Honor Paket</p>
                            <p class="font-black text-blue-700">
                                Rp{{ number_format($honor->total_teaching_honor ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs text-slate-500">Transport</p>
                            <p class="font-black text-slate-900">
                                Rp{{ number_format($honor->total_transport ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 p-4">
                            <p class="text-xs text-emerald-500">Tambahan</p>
                            <p class="font-black text-emerald-700">
                                Rp{{ number_format($honor->total_additional_honor ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-red-50 p-4">
                            <p class="text-xs text-red-500">Potongan Alpa</p>
                            <p class="font-black text-red-700">
                                Rp{{ number_format($honor->total_deduction ?? 0, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-red-400">
                                {{ $honor->total_absent_hours ?? 0 }} JP
                            </p>
                        </div>

                        <div class="rounded-2xl bg-amber-50 p-4">
                            <p class="text-xs text-amber-600">Sisa Bayar</p>
                            <p class="font-black text-amber-700">
                                Rp{{ number_format($remaining, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-blue-600 p-4 text-white">
                            <p class="text-xs text-blue-100">Total Diterima</p>
                            <p class="font-black">
                                Rp{{ number_format($honor->grand_total ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl bg-emerald-50 p-4">
                        <p class="text-xs text-emerald-500">Sudah Dibayar</p>
                        <p class="font-black text-emerald-700">
                            Rp{{ number_format($paidTotal, 0, ',', '.') }}
                        </p>
                    </div>

                    @if ($honor->payments->count())
                        <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black text-slate-500 mb-3">
                                Riwayat Pembayaran
                            </p>

                            <div class="space-y-2">
                                @foreach ($honor->payments as $payment)
                                    <div class="flex items-center justify-between gap-3 rounded-xl bg-white p-3">
                                        <div>
                                            <p class="font-bold text-slate-800">
                                                {{ \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y') }}
                                            </p>

                                            <p class="text-xs text-slate-500">
                                                {{ strtoupper($payment->payment_method) }}
                                                @if ($payment->reference_number)
                                                    • {{ $payment->reference_number }}
                                                @endif
                                            </p>

                                            @if ($payment->note)
                                                <p class="text-xs text-slate-400 mt-1">
                                                    {{ $payment->note }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="text-right">
                                            <p class="font-black text-emerald-700">
                                                Rp{{ number_format($payment->amount, 0, ',', '.') }}
                                            </p>

                                            <a href="{{ route('honor-payments.receipt', $payment->id) }}"
                                                class="text-xs font-black text-blue-600">
                                                Cetak Bukti
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-3xl p-10 text-center text-slate-500">
                    Belum ada rekap. Klik Generate Rekap.
                </div>
            @endforelse

            {{ $honors->links() }}
        </section>
    </div>

    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-6">
                <h3 class="text-xl font-black mb-1">
                    Bayar Honor Guru
                </h3>

                <p class="text-sm text-slate-500 mb-5">
                    Simpan riwayat pembayaran honor guru.
                </p>

                <form wire:submit="savePayment" class="space-y-4">
                    <div>
                        <label class="text-sm font-bold">Tanggal Bayar</label>
                        <input wire:model="payment_date" type="date"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('payment_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Nominal Bayar</label>
                        <input wire:model="payment_amount" type="number"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @error('payment_amount') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Metode Pembayaran</label>
                        <select wire:model="payment_method"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>
                        @error('payment_method') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold">Nomor Bukti / Referensi</label>
                        <input wire:model="reference_number" type="text"
                            placeholder="Opsional"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="text-sm font-bold">Catatan</label>
                        <textarea wire:model="payment_note" rows="3"
                            class="mt-1 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex gap-3 pt-3">
                        <button type="button" wire:click="$set('showPaymentModal', false)"
                            class="flex-1 px-5 py-3 rounded-2xl bg-slate-100 font-black">
                            Batal
                        </button>

                        <button type="submit"
                            class="flex-1 px-5 py-3 rounded-2xl bg-emerald-600 text-white font-black shadow">
                            Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</div>
