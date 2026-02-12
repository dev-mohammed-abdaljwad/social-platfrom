@extends('layouts.app')

@section('title', 'Settings - SocialHub')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Settings</h1>

        <!-- Success Alert -->
        <div id="successAlert" class="hidden mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm"></div>
        
        <!-- Error Alert -->
        <div id="errorAlert" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"></div>

        <!-- Profile Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Profile Information</h2>
            
            <form id="profileForm" class="space-y-4">
                <!-- Profile Picture -->
                <div class="flex items-center gap-4">
                    <img 
                        id="profilePreview"
                        src="https://ui-avatars.com/api/?name=User&size=100" 
                        alt="Profile" 
                        class="w-20 h-20 rounded-full object-cover"
                    >
                    <div>
                        <label class="cursor-pointer px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-700 transition-colors">
                            Change Photo
                            <input type="file" id="profilePicture" accept="image/*" class="hidden" onchange="previewImage(this)">
                        </label>
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG. Max 2MB</p>
                    </div>
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Your name"
                    >
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">@</span>
                        <input 
                            type="text" 
                            id="username" 
                            name="username"
                            class="flex-1 px-4 py-2 rounded-r-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="username"
                        >
                    </div>
                </div>

                <!-- Bio -->
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                    <textarea 
                        id="bio" 
                        name="bio"
                        rows="3"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        placeholder="Tell us about yourself..."
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1"><span id="bioCount">0</span>/200 characters</p>
                </div>

                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Save Changes
                </button>
            </form>
        </div>

        <!-- Account Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Account Settings</h2>
            
            <form id="accountForm" class="space-y-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="email@example.com"
                    >
                </div>

                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Update Email
                </button>
            </form>
        </div>

        <!-- Password Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Change Password</h2>
            
            <form id="passwordForm" class="space-y-4">
                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter current password"
                    >
                </div>

                <!-- New Password -->
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password"
                        minlength="8"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter new password"
                    >
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Confirm new password"
                    >
                </div>

                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Update Password
                </button>
            </form>
        </div>

        <!-- Privacy Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Privacy Settings</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-medium text-gray-800">Private Account</h3>
                        <p class="text-sm text-gray-500">Only friends can see your posts</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="privateAccount" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-medium text-gray-800">Show Online Status</h3>
                        <p class="text-sm text-gray-500">Let others see when you're online</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="showOnline" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
            <h2 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-medium text-gray-800">Delete Account</h3>
                        <p class="text-sm text-gray-500">Permanently delete your account and all data</p>
                    </div>
                    <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Load user data
    async function loadUserData() {
        const token = localStorage.getItem('auth_token');
        if (!token) return;
        
        try {
            const response = await fetch('/api/v1/auth/user', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.success && data.data) {
                const user = data.data;
                document.getElementById('name').value = user.name || '';
                document.getElementById('username').value = user.username || '';
                document.getElementById('bio').value = user.bio || '';
                document.getElementById('email').value = user.email || '';
                
                if (user.profile_picture) {
                    document.getElementById('profilePreview').src = `/storage/${user.profile_picture}`;
                } else {
                    document.getElementById('profilePreview').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&size=100`;
                }
                
                updateBioCount();
            }
        } catch (error) {
            console.error('Error loading user:', error);
        }
    }

    // Preview image
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Bio character count
    function updateBioCount() {
        const bio = document.getElementById('bio');
        const count = document.getElementById('bioCount');
        count.textContent = bio.value.length;
    }

    document.getElementById('bio').addEventListener('input', updateBioCount);

    // Profile form submit
    document.getElementById('profileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }
        
        const formData = new FormData();
        formData.append('name', document.getElementById('name').value);
        formData.append('username', document.getElementById('username').value);
        formData.append('bio', document.getElementById('bio').value);
        
        const profilePicture = document.getElementById('profilePicture').files[0];
        if (profilePicture) {
            formData.append('profile_picture', profilePicture);
        }
        
        try {
            const response = await fetch('/api/v1/users/profile', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('Profile updated successfully!');
            } else {
                showError(data.message || 'Failed to update profile.');
            }
        } catch (error) {
            showError('Something went wrong.');
        }
    });

    // Password form submit
    document.getElementById('passwordForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const newPass = document.getElementById('new_password').value;
        const confirmPass = document.getElementById('confirm_password').value;
        
        if (newPass !== confirmPass) {
            showError('Passwords do not match.');
            return;
        }
        
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '/login';
            return;
        }
        
        try {
            const response = await fetch('/api/v1/users/password', {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    current_password: document.getElementById('current_password').value,
                    password: newPass,
                    password_confirmation: confirmPass
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('Password updated successfully!');
                document.getElementById('passwordForm').reset();
            } else {
                showError(data.message || 'Failed to update password.');
            }
        } catch (error) {
            showError('Something went wrong.');
        }
    });

    function showSuccess(message) {
        const alert = document.getElementById('successAlert');
        alert.textContent = message;
        alert.classList.remove('hidden');
        document.getElementById('errorAlert').classList.add('hidden');
        setTimeout(() => alert.classList.add('hidden'), 5000);
    }

    function showError(message) {
        const alert = document.getElementById('errorAlert');
        alert.textContent = message;
        alert.classList.remove('hidden');
        document.getElementById('successAlert').classList.add('hidden');
    }

    function confirmDelete() {
        if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
            // Call delete endpoint
            alert('Account deletion is disabled in demo mode.');
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', loadUserData);
</script>
@endpush
