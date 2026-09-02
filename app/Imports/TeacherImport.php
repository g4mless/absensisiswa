<?php

namespace App\Imports;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TeacherImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            $user = User::create([
                'name' => $row['nama'],
                'username' => $row['nip'],
                'role' => 'guru',
                'password' => Hash::make($row['nip']),
            ]);

            return Teacher::create([
                'user_id' => $user->id,
                'nip' => $row['nip'],
            ]);
        });
    }

    public function rules(): array
    {
        return [
            'nip' => ['required', 'string', 'unique:teachers,nip'],
            'nama' => ['required', 'string'],
        ];
    }
}
