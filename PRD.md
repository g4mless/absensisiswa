# PRD — Sistem Absensi Sekolah & PKL

## 1. Overview

Bangun aplikasi **web-based** untuk absensi siswa sekolah dan siswa PKL.

Konsep utama:

* **Daily Attendance** = kehadiran siswa di sekolah.
* **Session Attendance** = kehadiran siswa pada setiap jam/sesi pelajaran.
* Keduanya **harus disimpan terpisah**.

Contoh:

```text
Daily Attendance : HADIR
Guru 1           : HADIR
Guru 2           : ALFA
Guru 3           : HADIR
```

Status daily attendance tetap `HADIR` walaupun siswa alfa di salah satu sesi.

---

# 2. Wajib Technology Stack

```text
Backend      : Laravel
Language     : PHP
Database     : PostgreSQL
Frontend     : Laravel Blade
JavaScript   : Alpine.js
CSS          : Tailwind CSS
UI Design    : Material Design / MUI-inspired
Realtime     : Laravel Reverb / Broadcasting / WebSocket
```

Ketentuan:

* Jangan gunakan React, Vue, Angular, atau SPA framework lain.
* Alpine.js adalah layer interaksi frontend.
* Tailwind adalah styling system.
* MUI React (`@mui/material`) **tidak digunakan**; gunakan Material Design/MUI sebagai referensi visual.
* Gunakan Laravel migrations, policies, validation, middleware, dan service layer.
* Jangan menambahkan dependency besar tanpa alasan teknis.

---

# 3. Mobile First

Aplikasi **WAJIB mobile-first**.

Prioritas:

```text
Mobile → Tablet → Desktop
```

Smartphone adalah platform utama untuk:

* Siswa.
* Siswa PKL.
* Guru.
* Pembimbing PKL.

Desktop lebih diutamakan untuk admin dan pengelolaan data dalam jumlah besar.

Tidak boleh ada horizontal overflow yang tidak diperlukan, tombol terlalu kecil, atau UI yang sulit digunakan di layar mobile.

---

# 4. Roles

User login utama:

```text
admin
guru
siswa
siswa_pkl
```

`guru_piket`, `pembimbing_pkl`, `walas`, dan `kaprog` adalah **assignment/capability tambahan**, bukan role login utama.

---

# 5. Permissions

| Fitur                | Admin | Guru            | Pembimbing PKL    | Siswa         | Siswa PKL     |
| -------------------- | ----- | --------------- | ----------------- | ------------- | ------------- |
| Kelola data master   | ✓     |                 |                   |               |               |
| Absensi sekolah      | ✓*    |                 |                   | ✓             |               |
| Update absensi siswa | ✓     | ✓               | sesuai kewenangan |               |               |
| Upload surat         | ✓     | ✓               | ✓                 |               |               |
| Rekap absensi        | ✓     | ✓               | terbatas          | milik sendiri | milik sendiri |
| Monitoring GPS PKL   | ✓     | jika pembimbing | ✓                 |               | milik sendiri |

Semua authorization wajib dilakukan di **backend**, bukan hanya dengan menyembunyikan menu frontend.

---

# 6. Struktur Halaman

## Admin

```text
/admin/dashboard
/admin/users
/admin/students
/admin/teachers
/admin/classes
/admin/majors
/admin/academic-years
/admin/subjects
/admin/teacher-subjects
/admin/homeroom-teachers
/admin/program-heads
/admin/pkl-supervisors
/admin/schedules
/admin/school-location
/admin/attendance
/admin/reports
```

## Guru

```text
/teacher/dashboard
/teacher/schedule
/teacher/classes
/teacher/classes/:id
/teacher/attendance
/teacher/attendance/:sessionId
/teacher/students/:id
/teacher/excuses
/teacher/reports
```

Guru dapat:

* melihat siswa sesuai kewenangan,
* update absensi,
* upload surat,
* melihat dan download rekap harian, mingguan, bulanan.

## Guru Piket

```text
/duty-teacher/dashboard
/duty-teacher/attendance/today
/duty-teacher/attendance/all
/duty-teacher/reports/semester
```

Fokus:

* siswa tidak hadir,
* izin,
* sakit,
* alfa,
* belum absen,
* semua absensi,
* rekap semester.

## Pembimbing PKL

```text
/pkl-supervisor/dashboard
/pkl-supervisor/students
/pkl-supervisor/students/:id
/pkl-supervisor/attendance
/pkl-supervisor/locations
/pkl-supervisor/locations/:studentId
```

Hanya dapat melihat siswa PKL yang dibimbing.

## Siswa

```text
/student/dashboard
/student/attendance
/student/attendance/history
/student/profile
```

Hanya dapat:

* absen,
* melihat status absensi sendiri,
* melihat riwayat sendiri.

## Siswa PKL

```text
/student-pkl/dashboard
/student-pkl/attendance
/student-pkl/location
/student-pkl/history
/student-pkl/profile
```

Hanya dapat:

* absen PKL,
* melihat absensi sendiri,
* mengirim GPS realtime,
* melihat status GPS.

---

# 7. Daily Attendance

Absensi kedatangan sekolah:

* Dibuka **06:00–07:30**.
* Hanya siswa yang melakukan absensi.
* Wajib GPS.
* Radius maksimum **50 meter** dari lokasi sekolah.
* Validasi jarak dilakukan server.
* Waktu resmi menggunakan server timestamp.
* Satu siswa hanya boleh satu absensi per tanggal.

Data:

```text
student_id
date
status
checkin_time
latitude
longitude
accuracy
source
```

