<x-admin-layout title="Laporan Stok">
    @push('styles')
        <style>
            .sr-card-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }
            .sr-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 20px; }
            .sr-card small { color: #6b7280; display: block; margin-bottom: 6px; }
            .sr-card strong { font-size: 26px; color: #0a1d37; }
            .sr-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; }
            .sr-panel h3 { margin: 0 0 12px; font-size: 16px; color: #111827; }
            .sr-filters { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 20px; }
            .sr-filters input, .sr-filters select { padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: #fff; }
            .sr-btn { padding: 8px 16px; border: 0; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; }
            .sr-btn-primary { background: #0a1d37; color: #fff; }
            .sr-btn-secondary { background: #e5e7eb; color: #1f2937; }
            .sr-btn-success { background: #16a34a; color: #fff; margin-left: auto; }
            .sr-table { width: 100%; border-collapse: collapse; font-size: 14px; }
            .sr-table th { text-align: left; padding: 10px; background: #f3f4f6; color: #374151; border-bottom: 1px solid #e5e7eb; }
            .sr-table td { padding: 10px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
            .sr-in { color: #15803d; font-weight: 700; }
            .sr-out { color: #b91c1c; font-weight: 700; }
            .sr-table .sr-right { text-align: right; }
            .sr-muted { color: #6b7280; font-size: 12px; }
            .sr-pager { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; font-size: 14px; }
            .sr-pager a, .sr-pager .disabled { padding: 6px 14px; border-radius: 6px; border: 1px solid #d1d5db; text-decoration: none; color: #1f2937; }
            .sr-pager .disabled { color: #9ca3af; background: #f9fafb; }
        </style>
    @endpush

    <form method="GET" action="{{ route('admin.stock-report') }}" class="sr-filters">
        <input type="date" name="start_date" value="{{ $start->toDateString() }}" title="Dari tanggal">
        <span>-</span>
        <input type="date" name="end_date" value="{{ $end->toDateString() }}" title="Sampai tanggal">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama produk...">
        <select name="type">
            <option value="">Semua Jenis</option>
            <option value="in" {{ ($filters['type'] ?? '') === 'in' ? 'selected' : '' }}>Stok Masuk</option>
            <option value="out" {{ ($filters['type'] ?? '') === 'out' ? 'selected' : '' }}>Stok Keluar</option>
        </select>
        <select name="reason">
            <option value="">Semua Alasan</option>
            @foreach(\App\Models\StockMovement::REASON_LABELS as $key => $label)
                <option value="{{ $key }}" {{ ($filters['reason'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="sr-btn sr-btn-primary">Terapkan</button>
        <a href="{{ route('admin.stock-report') }}" class="sr-btn sr-btn-secondary">Reset</a>
        <a href="{{ route('admin.stock-report.export', request()->query()) }}" class="sr-btn sr-btn-success">Export Excel</a>
    </form>

    <div class="sr-card-row">
        <div class="sr-card"><small>Total Stok Masuk</small><strong>{{ number_format((int) $totals->total_in, 0, ',', '.') }}</strong></div>
        <div class="sr-card"><small>Total Stok Keluar</small><strong>{{ number_format((int) $totals->total_out, 0, ',', '.') }}</strong></div>
        <div class="sr-card"><small>Jumlah Pergerakan</small><strong>{{ number_format((int) $totals->movements, 0, ',', '.') }}</strong></div>
    </div>

    <div class="sr-panel">
        <h3>Ringkasan per Produk</h3>
        <table class="sr-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="sr-right">Masuk</th>
                    <th class="sr-right">Keluar</th>
                    <th class="sr-right">Stok Saat Ini</th>
                </tr>
            </thead>
            <tbody>
                @forelse($perProduct as $row)
                <tr>
                    <td>{{ $row->product_name ?? 'Produk #' . $row->product_id }}</td>
                    <td class="sr-right sr-in">{{ (int) $row->total_in }}</td>
                    <td class="sr-right sr-out">{{ (int) $row->total_out }}</td>
                    <td class="sr-right">{{ $currentStock[$row->product_id] ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; padding: 24px; color:#6b7280;">Belum ada pergerakan stok pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="sr-panel">
        <h3>Riwayat Pergerakan Stok</h3>
        <table class="sr-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Produk</th>
                    <th class="sr-right">Perubahan</th>
                    <th class="sr-right">Stok</th>
                    <th>Alasan</th>
                    <th>Referensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        {{ $m->product_name ?? '-' }}
                        @if($m->variant_label)<div class="sr-muted">{{ $m->variant_label }}</div>@endif
                    </td>
                    <td class="sr-right {{ $m->quantity_change >= 0 ? 'sr-in' : 'sr-out' }}">
                        {{ $m->quantity_change > 0 ? '+' : '' }}{{ $m->quantity_change }}
                    </td>
                    <td class="sr-right">{{ $m->stock_before }} &rarr; {{ $m->stock_after }}</td>
                    <td>
                        {{ $m->reason_label }}
                        @if($m->note)<div class="sr-muted">{{ $m->note }}</div>@endif
                    </td>
                    <td>
                        @if($m->reference_type)
                            {{ $m->reference_type === 'custom_order' ? 'Pesanan Custom' : 'Pesanan' }} #{{ $m->reference_id }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; padding: 24px; color:#6b7280;">Belum ada pergerakan stok pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($movements->hasPages())
        <div class="sr-pager">
            @if($movements->onFirstPage())
                <span class="disabled">Previous</span>
            @else
                <a href="{{ $movements->previousPageUrl() }}">Previous</a>
            @endif
            <span>Halaman {{ $movements->currentPage() }} dari {{ $movements->lastPage() }} ({{ $movements->total() }} catatan)</span>
            @if($movements->hasMorePages())
                <a href="{{ $movements->nextPageUrl() }}">Next</a>
            @else
                <span class="disabled">Next</span>
            @endif
        </div>
        @endif
    </div>
</x-admin-layout>