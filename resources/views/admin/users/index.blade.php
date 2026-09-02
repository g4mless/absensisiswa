@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Pengguna</h1>
            <p class="text-gray-500">Kelola pengguna sistem dan peran mereka</p>
        </div>
        <x-button variant="primary" x-data="" x-on:click="$dispatch('open-modal', 'create-user')">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </x-button>
    </div>

    @if(session('success'))
        <x-alert variant="success" title="Berhasil" dismissible>{{ session('success') }}</x-alert>
    @endif

    <x-card>
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.users.index') }}">
                <x-search-input name="search" placeholder="Cari pengguna..." value="{{ request('search') }}" />
            </form>
        </div>

        <div class="overflow-x-auto">
            <x-table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Peran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-primary-600">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ $user->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->username }}</td>
                            <td>
                                <x-badge variant="{{ $user->role === 'admin' ? 'danger' : ($user->role === 'teacher' ? 'info' : 'success') }}">
                                    {{ ucfirst($user->role) }}
                                </x-badge>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}">
                                        <x-button variant="ghost" size="sm">Edit</x-button>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="danger" size="sm" type="submit">Hapus</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state title="Tidak ada pengguna ditemukan" description="Tidak ada pengguna yang cocok dengan kriteria pencarian Anda." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$users" />
        </div>
    </x-card>
</div>

<x-modal name="create-user">
    <x-slot name="header">Buat Pengguna Baru</x-slot>
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <x-input label="Nama Lengkap" name="name" :error="$errors->first('name')" value="{{ old('name') }}" />
            <x-input label="Username" name="username" :error="$errors->first('username')" value="{{ old('username') }}" />
            <x-input label="Password" name="password" type="password" :error="$errors->first('password')" />
            <x-select label="Peran" name="role" :options="['admin' => 'Admin', 'teacher' => 'Guru', 'student' => 'Siswa']" placeholder="Pilih Peran" :error="$errors->first('role')" />
        </div>
        <x-slot name="footer">
            <x-button variant="ghost" x-on:click="$dispatch('close-modal', 'create-user')">Batal</x-button>
            <x-button variant="primary" type="submit">Buat Pengguna</x-button>
        </x-slot>
    </form>
</x-modal>
@endsection
