# A7.9.6 - Dataset sintetis dengan nama mudah dibaca + ekspansi (FIX 1)

## FIX 1 — service_tickets: 10/12 saat rehearsal (26 Nov 2026)

Pada paket awal, `service_tickets` menggunakan `(n-1)%5`, padahal dari 5 serial number sintetis A794, `UAT-SN005` masih **tanpa customer** (`customer_id IS NULL`). Setelah filter, hanya ada **4 serial eligible**. Dua iterasi (nomor 5 dan 10) tidak menemukan pasangan; 10 tiket tersisip, sehingga postcheck yang mengharapkan 12 menghentikan transaksi.

Paket FIX 1 memakai `count(*) OVER () AS eligible_serial_count` pada serial dengan customer, kemudian melakukan pemetaan siklis sesuai **jumlah serial yang benar-benar eligible**, sehingga menghasilkan 12 tiket (pada fixture awal: 4 serial × 3 tiket). Guard baru memastikan minimal satu serial eligible. Tidak mengubah data A794, tidak membuat customer palsu untuk serial unassigned, dan tidak mengubah total tambahan **1.684 record**.

**Rehearsal yang gagal dengan ON_ERROR_STOP otomatis dibatalkan oleh PostgreSQL ketika koneksi psql ditutup; jangan jalankan commit dari paket lama.** Sebelum mencoba FIX 1, verifikasi belum ada record `DEMO26-...`:

```powershell
& "$pg\psql.exe" @conn -c "SELECT (SELECT count(*) FROM customers WHERE customer_code LIKE 'DEMO26-C%') AS demo_customers, (SELECT count(*) FROM service_tickets WHERE ticket_number LIKE 'DEMO26-T%') AS demo_tickets;"
```

Target: `0` dan `0`. Jika bukan nol, hentikan import dan periksa status data sebelum tindakan berikutnya. Ekstrak ulang **semua** file dari ZIP FIX 1 agar SQL dan SHA-256 manifest tetap cocok. Jalankan quick-check, rehearsal, commit, validator dalam urutan tersebut. Password database yang salah pada koneksi pertama tidak mengubah data.

**Khusus PostgreSQL lokal disposable `crm_medical_device` di `127.0.0.1:5432`.** Jangan digunakan pada server pelanggan, development bersama, staging produksi, ataupun database asli yang belum disetujui untuk UAT.

Paket ini **tidak mereset database**, tidak menghapus data, tidak mengubah schema, tidak mengganti UUID maupun password/email/role grant akun lama. Script mengubah label yang **masih berbentuk placeholder asli A794** dan menambahkan **1.684 baris** data fiktif, antara lain nama perusahaan/RS/klinik, orang Indonesia dengan peran pekerjaan, nama alat kesehatan, dan Opportunity/Quotation/Order terhubung. Semua nomor HP/alamat/email bersifat simulasi; email memakai domain `.invalid`. Nama perusahaan, merek, dan staf adalah **fiktif**, walaupun mungkin kebetulan menyerupai nama di dunia nyata.

Saat impor, label akun yang lama berubah menjadi misalnya `Rizky Pratama - Sales Executive`, sementara email `uat.user01@example.invalid` dan credential-nya **tetap**. `Dimas Pratama - Technician` ditambahkan sebagai akun baru, tetapi memakai password bcrypt acak yang dibuang dan role `UAT Technician` **tanpa permission**; belum bisa login hingga Super Admin mengatur ulang password dan memberikan akses. Akun Super Admin sebelumnya tetap ada.

## Pra-syarat wajib

1. Pastikan A794 telah diimpor dan `crm:super-admin:bootstrap` sudah berhasil; database `crm_medical_device` merupakan salinan lokal untuk UAT.
2. Pastikan perubahan CRUD Branches yang penting sudah disimpan. STOP Laravel, queue worker, scheduler, dan sesi penulis lainnya.
3. **Backup kondisi terbaru, setelah pembuatan Branches & Super Admin** ke file baru:

```powershell
$pg = 'C:\Program Files\PostgreSQL\18\bin'
$backupDir = Join-Path $HOME 'CRM-Backups'
New-Item -ItemType Directory -Force $backupDir | Out-Null
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupPath = Join-Path $backupDir ("crm_before_a796_$stamp.backup")
& "$pg\pg_dump.exe" -h 127.0.0.1 -p 5432 -U postgres -d crm_medical_device -F c -f $backupPath
if ($LASTEXITCODE -ne 0) { throw 'Backup failed. Stop.' }
Get-Item $backupPath
```

Ekstrak ZIP ke root proyek `crm-medical-device` (hanya menambah file `database/uat`, `tools`, `docs`). Jalankan dari root proyek:

```powershell
php tools/a796_quick_check.php
$conn = @('-X','-v','ON_ERROR_STOP=1','-h','127.0.0.1','-p','5432','-U','postgres','-d','crm_medical_device')
& "$pg\psql.exe" @conn -f database/uat/a796_rehearsal.sql
if ($LASTEXITCODE -ne 0) { throw 'Rehearsal failed. Do not commit.' }
```

Target: `A796_HUMANIZED_EXPANSION_REHEARSAL_ROLLED_BACK`. Rehearsal selalu ROLLBACK; baru setelah itu:

```powershell
& "$pg\psql.exe" @conn -f database/uat/a796_commit.sql
if ($LASTEXITCODE -ne 0) { throw 'Commit failed. Do not rerun without investigation.' }
& "$pg\psql.exe" @conn -f tools/a796_verify.sql
if ($LASTEXITCODE -ne 0) { throw 'Validation failed.' }
```

Target: `A796_HUMANIZED_EXPANSION_COMMITTED`, lalu `A796_HUMANIZED_EXPANSION_VALIDATED`.

**Penting:** Import ini **tidak idempotent**: menjalankan commit kedua kali akan ditolak dengan jelas. Setelah import, **jangan ulangi** validator A794 yang memerlukan jumlah record awal tanpa perubahan. Validator A796 memeriksa penambahan 1.684 record, kesesuaian subtotal transaksi, stok baru, role primer, permissions (117) dan migration (32). Validasi ini disarankan sebelum melakukan CRUD lewat UI; edit/hapus record DEMO26 nantinya akan mengubah count dan menggagalkan validasi exact (sudah sewajarnya). Dataset yang diimpor SQL **tidak mensimulasikan** audit/controller/notifications real-time, pengiriman tidak memotong stok secara otomatis, dan tidak membuktikan keberhasilan workflow HTTP. UAT tetap diperlukan.

Apabila muncul error `STOP`, periksa output dan **jangan** mencoba reset/force. Simpan backup lama dan yang baru. Nama customer dan pegawai dalam paket sepenuhnya contoh.
