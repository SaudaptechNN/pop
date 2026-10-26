<?php
$footerServices = array_filter(get_services(), function ($s) { return (int) $s['in_footer'] === 1; });
$phone = setting('phone');
$email = setting('email');
?>
    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <a class="brand footer-brand" href="<?= e(url('index.php')) ?>"><?php include __DIR__ . '/partials/logo.php'; ?></a>
                <p><?= e(setting('footer_text')) ?></p>
            </div>
            <div>
                <h4>Explore</h4>
                <ul class="footer-links">
                    <li><a href="<?= e(url('services.php')) ?>">Services</a></li>
                    <?php foreach ($footerServices as $s): ?>
                        <li><a href="<?= e(service_url($s['slug'])) ?>"><?= e($s['page_title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h4>Contact</h4>
                <ul class="footer-links">
                    <li><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= e($phone) ?></a></li>
                    <li><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <span>&copy; <span id="year"><?= date('Y') ?></span> <?= e(setting('site_name')) ?></span>
                <span class="footer-credit"><span class="credit-dot"></span> <?= e(setting('footer_credit')) ?></span>
            </div>
        </div>
    </footer>
</div>
<script src="<?= e(url('assets/js/script.js')) ?>"></script>
</body>
</html>
