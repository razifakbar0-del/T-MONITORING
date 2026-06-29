<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rekon Supplier</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

            @if(session('success'))
                <div class="flex items-center gap-3 p-4 text-sm text-green-800 bg-green-100 border border-green-300 rounded-xl">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Header --}}
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Daftar Supplier</h3>
                    <p class="text-sm text-gray-500 mt-1">Kelola supplier dan lakukan rekonsiliasi data transaksi.</p>
                </div>
                <a href="{{ route('rekon.create') }}"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Supplier
                </a>
            </div>

            {{-- Daftar Supplier --}}
            @forelse($suppliers as $supplier)
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        {{-- Info --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h4 class="text-lg font-bold text-gray-800">{{ $supplier->name }}</h4>
                                @if($supplier->is_active)
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                                @endif
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $supplier->method }}</span>
                            </div>
                            <p class="text-sm font-mono text-gray-500 truncate">{{ $supplier->api_url }}</p>
                            @if($supplier->notes)
                                <p class="text-sm text-gray-400 mt-1">{{ $supplier->notes }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-2">{{ number_format($supplier->mutations_count) }} data tersimpan</p>
                        </div>

                        {{-- Aksi --}}
                        <div class="flex flex-wrap gap-2">
                            {{-- Test API --}}
                            <button onclick="testApi({{ $supplier->id }}, '{{ $supplier->name }}')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Test API
                            </button>

                            {{-- Sync --}}
                            <button onclick="openSyncModal({{ $supplier->id }}, '{{ $supplier->name }}')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Sync Data
                            </button>

                            {{-- Rekon --}}
                            <button onclick="openRekonModal({{ $supplier->id }}, '{{ $supplier->name }}')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Rekonsiliasi
                            </button>

                            {{-- Edit --}}
                            <a href="{{ route('rekon.edit', $supplier) }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-yellow-900 text-sm font-semibold rounded-lg transition">
                                Edit
                            </a>

                            {{-- Hapus --}}
                            <form method="POST" action="{{ route('rekon.destroy', $supplier) }}"
                                onsubmit="return confirm('Hapus supplier {{ $supplier->name }}? Semua data sync dan rekon akan terhapus.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-semibold rounded-lg transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow p-12 text-center">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-gray-400 text-sm">Belum ada supplier. Klik "Tambah Supplier" untuk memulai.</p>
                </div>
            @endforelse

        </div>
    </div>

    {{-- Modal Sync --}}
    <div id="syncModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Sync Data — <span id="syncSupplierName"></span></h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tanggal Mulai</label>
                    <input type="date" id="syncStart" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tanggal Selesai</label>
                    <input type="date" id="syncEnd" value="{{ now()->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div id="syncResult" class="hidden p-3 rounded-lg text-sm"></div>
            </div>
            <div class="flex gap-3 mt-5">
                <button id="btnSync"
                    onclick="doSync()"
                    class="flex-1 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-lg transition">
                    Mulai Sync
                </button>
                <button onclick="closeSyncModal()"
                    class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Rekon --}}
    <div id="rekonModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Rekonsiliasi — <span id="rekonSupplierName"></span></h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tanggal Mulai</label>
                    <input type="date" id="rekonStart" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Tanggal Selesai</label>
                    <input type="date" id="rekonEnd" value="{{ now()->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div id="rekonResult" class="hidden p-3 rounded-lg text-sm"></div>
            </div>
            <div class="flex gap-3 mt-5">
                <button id="btnRekon" onclick="doRekon()"
                    class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition">
                    Mulai Rekon
                </button>
                <button onclick="closeRekonModal()"
                    class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        let activeSupplierId   = null;
        let activeSupplierName = null;

        // ── Test API ──────────────────────────────────────
        async function testApi(id, name) {
            if (!confirm(`Test koneksi API ke "${name}"?`)) return;
            try {
                const res  = await fetch(`/rekon/${id}/test-api`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                if (data.success) {
                    alert(`✅ Koneksi berhasil!\nHTTP: ${data.http_status}\nJumlah data: ${data.data_count}\n\nSample:\n${JSON.stringify(data.sample, null, 2)}`);
                } else {
                    alert(`❌ Koneksi gagal!\n${data.error}`);
                }
            } catch (e) {
                alert('Error: ' + e.message);
            }
        }

        // ── Sync Modal ─────────────────────────────────────
        function openSyncModal(id, name) {
            activeSupplierId   = id;
            activeSupplierName = name;
            document.getElementById('syncSupplierName').textContent = name;
            document.getElementById('syncResult').classList.add('hidden');
            document.getElementById('syncModal').classList.remove('hidden');
        }
        function closeSyncModal() { document.getElementById('syncModal').classList.add('hidden'); }

        async function doSync() {
            const start   = document.getElementById('syncStart').value;
            const end     = document.getElementById('syncEnd').value;
            const btn     = document.getElementById('btnSync');
            const result  = document.getElementById('syncResult');

            btn.disabled    = true;
            btn.textContent = 'Menyinkronkan...';
            result.classList.add('hidden');

            try {
                const res  = await fetch(`/rekon/${activeSupplierId}/sync`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ start, end }),
                });
                const data = await res.json();
                result.classList.remove('hidden');
                if (data.success) {
                    result.className = 'p-3 rounded-lg text-sm bg-green-100 text-green-800';
                    result.textContent = `✅ ${data.message}`;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    result.className = 'p-3 rounded-lg text-sm bg-red-100 text-red-800';
                    result.textContent = `❌ ${data.error}`;
                }
            } catch (e) {
                result.classList.remove('hidden');
                result.className = 'p-3 rounded-lg text-sm bg-red-100 text-red-800';
                result.textContent = 'Error: ' + e.message;
            } finally {
                btn.disabled    = false;
                btn.textContent = 'Mulai Sync';
            }
        }

        // ── Rekon Modal ────────────────────────────────────
        function openRekonModal(id, name) {
            activeSupplierId   = id;
            activeSupplierName = name;
            document.getElementById('rekonSupplierName').textContent = name;
            document.getElementById('rekonResult').classList.add('hidden');
            document.getElementById('rekonModal').classList.remove('hidden');
        }
        function closeRekonModal() { document.getElementById('rekonModal').classList.add('hidden'); }

        async function doRekon() {
            const start  = document.getElementById('rekonStart').value;
            const end    = document.getElementById('rekonEnd').value;
            const btn    = document.getElementById('btnRekon');
            const result = document.getElementById('rekonResult');

            btn.disabled    = true;
            btn.textContent = 'Memproses...';
            result.classList.add('hidden');

            try {
                const res  = await fetch(`/rekon/${activeSupplierId}/rekon`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ start, end }),
                });
                const data = await res.json();
                result.classList.remove('hidden');
                if (data.success) {
                    const s = data.summary;
                    result.className = 'p-3 rounded-lg text-sm bg-green-100 text-green-800';
                    result.innerHTML = `✅ Rekon selesai!<br>
                        ✔ Match: <b>${s.match}</b> &nbsp;
                        ❌ Hanya Lokal: <b>${s.only_local}</b> &nbsp;
                        ⚠ Hanya Supplier: <b>${s.only_supplier}</b> &nbsp;
                        ≠ Selisih: <b>${s.selisih}</b>
                        <br><a href="/rekon/${activeSupplierId}/result?start=${start}&end=${end}" class="underline font-bold mt-1 inline-block">Lihat Detail →</a>`;
                } else {
                    result.className = 'p-3 rounded-lg text-sm bg-red-100 text-red-800';
                    result.textContent = `❌ ${data.error}`;
                }
            } catch (e) {
                result.classList.remove('hidden');
                result.className = 'p-3 rounded-lg text-sm bg-red-100 text-red-800';
                result.textContent = 'Error: ' + e.message;
            } finally {
                btn.disabled    = false;
                btn.textContent = 'Mulai Rekon';
            }
        }
    </script>
</x-app-layout>