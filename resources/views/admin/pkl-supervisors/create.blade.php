@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.pkl-supervisors.index') }}" class="text-primary-600 hover:text-primary-500 text-sm font-medium">&larr; Kembali ke Pembimbing PKL</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Tambah Penugasan Pembimbing PKL</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.pkl-supervisors.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <x-input label="Nama Pembimbing PKL" name="supervisor_name" :error="$errors->first('supervisor_name')" value="{{ old('supervisor_name') }}" placeholder="contoh: Budi Santoso" />
                <x-input label="Nama Perusahaan/Tempat" name="company_name" :error="$errors->first('company_name')" value="{{ old('company_name') }}" placeholder="contoh: PT Teknologi Nusantara" />
                <x-input label="Alamat Perusahaan" name="company_address" :error="$errors->first('company_address')" value="{{ old('company_address') }}" />
                <x-input label="Nomor Kontak" name="contact_phone" :error="$errors->first('contact_phone')" value="{{ old('contact_phone') }}" placeholder="contoh: 08123456789" />
            </div>
            <div class="flex items-center gap-3 mt-6">
                <x-button variant="primary" type="submit">Buat Penugasan</x-button>
                <a href="{{ route('admin.pkl-supervisors.index') }}">
                    <x-button variant="ghost">Batal</x-button>
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection
