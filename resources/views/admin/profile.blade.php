<x-admin-layout>
    <x-slot name="title">Admin Profile</x-slot>
    @vite(['resources/css/admin/profile.css'])
    

    <div class="profile-container">
        <div class="profile-grid">
            <!-- Left Column (1/3 width) -->
            <div class="left-column">
                <!-- Profile Photo Card -->
                <div class="profile-card">
                    <h3 class="section-title">Profile Photo</h3>
                    <form action="{{ route('admin.profile.update-avatar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="avatar-section">
                            @if($admin->avatar)
                                <img src="{{ Storage::url($admin->avatar) }}" alt="Profile Photo" class="avatar-preview">
                            @else
                                <div class="avatar-placeholder">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                                </div>
                            @endif

                            <div class="avatar-actions">
                                <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;">
                                <button type="button" onclick="document.getElementById('avatar').click()" class="btn-upload">
                                    <span class="material-icons">photo_camera</span>
                                    Upload Photo
                                </button>
                                
                                @if($admin->avatar)
                                    <form action="{{ route('admin.profile.delete-avatar') }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete-avatar" onclick="return confirm('Are you sure you want to delete your avatar?')">
                                            <span class="material-icons">delete</span>
                                            Remove Photo
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Change Password Card -->
                <div class="profile-card">
                    <h3 class="section-title">Change Password</h3>
                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="name" value="{{ $admin->name }}">
                        
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-input" required>
                            @error('current_password')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-input" required>
                            @error('password')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-input" required>
                        </div>

                        <button type="submit" class="btn-primary">
                            <span class="material-icons">save</span>
                            Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column (2/3 width) -->
            <div>
                <!-- Profile Information Card -->
                <div class="profile-card">
                    <h3 class="section-title">Profile Information</h3>
                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" value="{{ $admin->name }}" class="form-input" required>
                            @error('name')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" value="{{ $admin->email }}" class="form-input bg-gray-100" readonly disabled>
                            <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <input type="text" value="{{ ucfirst($admin->role) }}" class="form-input bg-gray-100" readonly disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <span class="badge-role">{{ ucfirst($admin->status) }}</span>
                        </div>

                        <button type="submit" class="btn-primary">
                            <span class="material-icons">save</span>
                            Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('avatar').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                this.form.submit();
            }
        });

        @if(session('success'))
            alert('{{ session('success') }}');
        @endif
    </script>

    @push('scripts')
    <script src="//unpkg.com/alpinejs" defer></script>
    @endpush
</x-admin-layout>
