/**
 * Analytics Dashboard - Simplified Version for Debugging
 */

console.log('ANALYTICS.JS LOADED');

let dateRange = {
    start: null,
    end: null
};

let analyticsState = {
    currentHash: '',
    pollingInterval: null,
    isPolling: false
};

// Simple test: set one element directly when page loads
window.addEventListener('load', function() {
    console.log('Window LOAD event fired');
    
    // Initialize date inputs with default values (last 30 days)
    const endDate = new Date();
    const startDate = new Date(endDate.getTime() - 30 * 24 * 60 * 60 * 1000);
    
    const startInput = document.getElementById('startDate');
    const endInput = document.getElementById('endDate');
    
    if (startInput && endInput) {
        startInput.valueAsDate = startDate;
        endInput.valueAsDate = endDate;
        
        dateRange.start = formatDateForAPI(startDate);
        dateRange.end = formatDateForAPI(endDate);
        
        console.log('✓ Date range initialized:', dateRange);
    }
    
    // Setup filter button
    const filterBtn = document.getElementById('filterBtn');
    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            const start = startInput.value;
            const end = endInput.value;
            if (start && end) {
                dateRange.start = start;
                dateRange.end = end;
                console.log('Filter applied:', dateRange);
                loadAnalyticsData();
            }
        });
    }
    
    // Setup reset button
    const resetBtn = document.getElementById('resetBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            const endDate = new Date();
            const startDate = new Date(endDate.getTime() - 30 * 24 * 60 * 60 * 1000);
            
            startInput.valueAsDate = startDate;
            endInput.valueAsDate = endDate;
            
            dateRange.start = formatDateForAPI(startDate);
            dateRange.end = formatDateForAPI(endDate);
            
            console.log('Reset to default range:', dateRange);
            loadAnalyticsData();
        });
    }
    
    const el = document.getElementById('total-revenue');
    if (el) {
        console.log('✓ Found total-revenue element');
        el.textContent = 'Loading...';
    } else {
        console.error('✗ ELEMENT NOT FOUND: total-revenue');
    }
    
    loadAnalyticsData();
    
    // Start polling for analytics changes
    startAnalyticsPolling();
});

/**
 * Start polling for analytics changes
 * Checks every 10 seconds if data has changed
 */
function startAnalyticsPolling() {
    if (analyticsState.isPolling) {
        console.log('Polling already active');
        return;
    }
    
    analyticsState.isPolling = true;
    console.log('✓ Starting analytics polling (every 10 seconds)');
    
    analyticsState.pollingInterval = setInterval(function() {
        checkAnalyticsChanges();
    }, 10000); // Check every 10 seconds
}

/**
 * Check if analytics data has changed
 */
