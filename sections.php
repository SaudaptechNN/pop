<?php
// Reusable page sections shared by the public pages.

function render_service_cards(array $services): void
{
    ?>
    <div class="grid-3">
        <?php foreach ($services as $s): ?>
            <article class="card">
                <div class="card-media"><img src="<?= e(asset($s['card_image'])) ?>" alt="<?= e($s['card_title']) ?>"></div>
                <div class="card-body">
                    <span class="tag">Experience</span>
                    <h3><?= e($s['card_title']) ?></h3>
                    <p><?= e($s['card_text']) ?></p>
                    <a href="<?= e(service_url($s['slug'])) ?>" class="btn btn-secondary">Explore experience</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php
}

function render_gallery(string $pageKey, string $eyebrow, string $title): void
{
    $items = get_gallery($pageKey);
    if (!$items) {
        return;
    }
    ?>
    <section class="section gallery-section<?= count($items) > 3 ? ' gallery-section-wide' : '' ?>">
        <div class="container">
            <div class="section-header"><span class="eyebrow"><?= e($eyebrow) ?></span><h2><?= rich($title) ?></h2></div>
            <div class="gallery-grid">
                <?php foreach ($items as $img): ?>
                    <figure class="gallery-item"><img src="<?= e(asset($img['image'])) ?>" alt="<?= e($img['caption']) ?>" loading="lazy"><figcaption><?= e($img['caption']) ?></figcaption></figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function render_marquee(): void
{
    $row = '';
    foreach (lines(setting('marquee_words')) as $word) {
        $row .= '<span>' . e($word) . '</span><i>&#10022;</i>';
    }
    echo '<div class="marquee" aria-hidden="true"><div class="marquee-track">' . $row . $row . '</div></div>';
}

function contact_icon(string $name): string
{
    $icons = [
        'phone' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 3.5 9 3l2 4.7-1.7 1.4a15.3 15.3 0 0 0 5.6 5.6l1.4-1.7 4.7 2-.5 2.4a2 2 0 0 1-2.2 1.6A16.8 16.8 0 0 1 5 6a2 2 0 0 1 1.6-2.5Z"/></svg>',
        'mail'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>',
        'pin'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>',
    ];
    return $icons[$name];
}
