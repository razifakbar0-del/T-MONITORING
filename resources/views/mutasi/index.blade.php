<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mutasi Rekening
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filter --}}
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex flex-col sm:flex-row gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tanggal Mulai</label>
                        <input type="date" id="startDate" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tanggal Selesai</label>
                        <input type="date" id="endDate" value="{{ now()->format('Y-m-d') }}"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                    </div>
                    <button id="btnTampilkan"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                        </svg>
                        Tampilkan
                    </button>
                </div>
            </div>

            {{-- Error --}}
            <div id="errorBox" class="hidden flex items-center gap-3 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-lg">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                </svg>
                <span id="errorMsg"></span>
            </div>

            {{-- Info Rekening --}}
            <div id="accountCard" class="hidden bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-xl shadow p-6 text-white">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <p class="text-indigo-200 text-xs font-semibold uppercase tracking-wide mb-1">Informasi Rekening</p>
                        <h3 class="text-xl font-bold" id="accNama">-</h3>
                        <p class="text-indigo-200 text-sm mt-1"><span id="accNo">-</span> &bull; <span id="accCabang">-</span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-indigo-200 text-xs font-semibold uppercase tracking-wide mb-1">Saldo Terakhir</p>
                        <p class="text-2xl font-bold" id="accSaldo">-</p>
                    </div>
                </div>
            </div>

            {{-- Summary --}}
            <div id="summaryCards" class="hidden grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-gray-400">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1" id="sumTotal">0</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Kredit</p>
                    <p class="text-2xl font-bold text-green-700 mt-1" id="sumKredit">Rp 0</p>
                    <p class="text-sm text-gray-400 mt-1">Uang masuk</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Debet</p>
                    <p class="text-2xl font-bold text-red-700 mt-1" id="sumDebet">Rp 0</p>
                    <p class="text-sm text-gray-400 mt-1">Uang keluar</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Net</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1" id="sumNet">Rp 0</p>
                </div>
            </div>

            {{-- Grafik --}}
            <div id="chartCard" class="hidden bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Grafik Saldo Mutasi</h3>
                <div class="relative h-64">
                    <canvas id="mutasiChart"></canvas>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Riwayat Mutasi</h3>
                    <span class="text-xs text-gray-400" id="jumlahTrx">0 transaksi ditemukan</span>
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
                        <tbody id="mutasiTableBody" class="bg-white divide-y divide-gray-100">
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">
                                    Pilih tanggal dan klik Tampilkan untuk memuat data.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Loading Overlay --}}
            <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm">        <svg class="animate-spin w-14 h-14 text-indigo-600 mb-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <p class="text-indigo-700 font-bold text-lg">Mengambil data mutasi...</p>
        <p class="text-gray-400 text-sm mt-1">Login ke API & memuat data, mohon tunggu</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        let mutasiChart = null;

        function formatRp(val) {
            return 'Rp ' + Math.abs(parseInt(val)).toLocaleString('id-ID');
        }

        document.getElementById('btnTampilkan').addEventListener('click', function () {
            const start = document.getElementById('startDate').value;
            const end   = document.getElementById('endDate').value;

            if (!start || !end) {
                alert('Pilih tanggal mulai dan selesai!');
                return;
            }

            // Tampilkan loading
            document.getElementById('loadingOverlay').classList.remove('hidden');
            document.getElementById('errorBox').classList.add('hidden');
            document.getElementById('accountCard').classList.add('hidden');
            document.getElementById('summaryCards').classList.add('hidden');
            document.getElementById('chartCard').classList.add('hidden');

            fetch(`{{ route('mutasi.fetch') }}?start=${start}&end=${end}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('loadingOverlay').classList.add('hidden');

                if (!data.success) {
                    document.getElementById('errorMsg').textContent = data.error;
                    document.getElementById('errorBox').classList.remove('hidden');
                    return;
                }

                // Info rekening
                if (data.accountInfo) {
                    document.getElementById('accNama').textContent    = data.accountInfo.nama;
                    document.getElementById('accNo').textContent      = data.accountInfo.accountNo;
                    document.getElementById('accCabang').textContent  = data.accountInfo.cabang;
                    document.getElementById('accSaldo').textContent   = formatRp(data.accountInfo.saldo);
                    document.getElementById('accountCard').classList.remove('hidden');
                }

                // Summary
                const s = data.summary;
                document.getElementById('sumTotal').textContent  = s.total_transaksi.toLocaleString('id-ID');
                document.getElementById('sumKredit').textContent = formatRp(s.total_kredit);
                document.getElementById('sumDebet').textContent  = formatRp(s.total_debet);
                document.getElementById('sumNet').textContent    = formatRp(s.net);
                document.getElementById('summaryCards').classList.remove('hidden');

                // Tabel
                const tbody = document.getElementById('mutasiTableBody');
                tbody.innerHTML = '';
                document.getElementById('jumlahTrx').textContent = data.mutasiData.length + ' transaksi ditemukan';

                data.mutasiData.forEach(item => {
                    const isKredit = item.type === 'kredit';
                    const row = `
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-sm text-gray-500">${item.no}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">${item.tanggal}</td>
                            <td class="px-4 py-3 text-sm font-mono text-indigo-700">${item.no_reference}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate">${item.keterangan}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold whitespace-nowrap ${isKredit ? 'text-green-700' : 'text-red-700'}">
                                ${isKredit ? '+' : ''}${formatRp(item.debet_kredit)}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700 whitespace-nowrap">${formatRp(item.saldo)}</td>
                        </tr>`;
                    tbody.insertAdjacentHTML('beforeend', row);
                });

                // Grafik
                const labels = data.mutasiData.map(i => i.tanggal);
                const saldos = data.mutasiData.map(i => i.saldo);

                if (mutasiChart) mutasiChart.destroy();

                document.getElementById('chartCard').classList.remove('hidden');
                mutasiChart = new Chart(document.getElementById('mutasiChart'), {
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
            })
            .catch(err => {
                document.getElementById('loadingOverlay').classList.add('hidden');
                document.getElementById('errorMsg').textContent = 'Terjadi kesalahan: ' + err.message;
                document.getElementById('errorBox').classList.remove('hidden');
            });
        });
    </script>
</x-app-layout>