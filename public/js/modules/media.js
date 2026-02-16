// public/js/modules/media.js
function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 10 * 1024 * 1024) {
            alert('Image size must be less than 10MB');
            input.value = '';
            return;
        }
        removeVideo();
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewContainer').classList.remove('hidden');
            document.getElementById('mediaPreview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}

function previewVideo(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 100 * 1024 * 1024) {
            alert('Video size must be less than 100MB');
            input.value = '';
            return;
        }
        removeImage();
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('videoPreview').src = e.target.result;
            document.getElementById('videoPreviewContainer').classList.remove('hidden');
            document.getElementById('mediaPreview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    document.getElementById('imageInput').value = '';
    document.getElementById('imagePreview').src = '';
    document.getElementById('imagePreviewContainer').classList.add('hidden');
    checkMediaPreview();
}

function removeVideo() {
    document.getElementById('videoInput').value = '';
    document.getElementById('videoPreview').src = '';
    document.getElementById('videoPreviewContainer').classList.add('hidden');
    checkMediaPreview();
}

function checkMediaPreview() {
    const imageHidden = document.getElementById('imagePreviewContainer').classList.contains('hidden');
    const videoHidden = document.getElementById('videoPreviewContainer').classList.contains('hidden');
    if (imageHidden && videoHidden) {
        document.getElementById('mediaPreview').classList.add('hidden');
    }
}