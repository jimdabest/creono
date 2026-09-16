<?php
/**
 * Partial: Modal xem trước Watermark dùng chung
 * Biến truyền vào:
 *   $previewMode   'upload' (dùng cho create/edit) | 'saved' (dùng cho detail)
 *   $productId     ID sản phẩm (chỉ cần khi $previewMode = 'saved')
 *   $inputFileId   ID của input file (mặc định 'document_file', chỉ cần khi mode upload)
 *   $watermarkText Text watermark (mặc định 'CRENO.VN SHOP')
 */
$previewMode   = $previewMode   ?? 'upload';
$productId     = $productId     ?? 0;
$inputFileId   = $inputFileId   ?? 'document_file';
$watermarkText = $watermarkText ?? 'CRENO.VN SHOP';
?>

<!-- Modal Xem trước dành cho Seller/Buyer -->
<div id="modalSellerPreview"
     style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 920px; height: 88vh; border-radius: 20px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #0071e3;"></span>
                <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">Bản xem trước có đóng dấu bản quyền</h4>
            </div>
            <button type="button" id="btnCloseSellerPreview"
                    style="background: #e2e8f0; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 16px;">✕</button>
        </div>
        <div id="sellerPreviewContent"
             style="flex: 1; background: #525659; overflow-y: auto; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 20px 10px;">
            <div id="sellerCanvasContainer" style="display: none; width: 100%; max-width: 760px;"></div>
            <iframe id="sellerPreviewIframe" style="display: none; width: 100%; height: 100%; border: none;"></iframe>
            <div id="sellerPreviewLoading" style="color: #ffffff; font-size: 14px; text-align: center; padding: 40px;">Đang tải và đóng dấu xem trước...</div>
        </div>
        <div style="padding: 12px 24px; border-top: 1px solid #e2e8f0; background: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; color: #64748b;">Watermark hiển thị: <strong><?= htmlspecialchars($watermarkText); ?></strong> (2 trang đầu)</span>
            <button type="button"
                    onclick="document.getElementById('btnCloseSellerPreview').click()"
                    style="padding: 8px 18px; border-radius: 10px; background: #0071e3; color: #fff; border: none; font-weight: 600; cursor: pointer;">Đóng xem trước</button>
        </div>
    </div>
</div>

