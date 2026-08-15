<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lainnya - LGI Store</title>
    @vite(['resources/css/guest/other-info.css', 'resources/css/components/footer.css'])
</head>
<body>
    <x-navbar />

    <div class="breadcrumb">
        <a href="{{ route('home') }}">← Kembali ke Beranda</a>
    </div>

    <div class="other-info-container">
        <div class="info-header">
            <h1>Informasi Lainnya</h1>
            <p>Temukan informasi penting seputar LGI Store</p>
        </div>

        <div class="info-grid">
            <!-- FAQ Card -->
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <h2>FAQ</h2>
                <p>Pertanyaan yang sering diajukan seputar produk dan layanan kami</p>
                <div class="info-content">
                    <div class="faq-item">
                        <h3>Bagaimana cara memesan produk custom?</h3>
                        <p>Anda dapat mengunjungi halaman produk, pilih opsi "Custom Design", lalu upload desain Anda atau konsultasi dengan tim kami.</p>
                    </div>
                    <div class="faq-item">
                        <h3>Berapa lama proses pengerjaan?</h3>
                        <p>Untuk produk ready stock: 1-3 hari kerja. Untuk produk custom: 7-14 hari kerja tergantung tingkat kesulitan.</p>
                    </div>
                    <div class="faq-item">
                        <h3>Apakah tersedia layanan COD?</h3>
                        <p>Ya, kami menyediakan layanan COD untuk area tertentu. Silahkan cek saat checkout.</p>
                    </div>
                </div>
            </div>

            <!-- Syarat & Ketentuan Card -->
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <h2>Syarat & Ketentuan</h2>
                <p>Ketentuan penggunaan layanan LGI Store</p>
                <div class="info-content">
                    <div class="term-item">
                        <h3>Pemesanan</h3>
                        <ul>
                            <li>Pemesanan dianggap sah setelah pembayaran dikonfirmasi</li>
                            <li>Minimal pemesanan untuk produk custom adalah 10 pcs</li>
                            <li>Perubahan pesanan hanya dapat dilakukan sebelum proses produksi dimulai</li>
                        </ul>
                    </div>
                    <div class="term-item">
                        <h3>Pembayaran</h3>
                        <ul>
                            <li>Pembayaran dapat dilakukan via transfer bank atau e-wallet</li>
                            <li>DP minimal 50% untuk pesanan custom</li>
                            <li>Pelunasan dilakukan sebelum pengiriman</li>
                        </ul>
                    </div>
                    <div class="term-item">
                        <h3>Pengembalian</h3>
                        <ul>
                            <li>Produk custom tidak dapat ditukar atau dikembalikan</li>
                            <li>Produk ready stock dapat dikembalikan dalam 3 hari jika ada cacat produksi</li>
                            <li>Komplain disertai foto dan video unboxing</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Kebijakan Privasi Card -->
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2>Kebijakan Privasi</h2>
                <p>Perlindungan data pribadi pelanggan</p>
                <div class="info-content">
                    <div class="privacy-item">
                        <h3>Pengumpulan Data</h3>
                        <p>Kami mengumpulkan data pribadi seperti nama, alamat, email, dan nomor telepon untuk keperluan pengiriman dan komunikasi.</p>
                    </div>
                    <div class="privacy-item">
                        <h3>Penggunaan Data</h3>
                        <p>Data pelanggan hanya digunakan untuk memproses pesanan, pengiriman, dan komunikasi terkait layanan kami.</p>
                    </div>
                    <div class="privacy-item">
                        <h3>Keamanan Data</h3>
                        <p>Kami menggunakan enkripsi dan sistem keamanan untuk melindungi data pribadi Anda dari akses tidak sah.</p>
                    </div>
                </div>
            </div>

            <!-- Panduan Ukuran Card -->
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-ruler"></i>
                </div>
                <h2>Panduan Ukuran</h2>
                <p>Panduan memilih ukuran yang tepat</p>
                <div class="info-content">
                    <div class="size-guide">
                        <h3>Ukuran Kaos & Jersey</h3>
                        <table class="size-table">
                            <thead>
                                <tr>
                                    <th>Ukuran</th>
                                    <th>Lebar (cm)</th>
                                    <th>Panjang (cm)</th>
                                    <th>Berat Badan (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>S</td>
                                    <td>45</td>
                                    <td>65</td>
                                    <td>45-55</td>
                                </tr>
                                <tr>
                                    <td>M</td>
                                    <td>48</td>
                                    <td>68</td>
                                    <td>55-65</td>
                                </tr>
                                <tr>
                                    <td>L</td>
                                    <td>51</td>
                                    <td>71</td>
                                    <td>65-75</td>
                                </tr>
                                <tr>
                                    <td>XL</td>
                                    <td>54</td>
                                    <td>74</td>
                                    <td>75-85</td>
                                </tr>
                                <tr>
                                    <td>XXL</td>
                                    <td>57</td>
                                    <td>77</td>
                                    <td>85-95</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="size-note"><i class="fas fa-info-circle"></i> Toleransi ukuran ±2cm</p>
                    </div>
                </div>
            </div>

            <!-- Cara Perawatan Card -->
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-tshirt"></i>
                </div>
                <h2>Cara Perawatan</h2>
                <p>Tips merawat produk agar awet</p>
                <div class="info-content">
                    <div class="care-item">
                        <h3><i class="fas fa-water"></i> Mencuci</h3>
                        <ul>
                            <li>Cuci dengan air dingin atau suhu maksimal 30°C</li>
                            <li>Pisahkan warna gelap dan terang</li>
                            <li>Hindari penggunaan pemutih</li>
                            <li>Balik pakaian sebelum dicuci untuk melindungi sablon</li>
                        </ul>
                    </div>
                    <div class="care-item">
                        <h3><i class="fas fa-wind"></i> Mengeringkan</h3>
                        <ul>
                            <li>Jemur di tempat teduh, hindari sinar matahari langsung</li>
                            <li>Jangan gunakan mesin pengering</li>
                            <li>Gantung dengan hanger untuk hasil lebih rapi</li>
                        </ul>
                    </div>
                    <div class="care-item">
                        <h3><i class="fas fa-fire"></i> Menyetrika</h3>
                        <ul>
                            <li>Setrika dengan suhu rendah-sedang</li>
                            <li>Hindari menyetrika langsung pada sablon</li>
                            <li>Gunakan kain pelapis saat menyetrika</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Kontak Kami Card -->
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h2>Hubungi Kami</h2>
                <p>Butuh bantuan? Kami siap membantu Anda</p>
                <div class="info-content">
                    <div class="contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <div>
                            <h3>Telepon</h3>
                            <p>+62 821 xxxx xxxx</p>
                            <small>Senin - Sabtu: 08.00 - 17.00 WIB</small>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fab fa-whatsapp"></i>
                        <div>
                            <h3>WhatsApp</h3>
                            <p>+62 821 xxxx xxxx</p>
                            <small>Fast Response 24/7</small>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h3>Email</h3>
                            <p>info@lgistore.com</p>
                            <small>Respon dalam 1x24 jam</small>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h3>Alamat</h3>
                            <p>Jl. Raya Lampung No. 123, Bandar Lampung</p>
                            <small>Buka Senin - Sabtu: 08.00 - 17.00 WIB</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-guest-footer />
</body>
</html>
