<?php if (isset($class) && isset($message)): ?>
    <div class="alert <?php echo htmlspecialchars($class); ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>