# Draft Isian Form Pendaftaran INOTEK Award 2026
**Inovasi**: e-Ticket Sarangan

Berikut adalah rumusan jawaban untuk mengisi form pendaftaran INOTEK Award berdasarkan rancangan sistem e-Ticket Sarangan:

---

### 1. Latar Belakang (Permasalahan)
Tingginya animo masyarakat untuk mengunjungi Telaga Sarangan seringkali memicu antrean panjang dan kemacetan di pintu masuk akibat proses transaksi tiket yang masih manual. Selain itu, penggunaan tiket fisik (kertas) rentan terhadap pemalsuan, penggunaan ulang, dan memicu penumpukan sampah. Dari sisi Pemerintah Daerah, pencatatan manual mengakibatkan data pengunjung tidak dapat dipantau secara *real-time* dan membuka peluang terjadinya selisih perhitungan atau kebocoran Pendapatan Asli Daerah (PAD).

### 2. Kondisi Sebelum Inovasi
Sebelum adanya inovasi ini, wisatawan harus mengantre lama di loket tiket dan membayar secara tunai. Petugas di pintu gerbang harus menyobek tiket kertas satu per satu yang sangat memakan waktu. Sementara itu, Pemerintah Daerah (Dinas Pariwisata/BAPPERIDA) baru dapat mengetahui total jumlah kunjungan dan pendapatan setelah dilakukan rekapitulasi manual di penghujung hari atau minggu, sehingga pengawasan terhadap PAD sangat lemah dan tertinggal.

### 3. Sasaran dan Tujuan Inovasi
**Sasaran:** Wisatawan, Petugas Lapangan, dan Pemerintah Daerah Kabupaten Magetan.
**Tujuan:**
1. Mengurai kemacetan loket dengan menyediakan layanan pemesanan tiket 100% online kapan saja dan di mana saja.
2. Mempermudah dan mempercepat tugas petugas lapangan dalam memvalidasi pengunjung cukup dengan pemindaian (*scan*) QR Code.
3. Mewujudkan transparansi serta mengamankan Pendapatan Asli Daerah (PAD) melalui *Dashboard Analitik* yang menyajikan data secara *real-time* dan akurat.

---
### 4. Materi Inovasi

**a. Deskripsi**
e-Ticket Sarangan adalah platform digitalisasi pariwisata berbasis web responsif yang mencakup sistem pembelian tiket masuk secara online, pembayaran nontunai (*cashless*), serta sistem validasi mandiri menggunakan teknologi QR Code terenkripsi yang saling terintegrasi langsung dengan *Dashboard* pemantauan Pemerintah Daerah.

**b. Bahan Baku**
Karena inovasi ini berupa perangkat lunak (*software*), bahan baku yang digunakan adalah:
* Bahasa Pemrograman & Framework: Laravel (Sistem Backend) dan Vue.js (Sistem Frontend/Antarmuka).
* Sistem Database: MySQL / PostgreSQL.
* Integrasi Pihak Ketiga: Payment Gateway (untuk menerima pembayaran QRIS, Virtual Account, dll).
* Perangkat Keras pendukung: *Smartphone* standar (untuk operasional pemindaian/ *scan* oleh petugas di lapangan).

**c. Cara Kerja**
1. **Pemesanan**: Wisatawan mengakses *website* melalui peramban (browser) di HP/PC tanpa perlu *install* aplikasi. Mereka memilih tiket, tanggal kunjungan, dan membayar secara *cashless*.
2. **Distribusi Tiket**: Sistem secara otomatis menerbitkan e-ticket berupa QR Code unik kepada wisatawan.
3. **Validasi**: Saat tiba di gerbang, wisatawan menunjukkan QR Code. Petugas menggunakan menu *Scanner* di sistem untuk memindai QR Code tersebut.
4. **Sinkronisasi**: Setelah dipindai, status tiket otomatis berubah menjadi 'Sudah Digunakan' sehingga tidak bisa dipakai ulang, dan data pendapatan tersebut detik itu juga langsung masuk ke *Dashboard* Pemerintah Daerah.

**d. Keunggulan**
* **Aman & Anti-Duplikasi:** QR Code bersifat unik dan tidak dapat di-*scan* dua kali oleh orang yang berbeda.
* **Sangat Praktis:** Berbasis web responsif (*Web App*), sehingga ramah memori HP karena pengunjung tidak diwajibkan mengunduh aplikasi di PlayStore/AppStore.
* **Transparansi Absolut:** Sistem pencatatan pendapatan yang tidak bisa dimanipulasi (*zero leakage*).
* **Ramah Lingkungan:** Meniadakan limbah tiket kertas (*paperless*).

**e. Hasil yang Diharapkan**
Terciptanya ekosistem pariwisata Magetan yang modern dan bebas hambatan antrean. Validasi di pintu masuk dapat dipangkas menjadi hitungan detik per kendaraan, serta jaminan peningkatan Pendapatan Asli Daerah (PAD) seiring dengan tertutupnya segala celah kebocoran tiket fisik.

**f. Manfaat Bagi Masyarakat / Lingkungan**
Bagi masyarakat (wisatawan), mereka mendapatkan kemudahan dan kenyamanan ekstra dalam merencanakan liburan tanpa takut kehabisan tiket atau terjebak antrean loket. Bagi lingkungan, inovasi ini menekan produksi limbah/sampah kertas yang sebelumnya dihasilkan dari ribuan sobekan tiket setiap harinya di area Telaga Sarangan.

**g. Rencana Keberlanjutan**
Sistem ini dirancang dengan skalabilitas (kemampuan pengembangan) yang tinggi. Setelah sukses diterapkan di Telaga Sarangan, sistem ini akan dengan sangat mudah direplikasi atau diterapkan di seluruh titik wisata lain di bawah naungan Kabupaten Magetan (seperti Mojosemi, Kebun Refugia, dll). Ke depannya, inovasi ini juga akan diintegrasikan dengan fitur pemesanan penginapan/hotel dan lapak UMKM lokal dalam satu wadah (*Super App* Pariwisata Magetan).
