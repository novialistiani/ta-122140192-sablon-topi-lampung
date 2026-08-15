/**
 * User Management JavaScript
 * Handles tab switching, modal operations, and CRUD actions for admin/customer users
 */

/**
 * Switch between Admin and Customer tabs
 * @param {string} tab - 'admin' or 'customer'
 */
function switchTab(tab) {
    const adminTab = document.getElementById('adminTab');
    const customerTab = document.getElementById('customerTab');
    const adminSection = document.getElementById('adminSection');
    const customerSection = document.getElementById('customerSection');

    if (tab === 'admin') {
        // Active admin tab
        adminTab.className = 'tab-segment tab-segment-active';
        customerTab.className = 'tab-segment tab-segment-inactive';
        
        // Show admin section
        adminSection.classList.remove('hidden');
        customerSection.classList.add('hidden');
    } else {
        // Active customer tab
        customerTab.className = 'tab-segment tab-segment-active';
        adminTab.className = 'tab-segment tab-segment-inactive';
        
        // Show customer section
        customerSection.classList.remove('hidden');
        adminSection.classList.add('hidden');
    }
}

/**
 * Open modal for adding new user
 * @param {string} type - 'admin' or 'customer'
 */
function openModal(type) {
    if (type === 'admin') {
        document.getElementById('addAdminModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close modal and reset form
 */
function closeModal() {
    document.getElementById('addAdminModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Reset form
    document.getElementById('addAdminForm').reset();
    
    // Clear errors
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
}

/**
 * Submit admin form via AJAX
 * @param {Event} event - Form submit event
 */
async function submitAdminForm(event) {
    event.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('addAdminForm');
    const formData = new FormData(form);
    
    // Clear previous errors
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Creating...</span>';
    
    try {
        const response = await fetch('/admin/api/admins', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Success
            closeModal();
            alert('Admin created successfully!');
            location.reload();
        } else {
            // Validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach(key => {
                    const errorElement = document.getElementById(`error_${key}`);
                    if (errorElement) {
                        errorElement.textContent = data.errors[key][0];
                    }
                });
            } else {
                alert(data.message || 'Failed to create admin');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg><span>Create Admin</span>';
    }
}

/**
 * Edit user (admin or customer)
 * @param {string} type - 'admin' or 'customer'
 * @param {number} id - User ID
 */
async function editUser(type, id) {
    // Customers cannot be edited
    if (type === 'customer') {
        alert('Customers cannot be edited by admin. They manage their own profile.');
        return;
    }

    try {
        // Fetch user data
        const response = await fetch(`/admin/api/${type}s/${id}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch user data');
        }
        
        const data = await response.json();
        const user = data.data;
        
        // Get current admin info
        const currentAdminId = parseInt(document.getElementById('currentAdminId')?.value || '0');
        const currentAdminRole = document.getElementById('currentAdminRole')?.value || 'admin';
        const isEditingSelf = currentAdminId === user.id;
        const isSuperAdmin = currentAdminRole === 'super_admin';
        
        // Populate modal with user data
        document.getElementById('editUserId').value = id;
        document.getElementById('editUserType').value = type;
        document.getElementById('editName').value = user.name;
        document.getElementById('editEmail').value = user.email;
        
        // Handle field visibility based on permissions
        const emailGroup = document.getElementById('editEmailGroup');
        const passwordGroup = document.getElementById('editPasswordGroup');
        const passwordConfirmGroup = document.getElementById('editPasswordConfirmGroup');
        const roleGroup = document.getElementById('editRoleGroup');
        const statusGroup = document.getElementById('editStatusGroup');
        const modalTitle = document.querySelector('#editUserModal .modal-title');
        
        if (isEditingSelf) {
            // Editing own account
            modalTitle.textContent = 'Edit My Profile';
            emailGroup.style.display = 'none'; // Cannot change own email
            passwordGroup.style.display = 'block'; // Can change password
            passwordConfirmGroup.style.display = 'block';
            roleGroup.style.display = 'none'; // Cannot change own role
            statusGroup.style.display = 'none'; // Cannot change own status
            document.getElementById('editPassword').required = false;
        } else if (isSuperAdmin) {
            // Super admin editing other admin
            modalTitle.textContent = 'Edit Admin';
            emailGroup.style.display = 'block'; // Can change email
            passwordGroup.style.display = 'none'; // Cannot change password
            passwordConfirmGroup.style.display = 'none';
            roleGroup.style.display = 'block'; // Can change role
            statusGroup.style.display = 'block'; // Can change status
            
            // Set current role and status
            document.getElementById('editRole').value = user.role;
            document.getElementById('editStatus').value = user.status;
        } else {
            // Regular admin trying to edit another admin (shouldn't happen)
            alert('Only Super Admin can edit other admin accounts.');
            return;
        }
        
        // Show modal
        document.getElementById('editUserModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load user data. Please try again.');
    }
}

/**
 * Delete user with confirmation
 * @param {string} type - 'admin' or 'customer'
 * @param {number} id - User ID
 */
async function deleteUser(type, id) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/api/${type}s/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert(data.message || 'User deleted successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to delete user');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

/**
 * Close edit modal
 */
function closeEditModal() {
    document.getElementById('editUserModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Reset form
    document.getElementById('editUserForm').reset();
    
    // Clear errors
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
}

/**
 * Submit edit form
 * @param {Event} event - Form submit event
 */
async function submitEditForm(event) {
    event.preventDefault();
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const form = document.getElementById('editUserForm');
    const formData = new FormData(form);
    const userId = document.getElementById('editUserId').value;
    const userType = document.getElementById('editUserType').value;
    
    // Clear previous errors
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Updating...</span>';
    
    try {
        const response = await fetch(`/admin/api/${userType}s/${userId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const data = await response.json();
        
        if (response.ok) {
            closeEditModal();
            alert('User updated successfully!');
            location.reload();
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(key => {
                    const errorElement = document.getElementById(`edit_error_${key}`);
                    if (errorElement) {
                        errorElement.textContent = data.errors[key][0];
                    }
                });
            } else {
                alert(data.message || 'Failed to update user');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span>Update User</span>';
    }
}

/**
 * View activity history for a user
 * @param {string} type - 'admin' or 'customer'
 * @param {number} id - User ID
 */
function viewHistory(type, id) {
    // Redirect to activity logs with filter
    window.location.href = `/admin/activity-logs?user_type=${type}&user_id=${id}`;
    
    // Alternative: Show in modal
    // alert('Viewing ' + type + ' history for ID: ' + id);
    // You can implement modal to show activity logs here
}

/**
 * View customer detail in modal
 * @param {number} id - Customer ID
 */
async function viewCustomerDetail(id) {
    try {
        const response = await fetch(`/admin/api/customers/${id}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            const customer = result.data;
            
            // Update modal content
            document.getElementById('customerDetailName').textContent = customer.name || '-';
            document.getElementById('customerDetailEmail').textContent = customer.email || '-';
            document.getElementById('customerDetailPhone').textContent = customer.phone || '-';
            document.getElementById('customerDetailAddress').textContent = customer.address || '-';
            document.getElementById('customerDetailCity').textContent = customer.city || '-';
            document.getElementById('customerDetailProvince').textContent = customer.province || '-';
            document.getElementById('customerDetailDistrict').textContent = customer.district || '-';
            document.getElementById('customerDetailPostalCode').textContent = customer.postal_code || '-';
            document.getElementById('customerDetailCreatedAt').textContent = customer.created_at || '-';
            document.getElementById('customerDetailUpdatedAt').textContent = customer.updated_at || '-';
            
            // Update avatar
            const avatarDiv = document.getElementById('customerDetailAvatar');
            if (customer.avatar) {
                avatarDiv.innerHTML = `<img src="${customer.avatar}" alt="${customer.name}" style="width: 100%; height: 100%; object-fit: cover;">`;
            } else {
                const initial = customer.name ? customer.name.charAt(0).toUpperCase() : 'U';
                avatarDiv.innerHTML = `<div style="width: 100%; height: 100%; background: #e0e0e0; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 600; color: #666;">${initial}</div>`;
            }
            
            // Show modal
            document.getElementById('customerDetailModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        } else {
            alert('Failed to load customer details: ' + result.message);
        }
    } catch (error) {
        console.error('Error loading customer details:', error);
        alert('An error occurred while loading customer details');
    }
}

/**
 * Close customer detail modal
 */
function closeCustomerDetailModal() {
    document.getElementById('customerDetailModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Export functions for use in blade templates
window.switchTab = switchTab;
window.openModal = openModal;
window.closeModal = closeModal;
window.submitAdminForm = submitAdminForm;
window.editUser = editUser;
window.deleteUser = deleteUser;
window.viewHistory = viewHistory;
window.closeEditModal = closeEditModal;
window.submitEditForm = submitEditForm;
window.viewCustomerDetail = viewCustomerDetail;
window.closeCustomerDetailModal = closeCustomerDetailModal;