<script src="<?php echo URLROOT; ?>/js/libs/pdf.min.js"></script>
<script>
    (function () {
        const PREVIEW_MODE   = '<?= $previewMode; ?>';
        const PRODUCT_ID     = <?= (int) $productId; ?>;
        const INPUT_FILE_ID  = '<?= $inputFileId; ?>';
        const WATERMARK_TEXT = '<?= addslashes($watermarkText); ?>';

        const btnPreview      = document.getElementById('btnSellerPreview');
        const modal           = document.getElementById('modalSellerPreview');
        const btnClose        = document.getElementById('btnCloseSellerPreview');
        const iframe          = document.getElementById('sellerPreviewIframe');
        const canvasContainer = document.getElementById('sellerCanvasContainer');
        const loading         = document.getElementById('sellerPreviewLoading');
        const inputDoc        = document.getElementById(INPUT_FILE_ID);

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

                ctx.save();
                ctx.translate(viewport.width / 2, viewport.height / 2);
                ctx.rotate(-45 * Math.PI / 180);
                ctx.font = 'bold ' + Math.round(viewport.width * 0.052) + 'px Arial, sans-serif';
                ctx.fillStyle = 'rgba(180, 180, 180, 0.45)';
                ctx.textAlign = 'center';
                ctx.fillText(watermarkText || WATERMARK_TEXT, 0, 0);
                ctx.restore();

                const badge = document.createElement('div');
                badge.style.cssText = 'position:absolute;top:10px;right:10px;background:rgba(15,23,42,0.8);color:#fff;font-size:11px;padding:4px 10px;border-radius:12px;font-weight:600;pointer-events:none;';
                badge.textContent = 'Trang ' + pageNum + ' / 2';
                pageBox.appendChild(badge);
                pageBox.appendChild(canvas);
                canvasContainer.appendChild(pageBox);
            }

            const lockBox = document.createElement('div');
            lockBox.style.cssText = 'position:relative;margin:0 auto 20px auto;background:#fffaf0;border-radius:6px;box-shadow:0 4px 20px rgba(0,0,0,0.3);overflow:hidden;width:100%;max-width:760px;min-height:380px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;text-align:center;box-sizing:border-box;border:2px dashed #fca5a5;';
            lockBox.innerHTML =
                '<div style="background:#fee2e2;color:#b91c1c;padding:6px 16px;border-radius:20px;font-size:12px;font-weight:700;margin-bottom:14px;text-transform:uppercase;">Giới hạn xem trước 2 trang đầu</div>' +
                '<h3 style="margin:0 0 10px 0;font-size:18px;font-weight:700;color:#1e293b;">Nội dung tiếp theo đã được khóa</h3>' +
                '<p style="margin:0 auto 20px auto;max-width:480px;font-size:13px;color:#64748b;line-height:1.6;">Hệ thống tự động hiển thị 2 trang đầu kèm Watermark bản quyền. Người mua sẽ mở khóa toàn bộ nội dung và tải file gốc sau khi thanh toán.</p>' +
                '<div style="font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:14px;width:100%;max-width:360px;">Bản quyền: ' + (watermarkText || WATERMARK_TEXT) + '</div>';
            canvasContainer.appendChild(lockBox);
        }

        function renderFallbackImages(images) {
            canvasContainer.innerHTML = '';
            canvasContainer.style.display = 'block';
            iframe.style.display = 'none';
            images.forEach(function (imgUrl) {
                const wrap = document.createElement('div');
                wrap.style.cssText = 'margin:0 auto 20px auto;background:#fff;border-radius:6px;box-shadow:0 4px 20px rgba(0,0,0,0.3);overflow:hidden;max-width:100%;';
                const imageEl = document.createElement('img');
                imageEl.src = imgUrl;
                imageEl.style.cssText = 'display:block;width:100%;height:auto;';
                wrap.appendChild(imageEl);
                canvasContainer.appendChild(wrap);
            });
        }

        if (!btnPreview || !modal) return;

        btnPreview.addEventListener('click', async function () {
            // ============ CHẾ ĐỘ DETAIL (saved): Xem file đã lưu trên server ============
            if (PREVIEW_MODE === 'saved') {
                modal.style.display = 'flex';
                loading.style.display = 'block';
                loading.textContent = 'Đang tải bản xem trước có đóng dấu...';
                iframe.style.display = 'none';
                canvasContainer.style.display = 'none';

                // Gọi API lấy thông tin file đã lưu
                try {
                    const response = await fetch('<?php echo URLROOT; ?>/products/getPreviewData/' + PRODUCT_ID, {
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const result = await response.json();

                    if (result.success) {
                        loading.style.display = 'none';

                        if (result.mode === 'server_pdf' && result.preview_url) {
                            iframe.src = result.preview_url + '#toolbar=0&navpanes=0&scrollbar=1';
                            iframe.style.display = 'block';
                        } else if (result.mode === 'canvas_fallback' && result.pdf_url && window.pdfjsLib) {
                            try {
                                await renderPdfWithCanvas(result.pdf_url, result.watermark_text || WATERMARK_TEXT);
                            } catch (errCanvas) {
                                console.warn('PDF.js render error:', errCanvas);
                                if (result.preview_images?.length) renderFallbackImages(result.preview_images);
                            }
                        } else if (result.preview_images?.length) {
                            renderFallbackImages(result.preview_images);
                        } else if (result.preview_url) {
                            iframe.src = result.preview_url + '#toolbar=0&navpanes=0&scrollbar=1';
                            iframe.style.display = 'block';
                        } else {
                            loading.innerHTML = '<div style="text-align:center;color:#fbbf24;padding:20px;">' + (result.message || 'Không thể tạo bản xem trước.') + '</div>';
                        }
                    } else {
                        loading.innerHTML = '<div style="text-align:center;color:#fbbf24;padding:20px;">' + (result.message || 'Không thể tạo bản xem trước.') + '</div>';
                    }
                } catch (err) {
                    loading.textContent = 'Lỗi kết nối máy chủ khi tải xem trước.';
                }
                return;
            }

            // ============ CHẾ ĐỘ UPLOAD (create/edit): Upload file mới để preview ============
            const hasDoc = inputDoc && inputDoc.files && inputDoc.files.length > 0;
            if (!hasDoc) {
                modal.style.display = 'flex';
                iframe.style.display = 'none';
                canvasContainer.style.display = 'none';
                loading.style.display = 'block';
                loading.innerHTML =
                    '<div style="text-align:center;padding:30px 20px;">' +
                    '<div style="font-size:16px;font-weight:600;color:#fff;margin-bottom:8px;">Chưa chọn file tài liệu PDF</div>' +
                    '<div style="font-size:13px;color:#94a3b8;">Tính năng Xem trước Watermark chỉ hoạt động với file PDF.<br>Hãy chọn file <strong>.pdf</strong> tại ô "File tài liệu" trước khi bấm xem trước.</div>' +
                    '<button onclick="document.getElementById(\'btnCloseSellerPreview\').click()" style="margin-top:20px;padding:8px 20px;border-radius:8px;background:#0071e3;color:#fff;border:none;cursor:pointer;font-weight:600;">Đóng</button>' +
                    '</div>';
                return;
            }

            const docFile = inputDoc.files[0];
            const ext = docFile.name.split('.').pop().toLowerCase();

            if (ext !== 'pdf') {
                modal.style.display = 'flex';
                iframe.style.display = 'none';
                canvasContainer.style.display = 'none';
                loading.style.display = 'block';
                loading.innerHTML =
                    '<div style="text-align:center;padding:30px 20px;">' +
                    '<div style="font-size:16px;font-weight:600;color:#fbbf24;margin-bottom:8px;">File .' + ext.toUpperCase() + ' không hỗ trợ xem trước</div>' +
                    '<div style="font-size:13px;color:#94a3b8;">Tính năng Xem trước Watermark chỉ hỗ trợ file PDF.<br>File ZIP, RAR không hiển thị được nội dung tài liệu bên trong.</div>' +
                    '<button onclick="document.getElementById(\'btnCloseSellerPreview\').click()" style="margin-top:20px;padding:8px 20px;border-radius:8px;background:#0071e3;color:#fff;border:none;cursor:pointer;font-weight:600;">Đóng</button>' +
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
                            await renderPdfWithCanvas(result.pdf_url, result.watermark_text || WATERMARK_TEXT);
                        } catch (errCanvas) {
                            console.warn('PDF.js render error:', errCanvas);
                            if (result.preview_images?.length) renderFallbackImages(result.preview_images);
                        }
                    } else if (result.preview_images?.length) {
                        renderFallbackImages(result.preview_images);
                    } else {
                        iframe.src = (result.preview_url || result.pdf_url) + '#toolbar=0&navpanes=0&scrollbar=1';
                        iframe.style.display = 'block';
                    }
                } else {
                    loading.innerHTML =
                        '<div style="text-align:center;color:#fbbf24;padding:20px;">' +
                        (result.message || 'Không thể tạo bản xem trước.') +
                        (result.hint ? '<br><small style="color:#94a3b8;">' + result.hint + '</small>' : '') +
                        '</div>';
                }
            } catch (err) {
                loading.textContent = 'Lỗi kết nối máy chủ khi đóng dấu xem trước.';
            }
        });

        const closeModal = function () {
            modal.style.display = 'none';
            iframe.src = '';
            canvasContainer.innerHTML = '';
        };
        btnClose.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
    })();
</script>
