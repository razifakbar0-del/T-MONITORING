<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Transaksi</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

            @if(session('success'))
                <div class="flex items-center gap-3 p-4 text-sm text-green-800 bg-green-100 border border-green-300 rounded-xl">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-xl">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Summary Cards --}}
            <div id="summaryCards" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-gray-400">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1" id="sumTotal">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Sukses</p>
                    <p class="text-2xl font-bold text-green-700 mt-1" id="sumSukses">{{ number_format($summary['total_sukses']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Gagal</p>
                    <p class="text-2xl font-bold text-red-700 mt-1" id="sumGagal">{{ number_format($summary['total_gagal']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-indigo-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Kredit</p>
                    <p class="text-xl font-bold text-indigo-700 mt-1" id="sumAmount">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Filter --}}
            <div class="bg-white rounded-xl shadow p-5">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tgl Mulai</label>
                        <input type="date" id="f_start" value="{{ request('start', now()->startOfMonth()->format('Y-m-d')) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tgl Selesai</label>
                        <input type="date" id="f_end" value="{{ request('end', now()->format('Y-m-d')) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">MSISDN</label>
                        <input type="text" id="f_msisdn" value="{{ request('msisdn') }}" placeholder="08xx..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Reseller / ID</label>
                        <input type="text" id="f_reseller" value="{{ request('reseller') }}" placeholder="Nama / request ID..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Kode Produk</label>
                        <input type="text" id="f_product" value="{{ request('product_code') }}" placeholder="ex: TP5..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Status</label>
                        <select id="f_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                            <option value="">Semua</option>
                            <option value="sukses" {{ request('status') === 'sukses' ? 'selected' : '' }}>Sukses</option>
                            <option value="gagal"  {{ request('status') === 'gagal'  ? 'selected' : '' }}>Gagal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Per Halaman</label>
                        <select id="f_perpage" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                            <option value="10"  {{ request('per_page', 15) == 10  ? 'selected' : '' }}>10</option>
                            <option value="15"  {{ request('per_page', 15) == 15  ? 'selected' : '' }}>15</option>
                            <option value="25"  {{ request('per_page', 15) == 25  ? 'selected' : '' }}>25</option>
                            <option value="50"  {{ request('per_page', 15) == 50  ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-4">
                    <button id="btnFilter"
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
                    <button id="btnExport"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg shadow transition ml-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Riwayat Transaksi</h3>
                    <span class="text-xs text-gray-400" id="tableInfo">
                        Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari {{ number_format($transactions->total()) }} transaksi
                    </span>
                </div>

                <div id="tableLoading" class="hidden py-12 text-center">
                    <svg class="animate-spin w-8 h-8 text-indigo-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <p class="text-sm text-gray-400">Memuat data...</p>
                </div>

                <div id="tableWrap" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">ID</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">MSISDN</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">Jam</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">Code</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">Reseller ID</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">Supplier</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">Keterangan</th>
                                <th class="px-3 py-3 text-right text-xs font-bold text-gray-500 uppercase">Debit</th>
                                <th class="px-3 py-3 text-right text-xs font-bold text-green-500 uppercase">Kredit</th>
                                <th class="px-3 py-3 text-right text-xs font-bold text-gray-500 uppercase">Total</th>
                                <th class="px-3 py-3 text-right text-xs font-bold text-indigo-500 uppercase">Profit</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white divide-y divide-gray-100">
                            @forelse($transactions as $trx)
                                @php
                                    $dt      = \Carbon\Carbon::parse($trx->trx_date);
                                    $isDebit = ($trx->debit ?? 0) > 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition cursor-pointer" onclick="window.location='{{ route('transactions.show', $trx->id) }}'">
                                    <td class="px-3 py-2 font-mono text-indigo-600 text-xs">{{ $trx->trx_id }}</td>
                                    <td class="px-3 py-2 text-gray-800 font-medium">{{ $trx->msisdn ?? '-' }}</td>
                                    <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ $dt->format('d M Y') }}</td>
                                    <td class="px-3 py-2 text-gray-500 whitespace-nowrap">{{ $dt->format('H:i:s') }}</td>
                                    <td class="px-3 py-2">
                                        @if($trx->product_code)
                                            <span class="inline-flex px-2 py-0.5 text-xs font-bold rounded-md bg-indigo-100 text-indigo-700">{{ $trx->product_code }}</span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="font-semibold text-gray-800">{{ $trx->reseller_name ?? '-' }}</div>
                                        @if($trx->request_id)
                                            <div class="text-xs text-gray-400">{{ $trx->request_id }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-gray-500">{{ $trx->supplier ?? '-' }}</td>
                                    <td class="px-3 py-2 text-gray-600 max-w-xs truncate">{{ $trx->customer_name ?? '-' }}</td>
                                    <td class="px-3 py-2 text-right text-red-600 font-semibold whitespace-nowrap">
                                        {{ ($trx->debit ?? 0) > 0 ? number_format($trx->debit, 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-green-600 font-semibold whitespace-nowrap">
                                        {{ ($trx->credit ?? 0) > 0 ? number_format($trx->credit, 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-gray-800 font-semibold whitespace-nowrap">
                                        {{ number_format($trx->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-indigo-600 font-semibold whitespace-nowrap">
                                        {{ ($trx->profit ?? 0) > 0 ? number_format($trx->profit, 0, ',', '.') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="px-5 py-12 text-center text-sm text-gray-400">
                                        Tidak ada data transaksi untuk filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div id="paginationWrap" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-4 {{ $transactions->hasPages() ? '' : 'hidden' }}">
                    <span class="text-xs text-gray-500" id="pageInfo">
                        Halaman {{ $transactions->currentPage() }} dari {{ $transactions->lastPage() }}
                    </span>
                    <div id="pageButtons" class="flex gap-1 flex-wrap">
                        @php $current = $transactions->currentPage(); $last = $transactions->lastPage(); @endphp
                        @for($p = 1; $p <= $last; $p++)
                            @if($p === 1 || $p === $last || ($p >= $current - 2 && $p <= $current + 2))
                                <button onclick="loadPage({{ $p }})"
                                    class="px-3 py-1 text-sm rounded-lg border transition {{ $p === $current ? 'bg-indigo-600 text-white border-indigo-600' : 'text-gray-600 border-gray-300 hover:bg-gray-100' }}">
                                    {{ $p }}
                                </button>
                            @elseif($p === $current - 3 || $p === $current + 3)
                                <span class="px-2 py-1 text-gray-400">...</span>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function getParams() {
            return {
                start:        document.getElementById('f_start').value,
                end:          document.getElementById('f_end').value,
                msisdn:       document.getElementById('f_msisdn').value,
                reseller:     document.getElementById('f_reseller').value,
                product_code: document.getElementById('f_product').value,
                status:       document.getElementById('f_status').value,
                per_page:     document.getElementById('f_perpage').value,
            };
        }

        function codeBadge(code) {
            if (!code) return '<span class="text-gray-300">-</span>';
            return `<span class="inline-flex px-2 py-0.5 text-xs font-bold rounded-md bg-indigo-100 text-indigo-700">${code}</span>`;
        }

        function fmt(val) {
            return val > 0 ? parseInt(val).toLocaleString('id-ID') : '—';
        }

        function loadPage(page) {
            const params = { ...getParams(), page };
            document.getElementById('tableLoading').classList.remove('hidden');
            document.getElementById('tableWrap').classList.add('opacity-40', 'pointer-events-none');

            fetch(`{{ route('transactions.index') }}?${new URLSearchParams(params)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('tableLoading').classList.add('hidden');
                document.getElementById('tableWrap').classList.remove('opacity-40', 'pointer-events-none');

                document.getElementById('sumTotal').textContent  = data.summary.total.toLocaleString('id-ID');
                document.getElementById('sumSukses').textContent = data.summary.total_sukses.toLocaleString('id-ID');
                document.getElementById('sumGagal').textContent  = data.summary.total_gagal.toLocaleString('id-ID');
                document.getElementById('sumAmount').textContent = 'Rp ' + parseInt(data.summary.total_amount).toLocaleString('id-ID');

                const tbody = document.getElementById('tableBody');
                tbody.innerHTML = '';
                if (!data.data.length) {
                    tbody.innerHTML = `<tr><td colspan="12" class="px-5 py-12 text-center text-sm text-gray-400">Tidak ada data.</td></tr>`;
                } else {
                    data.data.forEach(t => {
                        tbody.insertAdjacentHTML('beforeend', `
                            <tr class="hover:bg-gray-50 transition cursor-pointer" onclick="window.location='/transactions/${t.id}'">
                                <td class="px-3 py-2 font-mono text-indigo-600 text-xs">${t.trx_id}</td>
                                <td class="px-3 py-2 text-gray-800 font-medium">${t.msisdn ?? '-'}</td>
                                <td class="px-3 py-2 text-gray-600 whitespace-nowrap">${t.trx_date}</td>
                                <td class="px-3 py-2 text-gray-500 whitespace-nowrap">${t.trx_time}</td>
                                <td class="px-3 py-2">${codeBadge(t.product_code)}</td>
                                <td class="px-3 py-2">
                                    <div class="font-semibold text-gray-800">${t.reseller_name ?? '-'}</div>
                                    ${t.request_id ? `<div class="text-xs text-gray-400">${t.request_id}</div>` : ''}
                                </td>
                                <td class="px-3 py-2 text-gray-500">${t.supplier ?? '-'}</td>
                                <td class="px-3 py-2 text-gray-600 max-w-xs truncate">${t.customer_name ?? '-'}</td>
                                <td class="px-3 py-2 text-right text-red-600 font-semibold whitespace-nowrap">${fmt(t.debit)}</td>
                                <td class="px-3 py-2 text-right text-green-600 font-semibold whitespace-nowrap">${fmt(t.credit)}</td>
                                <td class="px-3 py-2 text-right text-gray-800 font-semibold whitespace-nowrap">${parseInt(t.amount).toLocaleString('id-ID')}</td>
                                <td class="px-3 py-2 text-right text-indigo-600 font-semibold whitespace-nowrap">${fmt(t.profit)}</td>
                            </tr>`);
                    });
                }

                document.getElementById('tableInfo').textContent = `Menampilkan ${data.from}–${data.to} dari ${data.total.toLocaleString('id-ID')} transaksi`;
                document.getElementById('pageInfo').textContent  = `Halaman ${data.current_page} dari ${data.last_page}`;

                const btnWrap = document.getElementById('pageButtons');
                btnWrap.innerHTML = '';
                document.getElementById('paginationWrap').classList.toggle('hidden', data.last_page <= 1);

                const cur = data.current_page, last = data.last_page;
                const pages = [];
                for (let i = 1; i <= last; i++) {
                    if (i === 1 || i === last || (i >= cur - 2 && i <= cur + 2)) pages.push(i);
                    else if (pages[pages.length-1] !== '...') pages.push('...');
                }
                pages.forEach(p => {
                    if (p === '...') { btnWrap.insertAdjacentHTML('beforeend', `<span class="px-2 py-1 text-gray-400">...</span>`); return; }
                    const btn = document.createElement('button');
                    btn.textContent = p;
                    btn.className = `px-3 py-1 text-sm rounded-lg border transition ${p === cur ? 'bg-indigo-600 text-white border-indigo-600' : 'text-gray-600 border-gray-300 hover:bg-gray-100'}`;
                    if (p !== cur) btn.onclick = () => loadPage(p);
                    btnWrap.appendChild(btn);
                });

                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(() => {
                document.getElementById('tableLoading').classList.add('hidden');
                document.getElementById('tableWrap').classList.remove('opacity-40', 'pointer-events-none');
            });
        }

        document.getElementById('btnFilter').addEventListener('click', () => loadPage(1));
        document.getElementById('btnReset').addEventListener('click', () => {
            document.getElementById('f_start').value    = '{{ now()->startOfMonth()->format('Y-m-d') }}';
            document.getElementById('f_end').value      = '{{ now()->format('Y-m-d') }}';
            document.getElementById('f_msisdn').value   = '';
            document.getElementById('f_reseller').value = '';
            document.getElementById('f_product').value  = '';
            document.getElementById('f_status').value   = '';
            document.getElementById('f_perpage').value  = '15';
            loadPage(1);
        });
        document.getElementById('btnExport').addEventListener('click', () => {
            window.location = `{{ route('transactions.export') }}?${new URLSearchParams(getParams())}`;
        });
        document.getElementById('f_perpage').addEventListener('change', () => loadPage(1));
    </script>
</x-app-layout>