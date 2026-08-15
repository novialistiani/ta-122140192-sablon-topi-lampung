<x-customer-layout title="Notifikasi" active="notifikasi">
    @vite(['resources/css/customer/Notifikasi.css'])

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex items-center justify-between">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                    <span class="ml-2 text-sm font-medium text-gray-700">Pilih semua</span>
                </label>
                <button id="markReadBtn" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">
                    <i class="fas fa-check-double mr-1"></i> Tandai sudah dibaca
                </button>
            </div>
        </div>

        <!-- Notification List -->
        <div class="space-y-3" id="notificationList">
            @forelse($notifications as $notification)
            <div class="notification-card bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow {{ $notification->is_read ? 'opacity-60' : '' }}" 
                 data-id="{{ $notification->id }}">
                <div class="flex items-start gap-4">
                    <input type="checkbox" class="notification-checkbox mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 flex-shrink-0" 
                           value="{{ $notification->id }}">
                    <div class="w-12 h-12 bg-gradient-to-br from-{{ $notification->type === 'order_approved' ? 'green' : ($notification->type === 'order_rejected' ? 'red' : 'blue') }}-100 to-{{ $notification->type === 'order_approved' ? 'green' : ($notification->type === 'order_rejected' ? 'red' : 'blue') }}-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-{{ $notification->type === 'order_approved' ? 'check-circle' : ($notification->type === 'order_rejected' ? 'times-circle' : 'info-circle') }} text-{{ $notification->type === 'order_approved' ? 'green' : ($notification->type === 'order_rejected' ? 'red' : 'blue') }}-600 text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 mb-1">{{ $notification->title }}</h3>
                        <p class="text-sm text-gray-600">{{ $notification->message }}</p>
                        <span class="text-xs text-gray-400 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                    @if($notification->action_url)
                    <a href="{{ $notification->action_url }}" 
                       class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition flex-shrink-0">
                        Tampilkan rincian
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <i class="fas fa-bell-slash text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500">Belum ada notifikasi</p>
            </div>
            @endforelse
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const notificationCheckboxes = document.querySelectorAll('.notification-checkbox');
            const markReadBtn = document.getElementById('markReadBtn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Select All Functionality
            selectAllCheckbox?.addEventListener('change', function() {
                notificationCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // Update Select All state when individual checkboxes change
            notificationCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(notificationCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(notificationCheckboxes).some(cb => cb.checked);
                    
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = someChecked && !allChecked;
                    }
                });
            });

            // Mark as Read Functionality
            markReadBtn?.addEventListener('click', async function() {
                const selectedIds = Array.from(notificationCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                if (selectedIds.length === 0) {
                    alert('Pilih minimal satu notifikasi untuk ditandai sudah dibaca');
                    return;
                }

                try {
                    const response = await fetch('/api/notifications/read-selected', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ notification_ids: selectedIds })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Update UI: Add opacity and uncheck
                        selectedIds.forEach(id => {
                            const card = document.querySelector(`.notification-card[data-id="${id}"]`);
                            if (card) {
                                card.classList.add('opacity-60');
                                const checkbox = card.querySelector('.notification-checkbox');
                                if (checkbox) checkbox.checked = false;
                            }
                        });

                        // Reset select all
                        if (selectAllCheckbox) {
                            selectAllCheckbox.checked = false;
                            selectAllCheckbox.indeterminate = false;
                        }

                        // Show success message
                        showToast('Notifikasi berhasil ditandai sudah dibaca', 'success');
                    } else {
                        showToast('Gagal menandai notifikasi', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan', 'error');
                }
            });

            // Toast notification helper
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} z-50`;
                toast.textContent = message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }

            // Poll for new notifications every 30 seconds
            setInterval(async function() {
                try {
                    const response = await fetch('/api/notifications/unread-count', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    
                    // Update badge in header if exists
                    const badge = document.querySelector('.notification-badge');
                    if (badge && data.unread_count > 0) {
                        badge.textContent = data.unread_count;
                        badge.classList.remove('hidden');
                    } else if (badge) {
                        badge.classList.add('hidden');
                    }
                } catch (error) {
                    console.error('Error polling notifications:', error);
                }
            }, 30000);
        });
    </script>
    @endpush
</x-customer-layout>
