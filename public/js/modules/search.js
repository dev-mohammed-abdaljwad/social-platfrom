document.addEventListener("DOMContentLoaded", () => {
    // فتح وغلق dropdown الأصدقاء
    document.querySelectorAll("#friendDropdownBtn").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const dropdown = btn.nextElementSibling;
            dropdown.classList.toggle("hidden");
        });
    });
});

// تحديث الـ UI بعد أي تغيير
function updateUserActions(userId, newStatus, friendshipId = null) {
    const container = document.getElementById(`user-actions-${userId}`);
    if (!container) return;

    let html = '';

    switch(newStatus) {
        case 'friends':
            html = `
                <div class="relative" id="friendDropdownContainer">
                    <button id="friendDropdownBtn" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        Friends
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="friendDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50 overflow-hidden">
                        <button onclick="removeFriend(${userId})" class="w-full px-4 py-3 text-left hover:bg-gray-50 flex items-center gap-3 text-gray-700">Unfriend</button>
                        <button onclick="blockUser(${userId})" class="w-full px-4 py-3 text-left hover:bg-gray-50 flex items-center gap-3 text-red-600">Block</button>
                        <div class="border-t border-gray-100"></div>
                        <a href="/friends" class="w-full px-4 py-3 text-left hover:bg-gray-50 flex items-center gap-3 text-gray-700">See All Friends</a>
                    </div>
                </div>
                <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Message</button>
            `;
            break;
        case 'pending_sent':
            html = `
                <button onclick="cancelFriendRequest(${friendshipId}, ${userId})" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancel Request</button>
                <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Message</button>
            `;
            break;
        case 'pending_received':
            html = `
                <div class="flex gap-2">
                    <button onclick="acceptFriendRequest(${friendshipId}, ${userId})" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Accept</button>
                    <button onclick="rejectFriendRequest(${friendshipId}, ${userId})" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Decline</button>
                </div>
                <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Message</button>
            `;
            break;
        default: // none
            html = `
                <button onclick="sendFriendRequest(${userId})" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Friend</button>
                <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Message</button>
            `;
    }

    container.innerHTML = html;

    // إعادة تفعيل الـ dropdown بعد إعادة البناء
    document.querySelectorAll("#friendDropdownBtn").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const dropdown = btn.nextElementSibling;
            dropdown.classList.toggle("hidden");
        });
    });
}

// أمثلة AJAX لكل Action (باستخدام fetch)
function sendFriendRequest(userId) {
    fetch(`/friends/send/${userId}`, { method: 'POST', headers: {'X-CSRF-TOKEN': csrfToken} })
        .then(res => res.json())
        .then(data => updateUserActions(userId, 'pending_sent', data.friendshipId));
}

function cancelFriendRequest(friendshipId, userId) {
    fetch(`/friends/${friendshipId}/cancel`, { method: 'POST', headers: {'X-CSRF-TOKEN': csrfToken} })
        .then(() => updateUserActions(userId, 'none'));
}

function acceptFriendRequest(friendshipId, userId) {
    fetch(`/friends/${friendshipId}/accept`, { method: 'POST', headers: {'X-CSRF-TOKEN': csrfToken} })
        .then(() => updateUserActions(userId, 'friends'));
}

function rejectFriendRequest(friendshipId, userId) {
    fetch(`/friends/reject/${friendshipId}`, { method: 'POST', headers: {'X-CSRF-TOKEN': csrfToken} })
        .then(() => updateUserActions(userId, 'none'));
}

function removeFriend(userId) {
    fetch(`/friends/${userId}/remove`, { method: 'POST', headers: {'X-CSRF-TOKEN': csrfToken} })
        .then(() => updateUserActions(userId, 'none'));
}

function blockUser(userId) {
    fetch(`/users/block/${userId}`, { method: 'POST', headers: {'X-CSRF-TOKEN': csrfToken} })
        .then(() => updateUserActions(userId, 'none'));
}
