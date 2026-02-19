// Guard: only run on the settings page where these elements exist
if (!document.getElementById('bio')) {
    // Not on the settings page — do nothing
} else {

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
    document.getElementById('profileForm').addEventListener('submit', async function (e) {
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
    document.getElementById('emailForm').addEventListener('submit', async function (e) {
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
    document.getElementById('passwordForm').addEventListener('submit', async function (e) {
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
    document.getElementById('deleteForm').addEventListener('submit', async function (e) {
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
    document.getElementById('deleteModal').addEventListener('click', function (e) {
        if (e.target === this) {
            hideDeleteModal();
        }
    });

} // end settings page guard