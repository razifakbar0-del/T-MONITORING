<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Summary / Ringkasan Harian
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filter Tanggal --}}
            <div class="bg-white rounded-xl shadow p-5">
                <form method="GET" action="{{ route('summary.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Pilih Tanggal</label>
                        <input type="date" name="date" value="{{ $date }}"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                    </div>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                        </svg>
                        Tampilkan
                    </button>
                    <span class="text-sm text-gray-500 self-center">
                        📅 {{ $selectedDate->translatedFormat('l, d F Y') }}
                    </span>
                </form>
            </div>

            {{-- Kartu Saldo --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-xl shadow p-6 text-white">
                    <p class="text-indigo-200 text-xs font-semibold uppercase tracking-wide mb-1">Saldo Rekening Bank</p>
                    <p class="text-3xl font-bold">
                        @if($saldoRekening !== null)
                            Rp {{ number_format($saldoRekening, 0, ',', '.') }}
                        @else
                            <span class="text-indigo-300 text-xl">Tidak tersedia</span>
                        @endif
                    </p>
                    <p class="text-indigo-200 text-xs mt-2">Real-time dari API Samantara</p>
                </div>
                <div class="bg-gradient-to-r from-emerald-600 to-emerald-800 rounded-xl shadow p-6 text-white">
                    <p class="text-emerald-200 text-xs font-semibold uppercase tracking-wide mb-1">Saldo Supplier Terakhir</p>
                    <p class="text-3xl font-bold">
                        @if($saldoSupplier !== null)
                            Rp {{ number_format($saldoSupplier, 0, ',', '.') }}
                        @else
                            <span class="text-emerald-300 text-xl">Belum ada data</span>
                        @endif
                    </p>
                    <p class="text-emerald-200 text-xs mt-2">Dari data supplier mutations</p>
                </div>
            </div>

            {{-- Kartu Transaksi --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Transaksi</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($totalTrx) }}</p>
                    <p class="text-sm text-gray-400 mt-1">{{ $selectedDate->format('d/m/Y') }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Trx Sukses</p>
                    <p class="text-3xl font-bold text-green-700 mt-1">{{ number_format($trxSukses) }}</p>
                    <p class="text-sm text-gray-400 mt-1">RC = 00</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Trx Gagal</p>
                    <p class="text-3xl font-bold text-red-700 mt-1">{{ number_format($trxGagal) }}</p>
                    <p class="text-sm text-gray-400 mt-1">RC != 00</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-purple-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Success Rate</p>
                    <p class="text-3xl font-bold text-purple-700 mt-1">
                        {{ $totalTrx > 0 ? number_format(($trxSukses / $totalTrx) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>

            {{-- Kartu Keuangan --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Penjualan</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-400 mt-1">Kredit (excl. Deposit/Refund)</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Pembelian</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-400 mt-1">Debet</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 {{ $profit >= 0 ? 'border-blue-500' : 'border-orange-500' }}">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Profit</p>
                    <p class="text-2xl font-bold {{ $profit >= 0 ? 'text-blue-700' : 'text-orange-700' }} mt-1">
                        Rp {{ number_format(abs($profit), 0, ',', '.') }}
                    </p>
                    <p class="text-sm text-gray-400 mt-1">{{ $profit >= 0 ? 'Surplus' : 'Defisit' }} — Penjualan - Pembelian</p>
                </div>
            </div>

            {{-- Grafik Per Jam --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Transaksi Per Jam — {{ $selectedDate->format('d/m/Y') }}</h3>
                @if($grafikJam->count() > 0)
                    <div class="relative h-64">
                        <canvas id="jamChart"></canvas>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-32 text-gray-400">
                        <p class="text-sm">Tidak ada transaksi pada tanggal ini.</p>
                    </div>
                @endif
            </div>

            {{-- Grafik 7 Hari --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Tren 7 Hari Terakhir</h3>
                <div class="relative h-64">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        // Grafik Per Jam
        @if($grafikJam->count() > 0)
        new Chart(document.getElementById('jamChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($grafikJam->pluck('jam')) !!},
                datasets: [
                    {
                        label: 'Jumlah Trx',
                        data: {!! json_encode($grafikJam->pluck('jumlah')) !!},
                        backgroundColor: 'rgba(99, 102, 241, 0.7)',
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Nominal (Rp)',
                        data: {!! json_encode($grafikJam->pluck('nominal')) !!},
                        type: 'line',
                        borderColor: 'rgba(249, 115, 22, 1)',
                        backgroundColor: 'rgba(249, 115, 22, 0.1)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: false,
                        tension: 0.3,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y:  { type: 'linear', position: 'left',  beginAtZero: true, ticks: { callback: v => v + ' trx' } },
                    y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false },
                          ticks: { callback: v => 'Rp ' + (v/1000000).toFixed(1) + ' Jt' } }
                }
            }
        });
        @endif

        // Grafik 7 Hari
        const last7 = {!! json_encode($last7Days) !!};
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: last7.map(d => d.label),
                datasets: [
                    {
                        label: 'Jumlah Trx',
                        data: last7.map(d => d.jumlah),
                        borderColor: 'rgba(99, 102, 241, 1)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 2, pointRadius: 4, fill: true, tension: 0.3,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Nominal (Rp)',
                        data: last7.map(d => d.nominal),
                        borderColor: 'rgba(16, 185, 129, 1)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2, pointRadius: 4, fill: false, tension: 0.3,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y:  { type: 'linear', position: 'left',  beginAtZero: true, ticks: { callback: v => v + ' trx' } },
                    y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false },
                          ticks: { callback: v => 'Rp ' + (v/1000000).toFixed(1) + ' Jt' } }
                }
            }
        });
    </script>
</x-app-layout>