function checkAnalyticsChanges() {
    const params = new URLSearchParams();
    params.append('last_hash', analyticsState.currentHash);
    
    fetch(`/admin/api/analytics/check-changes?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            if (data.hasChanges) {
                console.log('✓ Analytics data has changed, refreshing...', data);
                analyticsState.currentHash = data.currentHash;
                loadAnalyticsData();
            } else {
                console.log('No changes detected. Hash:', data.currentHash);
                analyticsState.currentHash = data.currentHash;
            }
        })
        .catch(err => {
            console.warn('Error checking analytics changes:', err);
        });
}

/**
 * Stop polling
 */
function stopAnalyticsPolling() {
    if (analyticsState.pollingInterval) {
        clearInterval(analyticsState.pollingInterval);
        analyticsState.pollingInterval = null;
        analyticsState.isPolling = false;
        console.log('Polling stopped');
    }
}

// Stop polling when page is hidden
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAnalyticsPolling();
        console.log('Page hidden, analytics polling paused');
    } else {
        startAnalyticsPolling();
        console.log('Page visible, analytics polling resumed');
    }
});

// Stop polling when leaving page
window.addEventListener('beforeunload', function() {
    stopAnalyticsPolling();
});

function formatDateForAPI(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function loadAnalyticsData() {
    console.log('Loading all analytics with date range:', dateRange);
    
    Promise.all([
        loadSalesOverview(),
        loadSalesTrend(),
        loadOrderStatus(),
        loadCustomerAnalytics(),
        loadConversionFunnel(),
    ]).catch(error => {
        console.error('Error loading analytics:', error);
    });
}


function loadSalesOverview() {
    const params = new URLSearchParams();
    if (dateRange.start) params.append('start_date', dateRange.start);
    if (dateRange.end) params.append('end_date', dateRange.end);
    
    const apiUrl = `/admin/api/analytics/sales-overview?${params.toString()}`;
    console.log('Fetching sales overview from:', apiUrl);
    
    return fetch(apiUrl)
        .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        })
        .then(data => {
            console.log('✓ Sales Overview data:', data);
            
            if (data.success && data.data) {
                const d = data.data;
                
                const revEl = document.getElementById('total-revenue');
                if (revEl) {
                    revEl.textContent = 'Rp ' + Number(d.totalRevenue).toLocaleString('id-ID', {maxFractionDigits: 0});
                }
                
                const completedEl = document.getElementById('completed-orders');
                if (completedEl) {
                    completedEl.textContent = d.completedOrders;
                }
                
                const ordersEl = document.getElementById('total-orders');
                if (ordersEl) {
                    ordersEl.textContent = d.totalOrders;
                }
                
                const conversionEl = document.getElementById('conversion-rate');
                if (conversionEl) {
                    conversionEl.textContent = Number(d.conversionRate).toFixed(2) + '%';
                }
                
                const aovEl = document.getElementById('aov');
                if (aovEl) {
                    aovEl.textContent = 'Rp ' + Number(d.averageOrderValue).toLocaleString('id-ID', {maxFractionDigits: 0});
                }
                
                const growthEl = document.getElementById('growth');
                if (growthEl) {
                    growthEl.textContent = d.revenueGrowth.toFixed(2) + '%';
                }
            }
        })
        .catch(err => console.error('Sales Overview Error:', err));
}

function loadSalesTrend() {
    const params = new URLSearchParams();
    if (dateRange.start) params.append('start_date', dateRange.start);
    if (dateRange.end) params.append('end_date', dateRange.end);
    
    const apiUrl = `/admin/api/analytics/sales-trend?${params.toString()}`;
    console.log('Fetching sales trend from:', apiUrl);
    
    return fetch(apiUrl)
        .then(r => r.json())
        .then(data => {
            console.log('Sales Trend data:', data);
            if (data.success && data.data && data.data.length > 0) {
                const chartData = data.data;
                const labels = chartData.map(item => new Date(item.date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' }));
                const revenues = chartData.map(item => Number(item.revenue));
                const orders = chartData.map(item => Number(item.orders));
                
                const ctx = document.getElementById('salesTrendChart');
                if (ctx && window.Chart) {
                    if (window.salesTrendChart && typeof window.salesTrendChart.destroy === 'function') {
                        window.salesTrendChart.destroy();
                    }
                    
                    window.salesTrendChart = new Chart(ctx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Revenue (Rp)',
                                    data: revenues,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                                    tension: 0.4,
                                    yAxisID: 'y',
                                    fill: true,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#3b82f6'
                                },
                                {
                                    label: 'Orders',
                                    data: orders,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                                    tension: 0.4,
                                    yAxisID: 'y1',
                                    fill: true,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#10b981'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            scales: {
                                y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Revenue (Rp)' } },
                                y1: { type: 'linear', display: true, position: 'right', title: { display: true, text: 'Orders' }, grid: { drawOnChartArea: false } }
                            }
                        }
                    });
                }
            }
        })
        .catch(err => console.error('Sales Trend Error:', err));
}

function loadOrderStatus() {
    const params = new URLSearchParams();
    if (dateRange.start) params.append('start_date', dateRange.start);
    if (dateRange.end) params.append('end_date', dateRange.end);
    
    const apiUrl = `/admin/api/analytics/order-status?${params.toString()}`;
    
    return fetch(apiUrl)
        .then(r => r.json())
        .then(data => {
            console.log('Order Status Response:', data);
            
            if (data.success && data.data) {
                const statuses = data.data.filter(s => s.count > 0);
                const colors = ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#6366f1'];
                
                // Always update the breakdown div
                const breakdown = document.getElementById('status-breakdown');
                if (breakdown) {
                    if (statuses.length > 0) {
                        breakdown.innerHTML = statuses.map((status, i) => `
                            <div class="status-item">
                                <div class="status-item-label">
                                    <div class="status-color-box" style="background-color: ${colors[i]}"></div>
                                    ${status.label}
                                </div>
                                <div class="status-item-count">${status.count}</div>
                            </div>
                        `).join('');
                    } else {
                        breakdown.innerHTML = '<div style="color: #9ca3af; padding: 16px; text-align: center;">No orders in this period</div>';
                    }
                }
                
                // Update chart if there is data
                if (statuses.length > 0) {
                    const labels = statuses.map(s => s.label);
                    const counts = statuses.map(s => s.count);
                    
                    const ctx = document.getElementById('orderStatusChart');
                    if (ctx && window.Chart) {
                        if (window.orderStatusChart && typeof window.orderStatusChart.destroy === 'function') {
                            window.orderStatusChart.destroy();
                        }
                        
                        window.orderStatusChart = new Chart(ctx.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: counts,
                                    backgroundColor: colors.slice(0, statuses.length),
                                    borderColor: '#fff',
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { position: 'bottom' } }
                            }
                        });
                    }
                }
            } else {
                console.error('Failed to load order status:', data);
                const breakdown = document.getElementById('status-breakdown');
                if (breakdown) {
                    breakdown.innerHTML = '<div style="color: #ef4444; padding: 16px;">Error loading data</div>';
                }
            }
        })
        .catch(err => {
            console.error('Order Status Error:', err);
            const breakdown = document.getElementById('status-breakdown');
            if (breakdown) {
                breakdown.innerHTML = '<div style="color: #ef4444; padding: 16px;">Error: ' + err.message + '</div>';
            }
        });
}

function loadCustomerAnalytics() {
    const params = new URLSearchParams();
    if (dateRange.start) params.append('start_date', dateRange.start);
    if (dateRange.end) params.append('end_date', dateRange.end);
    
    const apiUrl = `/admin/api/analytics/customer?${params.toString()}`;
    
    return fetch(apiUrl)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const d = data.data;
                
                const els = {
                    'new-customers': document.getElementById('new-customers'),
                    'active-customers': document.getElementById('active-customers'),
                    'total-customers': document.getElementById('total-customers'),
                    'purchasing-rate': document.getElementById('purchasing-rate'),
                    'rfm-table': document.getElementById('rfm-table')
                };
                
                if (els['new-customers']) els['new-customers'].textContent = d.newCustomers;
                if (els['active-customers']) els['active-customers'].textContent = d.activeCustomers;
                if (els['total-customers']) els['total-customers'].textContent = d.totalCustomers;
                if (els['purchasing-rate']) els['purchasing-rate'].textContent = d.purchasingRate.toFixed(2) + '%';
                
                // Render Customer Distribution Pie Chart
                renderCustomerDistributionChart(d);
                
                // Render RFM Bar Chart
                renderRFMChart(d.rfmTop);
                
                // Render RFM Table with ranking
                if (els['rfm-table'] && d.rfmTop && d.rfmTop.length > 0) {
                    els['rfm-table'].innerHTML = d.rfmTop.map((c, idx) => `
                        <tr>
                            <td><strong>#${idx + 1}</strong></td>
                            <td><strong>${c.customer}</strong></td>
                            <td>${c.recency} days</td>
                            <td>${c.frequency}x</td>
                            <td><strong>Rp ${Number(c.monetary).toLocaleString('id-ID', {maxFractionDigits: 0})}</strong></td>
                        </tr>
                    `).join('');
                }
            }
        })
        .catch(err => console.error('Customer Analytics Error:', err));
}

function renderCustomerDistributionChart(data) {
    const ctx = document.getElementById('customerDistributionChart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window.customerDistributionChartInstance) {
        window.customerDistributionChartInstance.destroy();
    }
    
    const inactive = data.totalCustomers - data.activeCustomers;
    
    window.customerDistributionChartInstance = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Active (Purchased)', 'Inactive (Not Purchased)'],
            datasets: [{
                data: [data.activeCustomers, inactive],
                backgroundColor: ['#10b981', '#ef4444'],
                borderColor: ['#059669', '#dc2626'],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: { size: 12, weight: 500 },
                        color: '#374151'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(10, 29, 55, 0.9)',
                    padding: 12,
                    titleFont: { size: 13 },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(context) {
                            const total = data.totalCustomers;
                            const value = context.parsed;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function renderRFMChart(rfmData) {
    const ctx = document.getElementById('rfmChart');
    if (!ctx || !rfmData || rfmData.length === 0) return;
    
    // Destroy existing chart if it exists
    if (window.rfmChartInstance) {
        window.rfmChartInstance.destroy();
    }
    
    // Get top 5 customers
    const topCustomers = rfmData.slice(0, 5);
    const labels = topCustomers.map(c => c.customer.split(' ')[0]); // First name only
    
    // Normalize data for visualization
    const maxMonetary = Math.max(...topCustomers.map(c => c.monetary));
    const monetaryNormalized = topCustomers.map(c => (c.monetary / maxMonetary) * 100);
    
    window.rfmChartInstance = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Monetary Value (% of Max)',
                    data: monetaryNormalized,
                    backgroundColor: '#3b82f6',
                    borderColor: '#1d4ed8',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Frequency (Orders)',
                    data: topCustomers.map(c => c.frequency * 10), // Scale for visibility
                    backgroundColor: '#8b5cf6',
                    borderColor: '#6d28d9',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: { size: 11, weight: 500 },
                        color: '#374151'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(10, 29, 55, 0.9)',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            if (context.datasetIndex === 0) {
                                const customer = topCustomers[context.dataIndex];
                                return `Value: Rp ${Number(customer.monetary).toLocaleString('id-ID')}`;
                            } else {
                                const customer = topCustomers[context.dataIndex];
                                return `Orders: ${customer.frequency}x`;
                            }
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        color: '#9ca3af',
                        font: { size: 11 }
                    },
                    grid: {
                        color: 'rgba(229, 231, 235, 0.5)'
                    }
                },
                y: {
                    ticks: {
                        color: '#9ca3af',
                        font: { size: 11, weight: 500 }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

function loadConversionFunnel() {
    const params = new URLSearchParams();
    if (dateRange.start) params.append('start_date', dateRange.start);
    if (dateRange.end) params.append('end_date', dateRange.end);
    
    const apiUrl = `/admin/api/analytics/conversion-funnel?${params.toString()}`;
    
    return fetch(apiUrl)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data.funnel) {
                const funnel = data.data.funnel;
                const maxCount = Math.max(...funnel.map(f => f.count), 1);
                
                const container = document.getElementById('funnel-stages');
                if (container) {
                    const colors = ['#3b82f6', '#2563eb', '#1d4ed8', '#1e40af', '#1e3a8a', '#0c4a6e'];
                    
                    container.innerHTML = funnel.map((item, i) => {
                        const width = item.count > 0 ? (item.count / maxCount) * 100 : 0;
                        return `
                            <div class="funnel-stage">
                                <div class="funnel-stage-label">
                                    <span class="funnel-stage-name">${item.stage}</span>
                                    <span class="funnel-stage-percent">${item.count} (${item.rate.toFixed(2)}%)</span>
                                </div>
                                <div class="funnel-bar" style="width: ${width}%; background: linear-gradient(90deg, ${colors[i]} 0%, ${colors[Math.min(i+1, colors.length-1)]} 100%);">
                                    ${width > 15 ? item.rate.toFixed(1) + '%' : ''}
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }
        })
        .catch(err => console.error('Conversion Funnel Error:', err));
}
