/**
 * Dashboard Charts JavaScript
 * Initializes Chart.js for sales visualization
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
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
});