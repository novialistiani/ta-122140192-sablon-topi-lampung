<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profil - LGI Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite([
        'resources/css/customer/shared.css', 
        'resources/css/customer/profile-form.css', 
        'resources/js/customer/profile-dropdown.js',
        'resources/js/components/notification-dropdown.js'
    ])
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <x-customer-sidebar active="profile" />

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <x-customer-header title="Profile" />

            <div class="content-wrapper">
                <!-- 2 Column Layout -->
                <div class="profile-layout">
                    <!-- Left Column -->
                    <div class="profile-left-column">
                        <!-- Profile Photo -->
                        <div class="profile-card">
                            <h3 class="card-title">Foto Profil</h3>
                            <div class="photo-upload-section">
                                <div class="photo-preview-large">
                                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}" 
                                         alt="Avatar" 
                                         id="avatar-preview"
                                         style="{{ $user->avatar ? '' : 'display: none;' }}">
                                    <div class="no-photo-large" id="no-avatar-placeholder" style="{{ $user->avatar ? 'display: none;' : '' }}">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="photo-actions-centered">
                                    <label for="avatar-input" class="btn-upload">
                                        <i class="fas fa-camera"></i> Upload Foto
                                    </label>
                                    <input type="file" id="avatar-input" accept="image/*" style="display: none;">
                                    <button type="button" id="delete-avatar-btn" class="btn-delete" style="{{ $user->avatar ? '' : 'display: none;' }}">
                                        <i class="fas fa-trash"></i> Hapus Foto
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Info -->
                        <div class="profile-card">
                            <h3 class="card-title">Informasi Dasar</h3>
                            <form id="profile-form">
                                @csrf
                                
                                <div class="form-group">
                                    <label for="name">Nama Lengkap *</label>
                                    <input type="text" id="name" name="name" value="{{ $user->name }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" value="{{ $user->email }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="phone">No. Telepon</label>
                                    <input type="tel" id="phone" name="phone" value="{{ $user->phone }}" placeholder="081234567890">
                                </div>
                            </form>
                        </div>

                        <!-- Change Password -->
                        <div class="profile-card">
                            <h3 class="card-title">Keamanan Akun</h3>
                            <form id="password-form">
                                @csrf
                                
                                <div class="form-group">
                                    <label for="current_password">Password Saat Ini *</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password Baru *</label>
                                    <input type="password" id="password" name="password" required minlength="8">
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation">Konfirmasi Password Baru *</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn-save btn-full">
                                        <i class="fas fa-key"></i> Ubah Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="profile-right-column">
                        <!-- Address Management -->
                        <div class="profile-card">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="card-title mb-0">Alamat Pengiriman</h3>
                                <button type="button" onclick="showAddAddressModal()" class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-medium rounded-lg transition text-sm">
                                    <i class="fas fa-plus mr-2"></i>Tambah Alamat
                                </button>
                            </div>

                            @if($user->addresses && $user->addresses->count() > 0)
                                <div class="space-y-3" id="addresses-list">
                                    @foreach($user->addresses as $address)
                                    <div class="border border-slate-200 rounded-lg p-4" data-address-id="{{ $address->id }}">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <p class="font-semibold text-slate-900">{{ $address->label ?? $address->recipient_name }}</p>
                                                    @if($address->is_primary)
                                                    <span class="px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">Utama</span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-slate-600">{{ $address->recipient_name }}</p>
                                                <p class="text-sm text-slate-600">{{ $address->phone }}</p>
                                                <p class="text-sm text-slate-600 mt-1">{{ $address->address }}, {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</p>
                                            </div>
                                            <div class="flex gap-2">
                                                @if(!$address->is_primary)
                                                <button onclick="setPrimaryAddress({{ $address->id }})" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                                    Jadikan Utama
                                                </button>
                                                @endif
                                                <button onclick="editAddress({{ $address->id }})" class="text-sm text-blue-600 hover:text-blue-700">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="deleteAddress({{ $address->id }})" class="text-sm text-red-600 hover:text-red-700">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-map-marker-alt text-4xl text-gray-300 mb-3"></i>
                                    <p class="text-gray-600">Belum ada alamat pengiriman</p>
                                    <button type="button" onclick="showAddAddressModal()" class="mt-4 px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-medium rounded-lg transition text-sm">
                                        <i class="fas fa-plus mr-2"></i>Tambah Alamat
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="loading-overlay" class="loading-overlay" style="display: none;">
        <div class="spinner"></div>
    </div>

    <!-- Address Modal -->
    <div id="addressModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto" style="backdrop-filter: blur(4px);">
        <div class="flex items-center justify-center min-h-screen p-4 sm:p-6">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-auto my-8">
                <div class="p-6 max-h-[90vh] overflow-y-auto">
                    <h2 class="text-xl font-bold text-slate-900 mb-4" id="modalTitle">Tambah Alamat</h2>
                    <form id="addressForm">
                        <input type="hidden" id="addressId" name="address_id">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Label Alamat</label>
                                <input type="text" id="addressLabel" name="label" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent" placeholder="Rumah, Kantor, dll">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Nama Penerima *</label>
                                <input type="text" id="recipientName" name="recipient_name" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">No. Telepon *</label>
                                <input type="tel" id="addressPhone" name="phone" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent" placeholder="08xxxxxxxxxx">
                            </div>
                            
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Alamat Lengkap *</label>
                                <textarea id="fullAddress" name="address" required rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Provinsi *</label>
                                <select id="addressProvince" name="province" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                                    <option value="">Pilih Provinsi</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Kota/Kabupaten *</label>
                                <select id="addressCity" name="city" required disabled class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent bg-slate-100">
                                    <option value="">Pilih Provinsi Terlebih Dahulu</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Kode Pos</label>
                                <input type="text" id="addressPostalCode" name="postal_code" maxlength="5" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent" placeholder="12345">
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" id="isPrimary" name="is_primary" class="h-4 w-4 rounded border-slate-300 text-yellow-400 focus:ring-yellow-400">
                                <label for="isPrimary" class="ml-2 text-sm text-slate-700">Jadikan alamat utama</label>
                            </div>
                        </div>
                        
                        <div class="flex gap-3 mt-6">
                            <button type="button" onclick="hideAddressModal()" class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 px-4 py-3 bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-medium rounded-xl transition">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let editingAddressId = null;

        // Data Provinsi dan Kota Indonesia
        const indonesiaRegions = {
            "Aceh": ["Banda Aceh", "Langsa", "Lhokseumawe", "Sabang", "Subulussalam", "Aceh Barat", "Aceh Barat Daya", "Aceh Besar", "Aceh Jaya", "Aceh Selatan", "Aceh Singkil", "Aceh Tamiang", "Aceh Tengah", "Aceh Tenggara", "Aceh Timur", "Aceh Utara", "Bener Meriah", "Bireuen", "Gayo Lues", "Nagan Raya", "Pidie", "Pidie Jaya", "Simeulue"],
            "Sumatera Utara": ["Medan", "Binjai", "Padangsidimpuan", "Pematangsiantar", "Sibolga", "Tanjungbalai", "Tebing Tinggi", "Asahan", "Batubara", "Dairi", "Deli Serdang", "Humbang Hasundutan", "Karo", "Labuhanbatu", "Labuhanbatu Selatan", "Labuhanbatu Utara", "Langkat", "Mandailing Natal", "Nias", "Nias Barat", "Nias Selatan", "Nias Utara", "Padang Lawas", "Padang Lawas Utara", "Pakpak Bharat", "Samosir", "Serdang Bedagai", "Simalungun", "Tapanuli Selatan", "Tapanuli Tengah", "Tapanuli Utara", "Toba Samosir"],
            "Sumatera Barat": ["Padang", "Bukittinggi", "Padangpanjang", "Pariaman", "Payakumbuh", "Sawahlunto", "Solok", "Agam", "Dharmasraya", "Kepulauan Mentawai", "Lima Puluh Kota", "Padang Pariaman", "Pasaman", "Pasaman Barat", "Pesisir Selatan", "Sijunjung", "Solok", "Solok Selatan", "Tanah Datar"],
            "Riau": ["Pekanbaru", "Dumai", "Bengkalis", "Indragiri Hilir", "Indragiri Hulu", "Kampar", "Kepulauan Meranti", "Kuantan Singingi", "Pelalawan", "Rokan Hilir", "Rokan Hulu", "Siak"],
            "Jambi": ["Jambi", "Sungai Penuh", "Batang Hari", "Bungo", "Kerinci", "Merangin", "Muaro Jambi", "Sarolangun", "Tanjung Jabung Barat", "Tanjung Jabung Timur", "Tebo"],
            "Sumatera Selatan": ["Palembang", "Lubuklinggau", "Pagar Alam", "Prabumulih", "Banyuasin", "Empat Lawang", "Lahat", "Muara Enim", "Musi Banyuasin", "Musi Rawas", "Musi Rawas Utara", "Ogan Ilir", "Ogan Komering Ilir", "Ogan Komering Ulu", "Ogan Komering Ulu Selatan", "Ogan Komering Ulu Timur", "Penukal Abab Lematang Ilir"],
            "Bengkulu": ["Bengkulu", "Bengkulu Selatan", "Bengkulu Tengah", "Bengkulu Utara", "Kaur", "Kepahiang", "Lebong", "Mukomuko", "Rejang Lebong", "Seluma"],
            "Lampung": ["Bandar Lampung", "Metro", "Lampung Barat", "Lampung Selatan", "Lampung Tengah", "Lampung Timur", "Lampung Utara", "Mesuji", "Pesawaran", "Pesisir Barat", "Pringsewu", "Tanggamus", "Tulang Bawang", "Tulang Bawang Barat", "Way Kanan"],
            "Kepulauan Bangka Belitung": ["Pangkalpinang", "Bangka", "Bangka Barat", "Bangka Selatan", "Bangka Tengah", "Belitung", "Belitung Timur"],
            "Kepulauan Riau": ["Batam", "Tanjungpinang", "Bintan", "Karimun", "Kepulauan Anambas", "Lingga", "Natuna"],
            "DKI Jakarta": ["Jakarta Barat", "Jakarta Pusat", "Jakarta Selatan", "Jakarta Timur", "Jakarta Utara", "Kepulauan Seribu"],
            "Jawa Barat": ["Bandung", "Banjar", "Bekasi", "Bogor", "Cimahi", "Cirebon", "Depok", "Sukabumi", "Tasikmalaya", "Bandung", "Bandung Barat", "Bekasi", "Bogor", "Ciamis", "Cianjur", "Cirebon", "Garut", "Indramayu", "Karawang", "Kuningan", "Majalengka", "Pangandaran", "Purwakarta", "Subang", "Sukabumi", "Sumedang", "Tasikmalaya"],
            "Jawa Tengah": ["Magelang", "Pekalongan", "Salatiga", "Semarang", "Surakarta", "Tegal", "Banjarnegara", "Banyumas", "Batang", "Blora", "Boyolali", "Brebes", "Cilacap", "Demak", "Grobogan", "Jepara", "Karanganyar", "Kebumen", "Kendal", "Klaten", "Kudus", "Magelang", "Pati", "Pekalongan", "Pemalang", "Purbalingga", "Purworejo", "Rembang", "Semarang", "Sragen", "Sukoharjo", "Tegal", "Temanggung", "Wonogiri", "Wonosobo"],
            "DI Yogyakarta": ["Yogyakarta", "Bantul", "Gunungkidul", "Kulon Progo", "Sleman"],
            "Jawa Timur": ["Batu", "Blitar", "Kediri", "Madiun", "Malang", "Mojokerto", "Pasuruan", "Probolinggo", "Surabaya", "Bangkalan", "Banyuwangi", "Blitar", "Bojonegoro", "Bondowoso", "Gresik", "Jember", "Jombang", "Kediri", "Lamongan", "Lumajang", "Madiun", "Magetan", "Malang", "Mojokerto", "Nganjuk", "Ngawi", "Pacitan", "Pamekasan", "Pasuruan", "Ponorogo", "Probolinggo", "Sampang", "Sidoarjo", "Situbondo", "Sumenep", "Trenggalek", "Tuban", "Tulungagung"],
            "Banten": ["Cilegon", "Serang", "Tangerang", "Tangerang Selatan", "Lebak", "Pandeglang", "Serang", "Tangerang"],
            "Bali": ["Denpasar", "Badung", "Bangli", "Buleleng", "Gianyar", "Jembrana", "Karangasem", "Klungkung", "Tabanan"],
            "Nusa Tenggara Barat": ["Bima", "Mataram", "Bima", "Dompu", "Lombok Barat", "Lombok Tengah", "Lombok Timur", "Lombok Utara", "Sumbawa", "Sumbawa Barat"],
            "Nusa Tenggara Timur": ["Kupang", "Alor", "Belu", "Ende", "Flores Timur", "Kupang", "Lembata", "Manggarai", "Manggarai Barat", "Manggarai Timur", "Nagekeo", "Ngada", "Rote Ndao", "Sabu Raijua", "Sikka", "Sumba Barat", "Sumba Barat Daya", "Sumba Tengah", "Sumba Timur", "Timor Tengah Selatan", "Timor Tengah Utara"],
            "Kalimantan Barat": ["Pontianak", "Singkawang", "Bengkayang", "Kapuas Hulu", "Kayong Utara", "Ketapang", "Kubu Raya", "Landak", "Melawi", "Mempawah", "Sambas", "Sanggau", "Sekadau", "Sintang"],
            "Kalimantan Tengah": ["Palangka Raya", "Barito Selatan", "Barito Timur", "Barito Utara", "Gunung Mas", "Kapuas", "Katingan", "Kotawaringin Barat", "Kotawaringin Timur", "Lamandau", "Murung Raya", "Pulang Pisau", "Seruyan", "Sukamara"],
            "Kalimantan Selatan": ["Banjarbaru", "Banjarmasin", "Balangan", "Banjar", "Barito Kuala", "Hulu Sungai Selatan", "Hulu Sungai Tengah", "Hulu Sungai Utara", "Kotabaru", "Tabalong", "Tanah Bumbu", "Tanah Laut", "Tapin"],
            "Kalimantan Timur": ["Balikpapan", "Bontang", "Samarinda", "Berau", "Kutai Barat", "Kutai Kartanegara", "Kutai Timur", "Mahakam Ulu", "Paser", "Penajam Paser Utara"],
            "Kalimantan Utara": ["Tarakan", "Bulungan", "Malinau", "Nunukan", "Tana Tidung"],
            "Sulawesi Utara": ["Bitung", "Kotamobagu", "Manado", "Tomohon", "Bolaang Mongondow", "Bolaang Mongondow Selatan", "Bolaang Mongondow Timur", "Bolaang Mongondow Utara", "Kepulauan Sangihe", "Kepulauan Siau Tagulandang Biaro", "Kepulauan Talaud", "Minahasa", "Minahasa Selatan", "Minahasa Tenggara", "Minahasa Utara"],
            "Sulawesi Tengah": ["Palu", "Banggai", "Banggai Kepulauan", "Banggai Laut", "Buol", "Donggala", "Morowali", "Morowali Utara", "Parigi Moutong", "Poso", "Sigi", "Tojo Una-Una", "Toli-Toli"],
            "Sulawesi Selatan": ["Makassar", "Palopo", "Parepare", "Bantaeng", "Barru", "Bone", "Bulukumba", "Enrekang", "Gowa", "Jeneponto", "Kepulauan Selayar", "Luwu", "Luwu Timur", "Luwu Utara", "Maros", "Pangkajene dan Kepulauan", "Pinrang", "Sidenreng Rappang", "Sinjai", "Soppeng", "Takalar", "Tana Toraja", "Toraja Utara", "Wajo"],
            "Sulawesi Tenggara": ["Baubau", "Kendari", "Bombana", "Buton", "Buton Selatan", "Buton Tengah", "Buton Utara", "Kolaka", "Kolaka Timur", "Kolaka Utara", "Konawe", "Konawe Kepulauan", "Konawe Selatan", "Konawe Utara", "Muna", "Muna Barat", "Wakatobi"],
            "Gorontalo": ["Gorontalo", "Boalemo", "Bone Bolango", "Gorontalo", "Gorontalo Utara", "Pohuwato"],
            "Sulawesi Barat": ["Majene", "Mamasa", "Mamuju", "Mamuju Tengah", "Mamuju Utara", "Polewali Mandar"],
            "Maluku": ["Ambon", "Tual", "Buru", "Buru Selatan", "Kepulauan Aru", "Maluku Barat Daya", "Maluku Tengah", "Maluku Tenggara", "Maluku Tenggara Barat", "Seram Bagian Barat", "Seram Bagian Timur"],
            "Maluku Utara": ["Ternate", "Tidore Kepulauan", "Halmahera Barat", "Halmahera Selatan", "Halmahera Tengah", "Halmahera Timur", "Halmahera Utara", "Kepulauan Sula", "Pulau Morotai", "Pulau Taliabu"],
            "Papua": ["Jayapura", "Asmat", "Biak Numfor", "Boven Digoel", "Deiyai", "Dogiyai", "Intan Jaya", "Jayapura", "Jayawijaya", "Keerom", "Kepulauan Yapen", "Lanny Jaya", "Mamberamo Raya", "Mamberamo Tengah", "Mappi", "Merauke", "Mimika", "Nabire", "Nduga", "Paniai", "Pegunungan Bintang", "Puncak", "Puncak Jaya", "Sarmi", "Supiori", "Tolikara", "Waropen", "Yahukimo", "Yalimo"],
            "Papua Barat": ["Sorong", "Fakfak", "Kaimana", "Manokwari", "Manokwari Selatan", "Maybrat", "Pegunungan Arfak", "Raja Ampat", "Sorong", "Sorong Selatan", "Tambrauw", "Teluk Bintuni", "Teluk Wondama"]
        };

        function showAddAddressModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Alamat';
            document.getElementById('addressForm').reset();
            document.getElementById('addressId').value = '';
            editingAddressId = null;
            populateProvinces();
            document.getElementById('addressModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent body scroll when modal open
        }

        function hideAddressModal() {
            document.getElementById('addressModal').classList.add('hidden');
            document.body.style.overflow = ''; // Restore body scroll
        }

        // Populate provinces dropdown
        function populateProvinces() {
            const provinceSelect = document.getElementById('addressProvince');
            provinceSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
            
            Object.keys(indonesiaRegions).sort().forEach(province => {
                const option = document.createElement('option');
                option.value = province;
                option.textContent = province;
                provinceSelect.appendChild(option);
            });
        }

        // Populate cities based on selected province
        function populateCities(province) {
            const citySelect = document.getElementById('addressCity');
            citySelect.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';
            
            if (province && indonesiaRegions[province]) {
                citySelect.disabled = false;
                citySelect.classList.remove('bg-slate-100');
                
                indonesiaRegions[province].sort().forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            } else {
                citySelect.disabled = true;
                citySelect.classList.add('bg-slate-100');
                citySelect.innerHTML = '<option value="">Pilih Provinsi Terlebih Dahulu</option>';
            }
        }

        // Province change event listener
        document.addEventListener('DOMContentLoaded', function() {
            const provinceSelect = document.getElementById('addressProvince');
            
            if (provinceSelect) {
                provinceSelect.addEventListener('change', function() {
                    const selectedProvince = this.value;
                    populateCities(selectedProvince);
                });
            }
        });

        function editAddress(id) {
            const addressCard = document.querySelector(`[data-address-id="${id}"]`);
            editingAddressId = id;
            
            document.getElementById('modalTitle').textContent = 'Edit Alamat';
            document.getElementById('addressId').value = id;
            
            // Here you would populate the form with address data
            // For now, showing the modal
            document.getElementById('addressModal').classList.remove('hidden');
            
            // Fetch address data and populate form
            // This would require an additional endpoint or passing data through data attributes
        }

        function setPrimaryAddress(id) {
            if (!confirm('Jadikan alamat ini sebagai alamat utama?')) return;
            
            fetch(`/profile/address/${id}/set-primary`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal mengatur alamat utama');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }

        function deleteAddress(id) {
            if (!confirm('Hapus alamat ini?')) return;
            
            fetch(`/profile/address/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-address-id="${id}"]`).remove();
                    
                    // Check if no addresses left
                    if (document.querySelectorAll('[data-address-id]').length === 0) {
                        location.reload();
                    }
                } else {
                    alert('Gagal menghapus alamat');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }

        // Postal code validation - only numbers
        document.addEventListener('DOMContentLoaded', function() {
            const postalCodeInput = document.getElementById('addressPostalCode');
            if (postalCodeInput) {
                postalCodeInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
            
            const phoneInput = document.getElementById('addressPhone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9+]/g, '');
                });
            }
        });

        document.getElementById('addressForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            
            formData.forEach((value, key) => {
                if (key === 'is_primary') {
                    data[key] = document.getElementById('isPrimary').checked;
                } else if (key !== 'address_id') {
                    data[key] = value;
                }
            });
            
            // Validate required fields
            if (!data.province || !data.city) {
                alert('Mohon lengkapi provinsi dan kota');
                return;
            }
            
            const addressId = document.getElementById('addressId').value;
            const url = addressId ? `/profile/address/${addressId}` : '/profile/address';
            const method = addressId ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideAddressModal();
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menyimpan alamat');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        });

        // Close modal when clicking outside
        document.getElementById('addressModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAddressModal();
            }
        });

        // =====================================================
        // Avatar Upload Handler (Inline backup)
        // =====================================================
        document.addEventListener('DOMContentLoaded', function() {
            const avatarInput = document.getElementById('avatar-input');
            const deleteAvatarBtn = document.getElementById('delete-avatar-btn');
            
            console.log('Avatar input element:', avatarInput);
            
            if (avatarInput) {
                avatarInput.addEventListener('change', async function(e) {
                    console.log('File selected:', e.target.files[0]);
                    const file = e.target.files[0];
                    if (!file) return;

                    // Validate file type
                    if (!file.type.startsWith('image/')) {
                        alert('File harus berupa gambar');
                        return;
                    }

                    // Validate file size (max 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file maksimal 2MB');
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('avatar', file);
                    
                    const loadingOverlay = document.getElementById('loading-overlay');
                    if (loadingOverlay) loadingOverlay.style.display = 'flex';
                    
                    try {
                        console.log('Uploading avatar...');
                        const response = await fetch('/profile/avatar', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        
                        console.log('Response status:', response.status);
                        const data = await response.json();
                        console.log('Response data:', data);
                        
                        if (data.success) {
                            // Update avatar preview
                            const avatarPreview = document.getElementById('avatar-preview');
                            const noAvatarPlaceholder = document.getElementById('no-avatar-placeholder');
                            const deleteBtn = document.getElementById('delete-avatar-btn');
                            
                            if (avatarPreview) {
                                avatarPreview.src = data.avatar_url;
                                avatarPreview.style.display = 'block';
                            }
                            if (noAvatarPlaceholder) {
                                noAvatarPlaceholder.style.display = 'none';
                            }
                            if (deleteBtn) {
                                deleteBtn.style.display = 'inline-flex';
                            }
                            
                            alert('Avatar berhasil diperbarui!');
                        } else {
                            alert('Gagal upload avatar: ' + (data.message || 'Unknown error'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat upload avatar: ' + error.message);
                    } finally {
                        if (loadingOverlay) loadingOverlay.style.display = 'none';
                    }
                });
            }
            
            // Delete avatar handler
            if (deleteAvatarBtn) {
                deleteAvatarBtn.addEventListener('click', async function() {
                    if (!confirm('Yakin ingin menghapus foto profil?')) return;
                    
                    const loadingOverlay = document.getElementById('loading-overlay');
                    if (loadingOverlay) loadingOverlay.style.display = 'flex';
                    
                    try {
                        const response = await fetch('/profile/avatar', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            const avatarPreview = document.getElementById('avatar-preview');
                            const noAvatarPlaceholder = document.getElementById('no-avatar-placeholder');
                            
                            if (avatarPreview) {
                                avatarPreview.src = '';
                                avatarPreview.style.display = 'none';
                            }
                            if (noAvatarPlaceholder) {
                                noAvatarPlaceholder.style.display = 'flex';
                            }
                            deleteAvatarBtn.style.display = 'none';
                            
                            alert('Avatar berhasil dihapus!');
                        } else {
                            alert('Gagal menghapus avatar: ' + (data.message || 'Unknown error'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus avatar');
                    } finally {
                        if (loadingOverlay) loadingOverlay.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>