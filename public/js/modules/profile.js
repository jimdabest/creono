// public/js/modules/profile.js
'use strict';

/**
 * Module xử lý profile
 */
const ProfileModule = (function() {
    'use strict';

    /**
     * Khởi tạo các tính năng profile
     */
    function init() {
        // Preview ảnh đại diện khi upload
        const avatarInput = document.querySelector('input[name="avatar"]');
        if (avatarInput) {
            avatarInput.addEventListener('change', function(e) {
                previewImage(this);
            });
        }

        // Xử lý form profile
        const profileForm = document.querySelector('form[action*="updateProfile"]');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                const bio = this.querySelector('textarea[name="bio"]');
                if (bio && bio.value.length > 500) {
                    e.preventDefault();
                    alert('Phần giới thiệu không được vượt quá 500 ký tự');
                    bio.focus();
                }
            });
        }
    }

    /**
     * Preview ảnh trước khi upload
     */
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Tìm hoặc tạo img preview
                let preview = document.querySelector('.avatar-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.className = 'avatar-preview';
                    preview.style.cssText = 'max-width: 150px; border-radius: 8px; margin-top: 10px;';
                    input.parentElement.appendChild(preview);
                }
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Public API
    return {
        init: init,
        previewImage: previewImage
    };

})();

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ProfileModule;
}