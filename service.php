<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/partials/sections.php';

$slug = (string) ($_GET['slug'] ?? '');
$service = $slug !== '' ? get_service($slug) : null;
if (!$service) {
    http_response_code(404);
    $page = '404';
    $pageTitle = 'Page not found';
    require __DIR__ . '/includes/header.php';
    ?>
    <main>
        <section class="service-page-hero">
            <div class="container">
                <div>
                    <span class="eyebrow">404</span>
                    <h1>This page could not be found.</h1>
                    <p>The experience you are looking for may have moved. Explore our services instead.</p>
                    <div class="hero-actions"><a href="<?= e(url('services.php')) ?>" class="btn btn-primary">View Services</a></div>
                </div>
            </div>
        </section>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$page = $service['slug'];
$pageTitle = $service['page_title'];
$metaDescription = $service['page_description'];

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="service-page-hero">
        <div class="container">
            <div>
                <span class="eyebrow"><?= e($service['eyebrow']) ?></span>
                <h1><?= e($service['page_title']) ?></h1>
                <p><?= e($service['page_description']) ?></p>
                <div class="hero-actions"><a href="<?= e(url('services.php')) ?>" class="btn btn-secondary">Back to Services</a></div>
            </div>
            <img src="<?= e(asset($service['hero_image'])) ?>" alt="<?= e($service['page_title']) ?>">
        </div>
    </section>

    <?php if ($service['layout'] === 'cards'): ?>
        <section class="section"><div class="container"><?php render_service_cards(get_services()); ?></div></section>
    <?php else: ?>
        <section class="section">
            <div class="container detail-grid">
                <div class="info-box">
                    <h3>Signature service</h3>
                    <ul><?php foreach (lines($service['points']) as $point): ?><li><?= e($point) ?></li><?php endforeach; ?></ul>
                </div>
                <div class="info-box">
                    <h3>Built for unforgettable events</h3>
                    <p>Every detail is curated around your guests, your story, and the energy you want your event to create.</p>
                    <ul><li>Luxury private parties</li><li>Brand launches and activations</li><li>High-touch social gatherings</li><li>Memorable event storytelling</li></ul>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php render_gallery($service['slug'], 'From our events', 'A closer <em>look.</em>'); ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
