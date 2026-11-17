# Test Matrix A7.4.8 — Activity & Work TO-DO Collaboration

**Status:** acceptance criteria yang perlu diverifikasi terhadap source lokal terbaru. Hasil di bawah **belum** diklaim PASS.

## Aktor yang digunakan

- A: creator Activity / Work TO-DO.
- B: assignee yang ditunjuk.
- C: Viewer yang ditambahkan secara eksplisit.
- D: Contributor yang ditambahkan secara eksplisit.
- E: sesama anggota Team tetapi **tidak** ditambahkan.
- F: user dari Branch lain tanpa akses eksplisit.
- G: user dengan akses CRM modul terbatas, tetapi menerima collaboration invite.

Gunakan 2 customer, 2 lead, 2 opportunity dengan cakupan RBAC berbeda. Jalankan pengujian menggunakan akun atau session berbeda, bukan hanya switch menu.

| ID | Skenario | Ekspektasi / acceptance | Cara uji |
|---|---|---|---|
| 01 | Creator membuka Activity miliknya | 200 dan data benar | Browser + Feature |
| 02 | Creator mengubah judul/relasi Activity | Update tersimpan; 302/200 sesuai implementasi | Browser + Feature |
| 03 | Creator menghapus Activity | Sesuai soft/hard delete policy; tidak meninggalkan akses file yatim | Feature |
| 04 | Assignee membuka Activity yang ditugaskan | Hanya data yang diizinkan, tidak seluruh modul CRM | Browser + Feature |
| 05 | Viewer membuka halaman detail | Read-only; tidak ada tombol mutasi | Browser |
| 06 | Viewer mencoba PATCH/POST komentar/progress via HTTP langsung | 403/404; tidak ada perubahan database | Feature |
| 07 | Contributor mencatat komentar pada Activity | Sukses, author dan timestamps benar | Browser + Feature |
| 08 | Contributor mencatat progress log | Sukses, append-only atau sesuai policy, audit tersedia | Browser + Feature |
| 09 | Contributor mencoba edit/delete Activity | 403/404; tidak ada perubahan | Feature |
| 10 | Contributor mencoba upload submission | 403/404; tidak ada file/record dibuat | Feature |
| 11 | Assignee mengunggah submission miliknya | Berhasil, file disimpan di private disk | Browser + Feature |
| 12 | Viewer/Contributor meminta URL download submission | 403/404; tidak ada redirect ke public storage | Feature |
| 13 | Assignee meminta submission assignee lain memakai UUID yang ditebak | 403/404 | Feature |
| 14 | Creator melihat submission tugasnya | Hanya jika dinyatakan dalam policy; route terotorisasi | Feature |
| 15 | User E dari Team yang sama membuka objek tanpa share | 403/404 | Feature |
| 16 | User F dari Branch lain membuka Activity tanpa share | 403/404 | Feature |
| 17 | Personal TO-DO oleh user A | Privat dari B/C/D/E/F, termasuk lewat API | Feature |
| 18 | User C/D mencoba membuka Personal TO-DO walau satu Team | 403/404 | Feature |
| 19 | Creator menambah Viewer via advanced picker | UI selection, save, refresh, record collaborator konsisten | Browser |
| 20 | Creator menambah Contributor via advanced picker | Contributor bisa komentar/progress tetapi bukan submit | Browser + Feature |
| 21 | Creator mencabut akses kolaborator | Akses berhenti segera, termasuk file/endpoint lama | Feature |
| 22 | Picker mencari user menurut Role/Branch/Team | Data terfilter sesuai scope; tidak memberi akses otomatis pada semua member | Browser + Feature |
| 23 | User nonaktif/tidak sah muncul di picker | Tidak bisa diberi akses, sesuai policy yang disetujui | Feature |
| 24 | User G diberi share Activity terkait customer yang tidak boleh ia baca | Response tidak memuat detail/customer sensitif; rule CRM tetap berlaku | Feature |
| 25 | Activity mengacu ke Lead/Opportunity di luar scope | Tidak dapat memperoleh detail melalui nested query, JSON atau DOM | Feature |
| 26 | Request mengganti related ID ke objek CRM terlarang | Ditolak / field tidak disimpan | Feature |
| 27 | Nested route menerima UUID parent A + child record milik B | 403/404; scope parent-child diverifikasi | Feature |
| 28 | Upload nama file berisi path traversal / tipe tidak diizinkan | 422/403; file aman | Feature |
| 29 | Mengakses file upload langsung melalui /storage/... | Tidak bisa membuka file privat | Browser |
| 30 | Upload melebihi ukuran limit | 422; tidak ada berkas parsial | Feature |
| 31 | Upload failed/revoked assignee | Tidak ada akses residual file/file record | Feature |
| 32 | Komentar/progress terjadi oleh user B/D | Audit trail merekam actor, action, resource dan waktu | Feature |
| 33 | Pembuatan/update/hapus berhasil/gagal | Toast sukses/error konsisten dan hilang otomatis | Browser |
| 34 | Konfirmasi penghapusan atau perubahan status | Modal konfirmasi konsisten, metode HTTP cocok dengan route (PATCH/DELETE) | Browser + Feature |
| 35 | Perubahan A7.4.8 | Calendar event, daily timeline, multi-day delete tidak regresi | Browser + Feature |
| 36 | Perubahan A7.4.8 | Work TO-DO existing assignment dan advanced picker tetap persist | Browser + Feature |
| 37 | Pesan validasi input salah | 422, tidak membocorkan data, UX mudah dipahami | Feature |
| 38 | Role/Branch/Team berubah setelah permission diberikan | Hasil mengikuti aturan snapshot/dynamic membership yang disepakati, tidak membuka akses tak sengaja | Feature |

## Gate penyelesaian

- Tidak ada 500, route 405 atau policy bypass dalam 38 kasus yang relevan.
- Semua operasi mutasi/unduh memakai otorisasi backend sesuai actor dan resource, bukan hanya pembatasan tampilan.
- File submission disimpan privat dan hanya dibuka lewat endpoint yang memeriksa ownership/assignment.
- Personal TO-DO tetap privat dan relasi CRM tidak bocor ketika dibagikan.
- Uji regresi A7.4.1–A7.4.7 lulus, termasuk pengujian browser yang sebelumnya belum tercakup.
- Hasil aktual pengujian didokumentasikan per kasus sebagai PASS / FAIL / BLOCKED + bukti log.

> Kode 403 atau 404 dipakai sesuai pilihan security policy; jangan mengekspos resource privat melalui 404/403 yang tidak konsisten. Status akses untuk creator, assignee, atau admin harus ditetapkan terhadap policy aktual.
