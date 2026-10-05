<x-admin-layout title="Statistik Chatbot">
    <div style="padding: 24px; max-width: 1000px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
            <h2 style="margin: 0; font-size: 22px;">Statistik Chatbot</h2>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                @foreach ([7 => '7 hari', 30 => '30 hari', 90 => '90 hari', 0 => 'Semua'] as $value => $label)
                    <a href="{{ request()->url() }}?days={{ $value }}"
                       style="padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 14px; border: 1px solid #d1d5db; {{ $days === $value ? 'background:#0b1f3a;color:#fff;border-color:#0b1f3a;' : 'background:#fff;color:#374151;' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <a href="{{ route('chatbot.index') }}" style="padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 14px; border: 1px solid #d1d5db; background:#fff; color:#374151;">Kembali</a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px;">
                <div style="color:#6b7280; font-size:13px;">Jumlah Percakapan</div>
                <div style="font-size:30px; font-weight:700;">{{ $totalConversations }}</div>
            </div>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px;">
                <div style="color:#6b7280; font-size:13px;">Pesan dari Pelanggan</div>
                <div style="font-size:30px; font-weight:700;">{{ $totalUserMessages }}</div>
            </div>
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px;">
                <div style="color:#6b7280; font-size:13px;">Eskalasi ke Admin</div>
                <div style="font-size:30px; font-weight:700;">{{ $escalated }}</div>
                <div style="color:#6b7280; font-size:13px;">{{ $escalationRate }}% dari percakapan</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px;">
                <h3 style="margin: 0 0 14px; font-size: 16px;">Topik Terbanyak</h3>
                @php $maxTopic = $topics ? max($topics) : 0; @endphp
                @forelse ($topics as $topic => $count)
                    <div style="margin-bottom: 12px;">
                        <div style="display:flex; justify-content:space-between; font-size:14px;">
                            <span>{{ $topic }}</span><strong>{{ $count }}</strong>
                        </div>
                        <div style="background:#f3f4f6; border-radius:6px; height:8px; margin-top:4px;">
                            <div style="background:#f59e0b; height:8px; border-radius:6px; width: {{ $maxTopic > 0 ? round($count / $maxTopic * 100) : 0 }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p style="color:#6b7280; font-size:14px;">Belum ada data pada periode ini.</p>
                @endforelse
            </div>

            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px;">
                <h3 style="margin: 0 0 14px; font-size: 16px;">Produk Paling Sering Ditanyakan</h3>
                @forelse ($topProducts as $row)
                    <div style="display:flex; justify-content:space-between; font-size:14px; padding:8px 0; border-bottom:1px solid #f3f4f6;">
                        <span>{{ $productNames[$row->product_id] ?? 'Produk #' . $row->product_id }}</span>
                        <strong>{{ $row->total }}</strong>
                    </div>
                @empty
                    <p style="color:#6b7280; font-size:14px;">Belum ada percakapan yang terkait produk.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>