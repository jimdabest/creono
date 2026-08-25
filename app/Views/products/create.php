<?php /** @var array $data */ ?>
<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="container page-container" style="margin-top: 40px; margin-bottom: 80px; max-width: 720px;">
    <h1 style="font-size: 32px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 8px; color: #1d1d1f;">
        Đăng sản phẩm mới
    </h1>
    <p style="font-size: 16px; color: #86868b; margin-bottom: 32px;">
        Tài liệu của bạn sẽ được kiểm duyệt trước khi đăng tải lên chợ.
    </p>

    <div class="card" style="max-width: 100%; padding: 32px 36px; border-radius: 20px;">
        <form action="<?php echo URLROOT; ?>/products/create" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token']); ?>">

            <!-- Tiêu đề -->
            <div class="form-group">
                <label for="title">Tiêu đề sản phẩm <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control <?php echo isset($data['errors']['title_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['product']->title ?? ''); ?>" placeholder="Ví dụ: Source Code Quản lý Bán hàng PHP" required>
                <span class="error-text"><?php echo $data['errors']['title_err'] ?? ''; ?></span>
            </div>

            <!-- Danh mục -->
            <div class="form-group">
                <label for="category_id">Danh mục <span class="text-danger">*</span></label>
                <select name="category_id" id="category_id" class="form-control <?php echo isset($data['errors']['category_err']) ? 'is-invalid' : ''; ?>" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($data['categories'] as $cat) : ?>
                        <option value="<?php echo $cat->id; ?>" <?php echo (isset($data['product']) && $data['product']->category_id == $cat->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="error-text"><?php echo $data['errors']['category_err'] ?? ''; ?></span>
            </div>

            <!-- Giá bán -->
            <div class="form-group">
                <label for="price">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" name="price" id="price" class="form-control <?php echo isset($data['errors']['price_err']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['product']->price ?? ''); ?>" placeholder="Ví dụ: 250000" required min="0" step="1000">
                <span class="error-text"><?php echo $data['errors']['price_err'] ?? ''; ?></span>
            </div>

            <!-- Mô tả -->
            <div class="form-group">
                <label for="description">Mô tả chi tiết <span class="text-danger">*</span></label>
                <textarea name="description" id="description" rows="6" class="form-control <?php echo isset($data['errors']['description_err']) ? 'is-invalid' : ''; ?>" placeholder="Mô tả nội dung tài liệu, công nghệ sử dụng, tính năng nổi bật..." required><?php echo htmlspecialchars($data['product']->description ?? ''); ?></textarea>
                <span class="error-text"><?php echo $data['errors']['description_err'] ?? ''; ?></span>
            </div>

            <!-- Ảnh cover -->
            <div class="form-group">
                <label for="preview_image">Ảnh cover (JPG, PNG, GIF, WebP)</label>
                <input type="file" name="preview_image" id="preview_image" class="form-control <?php echo isset($data['errors']['preview_err']) ? 'is-invalid' : ''; ?>" accept="image/*">
                <span class="error-text"><?php echo $data['errors']['preview_err'] ?? ''; ?></span>
                <small class="form-hint">Tải lên ảnh đại diện cho sản phẩm. Tối ưu kích thước 600x400px.</small>
            </div>

            <!-- File tài liệu -->
            <div class="form-group">
                <label for="document_file">File tài liệu (ZIP, PDF, RAR) <span class="text-danger">*</span></label>
                <input type="file" name="document_file" id="document_file" class="form-control <?php echo isset($data['errors']['document_err']) ? 'is-invalid' : ''; ?>" accept=".zip,.pdf,.rar" required>
                <span class="error-text"><?php echo $data['errors']['document_err'] ?? ''; ?></span>
                <small class="form-hint">Chỉ chấp nhận file ZIP, PDF, RAR. Dung lượng tối đa 50MB.</small>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 28px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 14px; font-size: 16px; font-weight: 600; border-radius: 14px;">Đăng sản phẩm</button>
                <a href="<?php echo URLROOT; ?>/seller/dashboard" class="btn btn-secondary" style="flex: 0.4; text-align: center; padding: 14px; border-radius: 14px; background: #f5f5f7; color: #1d1d1f; text-decoration: none;">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/Views/inc/footer.php'; ?>