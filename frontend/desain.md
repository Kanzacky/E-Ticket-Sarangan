# DESAIN.md — e-Ticket Sarangan

## 1. Tujuan Desain

e-Ticket Sarangan adalah platform pemesanan tiket wisata yang dibuat untuk kebutuhan **INOTEK Award 2026**. Desain harus terasa seperti produk digital wisata yang benar-benar dirancang untuk digunakan, bukan template dashboard/landing page hasil generator AI.

Prinsip utama:

- Natural dan manusiawi.
- Elegan tetapi tidak berlebihan.
- Memiliki identitas destinasi Sarangan.
- Fokus pada pengalaman wisata dan kemudahan booking.
- Visual hierarchy jelas.
- Tidak menggunakan dekorasi hanya untuk memenuhi ruang.
- Tidak menggunakan terlalu banyak gradient, glassmorphism, shadow, rounded card, atau icon.
- Tidak membuat semua section terlihat sama.
- Tidak menggunakan copywriting generik seperti "Revolutionize your experience", "The future of...", atau kalimat AI-slop lainnya.

---

## 2. Arah Visual

### Karakter

**Natural tourism × modern digital service × premium local experience**

Nuansa yang ingin dicapai:

- Danau dan pegunungan.
- Udara sejuk.
- Wisata keluarga.
- Profesional dan terpercaya.
- Modern tanpa kehilangan karakter lokal.

Hindari tampilan:

- SaaS startup generik.
- Dashboard AI.
- Landing page penuh gradient ungu/biru.
- Neon/cyberpunk.
- Terlalu banyak floating cards.
- Terlalu banyak glassmorphism.
- Semua elemen berbentuk pill.

---

## 3. Warna

Gunakan palet yang terinspirasi dari lingkungan Sarangan.

### Primary

Hijau gelap / forest:

`#173B35`

Digunakan untuk:
- Header
- CTA utama
- Heading tertentu
- Elemen brand

### Secondary

Hijau natural:

`#4F7465`

Digunakan sebagai aksen.

### Background

Off-white hangat:

`#F7F5EF`

Bukan putih murni untuk seluruh halaman.

### Surface

`#FFFFFF`

Digunakan untuk area konten dan komponen penting.

### Text

Primary:

`#1D2724`

Secondary:

`#66706C`

Muted:

`#8B928F`

### Accent

Gunakan aksen hangat secara terbatas:

`#C9965B`

Hanya untuk:
- highlight
- harga
- dekorasi kecil
- status tertentu

Jangan menggunakan banyak warna aksen sekaligus.

---

## 4. Typography

Gunakan maksimal dua keluarga font.

Rekomendasi:

- Heading: **Plus Jakarta Sans** atau **Manrope**
- Body: **Inter** atau **Plus Jakarta Sans**

Karakter:

- Heading kuat tetapi tidak terlalu besar.
- Body nyaman dibaca.
- Hindari heading seluruhnya uppercase.
- Hindari font display yang terlalu dekoratif.

Skala awal:

- Hero heading: 48–64px desktop
- Section heading: 32–42px
- Card heading: 18–22px
- Body: 15–17px
- Small text: 13–14px

Mobile harus memiliki skala yang proporsional, bukan sekadar mengecilkan desktop.

---

## 5. Layout

Gunakan container utama:

`max-width: 1180–1240px`

Horizontal padding:

- Desktop: 32px
- Tablet: 24px
- Mobile: 20px

Gunakan whitespace yang cukup.

Jangan membuat setiap section penuh dengan card.

Gunakan kombinasi:

- full-width image
- split layout
- editorial content
- cards
- statistics
- CTA
- whitespace

Agar ritme halaman tidak monoton.

---

## 6. Navbar

Navbar harus sederhana.

Struktur:

**Logo e-Ticket Sarangan**

Menu:

- Beranda
- Tiket
- Cara Pesan
- Tentang Sarangan

