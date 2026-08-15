<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tentang Sablon Topi Lampung - Spesialis sablon satuan, jersey custom, dan apparel berkualitas dari Lampung sejak 2012.">
    <title>Tentang Kami - Sablon Topi Lampung | LGI Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/guest/about.css', 'resources/css/components/navbar.css', 'resources/css/components/footer.css'])
</head>
<body>
    <x-navbar />

    <!-- Hero Section with Profile Image -->
    <section class="about-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-image">
                    <img src="{{ asset('images/profil.png') }}" alt="Sablon Topi Lampung Profile">
                </div>
                <div class="hero-text">
                    <span class="hero-badge">Sejak 2012</span>
                    <h1>Sablon Topi Lampung</h1>
                    <p>Spesialis Sablon Satuan & Jersey Custom Terpercaya di Lampung</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Introduction Section -->
    <section class="about-section">
        <div class="container">
            <div class="intro-content">
                <h2>Selamat Datang</h2>
                <p>
                    Kami adalah <strong>spesialis sablon satuan, jersey custom, dan apparel berkualitas</strong> yang telah melayani 
                    ribuan pelanggan dari berbagai kalangan sejak tahun 2012. Dari komunitas olahraga, tim futsal, hingga kebutuhan 
                    seragam kantor dan event – kami siap mewujudkan desain impian Anda.
                </p>
                <p>
                    Berlokasi di <strong>Bandar Lampung</strong>, kami bangga menjadi bagian dari industri kreatif lokal dengan 
                    dua outlet strategis yang siap melayani Anda.
                </p>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="about-section">
        <div class="container">
            <div class="section-header">
                <h2>Layanan Kami</h2>
                <p>Berbagai produk dan layanan untuk memenuhi kebutuhan Anda</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <i class="fas fa-hat-cowboy"></i>
                    <h3>Sablon Topi</h3>
                    <p>Sablon topi satuan dengan teknik DTF, Flex, Flock. Hasil rapi dan tahan lama.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-tshirt"></i>
                    <h3>Jersey Custom</h3>
                    <p>Jersey tim olahraga dengan desain custom full print dan bahan premium.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-child"></i>
                    <h3>Kids Jersey</h3>
                    <p>Jersey anak-anak dengan ukuran lengkap untuk tim junior.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-users"></i>
                    <h3>Apparel Komunitas</h3>
                    <p>Kaos, hoodie, dan jaket custom untuk komunitas atau event.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-palette"></i>
                    <h3>Custom Design</h3>
                    <p>Tim desainer siap membantu mewujudkan ide Anda.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-truck"></i>
                    <h3>Pengiriman</h3>
                    <p>Melayani pengiriman ke seluruh Indonesia dengan aman.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Advantages Section -->
    <section class="about-section alt-bg">
        <div class="container">
            <div class="section-header">
                <h2>Mengapa Memilih Kami</h2>
            </div>
            <div class="advantages-grid">
                <div class="advantage-item">
                    <i class="fas fa-medal"></i>
                    <h3>Kualitas Premium</h3>
                    <p>Bahan berkualitas tinggi dan teknik sablon terbaik untuk hasil tahan lama.</p>
                </div>
                <div class="advantage-item">
                    <i class="fas fa-bolt"></i>
                    <h3>Pengerjaan Cepat</h3>
                    <p>Proses produksi efisien dengan estimasi waktu yang jelas.</p>
                </div>
                <div class="advantage-item">
                    <i class="fas fa-heart"></i>
                    <h3>Pelayanan Ramah</h3>
                    <p>Tim siap membantu dari konsultasi desain hingga after-sales.</p>
                </div>
                <div class="advantage-item">
                    <i class="fas fa-shield-halved"></i>
                    <h3>Terpercaya</h3>
                    <p>Lebih dari satu dekade pengalaman dan ribuan pelanggan puas.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Locations Section -->
    <section class="about-section">
        <div class="container">
            <div class="section-header">
                <h2>Lokasi Toko</h2>
                <p>Kunjungi outlet kami di Bandar Lampung</p>
            </div>
            <div class="locations-grid">
                <div class="location-card">
                    <div class="location-header">
                        <i class="fas fa-store"></i>
                        <span class="location-badge">Pusat</span>
                    </div>
                    <h3>LGI SPORT</h3>
                    <p class="location-address">
                        Jl. Bumi Manti 2 – Sebelah Musholla Al-Huda<br>
                        Labuhanratu, Kampung Baru Unila, Bandar Lampung
                    </p>
                    <a href="https://maps.app.goo.gl/dwz2Sfr1knfwRmH68" target="_blank" class="location-link">
                        <i class="fas fa-map-marker-alt"></i> Lihat di Maps
                    </a>
                </div>
                <div class="location-card">
                    <div class="location-header">
                        <i class="fas fa-store-alt"></i>
                        <span class="location-badge">Cabang</span>
                    </div>
                    <h3>LGI STORE</h3>
                    <p class="location-address">
                        Jl. Ratu Dibalau – Seberang Panglong Kayu Jaya Abadi<br>
                        Way Kandis, Bandar Lampung
                    </p>
                    <a href="https://maps.app.goo.gl/x3ArtX4xwgp1MMKf7" target="_blank" class="location-link">
                        <i class="fas fa-map-marker-alt"></i> Lihat di Maps
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="about-section alt-bg">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <h2>Hubungi Kami</h2>
                    <div class="contact-items">
                        <div class="contact-item">
                            <i class="fab fa-whatsapp"></i>
                            <div>
                                <span class="contact-label">WhatsApp</span>
                                <a href="https://wa.me/{{ str_replace('+', '', env('ADMIN_WHATSAPP_NUMBER', '62895085858888')) }}">{{ env('ADMIN_WHATSAPP_NUMBER_DISPLAY', '0895-0858-5888') }}</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <span class="contact-label">Email</span>
                                <a href="mailto:bfaster21@gmail.com">bfaster21@gmail.com</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fab fa-instagram"></i>
                            <div>
                                <span class="contact-label">Instagram</span>
                                <a href="https://instagram.com/sablontopilampung" target="_blank">@sablontopilampung</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hours-info">
                    <h2>Jam Operasional</h2>
                    <div class="hours-list">
                        <div class="hours-item">
                            <span>Senin - Sabtu</span>
                            <span>08.00 - 21.00 WIB</span>
                        </div>
                        <div class="hours-item">
                            <span>Minggu</span>
                            <span>12.00 - 21.00 WIB</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="about-section cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Siap Mewujudkan Desain Impian Anda?</h2>
                <p>Konsultasikan kebutuhan sablon dan apparel Anda sekarang!</p>
                <div class="cta-buttons">
                    <a href="https://wa.me/{{ str_replace('+', '', env('ADMIN_WHATSAPP_NUMBER', '62895085858888')) }}" class="cta-btn primary" target="_blank">
                        <i class="fab fa-whatsapp"></i> Chat WhatsApp
                    </a>
                    <a href="{{ route('home') }}" class="cta-btn secondary">
                        <i class="fas fa-shopping-bag"></i> Lihat Katalog
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <x-guest-footer />

    <!-- Unified Chatbot Popup - Only show for logged in customers -->
    @auth('web')
        @if(!auth()->guard('admin')->check())
            <x-unified-chatbot-popup />
        @endif
    @endauth
</body>
</html>
