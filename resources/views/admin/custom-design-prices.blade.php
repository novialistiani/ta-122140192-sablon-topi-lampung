<x-admin-layout title="Kelola Harga Custom Design">
    @push('styles')
        <style>
            .price-management-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 24px;
            }

            .section-card {
                background: white;
                border-radius: 12px;
                padding: 24px;
                margin-bottom: 24px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .section-title {
                font-size: 20px;
                font-weight: 600;
                color: #0a1d37;
                margin-bottom: 20px;
                padding-bottom: 12px;
                border-bottom: 2px solid #fbbf24;
            }

            .price-table {
                width: 100%;
                border-collapse: collapse;
            }

            .price-table thead {
                background: #f9fafb;
            }

            .price-table th {
                padding: 12px;
                text-align: left;
                font-weight: 600;
                color: #374151;
                border-bottom: 2px solid #e5e7eb;
            }

            .price-table td {
                padding: 12px;
                border-bottom: 1px solid #e5e7eb;
            }

            .price-table tbody tr:hover {
                background: #f9fafb;
            }

            .code-badge {
                display: inline-block;
                background: #0a1d37;
                color: white;
                padding: 4px 12px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 14px;
            }

            .price-input {
                width: 150px;
                padding: 8px 12px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 14px;
            }

            .price-input:focus {
                outline: none;
                border-color: #fbbf24;
                box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
            }

            .save-btn {
                background: #fbbf24;
                color: #0a1d37;
                padding: 8px 16px;
                border-radius: 6px;
                border: none;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                font-size: 14px;
            }

            .save-btn:hover {
                background: #f59e0b;
                transform: translateY(-1px);
            }

            .save-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .toggle-switch {
                position: relative;
                width: 48px;
                height: 24px;
                background: #d1d5db;
                border-radius: 12px;
                cursor: pointer;
                transition: background 0.3s;
            }

            .toggle-switch.active {
                background: #10b981;
            }

            .toggle-switch::after {
                content: '';
                position: absolute;
                top: 2px;
                left: 2px;
                width: 20px;
                height: 20px;
                background: white;
                border-radius: 50%;
                transition: transform 0.3s;
            }

            .toggle-switch.active::after {
                transform: translateX(24px);
            }

            .status-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
            }

            .status-active {
                background: #d1fae5;
                color: #065f46;
            }

            .status-inactive {
                background: #fee2e2;
                color: #991b1b;
            }

            .alert {
                padding: 16px;
                border-radius: 8px;
                margin-bottom: 24px;
            }

            .alert-success {
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #86efac;
            }

            .empty-state {
                text-align: center;
                padding: 48px 24px;
                color: #6b7280;
            }

            .empty-state-icon {
                font-size: 48px;
                margin-bottom: 16px;
                opacity: 0.5;
            }
        </style>
    @endpush

    <div class="price-management-container">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Initialize Button (show when no data) -->
        @if($uploadSections->isEmpty() && $cuttingTypes->isEmpty())
            <div class="section-card" style="text-align: center; padding: 40px;">
                <div style="font-size: 48px; margin-bottom: 16px;">⚙️</div>
                <h3 style="margin-bottom: 12px; color: #374151;">Belum Ada Data Harga</h3>
                <p style="margin-bottom: 20px; color: #6b7280;">Klik tombol di bawah untuk menginisialisasi data harga default.</p>
                <form action="{{ route('admin.custom-design-prices.init') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="save-btn" style="padding: 12px 24px; font-size: 16px;">
                        <i class="fas fa-magic"></i> Initialize Default Prices
                    </button>
                </form>
            </div>
        @endif

        <!-- Upload Sections -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fas fa-upload"></i> Harga Upload Bagian
            </h2>
            
            @if($uploadSections->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <p>Belum ada data harga. Klik "Initialize Default Prices" untuk memulai.</p>
                </div>
            @else
                <table class="price-table">
                    <thead>
                        <tr>
                            <th width="80">Kode</th>
                            <th>Nama Bagian</th>
                            <th width="180">Harga Custom (Rp)</th>
                            <th width="100">Status</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uploadSections as $section)
                            <tr>
                                <td><span class="code-badge">{{ $section->code }}</span></td>
                                <td>{{ $section->name }}</td>
                                <td>
                                    <input 
                                        type="number" 
                                        class="price-input" 
                                        value="{{ $section->price }}" 
                                        data-id="{{ $section->id }}"
                                        data-original="{{ $section->price }}"
                                        min="0"
                                        step="1000"
                                    >
                                </td>
                                <td>
                                    <span class="status-badge {{ $section->is_active ? 'status-active' : 'status-inactive' }}">
                                        {{ $section->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="save-btn" onclick="updatePrice({{ $section->id }})">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                    <div 
                                        class="toggle-switch {{ $section->is_active ? 'active' : '' }}" 
                                        onclick="toggleStatus({{ $section->id }})"
                                        style="display: inline-block; margin-left: 8px;"
                                    ></div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Cutting Types -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fas fa-cut"></i> Harga Jenis Cutting
            </h2>
            
            @if($cuttingTypes->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <p>Belum ada data harga. Klik "Initialize Default Prices" untuk memulai.</p>
                </div>
            @else
                <table class="price-table">
                    <thead>
                        <tr>
                            <th width="120">Kode</th>
                            <th>Jenis Cutting</th>
                            <th width="180">Harga Custom (Rp)</th>
                            <th width="100">Status</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cuttingTypes as $cutting)
                            <tr>
                                <td><span class="code-badge">{{ strtoupper(str_replace('-', ' ', $cutting->code)) }}</span></td>
                                <td>{{ $cutting->name }}</td>
                                <td>
                                    <input 
                                        type="number" 
                                        class="price-input" 
                                        value="{{ $cutting->price }}" 
                                        data-id="{{ $cutting->id }}"
                                        data-original="{{ $cutting->price }}"
                                        min="0"
                                        step="1000"
                                    >
                                </td>
                                <td>
                                    <span class="status-badge {{ $cutting->is_active ? 'status-active' : 'status-inactive' }}">
                                        {{ $cutting->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="save-btn" onclick="updatePrice({{ $cutting->id }})">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                    <div 
                                        class="toggle-switch {{ $cutting->is_active ? 'active' : '' }}" 
                                        onclick="toggleStatus({{ $cutting->id }})"
                                        style="display: inline-block; margin-left: 8px;"
                                    ></div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            async function updatePrice(id) {
                const input = document.querySelector(`input[data-id="${id}"]`);
                const price = input.value;
                const button = event.target.closest('button');
                
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                
                try {
                    const response = await fetch(`/admin/api/custom-design-prices/${id}/price`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ price })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        input.dataset.original = price;
                        button.innerHTML = '<i class="fas fa-check"></i> Tersimpan';
                        setTimeout(() => {
                            button.innerHTML = '<i class="fas fa-save"></i> Simpan';
                            button.disabled = false;
                        }, 1500);
                    } else {
                        alert('Gagal menyimpan harga');
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-save"></i> Simpan';
                    }
                } catch (error) {
                    console.error(error);
                    alert('Terjadi kesalahan');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-save"></i> Simpan';
                }
            }
            
            async function toggleStatus(id) {
                const toggle = event.target;
                
                try {
                    const response = await fetch(`/admin/api/custom-design-prices/${id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        toggle.classList.toggle('active');
                        const statusBadge = toggle.closest('tr').querySelector('.status-badge');
                        if (data.data.is_active) {
                            statusBadge.classList.remove('status-inactive');
                            statusBadge.classList.add('status-active');
                            statusBadge.textContent = 'Aktif';
                        } else {
                            statusBadge.classList.remove('status-active');
                            statusBadge.classList.add('status-inactive');
                            statusBadge.textContent = 'Nonaktif';
                        }
                    }
                } catch (error) {
                    console.error(error);
                    alert('Terjadi kesalahan');
                }
            }
        </script>
    @endpush
</x-admin-layout>
