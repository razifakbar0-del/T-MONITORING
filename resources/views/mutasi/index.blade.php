<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mutasi Rekening
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filter Tanggal --}}
            <div class="bg-white rounded-xl shadow p-5">
                <form method="GET" action="{{ route('mutasi.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tanggal Mulai</label>
                        <input type="date" name="start" value="{{ $startDate }}"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tanggal Selesai</label>
                        <input type="date" name="end" value="{{ $endDate }}"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                    </div>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                        </svg>
                        Tampilkan
                    </button>
                </form>
            </div>

            @if($error)
                <div class="flex items-center gap-3 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-lg">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $error }}</span>
                </div>
            @endif

            @if($accountInfo)
            {{-- Info Rekening --}}
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-xl shadow p-6 text-white">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <p class="text-indigo-200 text-xs font-semibold uppercase tracking-wide mb-1">Informasi Rekening</p>
                        <h3 class="text-xl font-bold">{{ $accountInfo['nama'] ?? '-' }}</h3>
                        <p class="text-indigo-200 text-sm mt-1">
                            {{ $accountInfo['accountNo'] ?? '-' }} &bull; {{ $accountInfo['cabang'] ?? '-' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-indigo-200 text-xs font-semibold uppercase tracking-wide mb-1">Saldo Terakhir</p>
                        <p class="text-2xl font-bold">
                            Rp {{ number_format((float) str_replace(['.', ' '], '', $accountInfo['saldo'] ?? '0'), 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Kartu Summary --}}
            @if($summary)
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-gray-400">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['total_transaksi']) }}</p>
                    <p class="text-sm text-gray-400 mt-1">{{ $startDate }} s/d {{ $endDate }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Kredit</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">Rp {{ number_format($summary['total_kredit'], 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-400 mt-1">Uang masuk</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Debet</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">Rp {{ number_format($summary['total_debet'], 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-400 mt-1">Uang keluar</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 {{ $summary['net'] >= 0 ? 'border-blue-500' : 'border-orange-500' }}">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Net</p>
                    <p class="text-2xl font-bold {{ $summary['net'] >= 0 ? 'text-blue-700' : 'text-orange-700' }} mt-1">
                        Rp {{ number_format(abs($summary['net']), 0, ',', '.') }}
                    </p>
                    <p class="text-sm text-gray-400 mt-1">{{ $summary['net'] >= 0 ? 'Surplus' : 'Defisit' }}</p>
                </div>
            </div>

            {{-- Grafik Mutasi --}}
            @if($mutasiData && $mutasiData->count() > 0)
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Grafik Mutasi Rekening</h3>
                <div class="relative h-64">
                    <canvas id="mutasiChart"></canvas>
                </div>
            </div>
            @endif
            @endif

            {{-- Tabel Mutasi --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Riwayat Mutasi</h3>
                    <span class="text-xs text-gray-400">{{ $mutasiData?->count() ?? 0 }} transaksi ditemukan</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">No Referensi</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Keterangan</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Debet/Kredit</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($mutasiData ?? [] as $item)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $item['no'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $item['tanggal'] }}</td>
                                    <td class="px-4 py-3 text-sm font-mono text-indigo-700">{{ $item['no_reference'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate">{{ $item['keterangan'] }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold whitespace-nowrap
                                        {{ $item['type'] === 'kredit' ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $item['type'] === 'kredit' ? '+' : '' }}Rp {{ number_format($item['debet_kredit'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-700 whitespace-nowrap">
                                        Rp {{ number_format($item['saldo'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">
                                        Tidak ada data mutasi untuk periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Chart.js --}}
    @if($mutasiData && $mutasiData->count() > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        const labels  = {!! json_encode($mutasiData->pluck('tanggal')->toArray()) !!};
        const saldos  = {!! json_encode($mutasiData->pluck('saldo')->toArray()) !!};
        const amounts = {!! json_encode($mutasiData->pluck('debet_kredit')->toArray()) !!};
        const types   = {!! json_encode($mutasiData->pluck('type')->toArray()) !!};

        new Chart(document.getElementById('mutasiChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Saldo (Rp)',
                    data: saldos,
                    borderColor: 'rgba(99, 102, 241, 1)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: true,
                    tension: 0.3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' Rp ' + parseInt(ctx.raw).toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: { callback: v => 'Rp ' + (v/1000000).toFixed(1) + ' Jt' }
                    }
                }
            }
        });
    </script>
    @endif
</x-app-layout>