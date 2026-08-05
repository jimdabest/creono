<?php require APPROOT . '/Views/inc/header.php'; ?>

<div class="card" style="max-width: 600px;">
    <h2>Vanilla JS Test</h2>
    
    <div style="margin: 20px 0;">
        <button id="testBtn" class="btn">Test Flash Message</button>
        <button id="testLoaderBtn" class="btn" style="background: #6c757d;">Test Loading</button>
        <button id="testValidationBtn" class="btn" style="background: #28a745;">Test Validation</button>
    </div>

    <!-- Thêm data-no-validation để ValidationModule không tự động can thiệp -->
    <form id="testForm" data-no-validation style="margin-top: 20px;">
        <div class="form-group">
            <label>Email (required):</label>
            <input type="email" name="email" required placeholder="test@email.com">
        </div>
        <div class="form-group">
            <label>Password (min 6):</label>
            <input type="password" name="password" required minlength="6" placeholder="Mật khẩu tối thiểu 6 ký tự">
        </div>
        <button type="submit" class="btn">Submit Test</button>
    </form>

    <div id="result" style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 4px; display: none;">
        
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test flash message
    document.getElementById('testBtn').addEventListener('click', function() {
        if (typeof FlashModule !== 'undefined') {
            FlashModule.show('✅ Đây là flash message từ Vanilla JS!', 'success');
        } else {
            alert('FlashModule chưa được load!');
        }
    });

    // Test loading
    document.getElementById('testLoaderBtn').addEventListener('click', function() {
        if (typeof window.showLoading === 'function') {
            window.showLoading(true);
            setTimeout(function() {
                window.showLoading(false);
            }, 3000);
        } else {
            alert('showLoading chưa được định nghĩa!');
        }
    });

    // Test validation - Sử dụng ValidationModule
    document.getElementById('testValidationBtn').addEventListener('click', function() {
        if (typeof ValidationModule !== 'undefined') {
            const form = document.getElementById('testForm');
            // Validate thủ công
            const isValid = ValidationModule.validateForm(form);
            
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';
            
            if (isValid) {
                resultDiv.innerHTML = '✅ Form validation thành công!';
                resultDiv.style.color = 'green';
            } else {
                resultDiv.innerHTML = '❌ Form có lỗi, vui lòng kiểm tra lại!';
                resultDiv.style.color = 'red';
            }
        } else {
            alert('ValidationModule chưa được load!');
        }
    });

    // Form submit - Tự xử lý nếu muốn bypass validation
    document.getElementById('testForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Nếu muốn dùng ValidationModule để validate
        if (typeof ValidationModule !== 'undefined') {
            const isValid = ValidationModule.validateForm(this);
            if (!isValid) {
                return; // Dừng nếu có lỗi
            }
        }
        
        const resultDiv = document.getElementById('result');
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '✅ Form submitted successfully!';
        resultDiv.style.color = 'green';
    });
});
</script>

<?php require APPROOT . '/Views/inc/footer.php'; ?>