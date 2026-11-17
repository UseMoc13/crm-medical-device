# CRM Medical Device — Roadmap Review (16 November 2026)

## Dasar dan status

Riwayat percakapan menempatkan pekerjaan di A7.4.8 **Activity & TO-DO Collaboration**. Data yang dapat dipertahankan:

| Fase | Fokus | Status historis |
|---|---|---|
| A7.4.1 | Calendar UUID / redirect | Sudah ditangani dalam fase sebelumnya, belum diverifikasi dari kode terbaru |
| A7.4.2 | Calendar UI / daily timeline | Sudah ditangani dalam fase sebelumnya, belum diverifikasi dari kode terbaru |
| A7.4.3 | Multi-day event deletion | Sudah ditangani dalam fase sebelumnya, belum diverifikasi dari kode terbaru |
| A7.4.4 | Collaborative TO-DO | Sudah ditangani dalam fase sebelumnya, belum diverifikasi dari kode terbaru |
| A7.4.5 | Organization | 13/13 quick checks PASS menurut riwayat |
| A7.4.6 | Teams | 16/16 quick checks PASS + browser menurut riwayat |
| A7.4.7 | Advanced picker | 10/10 quick checks PASS; save/persistence/browser/upload belum sepenuhnya teruji |
| A7.4.8 | Activity & TO-DO Collaboration | UI awal di detail Activity dan Work TO-DO; audit end-point dan kebocoran relasi CRM belum selesai |

**Peringatan versi:** ZIP terlampir dan remote main GitHub berada di sekitar 5 November 2026 dan tidak memuat implementasi A7.4.x terbaru; daftar status di atas berasal dari riwayat chat, bukan hasil menguji source terkini.

## Kontrak akses (keputusan yang perlu dipertahankan)

1. Creator: mengendalikan CRUD objek yang dibuatnya (berdasarkan policy global yang berlaku).
2. Assignee: melakukan pekerjaan / submission; hak edit objek tidak otomatis sama dengan Creator.
3. Viewer: membaca objek yang diberikan aksesnya; tidak mengubah komentar, progres, atau submission.
4. Contributor: membaca, berkomentar, memperbarui progress log; bukan pengguna file submission.
5. Team membership, Role, Branch: sumber untuk menemukan anggota/picker, **bukan** izin implisit membaca seluruh objek Team.
6. Personal TO-DO: privat, tidak termasuk pool share Work TO-DO.
7. File submission: akses privat terpisah dan dicek server-side per objek, assignee, owner/policy.
8. Tautan relasi Customer, Lead, Opportunity: wajib melalui scope permission CRM modul asal; share Activity tidak menaikkan izin objek relasi.

## Rekomendasi urutan lanjutan, tanpa mengubah penomoran roadmap

### A7.4.8 — Verifikasi fondasi (sekarang)
- Jalankan audit-a748.ps1 pada source lokal terbaru.
- Inventaris models, migrations, policies, controllers, route, Blade detail Activity/TO-DO, file submission handling.
- Cocokkan implementasi dengan decision matrix (creator/assignee/viewer/contributor).

### A7.4.8 — Penyempurnaan otorisasi backend
- Konsolidasikan rule akses menjadi satu policy/service per resource.
- Tegakkan parent-child scoping; jangan percaya UUID request saja.
- Validasi akses file dan relasi Customer/Lead/Opportunity.
- Definisikan serta uji konsekuensi revoke collaborator.

### A7.4.8 — UX dan fungsional
- Pastikan Advanced Picker benar-benar menyimpan assignment dan bertahan setelah refresh.
- Samakan toast & modal konfirmasi dengan modul lainnya.
- Pastikan error 403/422/405 ditangani dengan jelas.

### A7.4.8 — Testing dan exit gate
- Jalankan 38 skenario di TEST_MATRIX_A748.md (pilih yang sesuai implementasi).
- Tambahkan feature test Laravel untuk seluruh rute sensitif dan IDOR cross-resource.
- Jalankan tes regresi A7.4.1–A7.4.7 sebelum menandai tahap selesai.

## Penyesuaian dengan dokumen requirement

Project CRM.pdf, halaman 10, menjelaskan ACTIVITIES (`user_id`, `customer_id`, `lead_id`, `opportunity_id`, `activity_type`, `subject`, `activity_date`). Halaman 24 menyebut TO-DO, Calendar, Notifications dan Favorites sebagai General & Workspace. Fitur kolaborasi baru merupakan perluasan yang harus **menjaga** relasi serta akses modul Customer & Lead / Sales CRM.

## Catatan penting

Paket ini adalah audit read-only serta acceptance plan, **bukan patch implementasi**. Jangan menjalankan migrasi A7.4.8 yang dihasilkan dari tebakan: source lokal pasca-5 November belum tersedia untuk diperiksa. Tidak ada file proyek diubah oleh script.
