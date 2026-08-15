<x-admin-layout title="Analytics Reports">
    @vite(['resources/css/admin/analytics.css', 'resources/js/admin/analytics.js'])

    <div class="analytics-content" data-base-url="{{ url('/') }}">
        <!-- Date Range Filter -->
        <div class="analytics-filter" style="background: white; padding: 16px 20px; border-radius: 6px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <label style="font-weight: 500; color: #374151; font-size: 13px; margin: 0;">Date Range:</label>
            <input type="date" id="startDate" style="padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px;">
            <span style="color: #9ca3af;">to</span>
            <input type="date" id="endDate" style="padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px;">
            <button id="filterBtn" style="padding: 6px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500; transition: all 0.3s;">Filter</button>
            <button id="resetBtn" style="padding: 6px 16px; background: #e5e7eb; color: #374151; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500; transition: all 0.3s;">Reset</button>
        </div>

        <!-- 1. SALES & REVENUE OVERVIEW -->
        <div class="stats-grid" id="sales-overview">
            <div class="stats-card">
                <div class="stats-card-label">Total Revenue</div>
                <p class="stats-card-value" id="total-revenue">Rp 0</p>
                <div class="stats-card-meta">From completed orders</div>
            </div>

            <div class="stats-card">
                <div class="stats-card-label">Completed Orders</div>
                <p class="stats-card-value" id="completed-orders">0</p>
                <div class="stats-card-meta">Successfully completed</div>
            </div>

            <div class="stats-card">
                <div class="stats-card-label">Total Orders</div>
                <p class="stats-card-value" id="total-orders">0</p>
                <div class="stats-card-meta">All statuses</div>
            </div>

            <div class="stats-card purple">
                <div class="stats-card-label">Conversion Rate</div>
                <p class="stats-card-value" id="conversion-rate">0%</p>
                <div class="stats-card-meta">Completed / Total</div>
            </div>

            <div class="stats-card orange">
                <div class="stats-card-label">Average Order Value</div>
                <p class="stats-card-value" id="aov">Rp 0</p>
                <div class="stats-card-meta">Per completed order</div>
            </div>

            <div class="stats-card green">
                <div class="stats-card-label">Revenue Growth</div>
                <p class="stats-card-value" id="growth">0%</p>
                <div class="stats-card-meta" id="growth-label">vs previous period</div>
            </div>
        </div>

        <!-- 2. SALES TREND CHART -->
        <div class="chart-container">
            <h3 class="chart-title">Sales Trend</h3>
            <div class="chart-wrapper">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- 3. ORDER STATUS & DISTRIBUTION -->
        <div class="charts-row">
            <div class="chart-container">
                <h3 class="chart-title">Order Status Distribution</h3>
                <div class="chart-wrapper">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>

            <div class="chart-container">
                <h3 class="chart-title">Status Breakdown</h3>
                <div class="status-list" id="status-breakdown">
                    <div class="loading">Loading...</div>
                </div>
            </div>
        </div>

        <!-- 4. CUSTOMER ANALYTICS -->
        <div class="chart-container">
            <h3 class="chart-title">Customer Analytics</h3>
            <div class="stats-grid" id="customer-stats">
                <div class="stats-card">
                    <div class="stats-card-label">New Customers</div>
                    <p class="stats-card-value" id="new-customers">0</p>
                    <div class="stats-card-meta">Registered this period</div>
                </div>

                <div class="stats-card purple">
                    <div class="stats-card-label">Active Customers</div>
                    <p class="stats-card-value" id="active-customers">0</p>
                    <div class="stats-card-meta">Purchased this period</div>
                </div>

                <div class="stats-card orange">
                    <div class="stats-card-label">Total Customers</div>
                    <p class="stats-card-value" id="total-customers">0</p>
                    <div class="stats-card-meta">Customer base</div>
                </div>

                <div class="stats-card green">
                    <div class="stats-card-label">Purchasing Rate</div>
                    <p class="stats-card-value" id="purchasing-rate">0%</p>
                    <div class="stats-card-meta">Active / Total</div>
                </div>
            </div>
        </div>

        <!-- Customer Distribution & RFM Charts -->
        <div class="charts-row">
            <!-- Customer Distribution Pie Chart -->
            <div class="chart-container">
                <h3 class="chart-title">Customer Distribution</h3>
                <div class="chart-wrapper" style="position: relative; height: 300px;">
                    <canvas id="customerDistributionChart"></canvas>
                </div>
            </div>

            <!-- RFM Analysis Bar Chart -->
            <div class="chart-container">
                <h3 class="chart-title">Top 5 Customers (RFM)</h3>
                <div class="chart-wrapper" style="position: relative; height: 300px;">
                    <canvas id="rfmChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Customers Table -->
        <div class="chart-container">
            <h3 class="chart-title">Top 10 Customers (Monetary Value)</h3>
            <div style="overflow-x: auto; display: block; width: 100%;">
                <table class="customer-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Customer Name</th>
                            <th>Recency (Days)</th>
                            <th>Frequency</th>
                            <th>Monetary Value</th>
                        </tr>
                    </thead>
                    <tbody id="rfm-table">
                        <tr><td colspan="5" class="loading">Loading customer data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="chart-container">
            <h3 class="chart-title">Conversion Funnel</h3>
            <p style="color: #6b7280; font-size: 12px; margin: 0 0 20px 0;">Visitor journey through purchase process</p>
            <div id="funnel-stages">
                <div class="loading">Loading funnel data...</div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Debug script to check if page elements are ready
            window.addEventListener('DOMContentLoaded', function() {
                console.log('=== Analytics Page Debug ===');
                console.log('Base URL:', document.body.getAttribute('data-base-url'));
                console.log('Period buttons:', document.querySelectorAll('.period-btn').length);
                console.log('Stats cards:', document.querySelectorAll('.stats-card').length);
                console.log('Charts:', {
                    salesTrend: !!document.getElementById('salesTrendChart'),
                    orderStatus: !!document.getElementById('orderStatusChart')
                });
                console.log('Total elements with IDs:', {
                    'total-revenue': !!document.getElementById('total-revenue'),
                    'total-orders': !!document.getElementById('total-orders'),
                    'aov': !!document.getElementById('aov'),
                    'growth': !!document.getElementById('growth')
                });
            });
        </script>
    @endpush
</x-admin-layout>
