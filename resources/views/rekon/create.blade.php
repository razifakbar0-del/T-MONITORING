<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('rekon.index') }}" class="text-gray-400 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Supplier</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow p-6">

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-300 rounded-lg text-sm text-red-800">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('rekon.store') }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Nama Supplier *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Samantara"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">API URL *</label>
                            <input type="url" name="api_url" value="{{ old('api_url') }}" required
                                placeholder="https://mpn-gateway.samantara.com/mpnbjt/api/mutasi"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-indigo-400 focus:border-indigo-400">
                            <p class="text-xs text-gray-400 mt-1">Tanpa parameter tanggal — parameter akan ditambah otomatis.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Method *</label>
                            <select name="method" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                                @foreach(['GET','POST','PUT','PATCH','DELETE'] as $m)
                                    <option value="{{ $m }}" {{ old('method','GET') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Response Path *</label>
                            <input type="text" name="response_path" value="{{ old('response_path','data') }}" required
                                placeholder="data"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-indigo-400 focus:border-indigo-400">
                            <p class="text-xs text-gray-400 mt-1">Path ke array data. Contoh: <code>data</code> atau <code>result.items</code></p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Param Tanggal Mulai *</label>
                            <input type="text" name="param_start" value="{{ old('param_start','start') }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-indigo-400 focus:border-indigo-400">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Param Tanggal Selesai *</label>
                            <input type="text" name="param_end" value="{{ old('param_end','end') }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-indigo-400 focus:border-indigo-400">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Headers (JSON)</label>
                            <textarea name="headers_json" rows="3" placeholder='{"Authorization": "Bearer TOKEN", "Accept": "application/json"}'
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-indigo-400 focus:border-indigo-400">{{ old('headers_json') }}</textarea>
                            <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak butuh header khusus.</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Body (JSON — untuk POST)</label>
                            <textarea name="body_json" rows="3" placeholder='{"api_key": "xxx"}'
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-indigo-400 focus:border-indigo-400">{{ old('body_json') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Field Map (JSON)</label>
                            <textarea name="field_map_json" rows="7"
                                placeholder='{
  "no_reference": "no_reference",
  "tanggal":      "tanggal",
  "keterangan":   "keterangan",
  "debet_kredit": "debet_kredit",
  "saldo":        "saldo"
}'
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-indigo-400 focus:border-indigo-400">{{ old('field_map_json') }}</textarea>
                            <p class="text-xs text-gray-400 mt-1">Kiri = nama field kita, Kanan = nama field dari API supplier. Kosongkan untuk gunakan default (Samantara).</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Catatan</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Opsional..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600">
                            <label for="is_active" class="text-sm text-gray-700 font-medium">Supplier Aktif</label>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow transition">
                            Simpan Supplier
                        </button>
                        <a href="{{ route('rekon.index') }}"
                            class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-lg transition">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>