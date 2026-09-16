// File: public/js/modules/marketplace.js

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ===== CUSTOM SORT DROPDOWN =====
    const sortDropdown = document.getElementById('customSortDropdown');
    const sortTrigger = document.getElementById('customSortTrigger');
    const sortOptions = document.querySelectorAll('.custom-sort-option');
    const realSortSelect = document.getElementById('realSortSelect');
    const sortForm = document.getElementById('sortForm');

    if (sortDropdown && sortTrigger) {
        // Click để mở/đóng menu
        sortTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            sortDropdown.classList.toggle('is-open');
        });

        // Xử lý khi chọn mục mới
        sortOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                const value = this.getAttribute('data-value');
                
                // Cập nhật value cho select thật và submit form
                realSortSelect.value = value;
                sortForm.submit();
            });
        });

        // Click ra ngoài thì tự động đóng menu
        document.addEventListener('click', function(e) {
            if (!sortDropdown.contains(e.target)) {
                sortDropdown.classList.remove('is-open');
            }
        });
    }
});