# A7.1 — Activities, Calendar, To-Do & Notes: audit dan rancangan

Tanggal audit: 14 November 2026. Basis: GitHub `UseMoc13/crm-medical-device`, branch `main`, dan dump PostgreSQL lokal `dump-crm_medical_device-202611121810.sql` (dump bisa berbeda dari database aktif). **Bukan audit seluruh file lokal yang belum di-push.**

## Temuan yang diverifikasi

1. `pages/activities.html`, `pages/calendar.html`, `pages/todo.html` masih prototipe HTML: menampilkan contoh data, memakai `js/app.js`, dan aksi simpan dummy. Jangan anggap sebagai CRUD Laravel.
2. `resources/views/components/sidebar.blade.php`: Activities, Calendar, To-Do masih `href="#"`, belum terhubung ke route Laravel.
3. `routes/web.php` pada `main`: belum ada route Activities, Calendar, To-Do, atau Personal Notes.
4. `app/Http/Controllers`: tidak ada Activity/Calendar/ToDo/PersonalNote Controller pada `main`.
5. Dump PostgreSQL: tabel `activities` sudah memiliki activity_id, user_id, customer_id, lead_id, opportunity_id, activity_type, subject, description, activity_date, created_at. **Jangan duplikasi Activity ke event kalender.**
6. Dump PostgreSQL juga sudah memiliki `notifications`. Belum ada tabel khusus task, calendar event pribadi, atau personal notes.
7. `resources/views/layouts/app.blade.php`: topbar berisi tombol notifikasi dan profil Administrator hardcoded. `routes/web.php` pada `main` tidak menunjukkan `auth` middleware atau login route. Tidak aman membuat endpoint Notes pribadi dengan kondisi ini. Audit hanya membuktikan kondisi branch `main`.
8. File `CrmSalesDataScope.php` tidak tersedia di branch `main` saat pemeriksaan sebelumnya, meskipun ada di instalasi lokal. **Bandingkan dengan file lokal sebelum mengganti apa pun.**

## Konsep data

- **Activity CRM**: data yang sudah ada di `activities`, dengan relasi Customer/Lead/Opportunity; Calendar menampilkannya sebagai event virtual berdasarkan `activity_date`.
- **Work To-Do**: `workspace_tasks` dengan `visibility=work` dan assignee; pembatasan berdasarkan owner/assignee *dan* role permission serta scope referensi CRM.
- **Personal To-Do**: baris `workspace_tasks` dengan `visibility=personal`; hanya owner.
- **Calendar mandiri**: `workspace_calendar_events`, versi pertama hanya event milik sendiri (`visibility=personal`). Tidak mereplikasi Activity/Task.
- **Personal Notes**: `workspace_personal_notes`; private-to-owner. Bisa terkait task / calendar event **hanya jika milik owner sama**. Validasi ini harus diterapkan di Controller/Service.
- **Activity Notes (work)**: belum di-migrate pada paket ini; pertimbangkan tabel tersendiri di tahap integrasi setelah kebutuhan history dan RBAC disepakati.
- **Notifications/Reminders**: gunakan `notifications` yang sudah ada; jangan menambah job reminder sebelum scheduler dan auth siap.

## Privasi dan syarat sebelum UI diaktifkan

Wajib sebelum route personal dirilis:
- Authenticated user yang nyata (`auth` middleware atau mekanisme ekuivalen); `auth()->id()` digunakan sebagai owner. Jangan menerima owner dari form.
- Endpoint GET/list/show/edit/store/update/delete memfilter `owner_user_id = auth()->id()` untuk personal, bukan sekadar menyembunyikan tombol di Blade.
- Work task harus menggunakan RBAC + Data Scope Customer/Lead/Opportunity; jangan memperbolehkan reference UUID di luar scope.
- Periksa hubungan milik pengguna untuk note yang menunjuk task atau calendar event.
- Validasi enum `visibility`, `status`, `priority`; `ends_at >= starts_at`; waktu/date timezone Asia/Jakarta di UI, simpan konsisten menurut kebijakan aplikasi.
- Untuk event kalender pribadi, nilai visibility harus hanya `personal` pada versi pertama.
- Pastikan navigasi sidebar/topbar hanya aktif setelah endpoint aman; static Administrator jangan digunakan sebagai identitas user.
- Test positif dan negatif untuk dua akun berbeda sebelum production.

## Status dan urutan berikutnya

| Tahap | Hasil |
| --- | --- |
| A7.1 Audit existing code, schema, topbar | Selesai berdasarkan GitHub `main` dan dump, ada perbedaan potensial dengan lokal |
| A7.1.1 Fondasi skema pribadi | Migration + model disiapkan, **belum dijalankan** |
| A7.2 Activity CRUD | Belum, perlu evaluasi auth/RBAC dan tabel existing |
| A7.3 To-Do CRUD | Belum; menunggu auth |
| A7.4 Calendar work + personal | Belum; menunggu auth |
| A7.5 Personal Notes topbar | Belum; menunggu auth |
| A7.6 Cross-module + notification | Belum |
| A7.7 QA + privacy + runtime test | Belum |

## Referensi kode yang diperiksa

- https://github.com/UseMoc13/crm-medical-device/blob/main/resources/views/components/sidebar.blade.php
- https://github.com/UseMoc13/crm-medical-device/blob/main/resources/views/layouts/app.blade.php
- https://github.com/UseMoc13/crm-medical-device/blob/main/routes/web.php
- https://github.com/UseMoc13/crm-medical-device/blob/main/pages/activities.html
- https://github.com/UseMoc13/crm-medical-device/blob/main/pages/calendar.html
- https://github.com/UseMoc13/crm-medical-device/blob/main/pages/todo.html
