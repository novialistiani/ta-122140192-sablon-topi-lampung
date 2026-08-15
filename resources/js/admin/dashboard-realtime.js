/**
 * Dashboard Real-time Updates
 * Implements polling-based real-time data refresh for admin dashboard
 * 
 * Features:
 * - Auto-refresh dashboard statistics
 * - Real-time chart updates
 * - Live order notifications
 * - Configurable refresh intervals
 */

class DashboardRealtime {
    constructor(options = {}) {
        this.refreshInterval = options.refreshInterval || 30000; // 30 seconds default
        this.statsEndpoint = '/admin/api/dashboard/stats';
        this.ordersEndpoint = '/admin/api/dashboard/recent-orders';
        this.salesEndpoint = '/admin/api/dashboard/sales-data';
        this.productsEndpoint = '/admin/api/dashboard/top-products';
        
        this.intervals = [];
        this.init();
    }

    /**
     * Initialize real-time updates
     */
    init() {
        console.log('Initializing Dashboard Real-time Updates');
        
        // Start polling
        this.startPolling();
        
        // Setup event listeners
        this.setupEventListeners();
        
        console.log('Dashboard Real-time initialized');
    }

    /**
     * Start polling for updates
     */
    startPolling() {
        // Poll statistics every 30 seconds
        const statsInterval = setInterval(() => {
            this.updateStatistics();
        }, this.refreshInterval);
        this.intervals.push(statsInterval);

        // Poll recent orders every 20 seconds
        const ordersInterval = setInterval(() => {
            this.updateRecentOrders();
        }, 20000);
        this.intervals.push(ordersInterval);

        // Poll sales chart every 60 seconds
        const chartInterval = setInterval(() => {
            this.updateSalesChart();
        }, 60000);
        this.intervals.push(chartInterval);

        // Poll top products every 45 seconds
        const productsInterval = setInterval(() => {
            this.updateTopProducts();
        }, 45000);
        this.intervals.push(productsInterval);
    }

