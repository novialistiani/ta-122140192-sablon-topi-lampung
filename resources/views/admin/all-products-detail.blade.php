<x-admin-layout title="Product Detail">
    @push('styles')
    @vite(['resources/css/admin/dashboard.css'])
    @endpush

    <div class="page-header">
        <h1 style="margin-bottom: 20px;">Product Detail: {{ $product->name }}</h1>
        <a href="{{ route('admin.all-products') }}" class="btn btn-secondary">← Back to Products</a>
    </div>

    <div style="margin-top: 30px; background: white; padding: 30px; border-radius: 10px;">
        <!-- Product Image Preview with Carousel -->
        <div style="margin-bottom: 30px;">
            <h2 style="font-size: 20px; margin-bottom: 15px;">Product Image</h2>
            <div style="text-align: center; position: relative;">
                @if(!empty($variantImages))
                    <!-- Image Carousel -->
                    <div id="productImageCarousel" style="position: relative; width: 300px; height: 300px; margin: 0 auto; background: #f9f9f9; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        @foreach($variantImages as $index => $image)
                            <img src="{{ $image }}" 
                                 alt="Product image {{ $index + 1 }}" 
                                 class="carousel-image"
                                 style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; opacity: {{ $index === 0 ? '1' : '0' }}; transition: opacity 0.5s ease-in-out; padding: 10px;">
                        @endforeach

                        <!-- Navigation Buttons -->
                        @if(count($variantImages) > 1)
                            <button id="carouselPrev" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 18px; z-index: 10; transition: background 0.3s;">
                                &#10094;
                            </button>
                            <button id="carouselNext" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 18px; z-index: 10; transition: background 0.3s;">
                                &#10095;
                            </button>

                            <!-- Indicator Dots -->
                            <div style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10;">
                                @foreach($variantImages as $index => $image)
                                    <button class="carousel-dot" data-index="{{ $index }}" style="width: 10px; height: 10px; border-radius: 50%; background: {{ $index === 0 ? 'rgba(0,0,0,0.7)' : 'rgba(0,0,0,0.3)' }}; border: none; cursor: pointer; transition: background 0.3s;">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Image Counter -->
                    @if(count($variantImages) > 1)
                        <p style="margin-top: 10px; color: #666; font-size: 14px;">
                            <span id="imageCounter">1</span> of {{ count($variantImages) }}
                        </p>
                    @endif
                @elseif($product->image)
                    <!-- Main Product Image -->
                    <img src="{{ $product->image && (str_starts_with($product->image, 'http://') || str_starts_with($product->image, 'https://')) ? $product->image : asset('storage/' . $product->image) }}" 
                         alt="{{ $product->name }}" 
                         style="max-width: 300px; max-height: 300px; object-fit: contain; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                @else
                    <!-- No Image Fallback -->
                    <svg style="width: 300px; height: 300px; background: #f0f0f0; border-radius: 10px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
                        <rect fill="#f0f0f0" width="400" height="400" rx="10"/>
                        <g transform="translate(100, 100)">
                            <rect fill="#e0e0e0" width="200" height="200" rx="10"/>
                            <circle fill="#d0d0d0" cx="100" cy="80" r="30"/>
                            <path fill="#d0d0d0" d="M 40 150 L 80 100 L 160 160 L 200 120 L 200 200 L 40 200 Z"/>
                        </g>
                        <text x="200" y="370" font-family="Arial" font-size="18" fill="#999" text-anchor="middle">No Main Image</text>
                    </svg>
                @endif
            </div>
        </div>

        <!-- Product Information -->
        <div style="margin-bottom: 30px;">
            <h2 style="font-size: 20px; margin-bottom: 15px;">Product Information</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold; width: 200px;">Product Name</td>
                    <td style="padding: 10px;">{{ $product->name }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold;">Category</td>
                    <td style="padding: 10px;">{{ $product->category ?? 'N/A' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold;">Price</td>
                    <td style="padding: 10px;">Rp {{ number_format((float)$product->price, 0, ',', '.') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold;">Total Variants</td>
                    <td style="padding: 10px;">{{ $product->variants->count() }} variant(s)</td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold;">Status</td>
                    <td style="padding: 10px;">
                        <span style="padding: 5px 10px; border-radius: 5px; background: {{ $product->is_active ? '#d4edda' : '#f8d7da' }}; color: {{ $product->is_active ? '#155724' : '#721c24' }};">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Product Variants -->
        <div style="margin-bottom: 30px;">
            <h2 style="font-size: 20px; margin-bottom: 15px;">Variants</h2>
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                <thead>
                    <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                        <th style="padding: 15px; text-align: left; border-right: 1px solid #ddd; width: 100px;">Image</th>
                        <th style="padding: 15px; text-align: left; border-right: 1px solid #ddd;">Color</th>
                        <th style="padding: 15px; text-align: left; border-right: 1px solid #ddd;">Size</th>
                        <th style="padding: 15px; text-align: left; border-right: 1px solid #ddd;">Price</th>
                        <th style="padding: 15px; text-align: left;">Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($product->variants as $variant)
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 15px; border-right: 1px solid #ddd; text-align: center;">
                            @if($variant->image)
                                <img src="{{ $variant->image && (str_starts_with($variant->image, 'http://') || str_starts_with($variant->image, 'https://')) ? $variant->image : asset('storage/' . $variant->image) }}" 
                                     alt="{{ $variant->color }}" 
                                     style="max-width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                            @else
                                <svg style="width: 80px; height: 80px; background: #f0f0f0; border-radius: 5px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                                    <rect fill="#f0f0f0" width="100" height="100"/>
                                    <text x="50" y="55" font-family="Arial" font-size="11" fill="#999" text-anchor="middle">No Image</text>
                                </svg>
                            @endif
                        </td>
                        <td style="padding: 15px; border-right: 1px solid #ddd;">{{ $variant->color ?? 'N/A' }}</td>
                        <td style="padding: 15px; border-right: 1px solid #ddd;">{{ $variant->size ?? 'N/A' }}</td>
                        <td style="padding: 15px; border-right: 1px solid #ddd;">Rp {{ number_format((float)$variant->price, 0, ',', '.') }}</td>
                        <td style="padding: 15px;">{{ $variant->stock }} units</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 15px; text-align: center; color: #999;">No variants found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Order Filter Tabs -->
        <div style="margin-bottom: 30px;">
            <h2 style="font-size: 20px; margin-bottom: 15px;">Orders by Category</h2>
            
            <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #ddd;">
                <button onclick="filterOrders('all')" class="tab-btn" style="padding: 10px 20px; border: none; background: none; cursor: pointer; border-bottom: 3px solid #0a1d37; color: #0a1d37; font-weight: bold;">
                    All ({{ count($orders) }})
                </button>
                <button onclick="filterOrders('pesan')" class="tab-btn" style="padding: 10px 20px; border: none; background: none; cursor: pointer; color: #666;">
                    Pesan ({{ $ordersByCategory['pesan']->count() }})
                </button>
                </button>
                <button onclick="filterOrders('proses')" class="tab-btn" style="padding: 10px 20px; border: none; background: none; cursor: pointer; color: #666;">
                    Proses ({{ $ordersByCategory['proses']->count() }})
                </button>
                <button onclick="filterOrders('completed')" class="tab-btn" style="padding: 10px 20px; border: none; background: none; cursor: pointer; color: #666;">
                    Completed ({{ $ordersByCategory['completed']->count() }})
                </button>
                <button onclick="filterOrders('cancel')" class="tab-btn" style="padding: 10px 20px; border: none; background: none; cursor: pointer; color: #666;">
                    Cancel ({{ $ordersByCategory['cancel']->count() }})
                </button>
                <button onclick="filterOrders('batal')" class="tab-btn" style="padding: 10px 20px; border: none; background: none; cursor: pointer; color: #666;">
                    Batal ({{ $ordersByCategory['batal']->count() }})
                </button>
            </div>

            <!-- Orders Table -->
            <div id="orders-container">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;" id="orders-table">
                    <thead>
                        <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                            <th style="padding: 15px; text-align: left; border-right: 1px solid #ddd;">Order ID</th>
                            <th style="padding: 15px; text-align: left; border-right: 1px solid #ddd;">Customer</th>
                            <th style="padding: 15px; text-align: left; border-right: 1px solid #ddd;">Status</th>
                            <th style="padding: 15px; text-align: left; border-right: 1px solid #ddd;">Total</th>
                            <th style="padding: 15px; text-align: left;">Date</th>
                        </tr>
                    </thead>
                    <tbody id="orders-body">
                        @forelse($orders as $order)
                        <tr style="border-bottom: 1px solid #ddd;" class="order-row" data-category="{{ $order->status }}">
                            <td style="padding: 15px; border-right: 1px solid #ddd;">{{ $order->order_number }}</td>
                            <td style="padding: 15px; border-right: 1px solid #ddd;">{{ $order->user->name ?? 'Unknown' }}</td>
                            <td style="padding: 15px; border-right: 1px solid #ddd;">
                                <span style="padding: 5px 10px; border-radius: 5px; background: {{ $order->status === 'completed' ? '#d4edda' : ($order->status === 'cancelled' ? '#f8d7da' : '#fff3cd') }}; color: {{ $order->status === 'completed' ? '#155724' : ($order->status === 'cancelled' ? '#721c24' : '#856404') }};">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td style="padding: 15px; border-right: 1px solid #ddd;">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td style="padding: 15px;">{{ $order->formatted_last_action }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="padding: 15px; text-align: center; color: #999;">No orders found for this product</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(empty($orders))
            <div style="text-align: center; padding: 30px; color: #999;">
                No orders found for this product yet.
            </div>
            @endif
        </div>
    </div>

    <script>
        function filterOrders(category) {
            const rows = document.querySelectorAll('.order-row');
            const buttons = document.querySelectorAll('.tab-btn');

            if (category === 'all') {
                rows.forEach(row => row.style.display = 'table-row');
            } else {
                rows.forEach(row => {
                    const rowCategory = row.getAttribute('data-category');
                    const categoryMap = {
                        'pending': 'pesan',
                        'processing': 'proses',
                        'completed': 'completed',
                        'cancelled': 'cancel',
                        'rejected': 'batal'
                    };
                    
                    if (categoryMap[rowCategory] === category) {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Update button styles
            buttons.forEach((btn, index) => {
                if (index === 0 && category === 'all') {
                    btn.style.borderBottom = '3px solid #0a1d37';
                    btn.style.color = '#0a1d37';
                    btn.style.fontWeight = 'bold';
                } else if (index > 0) {
                    const btnText = btn.textContent.split('(')[0].trim().toLowerCase();
                    if (btnText === category || category === 'all') {
                        if (category !== 'all') {
                            btn.style.borderBottom = '3px solid #0a1d37';
                            btn.style.color = '#0a1d37';
                            btn.style.fontWeight = 'bold';
                        } else {
                            btn.style.borderBottom = 'none';
                            btn.style.color = '#666';
                            btn.style.fontWeight = 'normal';
                        }
                    } else {
                        btn.style.borderBottom = 'none';
                        btn.style.color = '#666';
                        btn.style.fontWeight = 'normal';
                    }
                } else if (index === 0) {
                    btn.style.borderBottom = 'none';
                    btn.style.color = '#666';
                    btn.style.fontWeight = 'normal';
                }
            });
        }

        // Image Carousel Functionality
        (function() {
            const carousel = document.getElementById('productImageCarousel');
            if (!carousel) return;

            let currentIndex = 0;
            const images = carousel.querySelectorAll('.carousel-image');
            const dots = document.querySelectorAll('.carousel-dot');
            const carouselPrev = document.getElementById('carouselPrev');
            const carouselNext = document.getElementById('carouselNext');
            const imageCounter = document.getElementById('imageCounter');

            if (images.length <= 1) return;

            function showImage(index) {
                // Wrap around
                currentIndex = (index + images.length) % images.length;

                // Update images
                images.forEach((img, i) => {
                    img.style.opacity = i === currentIndex ? '1' : '0';
                });

                // Update dots
                dots.forEach((dot, i) => {
                    dot.style.background = i === currentIndex ? 'rgba(0,0,0,0.7)' : 'rgba(0,0,0,0.3)';
                });

                // Update counter
                if (imageCounter) {
                    imageCounter.textContent = currentIndex + 1;
                }
            }

            // Navigation handlers
            if (carouselPrev) {
                carouselPrev.addEventListener('click', () => {
                    showImage(currentIndex - 1);
                });
                carouselPrev.addEventListener('mouseover', function() {
                    this.style.background = 'rgba(0,0,0,0.8)';
                });
                carouselPrev.addEventListener('mouseout', function() {
                    this.style.background = 'rgba(0,0,0,0.5)';
                });
            }

            if (carouselNext) {
                carouselNext.addEventListener('click', () => {
                    showImage(currentIndex + 1);
                });
                carouselNext.addEventListener('mouseover', function() {
                    this.style.background = 'rgba(0,0,0,0.8)';
                });
                carouselNext.addEventListener('mouseout', function() {
                    this.style.background = 'rgba(0,0,0,0.5)';
                });
            }

            // Dot click handlers
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    showImage(index);
                });
            });

            // Auto-rotate carousel
            setInterval(() => {
                showImage(currentIndex + 1);
            }, 3000);
        })();

        // Real-time Orders Sync
        (function() {
            const productId = {{ $product->id }};
            const ordersBody = document.getElementById('orders-body');
            const orderCategoryTabs = document.querySelectorAll('.tab-btn');
            let currentCategory = 'all';

            // Function to fetch orders from API
            async function fetchOrders() {
                try {
                    const response = await fetch(`/admin/api/products/${productId}/orders`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (!response.ok) throw new Error('Failed to fetch orders');

                    const data = await response.json();
                    if (data.success) {
                        updateOrdersDisplay(data);
                    }
                } catch (error) {
                    console.error('Error fetching orders:', error);
                }
            }

            // Function to update orders display
            function updateOrdersDisplay(data) {
                const { orders, categories } = data;

                // Update category counts in tabs
                updateCategoryTabs(categories);

                // Filter and display orders based on current category
                displayOrders(orders, categories, currentCategory);
            }

            // Update category tab counts
            function updateCategoryTabs(categories) {
                const categoryMap = {
                    'all': 'All',
                    'pesan': 'Pesan',
                    'proses': 'Proses',
                    'completed': 'Completed',
                    'cancel': 'Cancel',
                    'batal': 'Batal'
                };

                orderCategoryTabs.forEach(btn => {
                    const btnText = btn.textContent.split('(')[0].trim();
                    let categoryKey = Object.keys(categoryMap).find(key => categoryMap[key] === btnText);

                    if (categoryKey && categories[categoryKey] !== undefined) {
                        btn.innerHTML = `${btnText} (${categories[categoryKey]})`;
                    }
                });
            }

            // Display orders in table
            function displayOrders(orders, categories, filterBy) {
                // Filter orders by category
                let filteredOrders = orders;
                if (filterBy !== 'all') {
                    const statusMap = {
                        'pesan': 'pending',
                        'proses': 'processing',
                        'completed': 'completed',
                        'cancel': 'cancelled',
                        'batal': 'rejected'
                    };
                    const filterStatus = statusMap[filterBy];
                    filteredOrders = orders.filter(order => order.status === filterStatus);
                }

                // Clear existing rows
                ordersBody.innerHTML = '';

                // Add rows for each order
                if (filteredOrders.length > 0) {
                    filteredOrders.forEach(order => {
                        const row = createOrderRow(order);
                        ordersBody.appendChild(row);
                    });
                } else {
                    // Show "No orders" message
                    const emptyRow = document.createElement('tr');
                    emptyRow.innerHTML = '<td colspan="5" style="padding: 15px; text-align: center; color: #999;">No orders found for this product</td>';
                    ordersBody.appendChild(emptyRow);
                }
            }

            // Create order row HTML
            function createOrderRow(order) {
                const row = document.createElement('tr');
                row.style.borderBottom = '1px solid #ddd';
                row.className = 'order-row';
                row.setAttribute('data-category', order.status);

                const statusColors = {
                    'completed': { bg: '#d4edda', color: '#155724' },
                    'cancelled': { bg: '#f8d7da', color: '#721c24' },
                    'rejected': { bg: '#f8d7da', color: '#721c24' },
                    'pending': { bg: '#fff3cd', color: '#856404' },
                    'processing': { bg: '#fff3cd', color: '#856404' }
                };

                const colors = statusColors[order.status] || { bg: '#fff3cd', color: '#856404' };

                row.innerHTML = `
                    <td style="padding: 15px; border-right: 1px solid #ddd;">${order.order_number}</td>
                    <td style="padding: 15px; border-right: 1px solid #ddd;">${order.customer_name}</td>
                    <td style="padding: 15px; border-right: 1px solid #ddd;">
                        <span style="padding: 5px 10px; border-radius: 5px; background: ${colors.bg}; color: ${colors.color};">
                            ${order.status_label}
                        </span>
                    </td>
                    <td style="padding: 15px; border-right: 1px solid #ddd;">${order.total_formatted}</td>
                    <td style="padding: 15px;">${order.created_at}</td>
                `;

                return row;
            }

            // Override filterOrders function to work with real-time data
            window.filterOrders = function(category) {
                currentCategory = category;
                
                // Update button styles
                orderCategoryTabs.forEach(btn => {
                    if (category === 'all' && btn.textContent.startsWith('All')) {
                        btn.style.borderBottom = '3px solid #0a1d37';
                        btn.style.color = '#0a1d37';
                        btn.style.fontWeight = 'bold';
                    } else {
                        const btnText = btn.textContent.split('(')[0].trim().toLowerCase();
                        const categoryMap = {
                            'pesan': 'pesan',
                            'proses': 'proses',
                            'completed': 'completed',
                            'cancel': 'cancel',
                            'batal': 'batal'
                        };

                        if (categoryMap[btnText] === category || (category === 'all' && btn.textContent.startsWith('All'))) {
                            if (category !== 'all' || btn.textContent.startsWith('All')) {
                                btn.style.borderBottom = '3px solid #0a1d37';
                                btn.style.color = '#0a1d37';
                                btn.style.fontWeight = 'bold';
                            } else {
                                btn.style.borderBottom = 'none';
                                btn.style.color = '#666';
                                btn.style.fontWeight = 'normal';
                            }
                        } else {
                            btn.style.borderBottom = 'none';
                            btn.style.color = '#666';
                            btn.style.fontWeight = 'normal';
                        }
                    }
                });

                // Fetch and display orders
                fetchOrders();
            };

            // Initial load
            fetchOrders();

            // Auto-sync orders every 5 seconds
            setInterval(fetchOrders, 5000);
        })();
    </script>

    @push('scripts')
    @endpush
</x-admin-layout>
