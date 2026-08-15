/**
 * Dashboard Charts JavaScript
 * Initializes Chart.js for sales visualization
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const ctx = document.getElementById('salesChart');
    
    if (ctx) {
        const salesChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
                datasets: [{
                    label: 'Penjualan',
                    data: [50, 80, 100, 120, 180, 380],
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
                        max: 400,
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 12,
                                weight: 500,
                            },
                            stepSize: 100,
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
    }
});
