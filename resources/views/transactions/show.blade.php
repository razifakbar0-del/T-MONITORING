<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('transactions.index') }}" class="text-gray-400 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Transaksi
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow overflow-hidden">

                {{-- Header --}}
                <div class="bg-indigo-600 px-6 py-5 text-white">
                    <p class="text-indigo-200 text-xs font-semibold uppercase tracking-wide mb-1">ID Transaksi</p>
                    <h3 class="text-xl font-bold font-mono">{{ $transaction->trx_id }}</h3>
                    <div class="mt-2">
                        @if($transaction->status === 'sukses')
                            <span class="inline-flex px-2 py-0.5 text-xs font-bold rounded-full bg-green-400 text-green-900">✓ SUKSES</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 text-xs font-bold rounded-full bg-red-400 text-red-900">✗ {{ strtoupper($transaction->status) }}</span>
                        @endif
                    </div>
                </div>

                {{-- Detail --}}
                <div class="divide-y divide-gray-100">
                    @php
                        $rows = [
                            ['Tanggal Transaksi',  \Carbon\Carbon::parse($transaction->trx_date)->format('d/m/Y H:i:s')],
                            ['Reseller',           $transaction->reseller_name   ?? '-'],
                            ['Request ID',         $transaction->request_id      ?? '-'],
                            ['MSISDN / Pelanggan', $transaction->msisdn          ?? '-'],
                            ['Produk',             $transaction->customer_name   ?? '-'],
                            ['Kode Produk',        $transaction->product_code    ?? '-'],
                            ['SN',                 $transaction->sn              ?? '-'],
                            ['Supplier',           $transaction->supplier        ?? '-'],
                            ['Amount',             'Rp ' . number_format($transaction->amount, 0, ',', '.')],
                            ['Profit',             'Rp ' . number_format($transaction->profit, 0, ',', '.')],
                            ['Debit',              'Rp ' . number_format($transaction->debit,  0, ',', '.')],
                            ['Credit',             'Rp ' . number_format($transaction->credit, 0, ',', '.')],
                            ['Balance',            'Rp ' . number_format($transaction->balance,0, ',', '.')],
                            ['Dibuat',             $transaction->created_at->format('d/m/Y H:i:s')],
                        ];
                    @endphp

                    @foreach($rows as [$label, $value])
                        <div class="flex px-6 py-3">
                            <span class="w-48 text-sm font-semibold text-gray-500 flex-shrink-0">{{ $label }}</span>
                            <span class="text-sm text-gray-800">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <a href="{{ route('transactions.index') }}"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow transition">
                        ← Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>