Status:

```text
HADIR
IZIN
SAKIT
ALFA
```

---

# 8. Session Attendance

Absensi per jam pelajaran berdasarkan jadwal mengajar.

Contoh:

```text
07:30–08:15 → Guru A → Matematika
08:15–09:00 → Guru B → Bahasa
```

Setiap jadwal menghasilkan `attendance_session`.

Setiap siswa maksimal memiliki satu `attendance_record` per session.

Data:

```text
session_id
student_id
status
checkin_time
updated_by
updated_at
```

Guru dapat mengubah status, misalnya:

```text
HADIR → ALFA
```

Perubahan tidak boleh mengubah daily attendance.

---

# 9. Audit Absensi

Semua perubahan absensi harus memiliki audit log:

```text
attendance_id
old_status
new_status
changed_by
reason
changed_at
```

Histori perubahan tidak boleh dihapus oleh guru biasa.

---

# 10. Surat Izin / Sakit

Surat hanya dapat diupload **guru atau admin**.

Data:

```text
student_id
date
type
file_path
uploaded_by
uploaded_at
note
```

Siswa tidak boleh mengupload surat.

---

# 11. Jadwal Mengajar

Admin mengelola:

* guru,
* mata pelajaran,
* kelas,
* hari,
* jam mulai,
* jam selesai,
* ruangan.

Entitas:

```text
teaching_schedules
    ↓
attendance_sessions
    ↓
attendance_records
```

---

# 12. PKL

Siswa PKL tetap menggunakan entity `students`.

Data PKL:

```text
student_id
tempat_pkl
pembimbing_id
start_date
end_date
status
```

Status:

```text
PLANNED
ACTIVE
COMPLETED
CANCELLED
```

Siswa PKL bukan prioritas utama untuk update absensi guru.

---

# 13. Realtime GPS PKL

Siswa PKL wajib mengirim lokasi realtime saat aktif PKL.

Gunakan:

```text
Browser Geolocation API
+
Laravel Reverb/Broadcasting/WebSocket
+
Alpine.js
```

Simpan histori:

```text
student_pkl_id
latitude
longitude
accuracy
recorded_at
```

Jangan polling database sebagai mekanisme realtime utama.

Update GPS dapat menggunakan interval sekitar **10–30 detik** atau berdasarkan perpindahan lokasi.

---

# 14. Guru Piket & Reports

Guru dapat download:

* Rekap harian.
* Rekap mingguan.
* Rekap bulanan.

Guru piket dapat:

* melihat absensi harian siswa yang tidak hadir,
* melihat seluruh absensi (`ALL`),
* melihat rekap semester.

Export minimal:

```text
CSV
XLSX
```

---

# 15. Database Entities

Gunakan relational database dengan FK, index, unique constraint, dan transaction.

Minimal tabel:

```text
users

students
teachers

academic_years
majors
classes

subjects
teacher_subjects

homeroom_teachers
program_heads

teaching_schedules
attendance_sessions

daily_attendance
attendance_records
attendance_logs

attendance_excuses

student_pkl
pkl_location_logs

school_locations
```

Constraint penting:

```text
UNIQUE(users.username)
UNIQUE(students.nis)
UNIQUE(teachers.nip)
UNIQUE(daily_attendance.student_id, daily_attendance.date)
UNIQUE(attendance_records.session_id, attendance_records.student_id)
```

---

# 16. School Location

Admin dapat mengatur:

```text
latitude
longitude
radius_meter
```

Default:

```text
radius_meter = 50
```

Jarak dihitung server menggunakan Haversine atau PostGIS.

---

# 17. Security

Wajib:

* password hashing,
* authentication middleware,
* backend authorization,
* Laravel Policies/Gates,
* server-side validation,
* file validation,
* upload size limits,
* protection terhadap SQL injection,
* protection terhadap IDOR,
* server timestamp,
* audit log,
* transaction untuk operasi penting.

Jangan percaya hasil perhitungan GPS dari frontend.

---

# 18. UI / Design System

Gunakan **Tailwind CSS + Alpine.js + Blade**.

Visual harus konsisten dengan **Material Design / MUI (mui.com)**.

Buat reusable Blade components:

```text
Button
Input
Select
Textarea
Card
Badge
Modal
Dialog
Dropdown
Tabs
Toast
Alert
Table
Pagination
File Upload
Loading
Empty State
Error State
```

Gunakan satu design system untuk seluruh role.

---

# 19. Struktur Backend

Gunakan Laravel dengan business logic terpisah dari controller.

Contoh service:

```text
AttendanceService
GpsValidationService
AttendanceReportService
PklService
PklLocationService
```

Authorization menggunakan Policies/Gates.

Frontend menggunakan:

```text
Blade
  +
Alpine.js
  +
Tailwind CSS
```

---

# 20. Prinsip Implementasi

AI coding agent wajib:

1. Memisahkan **daily attendance** dan **session attendance**.
2. Tidak memberikan authorization hanya di frontend.
3. Memvalidasi GPS di server.
4. Menggunakan server timestamp.
5. Menyimpan audit perubahan absensi.
6. Tidak mengubah daily attendance ketika session attendance berubah.
7. Menjadikan siswa PKL sebagai entity siswa yang sama.
8. Menggunakan mobile-first.
9. Menggunakan Laravel + Blade + Alpine.js + Tailwind CSS + PostgreSQL.
10. Menggunakan Material Design/MUI sebagai **design reference**, bukan React MUI.
11. Menghindari overengineering.
12. Membuat komponen UI reusable dan konsisten.