    /**
     * Update dashboard statistics
     */
    async updateStatistics() {
        try {
            const response = await fetch(this.statsEndpoint, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (response.status === 401) {
                console.warn('Session expired, redirecting to login');
                window.location.href = '/admin/login';
                return;
            }

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            this.renderStatistics(data);

        } catch (error) {
            console.error('Failed to update statistics:', error);
        }
    }

    /**
     * Update recent orders table
     */
    async updateRecentOrders() {
        try {
            const response = await fetch(`${this.ordersEndpoint}?limit=10`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const orders = await response.json();
            this.renderRecentOrders(orders);

        } catch (error) {
            console.error('Failed to update recent orders:', error);
        }
    }

    /**
     * Update sales chart
     */
    async updateSalesChart() {
        try {
            const response = await fetch(this.salesEndpoint, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            this.updateChart(data);

        } catch (error) {
            console.error('Failed to update sales chart:', error);
        }
    }

    /**
     * Render statistics data
     */
    renderStatistics(data) {
        // Update total sold
        const soldElement = document.querySelector('[data-stat="total_sold"]');
        if (soldElement) {
            const oldValue = parseInt(soldElement.textContent.replace(/\D/g, ''));
            const newValue = data.total_sold;
            
            if (oldValue !== newValue) {
                soldElement.textContent = this.formatNumber(newValue);
                this.highlightElement(soldElement);
            }
        }

        // Update revenue
        const revenueElement = document.querySelector('[data-stat="revenue"]');
        if (revenueElement) {
            const newValue = this.formatCurrency(data.revenue);
            if (revenueElement.textContent !== newValue) {
                revenueElement.textContent = newValue;
                this.highlightElement(revenueElement);
            }
        }

        // Update customers
        const customersElement = document.querySelector('[data-stat="customers"]');
        if (customersElement) {
            const oldValue = parseInt(customersElement.textContent.replace(/\D/g, ''));
            const newValue = data.customers;
            
            if (oldValue !== newValue) {
                customersElement.textContent = this.formatNumber(newValue);
                this.highlightElement(customersElement);
            }
        }

        // Update pending orders
        const pendingElement = document.querySelector('[data-stat="pending_orders"]');
        if (pendingElement) {
            const oldValue = parseInt(pendingElement.textContent.replace(/\D/g, ''));
            const newValue = data.pending_orders;
            
            if (oldValue !== newValue) {
                pendingElement.textContent = this.formatNumber(newValue);
                this.highlightElement(pendingElement);
            }
        }
    }

    /**
     * Render recent orders
     */
    renderRecentOrders(orders) {
        const tbody = document.querySelector('#recent-orders-tbody') || document.querySelector('.orders-table tbody');
        if (!tbody) return;

        // Check if data has changed
        const currentRows = tbody.querySelectorAll('.table-row');
        if (currentRows.length === orders.length) {
            let hasChanged = false;
            for (let i = 0; i < orders.length; i++) {
                if (currentRows[i].querySelector('[data-order-id]')?.textContent !== orders[i].id) {
                    hasChanged = true;
                    break;
                }
            }
            if (!hasChanged) return;
        }

        // Clear tbody
        tbody.innerHTML = '';

        // Add new rows
        if (orders.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.className = 'table-row';
            emptyRow.innerHTML = '<td colspan="7" class="table-td text-center py-4 text-gray-500">Tidak ada pesanan terbaru</td>';
            tbody.appendChild(emptyRow);
            return;
        }

        orders.forEach(order => {
            const row = this.createOrderRow(order);
            tbody.appendChild(row);
        });

        this.highlightElement(tbody);
    }

    /**
     * Create an order row element
     */
    createOrderRow(order) {
        const row = document.createElement('tr');
        row.className = 'table-row';
        row.innerHTML = `
            <td class="table-td">
                <input type="checkbox">
            </td>
            <td class="table-td table-td-product">${this.escapeHtml(order.product)}</td>
            <td class="table-td table-td-text" data-order-id="${this.escapeHtml(order.id)}">${this.escapeHtml(order.id)}</td>
            <td class="table-td table-td-text">${this.escapeHtml(order.date)}</td>
            <td class="table-td table-td-customer">${this.escapeHtml(order.customer)}</td>
            <td class="table-td">
                <span class="status-badge status-${order.status.toLowerCase()}">
                    ${this.escapeHtml(order.status)}
                </span>
            </td>
            <td class="table-td table-td-amount">${this.escapeHtml(order.amount)}</td>
        `;
        return row;
    }

    /**
     * Update sales chart
     */
    updateChart(data) {
        // Global chart instance from dashboard-charts.js
        if (window.salesChart) {
            window.salesChart.data.labels = data.labels;
            window.salesChart.data.datasets[0].data = data.datasets[0].data;
            window.salesChart.update();
        }
    }

    /**
     * Update top products
     */
    async updateTopProducts() {
        try {
            const response = await fetch(`${this.productsEndpoint}?limit=5`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const products = await response.json();
            this.renderTopProducts(products);

        } catch (error) {
            console.error('Failed to update top products:', error);
        }
    }

    /**
     * Render top products
     */
    renderTopProducts(products) {
        const productList = document.querySelector('.product-list');
        if (!productList) return;

        // Check if data has changed
        const currentItems = productList.querySelectorAll('.product-item');
        if (currentItems.length === products.length) {
            let hasChanged = false;
            for (let i = 0; i < products.length; i++) {
                const currentName = currentItems[i].querySelector('.product-name')?.textContent;
                if (currentName !== products[i].name) {
                    hasChanged = true;
                    break;
                }
            }
            if (!hasChanged) return;
        }

        // Clear existing products
        productList.innerHTML = '';

        // Add new products
        const colorClasses = ['blue', 'green', 'yellow', 'purple', 'pink'];
        products.forEach((product, index) => {
            const colorClass = colorClasses[index] || 'blue';
            const item = document.createElement('div');
            item.className = 'product-item';
            item.innerHTML = `
                <div class="product-icon-wrapper product-icon-${colorClass}">
                    <i class="fas fa-box product-icon"></i>
                </div>
                <div class="product-info">
                    <p class="product-name">${this.escapeHtml(product.name)}</p>
                    <p class="product-sales">${product.variant_count || 0} variant</p>
                </div>
                <p class="product-price">Lihat detail</p>
            `;
            productList.appendChild(item);
        });

        this.highlightElement(productList);
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for window focus to refresh data when coming back to tab
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                console.log('Dashboard gained focus, refreshing data...');
                this.updateStatistics();
                this.updateRecentOrders();
                this.updateSalesChart();
            }
        });

        // Manual refresh button
        const refreshBtn = document.querySelector('[data-action="refresh-dashboard"]');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.updateStatistics();
                this.updateRecentOrders();
                this.updateSalesChart();
                this.showNotification('Dashboard refreshed', 'success');
            });
        }
    }

    /**
     * Highlight element to show update
     */
    highlightElement(element) {
        element.classList.add('highlight-update');
        setTimeout(() => {
            element.classList.remove('highlight-update');
        }, 2000);
    }

    /**
     * Format number
     */
    formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        }).format(amount);
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: ${type === 'success' ? '#10b981' : '#3b82f6'};
            color: white;
            border-radius: 8px;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        `;
        
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    /**
     * Cleanup intervals
     */
    destroy() {
        this.intervals.forEach(interval => clearInterval(interval));
        this.intervals = [];
        console.log('Dashboard Real-time destroyed');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardRealtime = new DashboardRealtime({
        refreshInterval: 30000 // 30 seconds
    });
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.dashboardRealtime) {
        window.dashboardRealtime.destroy();
    }
});

// Add CSS for highlight animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    .highlight-update {
        background-color: #fef3c7 !important;
        transition: background-color 0.3s ease-out;
    }

    .notification {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        font-weight: 500;
    }
`;
document.head.appendChild(style);
