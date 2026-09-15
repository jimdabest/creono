<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px; max-width: 720px;">
    <h1 style="font-size: 32px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 8px; color: #1d1d1f;">
        Chỉnh sửa sản phẩm
    </h1>
    <p style="font-size: 16px; color: #86868b; margin-bottom: 32px;">
        Cập nhật thông tin tài liệu của bạn.
    </p>

    <div class="card" style="max-width: 100%; padding: 32px 36px; border-radius: 20px;">
        <form action="<?php echo URLROOT; ?>/products/edit/<?php echo $data['product']->id; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token']); ?>">

            <!-- Tiêu đề -->
            <div class="form-group">
                <label for="title">Tiêu đề sản phẩm <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control <?php echo isset($data['errors']['title_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['product']->title ?? ''); ?>" required>
                <span class="error-text"><?php echo $data['errors']['title_err'] ?? ''; ?></span>
            </div>

            <!-- Danh mục -->
            <div class="form-group">
                <label for="category_id">Danh mục <span class="text-danger">*</span></label>
                <select name="category_id" id="category_id" class="form-control <?php echo isset($data['errors']['category_err']) ? 'is-invalid' : ''; ?>" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($data['categories'] as $cat) : ?>
                        <option value="<?php echo $cat->id; ?>" <?php echo ($data['product']->category_id == $cat->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="error-text"><?php echo $data['errors']['category_err'] ?? ''; ?></span>
            </div>

            <!-- Giá bán -->
            <div class="form-group">
                <label for="price">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" name="price" id="price" class="form-control <?php echo isset($data['errors']['price_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['product']->price ?? ''); ?>" required min="0" step="1000">
                <span class="error-text"><?php echo $data['errors']['price_err'] ?? ''; ?></span>
            </div>

            <!-- Mô tả -->
            <div class="form-group">
                <label for="description">Mô tả chi tiết <span class="text-danger">*</span></label>
                <textarea name="description" id="description" rows="6" class="form-control <?php echo isset($data['errors']['description_err']) ? 'is-invalid' : ''; ?>" required><?php echo htmlspecialchars($data['product']->description ?? ''); ?></textarea>
                <span class="error-text"><?php echo $data['errors']['description_err'] ?? ''; ?></span>
            </div>

            <!-- Ảnh cover -->
            <div class="form-group">
                <label for="preview_image">Ảnh cover (JPG, PNG, GIF, WebP)</label>
                <?php if (!empty($data['product']->preview_url)) : ?>
                    <div style="margin-bottom: 8px;">
                        <img src="<?php echo URLROOT . htmlspecialchars($data['product']->preview_url); ?>" style="max-width: 120px; border-radius: 8px; border: 1px solid #ddd; padding: 4px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="preview_image" id="preview_image" class="form-control <?php echo isset($data['errors']['preview_err']) ? 'is-invalid' : ''; ?>" accept="image/*">
                <span class="error-text"><?php echo $data['errors']['preview_err'] ?? ''; ?></span>
                <small class="form-hint">Để trống nếu không muốn thay đổi ảnh.</small>
            </div>

            <!-- File tài liệu -->
            <div class="form-group">
                <label for="document_file">File tài liệu (ZIP, PDF, RAR)</label>
                <?php if (!empty($data['document']->file_url)) : ?>
                    <div style="margin-bottom: 8px; font-size: 14px; color: #0071e3;">
                        File hiện tại: <a href="<?php echo URLROOT . htmlspecialchars($data['document']->file_url); ?>" target="_blank">Tải xuống</a>
                    </div>
                <?php endif; ?>
                <input type="file" name="document_file" id="document_file" class="form-control <?php echo isset($data['errors']['document_err']) ? 'is-invalid' : ''; ?>" accept=".zip,.pdf,.rar">
                <span class="error-text"><?php echo $data['errors']['document_err'] ?? ''; ?></span>
                <small class="form-hint">Để trống nếu không muốn thay đổi file.</small>
            </div>

            <!-- Nút Xem trước Watermark trước khi lưu (UC28 Seller Preview) -->
            <div style="margin: 18px 0; padding: 14px 18px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                <div>
                    <div style="font-weight: 600; font-size: 14px; color: #1e293b;">Kiểm tra Watermark trước khi lưu</div>
                    <div style="font-size: 12px; color: #64748b;">Xem thực tế dấu bản quyền hiển thị trên ảnh hoặc file PDF mới chọn.</div>
                </div>
                <button type="button" id="btnSellerPreview" style="padding: 9px 18px; border-radius: 10px; background: #ffffff; border: 1.5px solid #0071e3; color: #0071e3; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; white-space: nowrap;">
                    <span>Xem trước Watermark</span>
                </button>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 28px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 14px; font-size: 16px; font-weight: 600; border-radius: 14px;">Cập nhật</button>
                <a href="<?php echo URLROOT; ?>/seller/dashboard" class="btn btn-secondary" style="flex: 0.4; text-align: center; padding: 14px; border-radius: 14px; background: #f5f5f7; color: #1d1d1f; text-decoration: none;">Hủy</a>
            </div>
        </form>
    </div>
</div>

<!-- Modal Xem trước dành cho Seller -->
<div id="modalSellerPreview" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 920px; height: 88vh; border-radius: 20px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #0071e3;"></span>
                <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">Bản xem trước Watermark thực tế (Seller Preview)</h4>
            </div>
            <button type="button" id="btnCloseSellerPreview" style="background: #e2e8f0; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 16px;">✕</button>
        </div>
        <div id="sellerPreviewContent" style="flex: 1; background: #525659; overflow-y: auto; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 20px 10px;">
            <div id="sellerCanvasContainer" style="display: none; width: 100%; max-width: 760px;"></div>
            <iframe id="sellerPreviewIframe" style="display: none; width: 100%; height: 100%; border: none;"></iframe>
            <div id="sellerPreviewLoading" style="color: #ffffff; font-size: 14px; text-align: center; padding: 40px;">Đang tải và đóng dấu xem trước...</div>
        </div>
        <div style="padding: 12px 24px; border-top: 1px solid #e2e8f0; background: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; color: #64748b;">Watermark hiển thị: <strong>CRENO.VN SHOP</strong> (2 trang đầu)</span>
            <button type="button" onclick="document.getElementById('btnCloseSellerPreview').click()" style="padding: 8px 18px; border-radius: 10px; background: #0071e3; color: #fff; border: none; font-weight: 600; cursor: pointer;">Đóng xem trước</button>
        </div>
    </div>
</div>

<script src="<?php echo URLROOT; ?>/js/libs/pdf.min.js"></script>
<script>
    (function() {
        const btnPreview = document.getElementById('btnSellerPreview');
        const modal = document.getElementById('modalSellerPreview');
        const btnClose = document.getElementById('btnCloseSellerPreview');
        const iframe = document.getElementById('sellerPreviewIframe');
        const canvasContainer = document.getElementById('sellerCanvasContainer');
        const loading = document.getElementById('sellerPreviewLoading');
        const inputDoc = document.getElementById('document_file');

        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = '<?php echo URLROOT; ?>/js/libs/pdf.worker.min.js';
        }

        async function renderPdfWithCanvas(pdfUrl, watermarkText) {
            canvasContainer.innerHTML = '';
            canvasContainer.style.display = 'block';
            iframe.style.display = 'none';

            const loadingTask = pdfjsLib.getDocument(pdfUrl);
            const pdf = await loadingTask.promise;
            const pagesToRender = Math.min(2, pdf.numPages);

            for (let pageNum = 1; pageNum <= pagesToRender; pageNum++) {
                const page = await pdf.getPage(pageNum);
                const viewport = page.getViewport({ scale: 1.3 });

                const pageBox = document.createElement('div');
                pageBox.style.cssText = 'position:relative;margin:0 auto 20px auto;background:#fff;border-radius:6px;box-shadow:0 4px 20px rgba(0,0,0,0.3);overflow:hidden;width:' + viewport.width + 'px;max-width:100%;';

                const canvas = document.createElement('canvas');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                canvas.style.cssText = 'display:block;width:100%;height:auto;';
                const ctx = canvas.getContext('2d');

                await page.render({ canvasContext: ctx, viewport: viewport }).promise;

                // Vẽ dấu Watermark chéo
                ctx.save();
                ctx.translate(viewport.width / 2, viewport.height / 2);
                ctx.rotate(-45 * Math.PI / 180);
                ctx.font = 'bold ' + Math.round(viewport.width * 0.052) + 'px Arial, sans-serif';
                ctx.fillStyle = 'rgba(180, 180, 180, 0.45)';
                ctx.textAlign = 'center';
                ctx.fillText(watermarkText || 'CRENO.VN SHOP', 0, 0);
                ctx.restore();

                const badge = document.createElement('div');
                badge.style.cssText = 'position:absolute;top:10px;right:10px;background:rgba(15,23,42,0.8);color:#fff;font-size:11px;padding:4px 10px;border-radius:12px;font-weight:600;pointer-events:none;';
                badge.textContent = 'Trang ' + pageNum + ' / 2';
                pageBox.appendChild(badge);

                pageBox.appendChild(canvas);
                canvasContainer.appendChild(pageBox);
            }

            // Trang thứ 3: Khóa nội dung
            const lockBox = document.createElement('div');
            lockBox.style.cssText = 'position:relative;margin:0 auto 20px auto;background:#fffaf0;border-radius:6px;box-shadow:0 4px 20px rgba(0,0,0,0.3);overflow:hidden;width:100%;max-width:760px;min-height:380px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;text-align:center;box-sizing:border-box;border:2px dashed #fca5a5;';

            lockBox.innerHTML = '<div style="background:#fee2e2;color:#b91c1c;padding:6px 16px;border-radius:20px;font-size:12px;font-weight:700;margin-bottom:14px;text-transform:uppercase;">' +
                'Giới hạn xem trước 2 trang đầu' +
                '</div>' +
                '<h3 style="margin:0 0 10px 0;font-size:18px;font-weight:700;color:#1e293b;">Nội dung tiếp theo đã được khóa</h3>' +
                '<p style="margin:0 auto 20px auto;max-width:480px;font-size:13px;color:#64748b;line-height:1.6;">' +
                'Hệ thống tự động hiển thị 2 trang đầu kèm Watermark bản quyền. Người mua sẽ mở khóa toàn bộ nội dung và tải file gốc sau khi thanh toán.' +
                '</p>' +
                '<div style="font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:14px;width:100%;max-width:360px;">' +
                'Bản quyền: ' + (watermarkText || 'CRENO.VN SHOP') +
                '</div>';

            canvasContainer.appendChild(lockBox);
        }

        function renderFallbackImages(images) {
            canvasContainer.innerHTML = '';
            canvasContainer.style.display = 'block';
            iframe.style.display = 'none';

            images.forEach(function(imgUrl, idx) {
                const wrap = document.createElement('div');
                wrap.style.cssText = 'margin:0 auto 20px auto;background:#fff;border-radius:6px;box-shadow:0 4px 20px rgba(0,0,0,0.3);overflow:hidden;max-width:100%;';
                const imageEl = document.createElement('img');
                imageEl.src = imgUrl;
                imageEl.style.cssText = 'display:block;width:100%;height:auto;';
                wrap.appendChild(imageEl);
                canvasContainer.appendChild(wrap);
            });
        }

        if (btnPreview && modal) {
            btnPreview.addEventListener('click', async function() {
                const hasDoc = inputDoc.files && inputDoc.files.length > 0;

                if (!hasDoc) {
                    // Chưa chọn file PDF mới -> mở xem trước bản hiện tại của sản phẩm
                    const currentProdId = <?= (int)$data['product']->id; ?>;
                    window.open('<?php echo URLROOT; ?>/products/preview/' + currentProdId, '_blank');
                    return;
                }

                const docFile = inputDoc.files[0];
                const ext = docFile.name.split('.').pop().toLowerCase();

                if (ext !== 'pdf') {
                    modal.style.display = 'flex';
                    iframe.style.display = 'none';
                    canvasContainer.style.display = 'none';
                    loading.style.display = 'block';
                    loading.innerHTML = '<div style="text-align:center;padding:30px 20px;">' +
                        '<div style="font-size:16px;font-weight:600;color:#fbbf24;margin-bottom:8px;">File .' + ext.toUpperCase() + ' không hỗ trợ xem trước</div>' +
                        '<div style="font-size:13px;color:#94a3b8;">Tính năng Xem trước Watermark chỉ hỗ trợ file PDF.<br>' +
                        'File ZIP, RAR không hiển thị được nội dung tài liệu bên trong.</div>' +
                        '<button onclick="document.getElementById(\'btnCloseSellerPreview\').click()" ' +
                        'style="margin-top:20px;padding:8px 20px;border-radius:8px;background:#0071e3;color:#fff;border:none;cursor:pointer;font-weight:600;">Đóng</button>' +
                        '</div>';
                    return;
                }

                modal.style.display = 'flex';
                iframe.style.display = 'none';
                canvasContainer.style.display = 'none';
                loading.style.display = 'block';
                loading.textContent = 'Đang đọc và đóng dấu Watermark file PDF...';

                const formData = new FormData();
                formData.append('document_file', docFile);

                try {
                    const response = await fetch('<?php echo URLROOT; ?>/products/previewUpload', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        loading.style.display = 'none';

                        if (result.mode === 'server_pdf' && result.preview_url) {
                            iframe.src = result.preview_url + '#toolbar=0&navpanes=0&scrollbar=1';
                            iframe.style.display = 'block';
                        } else if (result.mode === 'canvas_fallback' && result.pdf_url && window.pdfjsLib) {
                            try {
                                await renderPdfWithCanvas(result.pdf_url, result.watermark_text || 'CRENO.VN SHOP');
                            } catch (errCanvas) {
                                console.warn('PDF.js render error, fallback to GD images:', errCanvas);
                                if (result.preview_images && result.preview_images.length > 0) {
                                    renderFallbackImages(result.preview_images);
                                }
                            }
                        } else if (result.preview_images && result.preview_images.length > 0) {
                            renderFallbackImages(result.preview_images);
                        } else {
                            iframe.src = (result.preview_url || result.pdf_url) + '#toolbar=0&navpanes=0&scrollbar=1';
                            iframe.style.display = 'block';
                        }
                    } else {
                        loading.innerHTML = '<div style="text-align:center;color:#fbbf24;padding:20px;">' +
                            (result.message || 'Không thể tạo bản xem trước.') +
                            (result.hint ? '<br><small style="color:#94a3b8;">' + result.hint + '</small>' : '') + '</div>';
                    }
                } catch (err) {
                    loading.textContent = 'Lỗi kết nối máy chủ khi đóng dấu xem trước.';
                }
            });

            const closeModal = function() {
                modal.style.display = 'none';
                iframe.src = '';
                canvasContainer.innerHTML = '';
            };

            btnClose.addEventListener('click', closeModal);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });
        }
    })();
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>