<div
    x-data="{
        name: '',
        code: '',
        error: '',
        saving: false,
        async save() {
            this.error = '';
            if (!this.name.trim() || !this.code.trim()) {
                this.error = 'Nama dan kode jurusan wajib diisi.';
                return;
            }
            this.saving = true;
            try {
                const res = await fetch('{{ route('admin.majors.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ name: this.name.trim(), code: this.code.trim().toUpperCase() }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.error = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Gagal menyimpan jurusan.';
                    return;
                }
                const select = document.getElementById('major_id');
                if (select) {
                    const opt = document.createElement('option');
                    opt.value = data.id;
                    opt.textContent = data.name + (data.code ? ' (' + data.code + ')' : '');
                    select.appendChild(opt);
                    select.value = data.id;
                }
                this.name = ''; this.code = '';
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'add-major' }));
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Jurusan berhasil ditambahkan.', type: 'success' } }));
            } catch (e) {
                this.error = 'Terjadi kesalahan. Coba lagi.';
            } finally {
                this.saving = false;
            }
        }
    }"
>
    <x-modal name="add-major">
        <x-slot name="header">Tambah Jurusan Baru</x-slot>

        <div class="space-y-4">
            <div class="space-y-1.5">
                <label for="quick_major_name" class="md-label">Nama Jurusan</label>
                <input id="quick_major_name" type="text" x-model="name" class="md-input" placeholder="contoh: Rekayasa Perangkat Lunak" />
            </div>
            <div class="space-y-1.5">
                <label for="quick_major_code" class="md-label">Kode</label>
                <input id="quick_major_code" type="text" x-model="code" class="md-input uppercase" placeholder="contoh: RPL" />
            </div>

            <template x-if="error">
                <p class="text-sm text-danger-500" x-text="error"></p>
            </template>

            <p class="text-xs text-gray-500">
                Butuh form lengkap? <a href="{{ route('admin.majors.create') }}" class="text-primary-600 hover:underline">Buka halaman jurusan</a>
            </p>
        </div>

        <x-slot name="footer">
            <x-button variant="ghost" x-on:click="$dispatch('close-modal', 'add-major')">Batal</x-button>
            <x-button variant="primary" x-on:click="save()" x-bind:disabled="saving" x-text="saving ? 'Menyimpan...' : 'Simpan Jurusan'"></x-button>
        </x-slot>
    </x-modal>
</div>