Aksi:

- Masuk
- Pesan Tiket

Saat scroll, navbar boleh menjadi sticky dengan background solid/translucent ringan.

Hindari navbar besar dengan terlalu banyak tombol.

---

## 7. Hero Landing Page

Hero adalah area paling penting.

Gunakan fotografi Sarangan yang kuat sebagai visual utama.

Komposisi yang disarankan:

Kiri:
- eyebrow kecil
- headline
- deskripsi singkat
- CTA

Kanan atau background:
- foto Danau Sarangan/pegunungan

Contoh copy:

Eyebrow:

`WISATA SARANGAN`

Headline:

`Nikmati Sarangan, mulai perjalananmu dari sini.`

Deskripsi:

`Pesan tiket kunjungan dengan mudah, tentukan tanggal kedatangan, dan simpan e-ticket langsung di perangkatmu.`

CTA utama:

`Pesan Tiket`

CTA sekunder:

`Lihat Informasi`

Jangan menggunakan hero dengan terlalu banyak floating card.

---

## 8. Trust / Information Strip

Setelah hero, tampilkan informasi singkat yang membantu keputusan pengguna.

Contoh:

- Tiket digital
- Booking online
- QR check-in
- Pembayaran aman

Gunakan layout horizontal sederhana.

Jangan menggunakan empat card besar yang semuanya memiliki icon lingkaran.

---

## 9. Section Pengalaman Sarangan

Gunakan layout editorial.

Contoh:

Kiri:
foto landscape Sarangan.

Kanan:
heading:

`Satu tempat, banyak cerita.`

Copy singkat mengenai:
- danau
- pegunungan
- suasana sejuk
- wisata keluarga

Gunakan foto sebagai elemen visual utama, bukan dekorasi tambahan.

---

## 10. Ticket Preview

Tampilkan jenis tiket yang tersedia.

Contoh:

### Dewasa

`Rp20.000`

### Anak

`Rp10.000`

Gunakan card sederhana dengan hierarki:

Nama → deskripsi → harga → CTA.

Harga harus berasal dari API/backend ketika data sudah tersedia.

Jangan hardcode harga pada production UI.

CTA:

`Pilih Tiket`

---

## 11. How It Works

Buat 3 atau 4 langkah.

1. Pilih tanggal
2. Pilih tiket
3. Bayar
4. Scan QR saat tiba

Gunakan nomor langkah dan garis/alur sederhana.

Hindari icon besar dan card yang terlalu dekoratif.

---

## 12. Visual Storytelling

Landing page harus memiliki minimal satu section yang terasa seperti editorial/travel magazine.

Contoh:

Foto besar + caption:

`Danau Sarangan`

`Udara pegunungan, air yang tenang, dan suasana yang membuat perjalanan terasa lebih lambat.`

Tujuannya memberi identitas lokal pada website.

---

## 13. CTA Akhir

Gunakan section CTA yang sederhana dan kuat.

Headline:

`Siap menikmati Sarangan?`

Subtext:

`Pesan tiketmu sebelum berangkat.`

Button:

`Pesan Tiket`

Jangan membuat CTA penuh gradient atau terlalu banyak ornamen.

---

## 14. Footer

Footer minimal:

- Logo
- Deskripsi singkat
- Navigasi
- Bantuan
- Kontak
- Copyright

Tambahkan informasi:

`e-Ticket Sarangan — Sistem Tiket Wisata Digital`

Jika alamat/kontak resmi belum tersedia, jangan mengarang data.

---

## 15. Image Direction

Prioritaskan foto asli atau foto destinasi yang realistis.

Jenis visual:

- Danau Sarangan
- Pegunungan
- Jalan/lingkungan sekitar
- Aktivitas wisata
- Detail alam

Hindari:
- stock photo yang terlalu generik
- gambar AI yang terlihat tidak natural
- foto dengan watermark
- terlalu banyak gambar dengan treatment berbeda

