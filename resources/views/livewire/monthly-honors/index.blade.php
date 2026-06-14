<div>
    <x-slot name="header">
        Rekap Honor Bulanan
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 p-6 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-blue-100 text-sm">Keuangan Guru</p>
                    <h1 class="text-2xl lg:text-3xl font-extrabold">Rekap Honor Bulanan</h1>
                    <p class="text-blue-50 mt-2">Honor JP + transport tepat waktu terhitung otomatis.</p>
                </div>

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
        </section>

        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Total Honor Mengajar</p>
                <h3 class="text-2xl font-extrabold">
                    Rp{{ number_format($totalTeachingHonor, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Total Transport</p>
                <h3 class="text-2xl font-extrabold">
                    Rp{{ number_format($totalTransport, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Grand Total</p>
                <h3 class="text-2xl font-extrabold text-blue-700">
                    Rp{{ number_format($totalGrand, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <p class="text-sm text-slate-500">Potongan Alpa</p>
                <h3 class="text-2xl font-black text-red-600">
                    Rp{{ number_format($totalDeduction ?? 0, 0, ',', '.') }}
                </h3>
            </div>
        </section>

        <section class="grid lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2 bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="relative">
                    <i data-lucide="search" class="w-5 h-5 absolute left-4 top-3.5 text-slate-400"></i>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari nama guru..."
                        class="w-full pl-12 pr-4 py-3 rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>
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

        <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 text-left text-sm text-slate-500">
                    <tr>
                        <th class="p-5">Guru</th>
                        <th class="p-5">Total JP</th>
                        <th class="p-5">Honor Mengajar</th>
                        <th class="p-5">Transport</th>
                        <th class="p-5">Grand Total</th>
                        <th class="p-5">Status</th>
                        <th class="p-5 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($honors as $honor)
                        <tr>
                            <td class="p-5 font-bold">{{ $honor->teacher->name }}</td>
                            <td class="p-5">{{ $honor->total_teaching_hours }} JP</td>
                            <td class="p-5">Rp{{ number_format($honor->total_teaching_honor, 0, ',', '.') }}</td>
                            <td class="p-5">Rp{{ number_format($honor->total_transport, 0, ',', '.') }}</td>
                            <td class="p-5 font-extrabold text-blue-700">
                                Rp{{ number_format($honor->grand_total, 0, ',', '.') }}
                            </td>
                            <div class="rounded-2xl bg-red-50 p-4">
                                <p class="text-xs text-red-500">Potongan Alpa</p>
                                <p class="font-black text-red-700">
                                    Rp{{ number_format($honor->total_deduction ?? 0, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-red-400">
                                    {{ $honor->total_absent_hours ?? 0 }} JP tidak hadir
                                </p>
                            </div>
                            <td class="p-5">
                                @if ($honor->payment_status === 'paid')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                        Sudah Dibayar
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                        Belum Dibayar
                                    </span>
                                @endif
                            </td>
                            <td class="p-5 text-right">
                                @if ($honor->payment_status === 'paid')
                                    <button wire:click="markAsUnpaid({{ $honor->id }})"
                                        class="px-4 py-2 rounded-xl bg-slate-100 font-bold">
                                        Batalkan
                                    </button>
                                @else
                                    <button wire:click="markAsPaid({{ $honor->id }})"
                                        class="px-4 py-2 rounded-xl bg-emerald-100 text-emerald-700 font-bold">
                                        Tandai Dibayar
                                    </button>
                                @endif
                                <a href="{{ route('monthly-honors.pdf', $honor->id) }}"
                                        class="px-4 py-2 rounded-xl bg-red-100 text-red-700 font-bold">
                                        PDF
                                    </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">
                                Belum ada rekap. Klik Generate Rekap.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        {{ $honors->links() }}
    </div>
</div>
