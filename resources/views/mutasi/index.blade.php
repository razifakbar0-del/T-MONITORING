<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mutasi Rekening</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filter --}}
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex flex-wrap gap-4 items-end">
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
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Keterangan</label>
                        <input type="text" id="filterKet" placeholder="Cari keterangan..."
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tipe</label>
                        <select id="filterTipe" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                            <option value="">Semua</option>
                            <option value="kredit">Kredit (Masuk)</option>
                            <option value="debet">Debet (Keluar)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Per Halaman</label>
                        <select id="perPage" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                            <option value="10">10</option>
                            <option value="15" selected>15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <button id="btnTampilkan"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                        </svg>
                        Tampilkan
                    </button>
                    <button id="btnReset"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-lg transition">
                        Reset
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

            {{-- Summary Cards --}}
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
                    <span class="text-xs text-gray-400" id="infoTrx">0 transaksi ditemukan</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">No Referensi</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Keterangan</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-red-500 uppercase">Debit</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-green-500 uppercase">Kredit</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Saldo</th>
                            </tr>
                        </thead>
                        <tbody id="mutasiTableBody" class="bg-white divide-y divide-gray-100">
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-400">
                                    Pilih tanggal dan klik Tampilkan untuk memuat data.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div id="paginationWrap" class="hidden px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-4">
                    <span class="text-xs text-gray-500" id="pageInfo"></span>
                    <div id="pageButtons" class="flex gap-1 flex-wrap"></div>
                </div>
            </div>

        </div>
    </div>

    {{-- Loading Overlay --}}
    <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm">
        <svg class="animate-spin w-14 h-14 text-indigo-600 mb-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <p class="text-indigo-700 font-bold text-lg">Mengambil data mutasi...</p>
        <p class="text-gray-400 text-sm mt-1">Memuat data dari database, mohon tunggu</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        let mutasiChart = null;
        let allData     = [];
        let currentPage = 1;

        function formatRp(val) {
            return 'Rp ' + Math.abs(parseInt(val)).toLocaleString('id-ID');
        }

        function getFiltered() {
            const ket  = document.getElementById('filterKet').value.toLowerCase();
            const tipe = document.getElementById('filterTipe').value;
            return allData.filter(item => {
                const matchKet  = !ket  || item.keterangan.toLowerCase().includes(ket);
                const matchTipe = !tipe || item.type === tipe;
                return matchKet && matchTipe;
            });
        }

        function renderTable(page) {
            currentPage     = page;
            const perPage   = parseInt(document.getElementById('perPage').value);
            const filtered  = getFiltered();
            const total     = filtered.length;
            const totalPage = Math.ceil(total / perPage);
            const start     = (page - 1) * perPage;
            const end       = Math.min(start + perPage, total);
            const pageData  = filtered.slice(start, end);

            document.getElementById('infoTrx').textContent =
                `Menampilkan ${start + 1}–${end} dari ${total.toLocaleString('id-ID')} transaksi`;
            document.getElementById('pageInfo').textContent =
                `Halaman ${page} dari ${totalPage}`;

            const tbody = document.getElementById('mutasiTableBody');
            tbody.innerHTML = '';

            if (pageData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="px-5 py-10 text-center text-sm text-gray-400">Tidak ada data untuk filter yang dipilih.</td></tr>`;
            } else {
                pageData.forEach((item, idx) => {
                    const isKredit = item.type === 'kredit';
                    const debitCol  = !isKredit
                        ? `<span class="font-semibold text-red-700">${formatRp(item.debet_kredit)}</span>`
                        : `<span class="text-gray-300">-</span>`;
                    const kreditCol = isKredit
                        ? `<span class="font-semibold text-green-700">${formatRp(item.debet_kredit)}</span>`
                        : `<span class="text-gray-300">-</span>`;

                    tbody.insertAdjacentHTML('beforeend', `
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-sm text-gray-500">${start + idx + 1}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">${item.tanggal}</td>
                            <td class="px-4 py-3 text-sm font-mono text-indigo-700">${item.no_reference}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate">${item.keterangan}</td>
                            <td class="px-4 py-3 text-sm text-right whitespace-nowrap">${debitCol}</td>
                            <td class="px-4 py-3 text-sm text-right whitespace-nowrap">${kreditCol}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700 whitespace-nowrap">${formatRp(item.saldo)}</td>
                        </tr>`);
                });
            }

            // Pagination buttons
            const btnWrap = document.getElementById('pageButtons');
            btnWrap.innerHTML = '';
            document.getElementById('paginationWrap').classList.toggle('hidden', totalPage <= 1);

            const makeBtn = (label, pg, disabled = false, active = false) => {
                const btn = document.createElement('button');
                btn.textContent = label;
                btn.className = `px-3 py-1 text-sm rounded-lg border transition ${
                    active   ? 'bg-indigo-600 text-white border-indigo-600' :
                    disabled ? 'text-gray-300 border-gray-200 cursor-not-allowed' :
                               'text-gray-600 border-gray-300 hover:bg-gray-100'
                }`;
                if (!disabled && !active) btn.onclick = () => renderTable(pg);
                btnWrap.appendChild(btn);
            };

            makeBtn('‹', page - 1, page === 1);
            const range = [];
            for (let i = 1; i <= totalPage; i++) {
                if (i === 1 || i === totalPage || (i >= page - 2 && i <= page + 2)) range.push(i);
                else if (range[range.length - 1] !== '...') range.push('...');
            }
            range.forEach(p => {
                if (p === '...') makeBtn('...', null, true);
                else makeBtn(p, p, false, p === page);
            });
            makeBtn('›', page + 1, page === totalPage);
        }

        document.getElementById('btnTampilkan').addEventListener('click', function () {
            const start = document.getElementById('startDate').value;
            const end   = document.getElementById('endDate').value;
            if (!start || !end) { alert('Pilih tanggal mulai dan selesai!'); return; }

            document.getElementById('loadingOverlay').classList.remove('hidden');
            document.getElementById('errorBox').classList.add('hidden');
            document.getElementById('accountCard').classList.add('hidden');
            document.getElementById('summaryCards').classList.add('hidden');
            document.getElementById('chartCard').classList.add('hidden');
            document.getElementById('paginationWrap').classList.add('hidden');

            fetch(`{{ route('mutasi.fetch') }}?start=${start}&end=${end}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('loadingOverlay').classList.add('hidden');

                if (!data.success) {
                    document.getElementById('errorMsg').textContent = data.error;
                    document.getElementById('errorBox').classList.remove('hidden');
                    return;
                }

                allData = data.mutasiData;

                if (data.accountInfo) {
                    document.getElementById('accNama').textContent   = data.accountInfo.nama;
                    document.getElementById('accNo').textContent     = data.accountInfo.accountNo;
                    document.getElementById('accCabang').textContent = data.accountInfo.cabang;
                    document.getElementById('accSaldo').textContent  = formatRp(data.accountInfo.saldo);
                    document.getElementById('accountCard').classList.remove('hidden');
                }

                const s = data.summary;
                document.getElementById('sumTotal').textContent  = s.total_transaksi.toLocaleString('id-ID');
                document.getElementById('sumKredit').textContent = formatRp(s.total_kredit);
                document.getElementById('sumDebet').textContent  = formatRp(s.total_debet);
                document.getElementById('sumNet').textContent    = formatRp(s.net);
                document.getElementById('summaryCards').classList.remove('hidden');

                renderTable(1);

                // Grafik per hari
                const perHari = {};
                allData.forEach(i => {
                    const tgl = i.tanggal.split(' ')[0];
                    perHari[tgl] = i.saldo;
                });
                const labels = Object.keys(perHari);
                const saldos = Object.values(perHari);

                if (mutasiChart) mutasiChart.destroy();
                document.getElementById('chartCard').classList.remove('hidden');
                mutasiChart = new Chart(document.getElementById('mutasiChart'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Saldo (Rp)',
                            data: saldos,
                            borderColor: 'rgba(99,102,241,1)',
                            backgroundColor: 'rgba(99,102,241,0.1)',
                            borderWidth: 2,
                            pointRadius: 2,
                            fill: true,
                            tension: 0.3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: { callbacks: { label: ctx => ' Rp ' + parseInt(ctx.raw).toLocaleString('id-ID') } }
                        },
                        scales: {
                            y: { beginAtZero: false, ticks: { callback: v => 'Rp ' + (v/1000000).toFixed(1) + ' Jt' } }
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

        document.getElementById('filterKet').addEventListener('input', () => renderTable(1));
        document.getElementById('filterTipe').addEventListener('change', () => renderTable(1));
        document.getElementById('perPage').addEventListener('change', () => renderTable(1));

        document.getElementById('btnReset').addEventListener('click', () => {
            document.getElementById('filterKet').value  = '';
            document.getElementById('filterTipe').value = '';
            document.getElementById('perPage').value    = '15';
            if (allData.length) renderTable(1);
        });
    </script>
</x-app-layout>