<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('rekon.index') }}" class="text-gray-400 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Hasil Rekon — {{ $supplier->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

            {{-- Periode --}}
            <div class="bg-white rounded-xl shadow p-5">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Periode</label>
                        <div class="flex gap-2 items-center">
                            <input type="date" name="start" value="{{ $start }}"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <span class="text-gray-400">s/d</span>
                            <input type="date" name="end" value="{{ $end }}"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Filter Status</label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="match"         {{ request('status') === 'match'         ? 'selected' : '' }}>✔ Match</option>
                            <option value="only_local"    {{ request('status') === 'only_local'    ? 'selected' : '' }}>❌ Hanya Lokal</option>
                            <option value="only_supplier" {{ request('status') === 'only_supplier' ? 'selected' : '' }}>⚠ Hanya Supplier</option>
                            <option value="selisih"       {{ request('status') === 'selisih'       ? 'selected' : '' }}>≠ Selisih</option>
                        </select>
                    </div>
                    <button type="submit"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition">
                        Tampilkan
                    </button>
                </form>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">✔ Match</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ number_format($summary['match'] ?? 0) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">❌ Hanya Lokal</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ number_format($summary['only_local'] ?? 0) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-yellow-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">⚠ Hanya Supplier</p>
                    <p class="text-2xl font-bold text-yellow-700 mt-1">{{ number_format($summary['only_supplier'] ?? 0) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow p-5 border-l-4 border-orange-500">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">≠ Selisih</p>
                    <p class="text-2xl font-bold text-orange-700 mt-1">{{ number_format($summary['selisih'] ?? 0) }}</p>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Detail Rekonsiliasi</h3>
                    <span class="text-xs text-gray-400">{{ number_format($results->total()) }} data</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">TRX ID / No Ref</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Amount Lokal</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Amount Supplier</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($results as $r)
                                @php
                                    $statusConfig = [
                                        'match'         => ['bg-green-100 text-green-800',  '✔ Match'],
                                        'only_local'    => ['bg-red-100 text-red-800',     '❌ Hanya Lokal'],
                                        'only_supplier' => ['bg-yellow-100 text-yellow-800','⚠ Hanya Supplier'],
                                        'selisih'       => ['bg-orange-100 text-orange-800','≠ Selisih'],
                                    ];
                                    [$badgeClass, $label] = $statusConfig[$r->status] ?? ['bg-gray-100 text-gray-600', $r->status];
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $badgeClass }}">{{ $label }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-mono text-indigo-700">
                                        {{ $r->transaction_trx_id ?? $r->supplier_no_reference ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-700">
                                        Rp {{ number_format($r->amount_local, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-700">
                                        Rp {{ number_format($r->amount_supplier, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold {{ $r->selisih != 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $r->selisih != 0 ? 'Rp ' . number_format(abs($r->selisih), 0, ',', '.') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400">
                                        Belum ada hasil rekon untuk periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($results->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">{{ $results->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>