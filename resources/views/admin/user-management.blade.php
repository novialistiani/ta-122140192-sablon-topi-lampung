<x-admin-layout title="User Management">
    <!-- Hidden fields for current admin info -->
    <input type="hidden" id="currentAdminId" value="{{ $currentAdmin->id }}">
    <input type="hidden" id="currentAdminRole" value="{{ $currentAdmin->role }}">
    
    <div class="user-management-wrapper">
        <!-- Tab Navigation - Segmented Control Style -->
        <div class="tab-segmented-container">
            @if($currentAdmin->isSuperAdmin())
            <button 
                onclick="switchTab('admin')"
                id="adminTab"
                class="tab-segment tab-segment-active"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tab-icon">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Admin</span>
            </button>
            @endif
            <button 
                onclick="switchTab('customer')"
                id="customerTab"
                class="tab-segment {{ $currentAdmin->isSuperAdmin() ? 'tab-segment-inactive' : 'tab-segment-active' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tab-icon">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 11l-3-3 3-3"></path>
                    <path d="M16 8h6"></path>
                </svg>
                <span>Customer</span>
            </button>
        </div>
        
        @if(!$currentAdmin->isSuperAdmin())
        <script>
            // Auto-show customer section for regular admin
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('customerSection').classList.remove('hidden');
            });
        </script>
        @endif

        <!-- Admin Section -->
        <div id="adminSection" class="table-card" @if(!$currentAdmin->isSuperAdmin()) style="display: none;" @endif>
            <!-- Header with Add Button -->
            <div class="table-card-header">
                <h2 class="table-card-title">Admin Accounts</h2>
                <div style="display: flex; gap: 12px;">
                    <a href="{{ route('admin.management-users.export-admins') }}" class="btn-add-primary" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Export Excel</span>
                    </a>
                    @if($currentAdmin->isSuperAdmin())
                    <button 
                        onclick="openModal('admin')"
                        class="btn-add-primary"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Add Admin</span>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Table -->
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr class="table-header-row">
                            <th class="table-header-cell" style="width: 60px;">Profile</th>
                            <th class="table-header-cell table-col-name">Name</th>
                            <th class="table-header-cell table-col-email">Email</th>
                            <th class="table-header-cell table-col-role">Role</th>
                            <th class="table-header-cell table-col-status">Status</th>
                            <th class="table-header-cell table-col-actions">Actions</th>
                            <th class="table-header-cell table-col-history">History</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        @forelse($admins as $admin)
                        <tr class="table-row">
                            <td class="table-cell" style="padding: 8px;">
                                @if($admin->avatar)
                                    <img src="{{ Storage::url($admin->avatar) }}" alt="{{ $admin->name }}" class="user-avatar-preview">
                                @else
                                    <div class="user-avatar-placeholder">
                                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="table-cell">
                                <span class="user-name">{{ $admin->name }}</span>
                            </td>
                            <td class="table-cell">
                                <span class="user-email">{{ $admin->email }}</span>
                            </td>
                            <td class="table-cell">
                                <span class="badge {{ $admin->role === 'super_admin' ? 'badge-blue' : ($admin->role === 'admin' ? 'badge-purple' : 'badge-gray') }}">
                                    {{ $admin->role_name }}
                                </span>
                            </td>
                            <td class="table-cell">
                                <span class="badge {{ $admin->status === 'active' ? 'badge-green' : 'badge-gray' }}">
                                    {{ ucfirst($admin->status) }}
                                </span>
                            </td>
                            <td class="table-cell">
                                <div class="action-buttons">
                                    @if($currentAdmin->isSuperAdmin() || $currentAdmin->id === $admin->id)
                                    <button 
                                        onclick="editUser('admin', {{ $admin->id }})"
                                        class="action-btn action-btn-edit"
                                        title="{{ $currentAdmin->id === $admin->id ? 'Edit My Profile' : 'Edit Admin' }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    @endif
                                    
                                    @if($currentAdmin->isSuperAdmin() && $currentAdmin->id !== $admin->id)
                                    <button 
                                        onclick="deleteUser('admin', {{ $admin->id }})"
                                        class="action-btn action-btn-delete"
                                        title="Delete Admin"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                    @endif
                                    
                                    @if(!$currentAdmin->isSuperAdmin() && $currentAdmin->id !== $admin->id)
                                    <span style="color: #9CA3AF; font-size: 12px; font-style: italic;">No access</span>
                                    @endif
                                </div>
                            </td>
                            <td class="table-cell">
                                <button 
                                    onclick="viewHistory('admin', {{ $admin->id }})"
                                    class="btn-history"
                                    title="View History"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <span>History</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="table-cell table-empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-icon">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <p class="empty-text">No admins found. Click "+ Add Admin" to create one.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing <span class="pagination-highlight">1–2</span> of <span class="pagination-highlight">10</span>
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn pagination-btn-disabled" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Previous</span>
                    </button>
                    <button class="pagination-btn">
                        <span>Next</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Customer Section (Hidden by default) -->
        <div id="customerSection" class="table-card hidden">
            <!-- Header -->
            <div class="table-card-header">
                <h2 class="table-card-title">Customer Accounts</h2>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <a href="{{ route('admin.management-users.export-customers') }}" class="btn-add-primary" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Export Excel</span>
                    </a>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6B7280;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>Customers register themselves. View-only mode.</span>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr class="table-header-row">
                            <th class="table-header-cell" style="width: 60px;">Profile</th>
                            <th class="table-header-cell" style="width: 16%;">Name</th>
                            <th class="table-header-cell" style="width: 23%;">Email</th>
                            <th class="table-header-cell" style="width: 16%;">Phone</th>
                            <th class="table-header-cell" style="width: 11%;">Status</th>
                            <th class="table-header-cell" style="width: 15%;">Actions</th>
                            <th class="table-header-cell" style="width: 10%;">History</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        @forelse($users as $user)
                        <tr class="table-row">
                            <td class="table-cell" style="padding: 8px;">
                                @if($user->avatar)
                                    <a href="{{ route('admin.customer-detail', $user->id) }}">
                                        <img src="{{ Storage::url($user->avatar) }}" 
                                             alt="{{ $user->name }}" 
                                             class="user-avatar-preview clickable-avatar" 
                                             style="cursor: pointer;"
                                             title="Click to view details">
                                    </a>
                                @else
                                    <a href="{{ route('admin.customer-detail', $user->id) }}">
                                        <div class="user-avatar-placeholder clickable-avatar" 
                                             style="cursor: pointer;"
                                             title="Click to view details">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    </a>
                                @endif
                            </td>
                            <td class="table-cell">
                                <span class="user-name">{{ $user->name }}</span>
                            </td>
                            <td class="table-cell">
                                <span class="user-email">{{ $user->email }}</span>
                            </td>
                            <td class="table-cell">
                                <span class="user-phone">{{ $user->phone ?? '-' }}</span>
                            </td>
                            <td class="table-cell">
                                <span class="badge badge-green">
                                    Active
                                </span>
                            </td>
                            <td class="table-cell">
                                <div class="action-buttons">
                                    <!-- Customer cannot be edited by admin -->
                                    <button 
                                        onclick="deleteUser('customer', {{ $user->id }})"
                                        class="action-btn action-btn-delete"
                                        title="Delete Customer"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="table-cell">
                                <button 
                                    onclick="viewHistory('customer', {{ $user->id }})"
                                    class="btn-history"
                                    title="View History"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <span>History</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="table-cell table-empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-icon">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                                <p class="empty-text">No customers found. Customers will appear here after Google login.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing <span class="pagination-highlight">1–1</span> of <span class="pagination-highlight">5</span>
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn pagination-btn-disabled" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        <span>Previous</span>
                    </button>
                    <button class="pagination-btn">
                        <span>Next</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div id="addAdminModal" class="modal-overlay hidden">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Add New Admin</h3>
                <button onclick="closeModal()" class="modal-close" type="button" aria-label="Close modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <form id="addAdminForm" onsubmit="submitAdminForm(event)" class="modal-body">
                <!-- Name -->
                <div class="form-group">
                    <label for="admin_name" class="form-label">Full Name <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="admin_name" 
                        name="name" 
                        class="form-input"
                        placeholder="Enter admin full name"
                        required
                    >
                    <span class="form-error" id="error_name"></span>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="admin_email" class="form-label">Email Address <span class="required">*</span></label>
                    <input 
                        type="email" 
                        id="admin_email" 
                        name="email" 
                        class="form-input"
                        placeholder="admin@example.com"
                        required
                    >
                    <span class="form-error" id="error_email"></span>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="admin_password" class="form-label">Password <span class="required">*</span></label>
                    <input 
                        type="password" 
                        id="admin_password" 
                        name="password" 
                        class="form-input"
                        placeholder="Minimum 6 characters"
                        minlength="6"
                        required
                    >
                    <span class="form-error" id="error_password"></span>
                    <small style="color: #6B7280; font-size: 12px; display: block; margin-top: 4px;">
                        Minimum 6 characters
                    </small>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="admin_password_confirmation" class="form-label">Confirm Password <span class="required">*</span></label>
                    <input 
                        type="password" 
                        id="admin_password_confirmation" 
                        name="password_confirmation" 
                        class="form-input"
                        placeholder="Re-enter password"
                        minlength="6"
                        required
                    >
                    <span class="form-error" id="error_password_confirmation"></span>
                </div>

                <!-- Role -->
                <div class="form-group">
                    <label for="admin_role" class="form-label">Role <span class="required">*</span></label>
                    <select id="admin_role" name="role" class="form-select" required>
                        <option value="">Select Role</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                    </select>
                    <span class="form-error" id="error_role"></span>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label for="admin_status" class="form-label">Status <span class="required">*</span></label>
                    <select id="admin_status" name="status" class="form-select" required>
                        <option value="">Select Status</option>
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <span class="form-error" id="error_status"></span>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeModal()" class="btn-cancel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" class="btn-primary" id="submitBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Create Admin</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal-overlay hidden">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Edit User</h3>
                <button onclick="closeEditModal()" class="modal-close" aria-label="Close modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form id="editUserForm" onsubmit="submitEditForm(event)" class="modal-body">
                <input type="hidden" id="editUserId" name="id">
                <input type="hidden" id="editUserType" name="type">

                <div class="form-group">
                    <label for="editName" class="form-label">Name <span class="required">*</span></label>
                    <input type="text" id="editName" name="name" class="form-input" required placeholder="Enter full name">
                    <span id="edit_error_name" class="form-error"></span>
                </div>

                <div class="form-group" id="editEmailGroup">
                    <label for="editEmail" class="form-label">Email <span class="required">*</span></label>
                    <input type="email" id="editEmail" name="email" class="form-input" placeholder="admin@example.com">
                    <span id="edit_error_email" class="form-error"></span>
                    <small style="color: #6B7280; font-size: 12px; display: block; margin-top: 4px;">
                        📧 Confirmation email will be sent to old email address
                    </small>
                </div>

                <div class="form-group" id="editPasswordGroup">
                    <label for="editPassword" class="form-label">New Password</label>
                    <input type="password" id="editPassword" name="password" class="form-input" placeholder="Leave blank to keep current password">
                    <span id="edit_error_password" class="form-error"></span>
                    <small style="color: #6B7280; font-size: 12px; display: block; margin-top: 4px;">
                        Minimum 6 characters
                    </small>
                </div>

                <div class="form-group" id="editPasswordConfirmGroup">
                    <label for="editPasswordConfirmation" class="form-label">Confirm New Password</label>
                    <input type="password" id="editPasswordConfirmation" name="password_confirmation" class="form-input" placeholder="Retype new password">
                    <span id="edit_error_password_confirmation" class="form-error"></span>
                </div>

                <!-- Role (Only for Super Admin editing other admins) -->
                <div class="form-group" id="editRoleGroup" style="display: none;">
                    <label for="editRole" class="form-label">Role <span class="required">*</span></label>
                    <select id="editRole" name="role" class="form-select">
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                    </select>
                    <span id="edit_error_role" class="form-error"></span>
                </div>

                <!-- Status (Only for Super Admin editing other admins) -->
                <div class="form-group" id="editStatusGroup" style="display: none;">
                    <label for="editStatus" class="form-label">Status <span class="required">*</span></label>
                    <select id="editStatus" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <span id="edit_error_status" class="form-error"></span>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeEditModal()" class="btn-cancel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Update User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Detail Modal -->
    <div id="customerDetailModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 class="modal-title">Customer Detail</h2>
                <button type="button" onclick="closeCustomerDetailModal()" class="modal-close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="modal-body" style="padding: 24px;">
                <!-- Avatar Section -->
                <div style="text-align: center; margin-bottom: 24px;">
                    <div id="customerDetailAvatar" style="width: 100px; height: 100px; border-radius: 50%; margin: 0 auto; overflow: hidden; border: 3px solid #e0e0e0;">
                        <!-- Avatar will be inserted here -->
                    </div>
                    <h3 id="customerDetailName" style="margin-top: 16px; font-size: 20px; font-weight: 600; color: #333;"></h3>
                </div>

                <!-- Detail Grid -->
                <div style="display: grid; gap: 16px;">
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value" id="customerDetailEmail"></div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value" id="customerDetailPhone"></div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Address</div>
                        <div class="detail-value" id="customerDetailAddress"></div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="detail-item">
                            <div class="detail-label">City</div>
                            <div class="detail-value" id="customerDetailCity"></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Province</div>
                            <div class="detail-value" id="customerDetailProvince"></div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="detail-item">
                            <div class="detail-label">District</div>
                            <div class="detail-value" id="customerDetailDistrict"></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Postal Code</div>
                            <div class="detail-value" id="customerDetailPostalCode"></div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="detail-item">
                            <div class="detail-label">Joined Date</div>
                            <div class="detail-value" id="customerDetailCreatedAt"></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Last Updated</div>
                            <div class="detail-value" id="customerDetailUpdatedAt"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeCustomerDetailModal()" class="btn-cancel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    <span>Close</span>
                </button>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/admin/user-management.css')
    @endpush

    @push('scripts')
    @vite('resources/js/admin/user-management.js')
    @endpush
</x-admin-layout>
