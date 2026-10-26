<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/partials/sections.php';

$page = 'home';
$pageTitle = '';
$services = get_services();
$email = setting('email');

// Result of the inquiry form (set by contact.php)
$formStatus = $_SESSION['inquiry_status'] ?? null;
$formErrors = $_SESSION['inquiry_errors'] ?? [];
$old = $_SESSION['inquiry_old'] ?? [];
unset($_SESSION['inquiry_status'], $_SESSION['inquiry_errors'], $_SESSION['inquiry_old']);

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="hero">
        <?php if (setting('hero_video') !== ''): ?>
            <video class="hero-video" autoplay muted loop playsinline poster="<?= e(asset(setting('hero_poster'))) ?>"><source src="<?= e(asset(setting('hero_video'))) ?>" type="video/mp4"></video>
        <?php endif; ?>
        <div class="hero-overlay"></div>
        <div class="container hero-grid">
            <div class="hero-copy">
                <span class="eyebrow"><?= e(setting('hero_eyebrow')) ?></span>
                <h1><?= e(setting('hero_title')) ?></h1>
                <p><?= e(setting('hero_text')) ?></p>
                <div class="hero-actions">
                    <a href="<?= e(url('services.php')) ?>" class="btn btn-primary">Explore Services</a>
                    <a href="<?= e(service_url('experience-catering')) ?>" class="btn btn-secondary">Explore Curated Experiences</a>
                </div>
                <div class="stat-strip">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="stat-item"><strong><?= e(setting("stat_{$i}_value")) ?></strong><span><?= e(setting("stat_{$i}_label")) ?></span></div>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="hero-visual" aria-label="Event experience carousel">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="hero-slide hero-slide-<?= $i ?>"><img src="<?= e(asset(setting("hero_slide_{$i}"))) ?>" alt="<?= e(setting('site_name')) ?> event setup <?= $i ?>"></div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <?php render_marquee(); ?>

    <section class="section section-ivory">
        <div class="container">
            <div class="section-header">
                <span class="eyebrow"><?= e(setting('services_eyebrow')) ?></span>
                <h2><?= rich(setting('services_title')) ?></h2>
                <p><?= e(setting('services_text')) ?></p>
            </div>
            <?php render_service_cards($services); ?>
        </div>
    </section>

    <section class="section promo-band section-sand">
        <div class="container">
            <h3><?= e(setting('promo_text')) ?></h3>
            <a href="mailto:<?= e($email) ?>" class="btn btn-primary"><?= e(setting('promo_button')) ?></a>
        </div>
    </section>

    <section class="section section-blush">
        <div class="container dual-grid">
            <div class="photo-panel"><img src="<?= e(asset(setting('about_image'))) ?>" alt="Guests enjoying drinks at <?= e(setting('site_name')) ?>"></div>
            <div>
                <span class="eyebrow"><?= e(setting('about_eyebrow')) ?></span>
                <h2><?= rich(setting('about_title')) ?></h2>
                <p><?= e(setting('about_text')) ?></p>
                <ul class="feature-list">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <li><span class="icon">&#10003;</span><div><strong><?= e(setting("feature_{$i}_title")) ?></strong><p><?= e(setting("feature_{$i}_text")) ?></p></div></li>
                    <?php endfor; ?>
                </ul>
            </div>
        </div>
    </section>

    <?php render_gallery('home', setting('gallery_eyebrow'), setting('gallery_title')); ?>

    <section class="section section-ivory-soft">
        <div class="container">
            <div class="section-header"><span class="eyebrow"><?= e(setting('events_eyebrow')) ?></span><h2><?= rich(setting('events_title')) ?></h2></div>
            <div class="grid-4">
                <?php foreach (lines(setting('events_list')) as $i => $name): ?>
                    <div class="mini-card"><div class="num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></div><h3><?= e($name) ?></h3><p><?= e(setting('events_text')) ?></p></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section contact-cta section-dark-panel">
        <div class="container contact-panel">
            <div class="contact-copy">
                <span class="eyebrow"><?= e(setting('contact_eyebrow')) ?></span>
                <h2><?= rich(setting('contact_title')) ?></h2>
                <p><?= e(setting('contact_text')) ?></p>
            </div>
            <div class="contact-boxes">
                <div class="contact-box"><span class="contact-icon"><?= contact_icon('phone') ?></span><div><strong>Call Us Today</strong><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a></div></div>
                <div class="contact-box"><span class="contact-icon"><?= contact_icon('mail') ?></span><div><strong>Email</strong><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></div></div>
                <div class="contact-box"><span class="contact-icon"><?= contact_icon('pin') ?></span><div><strong>Location</strong><span><?= e(setting('address')) ?></span></div></div>
            </div>
        </div>
    </section>

    <section id="event-contact" class="section section-cream event-contact-section">
        <div class="container">
            <div class="form-showcase">
                <div class="form-header">
                    <span class="eyebrow"><?= e(setting('form_eyebrow')) ?></span>
                    <h3><?= e(setting('form_title')) ?></h3>
                    <p><?= e(setting('form_text')) ?></p>
                </div>
                <?php if ($formStatus === 'sent'): ?>
                    <div class="form-alert form-alert-success" role="status"><?= e(setting('form_success')) ?></div>
                <?php elseif ($formErrors): ?>
                    <div class="form-alert form-alert-error" role="alert"><?php foreach ($formErrors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
                <?php endif; ?>
                <form class="newsletter-form event-contact-form" method="post" action="<?= e(url('contact.php')) ?>">
                    <?= csrf_field() ?>
                    <div class="hp-field" aria-hidden="true"><label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                    <div class="field-row">
                        <label><span>Your name</span><input name="name" type="text" placeholder="Your full name" maxlength="150" value="<?= e($old['name'] ?? '') ?>" required></label>
                        <label><span>Email address</span><input name="email" type="email" placeholder="you@example.com" maxlength="190" value="<?= e($old['email'] ?? '') ?>" required></label>
                    </div>
                    <div class="field-row">
                        <label><span>Event type</span><input name="event" type="text" placeholder="Wedding, corporate event, launch..." maxlength="190" value="<?= e($old['event'] ?? '') ?>" required></label>
                        <label><span>Event date</span><input name="date" type="date" value="<?= e($old['date'] ?? '') ?>"></label>
                    </div>
                    <label><span>Tell us about your vision</span><textarea name="message" placeholder="Tell us about your guests, location, and the experience you have in mind." maxlength="5000" required><?= e($old['message'] ?? '') ?></textarea></label>
                    <button class="btn btn-primary submit-btn" type="submit">Send Event Inquiry</button>
                </form>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
