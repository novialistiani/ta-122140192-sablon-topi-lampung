/**
 * Dashboard Charts JavaScript
 * - Grafik Penjualan (Chart.js) dengan data dari API
 * - Tabel Pesanan Terbaru dengan data dari API
 */

document.addEventListener('DOMContentLoaded', function() {
    // ===== Grafik Penjualan =====
    const ctx = document.getElementById('salesChart');

    if (ctx) {
        // Buat chart kosong dulu, nanti diisi setelah data dari API datang
        const salesChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Penjualan',
                    data: [],
                    borderColor: '#0a1d37',
                    backgroundColor: 'rgba(10, 29, 55, 0.05)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#fbbf24',
                    pointBorderColor: '#0a1d37',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(10, 29, 55, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        borderRadius: 8,
                        displayColors: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 12,
                                weight: 500,
                            },
                        },
                        grid: {
                            color: 'rgba(229, 231, 235, 0.5)',
                            drawBorder: false,
                        }
                    },
                    x: {
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 12,
                                weight: 500,
                            }
                        },
                        grid: {
                            display: false,
                            drawBorder: false,
                        }
                    }
                }
            }
        });

        // Ambil data penjualan asli dari database, lalu update chart
        fetch('/admin/api/dashboard/sales-data')
            .then(response => response.json())
            .then(result => {
                salesChart.data.labels = result.labels;
                salesChart.data.datasets[0].data = result.datasets[0].data;
                salesChart.update();
            })
            .catch(err => console.error('Sales Chart Error:', err));
    }

    // ===== Tabel Pesanan Terbaru =====
    const tbody = document.getElementById('recent-orders-tbody');

    if (tbody) {
        const statusLabels = {
            completed: 'Selesai',
            pending: 'Menunggu',
            processing: 'Diproses',
            cancelled: 'Dibatalkan',
            rejected: 'Ditolak',
            approved: 'Disetujui',
        };
        const statusColors = {
            completed: '#10b981',
            pending: '#f59e0b',
            processing: '#3b82f6',
            cancelled: '#ef4444',
            rejected: '#ef4444',
            approved: '#8b5cf6',
        };

        // Mencegah teks dari data dianggap sebagai HTML
        const escapeHtml = (value) => String(value ?? '-')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');

        const showMessage = (message) => {
            tbody.innerHTML = `
                <tr class="table-row">
                    <td colspan="7" class="table-td text-center py-4 text-gray-500">${message}</td>
                </tr>`;
        };

        fetch('/admin/api/dashboard/recent-orders?limit=10')
            .then(response => response.json())
            .then(orders => {
                if (!Array.isArray(orders) || orders.length === 0) {
                    showMessage('Belum ada pesanan');
                    return;
                }

                tbody.innerHTML = orders.map(order => {
                    const color = statusColors[order.status] || '#9ca3af';
                    const label = statusLabels[order.status] || order.status;
                    return `
                        <tr class="table-row">
                            <td class="table-checkbox-cell"><input type="checkbox"></td>
                            <td class="table-td">${escapeHtml(order.product)}</td>
                            <td class="table-td">${escapeHtml(order.id)}</td>
                            <td class="table-td">${escapeHtml(order.date)}</td>
                            <td class="table-td">${escapeHtml(order.customer)}</td>
                            <td class="table-td"><span style="padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; color: white; background: ${color};">${escapeHtml(label)}</span></td>
                            <td class="table-td">${escapeHtml(order.amount)}</td>
                        </tr>`;
                }).join('');
            })
            .catch(err => {
                console.error('Recent Orders Error:', err);
                showMessage('Gagal memuat pesanan terbaru');
            });
    }
});