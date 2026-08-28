# SkillSwapp

Platform web untuk mahasiswa saling menukar skill/jasa tanpa uang, menggunakan sistem kredit waktu (time banking).

## Latar Belakang

Banyak mahasiswa memiliki skill (desain, coding, menulis, edit video, bahasa asing, dll) tetapi tidak punya budget untuk membayar jasa freelance saat membutuhkan bantuan di skill yang tidak mereka kuasai. Di sisi lain, bantuan gratis sulit didapat karena tidak ada sistem yang jelas soal "balas budi". SkillSwapp hadir sebagai solusi: platform pertukaran skill antar mahasiswa berbasis kredit waktu, bukan uang.

## Masalah yang Diselesaikan

- Jasa freelance di luar terlalu mahal untuk kantong mahasiswa
- Bantuan gratis antar mahasiswa tidak terorganisir dan rawan kesalahpahaman soal timbal balik
- Tidak ada wadah khusus untuk ekosistem tukar-menukar skill di lingkup kampus

## Solusi

Platform web tempat mahasiswa:
- Mendaftarkan skill yang mereka tawarkan dan skill yang mereka butuhkan
- Saling membantu menggunakan sistem kredit waktu (1 jam kerja = 1 kredit)
- Membangun reputasi melalui rating, review, dan kontribusi jam bantuan

## Cara Kerja

1. **Profil Skill** — User mendaftarkan skill yang ditawarkan (misal: "bisa desain poster") dan skill yang dibutuhkan.
2. **Marketplace/Matching** — User dapat browse penawaran atau memposting request bantuan.
3. **Kredit Waktu** — User baru mendapat starting credit gratis. Membantu orang lain menambah kredit; menggunakan jasa orang lain mengurangi kredit.
4. **Konfirmasi Dua Arah** — Kredit baru berpindah setelah kedua pihak mengonfirmasi bahwa sesi bantuan telah selesai, untuk mencegah kecurangan.
5. **Rating & Review** — Menjaga kualitas dan kepercayaan antar pengguna.
6. **Chat/Koordinasi Jadwal** — Untuk mengatur waktu dan format sesi (online/offline).

## Fitur Utama

- Sistem reputasi untuk tiap mahasiswa
- Badge/level berdasarkan jumlah jam kontribusi (gamifikasi)
- Kategori skill (akademik, teknis, kreatif, bahasa, dll)
- Leaderboard kontributor terbanyak per kampus
- Verifikasi kampus (memastikan user adalah mahasiswa asli)

## Keunikan & Niche

- Fokus spesifik pada ekosistem kampus/mahasiswa, bukan platform freelance umum
- Model ekonomi non-uang (time banking) yang jarang diterapkan di produk lokal
- Murni komunitas mandiri, tidak memerlukan integrasi sistem eksternal

## SDG Alignment

- **SDG 4** — Quality Education: akses ke bantuan belajar/skill
- **SDG 8** — Decent Work: praktik tukar jasa yang adil tanpa eksploitasi
- **SDG 10** — Reduced Inequalities: mahasiswa kurang mampu tetap bisa mendapat bantuan skill tanpa uang

## Tech Stack (Usulan)

**Frontend**
- Next.js (React)
- Tailwind CSS

**Backend & Database**
- Supabase (PostgreSQL + Auth + Realtime)
- Row-level security untuk transaksi kredit yang aman

**Fitur Teknis Kunci**
- Sistem kredit berbasis ledger (tabel `transactions` terpisah, bukan sekadar kolom angka) agar ada histori dan tidak bisa dimanipulasi
- Verifikasi kampus melalui validasi domain email saat signup
- Status transaksi bertahap: `pending → confirmed_by_A → confirmed_by_B → completed`
- Search & filter kategori skill untuk matching (tanpa algoritma AI kompleks di tahap awal)

## 📂 Status Proyek

Tahap: Ideation / Proposal

## 📄 Lisensi

Belum ditentukan.
