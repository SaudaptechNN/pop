<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/partials/sections.php';

$page = 'services';
$pageTitle = 'Services';
$services = get_services();

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="service-page-hero">
        <div class="container">
            <div>
                <span class="eyebrow"><?= e(setting('services_page_eyebrow')) ?></span>
                <h1><?= e(setting('services_page_title')) ?></h1>
                <p><?= e(setting('services_page_text')) ?></p>
                <div class="hero-actions">
                    <a href="<?= e(service_url('corporate-catering')) ?>" class="btn btn-primary">Corporate Events</a>
                    <a href="<?= e(service_url('live-food-stations')) ?>" class="btn btn-secondary">Live Food Stations</a>
                </div>
            </div>
            <img src="<?= e(asset(setting('services_page_image'))) ?>" alt="<?= e(setting('site_name')) ?> cart styled for an event">
        </div>
    </section>
    <section class="section"><div class="container"><?php render_service_cards($services); ?></div></section>
    <?php render_gallery('home', setting('gallery_eyebrow'), 'Real events. <em>Real guests.</em>'); ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