Semua gambar harus memiliki aspect ratio yang konsisten dan crop yang terkontrol.

---

## 16. UI Components

### Button

Primary:
- solid forest green
- radius 8–10px
- tinggi 44–48px

Secondary:
- outline atau subtle surface

Jangan semua button berbentuk pill.

### Card

- radius 12–16px
- border tipis
- shadow sangat halus jika diperlukan
- padding proporsional

Tidak setiap elemen harus menjadi card.

### Input

- height sekitar 44–48px
- border jelas
- focus state jelas
- label selalu terlihat

### Badge

Gunakan hanya untuk status.

Contoh:

`Tersedia`
`PENDING`
`PAID`
`USED`

---

## 17. Motion

Animasi harus subtle.

Gunakan:

- fade
- slide pendek
- hover transition
- image scale kecil

Durasi sekitar 150–300ms.

Hindari:
- animasi berulang tanpa alasan
- floating animation di semua elemen
- parallax berlebihan
- entrance animation pada setiap section

---

## 18. Responsive

### Desktop

Gunakan layout editorial dan whitespace luas.

### Tablet

Pertahankan hierarchy, kurangi kolom.

### Mobile

Prioritaskan:

1. Hero
2. CTA
3. Tiket
4. Informasi
5. Cara booking

Navbar berubah menjadi mobile menu.

CTA booking harus mudah ditemukan.

Tidak boleh ada horizontal overflow.

---

## 19. Accessibility

Wajib:

- semantic HTML
- alt text pada gambar
- keyboard navigation
- focus state
- contrast yang cukup
- button memiliki label jelas
- form memiliki label
- jangan menggunakan warna sebagai satu-satunya indikator status

---

## 20. Performance

- Optimalkan gambar.
- Lazy-load gambar di bawah fold.
- Hindari library UI besar jika tidak diperlukan.
- Hindari animasi berat.
- Jangan mengirim data API yang tidak digunakan.
- Pastikan landing page tetap cepat pada koneksi mobile.

---

## 21. Anti AI-Slop Rules

JANGAN:

- menggunakan gradient ungu/biru sebagai default.
- membuat semua section berupa rounded card.
- menambahkan banyak icon hanya agar terlihat "modern".
- menggunakan glassmorphism di semua komponen.
- membuat heading terlalu besar sampai memenuhi layar.
- menggunakan 3–4 font berbeda.
- menggunakan copywriting generik.
- membuat statistik palsu.
- membuat review/testimonial palsu.
- membuat penghargaan/logo partner yang belum ada.
- mengarang alamat, nomor telepon, jumlah pengunjung, rating, atau data wisata.
- membuat elemen dekoratif tanpa fungsi.
- mengulang pola card yang sama di seluruh halaman.

PRINSIP:

> Jika sebuah elemen tidak membantu pengguna memahami Sarangan, memilih tiket, atau melakukan booking, pertimbangkan untuk menghapusnya.

---

## 22. Data & Backend

Landing page harus siap terhubung ke API.

Gunakan:

`VITE_API_URL`

Jangan hardcode API URL di banyak tempat.

Data tiket dari:

`GET /api/ticket-types`

Jangan membuat data harga palsu ketika API belum tersedia.

Jika API gagal:
- tampilkan fallback UI yang informatif
- jangan menampilkan data palsu
- jangan membuat error teknis terlihat oleh user

---

## 23. Definition of Done

Landing page dianggap selesai jika:

- terlihat seperti website wisata yang dirancang manusia.
- identitas Sarangan terasa.
- CTA booking jelas.
- responsive.
- tidak ada horizontal overflow.
- tidak ada console error yang disebabkan perubahan baru.
- tidak ada fake data.
- tidak ada broken image.
- API configuration tetap aman.
- build production berhasil.
- halaman login/register/booking existing tidak rusak.
