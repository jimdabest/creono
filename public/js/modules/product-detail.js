// public/js/modules/product-detail.js
'use strict';

const ProductDetailModule = (function() {
    'use strict';

    function init() {
        initStarRating();
        initReviewForm();
        initReplySystem();
        initCartAndFavorite();
    }

    // 1. STAR RATING INTERACTIVE LOGIC
    function initStarRating() {
        const starPicker = document.getElementById('starPicker');
        const ratingInput = document.getElementById('ratingInput');
        const ratingLabel = document.getElementById('ratingLabel');
        const ratingErr = document.getElementById('rating_err');

        const ratingTexts = {
            1: '1 sao - Rất kém', 2: '2 sao - Kém', 3: '3 sao - Bình thường',
            4: '4 sao - Tốt', 5: '5 sao - Rất tốt'
        };

        if (starPicker) {
            const stars = starPicker.querySelectorAll('.star-item');

            stars.forEach(function(star) {
                star.addEventListener('mouseenter', function() {
                    const val = parseInt(this.getAttribute('data-value'));
                    highlightStars(stars, val);
                    if (ratingLabel) ratingLabel.textContent = '(' + ratingTexts[val] + ')';
                });

                starPicker.addEventListener('mouseleave', function() {
                    const currentVal = parseInt(ratingInput.value) || 0;
                    highlightStars(stars, currentVal);
                    if (ratingLabel) ratingLabel.textContent = currentVal > 0 ? '(' + ratingTexts[currentVal] + ')' : '';
                });

                star.addEventListener('click', function() {
                    const val = parseInt(this.getAttribute('data-value'));
                    ratingInput.value = val;
                    highlightStars(stars, val);
                    if (ratingLabel) ratingLabel.textContent = '(' + ratingTexts[val] + ')';
                    if (ratingErr) ratingErr.textContent = '';
                });
            });
        }
    }

    function highlightStars(stars, count) {
        stars.forEach(function(s) {
            const sVal = parseInt(s.getAttribute('data-value'));
            if (sVal <= count) s.classList.add('active');
            else s.classList.remove('active');
        });
    }

    // 2. AJAX SUBMIT FOR MAIN REVIEW FORM
    function initReviewForm() {
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const ratingInput = document.getElementById('ratingInput');
                const ratingErr = document.getElementById('rating_err');
                const commentVal = document.getElementById('reviewComment').value.trim();
                const commentErr = document.getElementById('comment_err');
                const selectedRating = parseInt(ratingInput.value) || 0;
                let hasErr = false;

                if (ratingErr) ratingErr.textContent = '';
                if (commentErr) commentErr.textContent = '';

                if (selectedRating <= 0) {
                    if (ratingErr) ratingErr.textContent = 'Vui lòng chọn số sao đánh giá';
                    hasErr = true;
                }

                if (!commentVal) {
                    if (commentErr) commentErr.textContent = 'Vui lòng nhập nhận xét';
                    hasErr = true;
                }

                if (hasErr) return;

                const submitBtn = document.getElementById('btnSubmitReview');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang gửi...';

                const formData = new FormData(reviewForm);

                fetch(reviewForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;

                    if (data.success) {
                        if (typeof FlashModule !== 'undefined') FlashModule.show('success', data.message);
                        else alert(data.message);

                        reviewForm.parentElement.innerHTML = '<div class="review-notice notice-success">✅ Bạn đã gửi đánh giá cho sản phẩm này. Cảm ơn phản hồi của bạn!</div>';

                        if (data.review) appendNewReviewToDOM(data.review, selectedRating);
                        if (data.rating_stats) updateRatingStatsUI(data.rating_stats);
                    } else {
                        if (data.require_login) {
                            window.location.href = window.APP_URL + '/users/login';
                            return;
                        }
                        if (typeof FlashModule !== 'undefined') FlashModule.show('error', data.message);
                        else alert(data.message);
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    console.error('Error submitting review:', err);
                });
            });
        }
    }

    // 3. TOGGLE REPLY FORM & AJAX SUBMIT REPLY
    function initReplySystem() {
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-toggle-reply')) {
                const parentId = e.target.getAttribute('data-parent-id');
                const replyBox = document.getElementById('reply-form-' + parentId);
                if (replyBox) replyBox.style.display = replyBox.style.display === 'none' ? 'block' : 'none';
            }

            if (e.target.classList.contains('btn-cancel-reply')) {
                const parentId = e.target.getAttribute('data-parent-id');
                const replyBox = document.getElementById('reply-form-' + parentId);
                if (replyBox) replyBox.style.display = 'none';
            }
        });

        document.addEventListener('submit', function(e) {
            if (e.target.classList.contains('form-reply-ajax')) {
                e.preventDefault();
                const form = e.target;
                const commentInput = form.querySelector('textarea[name="comment"]');
                if (!commentInput.value.trim()) {
                    alert('Vui lòng nhập nội dung phản hồi');
                    return;
                }

                const submitBtn = form.querySelector('.btn-submit-reply');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang gửi...';

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Gửi trả lời';

                    if (data.success) {
                        if (typeof FlashModule !== 'undefined') FlashModule.show('success', data.message);
                        commentInput.value = '';
                        const parentId = form.querySelector('input[name="parent_id"]').value;
                        const replyBox = document.getElementById('reply-form-' + parentId);
                        if (replyBox) replyBox.style.display = 'none';
                        if (data.reply) appendReplyToDOM(parentId, data.reply);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Gửi trả lời';
                    console.error('Reply error:', err);
                });
            }
        });
    }

    // 4. ADD TO CART & TOGGLE FAVORITE
    function initCartAndFavorite() {
        const btnAddToCart = document.getElementById('btnAddToCart');
        if (btnAddToCart) {
            btnAddToCart.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const originalHtml = this.innerHTML;
                this.disabled = true;
                this.querySelector('span').textContent = 'Đang thêm...';

                const formData = new FormData();
                formData.append('product_id', productId);

                fetch(window.APP_URL + '/carts/add', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    this.disabled = false;
                    if (data.success) {
                        this.querySelector('span').textContent = '✓ Đã có trong giỏ';
                        if (typeof FlashModule !== 'undefined') FlashModule.show('success', data.message);
                        else alert(data.message);
                        const badges = document.querySelectorAll('#nav-cart-badge');
                        badges.forEach(b => { b.textContent = data.cart_count; b.style.display = 'flex'; });
                    } else {
                        this.innerHTML = originalHtml;
                        alert(data.message);
                    }
                })
                .catch(err => {
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                    console.error('Add cart error:', err);
                });
            });
        }

        const btnToggleFavorite = document.getElementById('btnToggleFavorite');
        if (btnToggleFavorite) {
            btnToggleFavorite.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const favIcon = this.querySelector('.fav-icon');
                const favText = document.getElementById('favText');
                this.disabled = true;

                const formData = new FormData();
                formData.append('product_id', productId);

                fetch(window.APP_URL + '/favorites/toggle', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    this.disabled = false;
                    if (data.success) {
                        if (data.is_favorited) {
                            this.classList.add('active');
                            if (favIcon) { favIcon.setAttribute('fill', '#ff3b30'); favIcon.setAttribute('stroke', '#ff3b30'); }
                            if (favText) favText.textContent = 'Đã yêu thích';
                        } else {
                            this.classList.remove('active');
                            if (favIcon) { favIcon.setAttribute('fill', 'none'); favIcon.setAttribute('stroke', 'currentColor'); }
                            if (favText) favText.textContent = 'Yêu thích';
                        }
                        if (typeof FlashModule !== 'undefined') FlashModule.show('success', data.message);
                    } else {
                        if (data.require_login) { window.location.href = window.APP_URL + '/users/login'; return; }
                        alert(data.message);
                    }
                })
                .catch(err => {
                    this.disabled = false;
                    console.error('Toggle favorite error:', err);
                });
            });
        }
    }

    // --- HELPER FUNCTIONS ---
    function appendNewReviewToDOM(reviewData, ratingVal) {
        const emptyMsg = document.getElementById('empty-reviews-msg');
        if (emptyMsg) emptyMsg.remove();
        const reviewsList = document.getElementById('reviewsList');
        if (!reviewsList) return;

        let starsHtml = '';
        for (let i = 1; i <= 5; i++) starsHtml += i <= ratingVal ? '★' : '☆';
        const initial = reviewData.user_name ? reviewData.user_name.charAt(0).toUpperCase() : 'U';

        const reviewCardHtml = `
        <div class="review-item-card">
            <div class="review-item-header">
                <div class="review-user-group">
                    <div class="seller-avatar-circle" style="width: 40px; height: 40px; font-size: 16px;">${initial}</div>
                    <div>
                        <div class="review-user-name">${escapeHtml(reviewData.user_name)}</div>
                        <div class="review-time">Vừa xong</div>
                    </div>
                </div>
                <div class="review-stars">${starsHtml}</div>
            </div>
            <div class="review-body">${escapeHtml(reviewData.comment).replace(/\n/g, '<br>')}</div>
        </div>`;
        reviewsList.insertAdjacentHTML('afterbegin', reviewCardHtml);
    }

    function appendReplyToDOM(parentId, replyData) {
        const repliesContainer = document.getElementById('replies-container-' + parentId);
        if (!repliesContainer) return;
        const replyHtml = `
        <div class="reply-item">
            <div class="reply-item-header">
                <span class="reply-user-name">${escapeHtml(replyData.user_name)}</span>
                <span class="reply-time">Vừa xong</span>
            </div>
            <div class="reply-body">${escapeHtml(replyData.comment).replace(/\n/g, '<br>')}</div>
        </div>`;
        repliesContainer.insertAdjacentHTML('beforeend', replyHtml);
    }

    function updateRatingStatsUI(stats) {
        if (!stats) return;
        const avgScore = document.getElementById('stats-avg-score');
        if (avgScore) avgScore.textContent = parseFloat(stats.average).toFixed(1);
        const totalCount = document.getElementById('stats-total-count');
        if (totalCount) totalCount.textContent = 'Dựa trên ' + stats.total + ' đánh giá';
        const heroCount = document.getElementById('hero-review-count');
        if (heroCount) heroCount.textContent = '(' + stats.total + ' đánh giá)';

        for (let star = 1; star <= 5; star++) {
            const count = stats[star] || 0;
            const pct = stats.total > 0 ? Math.round((count / stats.total) * 100) : 0;
            const barFill = document.getElementById('bar-fill-' + star);
            if (barFill) barFill.style.width = pct + '%';
            const barCount = document.getElementById('bar-count-' + star);
            if (barCount) barCount.textContent = count;
        }
    }

    function escapeHtml(str) {
        return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // Public API
    return { init: init };
})();

// Tự động khởi tạo nếu script được tải trực tiếp
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ProductDetailModule !== 'undefined') {
        ProductDetailModule.init();
    }
});