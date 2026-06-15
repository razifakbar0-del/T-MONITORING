<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengelolaan Manajemen User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="bg-white/90 backdrop-blur-sm p-6 rounded-lg shadow h-fit">
                <h3 class="text-md font-bold text-gray-800 mb-4">Tambah User Baru</h3>
                
                @if(session('success'))
                    <div class="p-3 mb-4 text-sm bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="p-3 mb-4 text-sm bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600">Nama Lengkap</label>
                        <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600">Alamat Email</label>
                        <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600">Password</label>
                        <input type="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600">Hak Akses (Role)</label>
                        <select name="role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="operator">Operator Lapangan</option>
                            <option value="admin">Administrator</option>
                            <option value="viewer">Viewer (Hanya Lihat)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded font-bold hover:bg-indigo-500 text-sm">Simpan Akun</button>
                </form>
            </div>

            <div class="bg-white/90 backdrop-blur-sm p-6 rounded-lg shadow md:col-span-2">
                <h3 class="text-md font-bold text-gray-800 mb-4">Daftar Akun Terdaftar</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50/70">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">Nama</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">Email</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">Role</th>
                                <th class="px-4 py-2 text-center font-semibold text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($users as $user)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-900">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                                    <td class="px-4 py-3">
                                        @if($user->role === 'admin')
                                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-800">
                                                {{ $user->role }}
                                            </span>
                                        @elseif($user->role === 'operator')
                                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">
                                                {{ $user->role }}
                                            </span>
                                        @elseif($user->role === 'viewer')
                                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800">
                                                {{ $user->role }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800">
                                                {{ $user->role }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST" action="{{ route('users.destroy', $user->id) }}" onsubmit="return confirm('Yakin hapus user ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold text-xs">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>