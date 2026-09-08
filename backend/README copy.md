<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

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

<<<<<<< HEAD
=======

>>>>>>> 128d39d080353a592cd69938180c97389d195693
## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


