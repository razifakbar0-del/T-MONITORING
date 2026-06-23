<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Monitoring Transaksi') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ── Flash Messages ── --}}
            @if(session('success'))
                <div class="flex items-center gap-3 p-4 text-sm text-green-800 bg-green-100 border border-green-300 rounded-lg">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-lg">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- ── Kartu Statistik ── --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $countHariIni }}</p>
                    <p class="text-sm text-gray-500 mt-1">Rp {{ number_format($statHariIni, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-orange-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">7 Hari Terakhir</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $countMinggu }}</p>
                    <p class="text-sm text-gray-500 mt-1">Rp {{ number_format($statMinggu, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">30 Hari Terakhir</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $countBulan }}</p>
                    <p class="text-sm text-gray-500 mt-1">Rp {{ number_format($statBulan, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-purple-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Semua</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($statTotal) }}</p>
                    <p class="text-sm text-gray-500 mt-1">transaksi tersimpan</p>
                </div>

            </div>

            {{-- ── Filter + Tombol Aksi ── --}}
            <div class="bg-white rounded-xl shadow p-4">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">

                    {{-- Filter Periode Grafik --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Filter Grafik:</label>
                        <div class="flex rounded-lg overflow-hidden border border-gray-300">
                            @foreach(['harian' => 'Harian', 'mingguan' => 'Mingguan (7 Hari)', 'bulanan' => 'Bulanan (30 Hari)'] as $val => $label)
                                <button type="submit" name="filter" value="{{ $val }}"
                                    class="px-3 py-2 text-sm font-medium transition
                                        {{ ($filter ?? 'harian') === $val
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-white text-gray-600 hover:bg-gray-100' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sinkronisasi + Upload + Export --}}
                    <div class="flex flex-wrap gap-2">

                        {{-- Modal trigger sinkronisasi --}}
                        <button type="button" onclick="document.getElementById('modalSync').classList.remove('hidden')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold rounded-lg shadow transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.253 8H18.2"/>
                            </svg>
                            SINKRONISASI API
                        </button>

                        {{-- Tombol Upload CSV --}}
                        <button type="button" onclick="document.getElementById('modalUploadCsv').classList.remove('hidden')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            UPLOAD CSV
                        </button>

                        <a href="{{ route('transactions.export') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg shadow transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            EXPORT CSV
                        </a>
                    </div>

                </form>
            </div>

            {{-- ── Grafik ── --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Grafik Volume Transaksi</h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            @if(($filter ?? 'harian') === 'harian') Per jam — hari ini
                            @elseif($filter === 'mingguan') Per hari — 7 hari terakhir
                            @else Per hari — 30 hari terakhir
                            @endif
                        </p>
                    </div>
                    <span class="text-xs text-gray-400">{{ now()->format('d M Y') }}</span>
                </div>

                @if(count($labels) > 0)
                    <div class="relative h-72">
                        <canvas id="transactionChart"></canvas>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                        <svg class="w-12 h-12 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="text-sm">Belum ada data untuk periode ini.</p>
                        <p class="text-xs mt-1">Silakan lakukan sinkronisasi API atau upload CSV terlebih dahulu.</p>
                    </div>
                @endif
            </div>

            {{-- ── Tabel Transaksi Terbaru ── --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">10 Transaksi Terbaru</h3>
                    <span class="text-xs text-gray-400">Diurutkan berdasarkan tanggal terbaru</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ID Transaksi (NTB)</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pelanggan / WP</th>
                                <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Nominal</th>
                                <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($transactions as $trx)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3 text-sm font-mono font-medium text-indigo-700">
                                        {{ $trx->trx_id }}
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-700">
                                        {{ $trx->customer_name ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3 text-sm text-right font-semibold text-gray-900">
                                        Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @php
                                            $s = strtolower($trx->status ?? '');
                                            $badge = match(true) {
                                                in_array($s, ['sukses','success','berhasil']) => 'bg-green-100 text-green-800',
                                                in_array($s, ['gagal','failed','error'])      => 'bg-red-100 text-red-800',
                                                default                                       => 'bg-yellow-100 text-yellow-800',
                                            };
                                        @endphp
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                            {{ $trx->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($trx->trx_date)->format('d M Y, H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">
                                        <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        Belum ada data transaksi. Silakan lakukan sinkronisasi API atau upload CSV.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Audit Log ── --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Log Aktivitas Sistem (Audit Log)</h3>
                    <span class="text-xs text-gray-400">Mencatat riwayat manipulasi data secara real-time</span>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($auditLogs as $index => $log)
                                <li>
                                    <div class="relative pb-8">
                                        @if($index !== count($auditLogs) - 1)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                @php
                                                    $act = strtoupper($log->activity);
                                                    $iconBg = match($act) {
                                                        'CREATE' => 'bg-green-500',
                                                        'DELETE' => 'bg-red-500',
                                                        'UPDATE' => 'bg-blue-500',
                                                        default  => 'bg-gray-500'
                                                    };
                                                @endphp
                                                <span class="h-8 w-8 rounded-full {{ $iconBg }} flex items-center justify-center ring-8 ring-white text-white font-bold text-xs">
                                                    {{ substr($act, 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-600">
                                                        <span class="font-bold text-gray-900">{{ $log->user->name ?? 'System/API' }}</span>
                                                        melakukan aksi <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800">{{ $log->activity }}</span>
                                                        pada komponen <span class="font-medium text-indigo-600">{{ $log->model_type }}</span>.
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1 bg-gray-50 p-2 rounded-lg border border-gray-100 font-mono">
                                                        {{ $log->details }}
                                                    </p>
                                                </div>
                                                <div class="text-right text-xs whitespace-nowrap text-gray-400">
                                                    <time datetime="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</time>
                                                    <p class="text-[10px] mt-0.5 text-gray-300">{{ $log->created_at->format('H:i:s') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <div class="text-center py-6 text-sm text-gray-400">
                                    <p>Belum ada rekaman log aktivitas sistem.</p>
                                </div>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ Modal Sinkronisasi ══ --}}
    <div id="modalSync" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
            <div class="bg-orange-500 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-bold text-lg">Sinkronisasi API Samantara</h4>
                <button onclick="document.getElementById('modalSync').classList.add('hidden')"
                    class="text-white hover:text-orange-200 text-2xl leading-none">&times;</button>
            </div>
            <form action="{{ route('api.sync') }}" method="GET" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start"
                        value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orange-400 focus:border-orange-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
                    <input type="date" name="end"
                        value="{{ now()->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orange-400 focus:border-orange-400">
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700">
                    <strong>Endpoint:</strong><br>
                    <code class="break-all">https://mpn-gateway.samantara.com/mpnbjt/api/mutasi?start=...&end=...</code>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold rounded-lg transition">
                        Mulai Sinkronisasi
                    </button>
                    <button type="button"
                        onclick="document.getElementById('modalSync').classList.add('hidden')"
                        class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ Modal Upload CSV ══ --}}
    <div id="modalUploadCsv" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
            <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-bold text-lg">Upload File CSV</h4>
                <button onclick="document.getElementById('modalUploadCsv').classList.add('hidden')"
                    class="text-white hover:text-blue-200 text-2xl leading-none">&times;</button>
            </div>
            <form action="{{ route('transactions.upload-csv') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih File CSV</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition cursor-pointer"
                        onclick="document.getElementById('csvFileInput').click()">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <p class="text-sm text-gray-500" id="csvFileName">Klik untuk pilih file CSV</p>
                        <p class="text-xs text-gray-400 mt-1">Format: CSV dengan separator titik koma (;)</p>
                    </div>
                    <input type="file" id="csvFileInput" name="csv_file" accept=".csv,.txt" class="hidden"
                        onchange="document.getElementById('csvFileName').textContent = this.files[0]?.name || 'Klik untuk pilih file CSV'">
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-xs text-yellow-700">
                    <strong>⚠️ Perhatian:</strong> Data yang sudah ada (berdasarkan ID) akan dilewati otomatis, tidak akan duplikat.
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition">
                        Upload & Import
                    </button>
                    <button type="button"
                        onclick="document.getElementById('modalUploadCsv').classList.add('hidden')"
                        class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ Chart.js ══ --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        @if(count($labels) > 0)
        const chartLabels = {!! json_encode($labels) !!};
        const chartValues = {!! json_encode($values) !!};
        const chartCounts = {!! json_encode($counts) !!};

        const ctx = document.getElementById('transactionChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Nominal Transaksi (Rp)',
                        data: chartValues,
                        backgroundColor: 'rgba(79, 70, 229, 0.7)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Jumlah Transaksi',
                        data: chartCounts,
                        type: 'line',
                        borderColor: 'rgba(249, 115, 22, 1)',
                        backgroundColor: 'rgba(249, 115, 22, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        fill: false,
                        tension: 0.3,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label(ctx) {
                                if (ctx.datasetIndex === 0) {
                                    return ' Rp ' + parseInt(ctx.raw).toLocaleString('id-ID');
                                }
                                return ' ' + ctx.raw + ' transaksi';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: v => 'Rp ' + (v / 1000000).toFixed(1) + ' Jt'
                        }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: { callback: v => v + ' trx' }
                    }
                }
            }
        });
        @endif
    </script>
</x-app-layout>