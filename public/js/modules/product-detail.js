// public/js/modules/product-detail.js
'use strict';

const ProductDetailModule = (function() {
    'use strict';

    function getBaseUrl() {
        if (typeof window.CREONO !== 'undefined' && window.CREONO.URLROOT) {
            return window.CREONO.URLROOT;
        }
        return window.APP_URL || '';
    }

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

        if (starPicker && ratingInput) {
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
            if (reviewForm.dataset.reviewInitialized === 'true') {
                return;
            }
            reviewForm.dataset.reviewInitialized = 'true';

            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('btnSubmitReview');
                if (submitBtn && submitBtn.disabled) {
                    return;
                }

                const ratingInput = document.getElementById('ratingInput');
                const ratingErr = document.getElementById('rating_err');
                const commentInput = document.getElementById('reviewComment');
                const commentVal = commentInput ? commentInput.value.trim() : '';
                const commentErr = document.getElementById('comment_err');
                const selectedRating = ratingInput ? (parseInt(ratingInput.value) || 0) : 0;
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

                const originalText = submitBtn ? submitBtn.textContent : 'Gửi đánh giá';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Đang gửi...';
                }

                const formData = new FormData(reviewForm);
                const baseUrl = getBaseUrl();

                fetch(reviewForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }

                    if (data.success) {
                        if (typeof FlashModule !== 'undefined') {
                            FlashModule.show(data.message || 'Đánh giá đã được gửi thành công!', 'success');
                        } else {
                            alert(data.message || 'Đánh giá đã được gửi thành công!');
                        }

                        if (reviewForm.parentElement) {
                            reviewForm.parentElement.innerHTML = '<div class="reviewed-notice" style="padding: 16px 20px; background: #e6f7ff; border: 1px solid #91d5ff; border-radius: 14px; color: #0050b3; font-size: 14px;">✅ Bạn đã gửi đánh giá cho sản phẩm này. Cảm ơn phản hồi của bạn!</div>';
                        }

                        if (data.review) appendNewReviewToDOM(data.review, selectedRating);
                        if (data.rating_stats) updateRatingStatsUI(data.rating_stats);
                    } else {
                        if (data.require_login) {
                            window.location.href = baseUrl + '/users/login';
                            return;
                        }
                        if (typeof FlashModule !== 'undefined') {
                            FlashModule.show(data.message || 'Đã xảy ra lỗi khi gửi đánh giá.', 'error');
                        } else {
                            alert(data.message || 'Đã xảy ra lỗi khi gửi đánh giá.');
                        }
                    }
                })
                .catch(err => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                    console.error('Error submitting review:', err);
                    if (typeof FlashModule !== 'undefined') {
                        FlashModule.show('Lỗi kết nối server khi gửi đánh giá. Vui lòng thử lại.', 'error');
                    }
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
                if (!commentInput || !commentInput.value.trim()) {
                    alert('Vui lòng nhập nội dung phản hồi');
                    return;
                }

                const submitBtn = form.querySelector('.btn-submit-reply');
                if (submitBtn && submitBtn.disabled) return;

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Đang gửi...';
                }

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Gửi trả lời';
                    }

                    if (data.success) {
                        if (typeof FlashModule !== 'undefined') {
                            FlashModule.show(data.message || 'Phản hồi đã được gửi!', 'success');
                        }
                        if (commentInput) commentInput.value = '';
                        const parentIdInput = form.querySelector('input[name="parent_id"]');
                        const parentId = parentIdInput ? parentIdInput.value : '';
                        const replyBox = document.getElementById('reply-form-' + parentId);
                        if (replyBox) replyBox.style.display = 'none';
                        if (data.reply && parentId) appendReplyToDOM(parentId, data.reply);
                    } else {
                        if (typeof FlashModule !== 'undefined') {
                            FlashModule.show(data.message || 'Có lỗi xảy ra', 'error');
                        } else {
                            alert(data.message);
                        }
                    }
                })
                .catch(err => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Gửi trả lời';
                    }
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
                const textSpan = this.querySelector('span');
                if (textSpan) textSpan.textContent = 'Đang thêm...';

                const formData = new FormData();
                formData.append('product_id', productId);
                const baseUrl = getBaseUrl();

                fetch(baseUrl + '/carts/add', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    this.disabled = false;
                    if (data.success) {
                        if (textSpan) textSpan.textContent = '✓ Đã có trong giỏ';
                        if (typeof FlashModule !== 'undefined') {
                            FlashModule.show(data.message || 'Đã thêm sản phẩm vào giỏ hàng!', 'success');
                        } else {
                            alert(data.message);
                        }
                        const badges = document.querySelectorAll('#nav-cart-badge');
                        badges.forEach(b => { b.textContent = data.cart_count; b.style.display = 'flex'; });
                    } else {
                        this.innerHTML = originalHtml;
                        if (typeof FlashModule !== 'undefined') {
                            FlashModule.show(data.message || 'Có lỗi xảy ra', 'error');
                        } else {
                            alert(data.message);
                        }
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
                const baseUrl = getBaseUrl();

                fetch(baseUrl + '/favorites/toggle', {
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
                        if (typeof FlashModule !== 'undefined') {
                            FlashModule.show(data.message, 'success');
                        }
                    } else {
                        if (data.require_login) { window.location.href = baseUrl + '/users/login'; return; }
                        if (typeof FlashModule !== 'undefined') {
                            FlashModule.show(data.message, 'error');
                        } else {
                            alert(data.message);
                        }
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
        <div class="review-item-card" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.06); border-radius: 16px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.02); animation: fadeIn 0.4s ease;">
            <div class="review-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div class="review-user-info" style="display: flex; align-items: center; gap: 12px;">
                    <div class="user-avatar-circle" style="width: 40px; height: 40px; border-radius: 50%; background: #0071e3; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px;">${initial}</div>
                    <div>
                        <div style="font-weight: 600; font-size: 15px; color: #1d1d1f;">${escapeHtml(reviewData.user_name)}</div>
                        <div style="font-size: 12px; color: #86868b;">Vừa xong</div>
                    </div>
                </div>
                <div class="review-stars" style="color: #ffb800; font-size: 16px;">${starsHtml}</div>
            </div>
            <div class="review-comment-body" style="font-size: 15px; color: #333; line-height: 1.6; margin-bottom: 16px;">${escapeHtml(reviewData.comment).replace(/\n/g, '<br>')}</div>
        </div>`;
        reviewsList.insertAdjacentHTML('afterbegin', reviewCardHtml);

        const badge = document.getElementById('reviews-total-badge');
        if (badge) {
            const countMatch = badge.textContent.match(/\d+/);
            const currentCount = countMatch ? parseInt(countMatch[0]) : 0;
            badge.textContent = `(${currentCount + 1})`;
        }
    }

    function appendReplyToDOM(parentId, replyData) {
        const repliesContainer = document.getElementById('replies-container-' + parentId);
        if (!repliesContainer) return;
        const replyHtml = `
        <div class="reply-item" style="margin-left: 36px; padding: 12px 16px; background: #f9f9fb; border-left: 3px solid var(--apple-blue, #0071e3); border-radius: 0 12px 12px 0; animation: fadeIn 0.3s ease;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                <span style="font-weight: 600; font-size: 14px; color: #1d1d1f;">${escapeHtml(replyData.user_name)}</span>
                <span style="font-size: 11px; color: #86868b;">Vừa xong</span>
            </div>
            <div style="font-size: 14px; color: #444; line-height: 1.5;">${escapeHtml(replyData.comment).replace(/\n/g, '<br>')}</div>
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