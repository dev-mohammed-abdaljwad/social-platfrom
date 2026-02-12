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
                        src="{{ auth()->user()->avatar_url }}" 
                        alt="Profile" 
                        class="w-20 h-20 rounded-full object-cover"
                    >
                    <div>
                        <label class="cursor-pointer px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-700 transition-colors inline-block">
                            Change Photo
                            <input type="file" id="profilePicture" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="hidden" onchange="uploadProfilePicture(this)">
                        </label>
                        @if(auth()->user()->profile_picture)
                        <button type="button" onclick="removeProfilePicture()" class="ml-2 px-3 py-2 bg-red-100 hover:bg-red-200 rounded-lg text-sm font-medium text-red-700 transition-colors">
                            Remove
                        </button>
                        @endif
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF, WebP. Max 5MB</p>
                    </div>
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name"
                        value="{{ auth()->user()->name }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Your name"
                        required
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
                            value="{{ auth()->user()->username }}"
                            class="flex-1 px-4 py-2 rounded-r-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="username"
                            required
                        >
                    </div>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone"
                        value="{{ auth()->user()->phone }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="+1 (555) 123-4567"
                    >
                </div>

                <!-- Bio -->
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                    <textarea 
                        id="bio" 
                        name="bio"
                        rows="3"
                        maxlength="500"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        placeholder="Tell us about yourself..."
                    >{{ auth()->user()->bio }}</textarea>
                    <p class="text-xs text-gray-500 mt-1"><span id="bioCount">{{ strlen(auth()->user()->bio ?? '') }}</span>/500 characters</p>
                </div>

                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Save Changes
                </button>
            </form>
        </div>

        <!-- Account Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Email Address</h2>
            
            <form id="emailForm" class="space-y-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        value="{{ auth()->user()->email }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="email@example.com"
                        required
                    >
                </div>

                <!-- Password for verification -->
                <div>
                    <label for="email_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input 
                        type="password" 
                        id="email_password" 
                        name="password"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter password to confirm"
                        required
                    >
                    <p class="text-xs text-gray-500 mt-1">Enter your password to confirm email change</p>
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
                        required
                    >
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        minlength="8"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter new password (min 8 characters)"
                        required
                    >
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Confirm new password"
                        required
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
                    <button onclick="showDeleteModal()" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-xl font-bold text-red-600 mb-4">Delete Account</h3>
            <p class="text-gray-600 mb-4">This action is <strong>permanent</strong> and cannot be undone. All your data including posts, comments, and friendships will be deleted.</p>
            
            <form id="deleteForm" class="space-y-4">
                <div>
                    <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-1">Enter your password to confirm</label>
                    <input 
                        type="password" 
                        id="delete_password" 
                        name="password"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Your password"
                        required
                    >
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="hideDeleteModal()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                        Delete My Account
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Bio character count
    function updateBioCount() {
        const bio = document.getElementById('bio');
        const count = document.getElementById('bioCount');
        count.textContent = bio.value.length;
    }
    document.getElementById('bio').addEventListener('input', updateBioCount);

    // Upload Profile Picture
    async function uploadProfilePicture(input) {
        if (!input.files || !input.files[0]) return;
        
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) {
            showError('Profile picture must be less than 5MB');
            return;
        }
        
        const formData = new FormData();
        formData.append('profile_picture', file);
        
        try {
            const response = await fetch('/profile/picture', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('profilePreview').src = data.profile_picture_url;
                showSuccess('Profile picture updated!');
                // Reload to show remove button if first upload
                setTimeout(() => location.reload(), 1000);
            } else {
                showError(data.message || 'Failed to upload profile picture');
            }
        } catch (error) {
            showError('Failed to upload profile picture');
        }
        
        input.value = '';
    }

    // Remove Profile Picture
    async function removeProfilePicture() {
        if (!confirm('Remove your profile picture?')) return;
        
        try {
            const response = await fetch('/profile/picture', {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('profilePreview').src = data.profile_picture_url;
                showSuccess('Profile picture removed!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showError(data.message || 'Failed to remove profile picture');
            }
        } catch (error) {
            showError('Failed to remove profile picture');
        }
    }

    // Profile form submit
    document.getElementById('profileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const response = await fetch('/profile', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    name: document.getElementById('name').value,
                    username: document.getElementById('username').value,
                    bio: document.getElementById('bio').value,
                    phone: document.getElementById('phone').value
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('Profile updated successfully!');
            } else {
                showError(data.message || 'Failed to update profile');
            }
        } catch (error) {
            showError('Something went wrong');
        }
    });

    // Email form submit
    document.getElementById('emailForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const response = await fetch('/profile/email', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    password: document.getElementById('email_password').value
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('Email updated successfully!');
                document.getElementById('email_password').value = '';
            } else {
                showError(data.message || 'Failed to update email');
            }
        } catch (error) {
            showError('Something went wrong');
        }
    });

    // Password form submit
    document.getElementById('passwordForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        
        if (password !== confirmation) {
            showError('Passwords do not match');
            return;
        }
        
        try {
            const response = await fetch('/profile/password', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    current_password: document.getElementById('current_password').value,
                    password: password,
                    password_confirmation: confirmation
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('Password updated successfully!');
                document.getElementById('passwordForm').reset();
            } else {
                showError(data.message || 'Failed to update password');
            }
        } catch (error) {
            showError('Something went wrong');
        }
    });

    // Delete Account Modal
    function showDeleteModal() {
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('deleteModal').classList.remove('flex');
        document.getElementById('delete_password').value = '';
    }

    // Delete form submit
    document.getElementById('deleteForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const response = await fetch('/profile', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    password: document.getElementById('delete_password').value
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = data.redirect || '/';
            } else {
                showError(data.message || 'Failed to delete account');
            }
        } catch (error) {
            showError('Something went wrong');
        }
    });

    // Alert helpers
    function showSuccess(message) {
        const alert = document.getElementById('successAlert');
        alert.textContent = message;
        alert.classList.remove('hidden');
        document.getElementById('errorAlert').classList.add('hidden');
        setTimeout(() => alert.classList.add('hidden'), 5000);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showError(message) {
        const alert = document.getElementById('errorAlert');
        alert.textContent = message;
        alert.classList.remove('hidden');
        document.getElementById('successAlert').classList.add('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Close modal on outside click
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideDeleteModal();
        }
    });
</script>
@endpush
