<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Data Transaksi
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="flex items-center gap-3 p-4 text-sm text-green-800 bg-green-100 border border-green-300 rounded-xl">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-xl">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-gray-400">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Sukses</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ number_format($summary['total_sukses']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Gagal</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ number_format($summary['total_gagal']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-indigo-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Amount</p>
                    <p class="text-xl font-bold text-indigo-700 mt-1">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Filter --}}
            <div class="bg-white rounded-xl shadow p-5">
                <form method="GET" action="{{ route('transactions.index') }}">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tgl Mulai</label>
                            <input type="date" name="start" value="{{ request('start', now()->startOfMonth()->format('Y-m-d')) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tgl Selesai</label>
                            <input type="date" name="end" value="{{ request('end', now()->format('Y-m-d')) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">MSISDN</label>
                            <input type="text" name="msisdn" value="{{ request('msisdn') }}" placeholder="08xx..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Reseller / ID</label>
                            <input type="text" name="reseller" value="{{ request('reseller') }}" placeholder="Nama / request ID..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Kode Produk</label>
                            <input type="text" name="product_code" value="{{ request('product_code') }}" placeholder="ex: BYU7GB..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Status</label>
                            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                                <option value="">Semua</option>
                                <option value="sukses" {{ request('status') === 'sukses' ? 'selected' : '' }}>Sukses</option>
                                <option value="gagal"  {{ request('status') === 'gagal'  ? 'selected' : '' }}>Gagal</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-4">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                            </svg>
                            Tampilkan
                        </button>
                        <a href="{{ route('transactions.index') }}"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-lg transition">
                            Reset
                        </a>
                        <a href="{{ route('transactions.export') }}?{{ http_build_query(request()->all()) }}"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg shadow transition ml-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                            </svg>
                            Export CSV
                        </a>
                    </div>
                </form>
            </div>

            {{-- Tabel --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Riwayat Transaksi</h3>
                    <span class="text-xs text-gray-400">
                        Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari {{ number_format($transactions->total()) }} transaksi
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">ID Transaksi</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Reseller</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">MSISDN</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Produk</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Supplier</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($transactions as $trx)
                                <tr class="hover:bg-gray-50 transition cursor-pointer" onclick="window.location='{{ route('transactions.show', $trx->id) }}'">
                                    <td class="px-4 py-3 text-sm font-mono text-indigo-700">{{ $trx->trx_id }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        <div>{{ $trx->reseller_name ?? '-' }}</div>
                                        @if($trx->request_id)
                                            <div class="text-xs text-gray-400">{{ $trx->request_id }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $trx->msisdn ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        <div>{{ $trx->customer_name ?? '-' }}</div>
                                        @if($trx->product_code)
                                            <div class="text-xs text-gray-400 font-mono">{{ $trx->product_code }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $trx->supplier ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold text-gray-800 whitespace-nowrap">
                                        Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($trx->status === 'sukses')
                                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">sukses</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $trx->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($trx->trx_date)->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400">
                                        Tidak ada data transaksi untuk filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($transactions->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>