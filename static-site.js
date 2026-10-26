(function () {
  const root = document.getElementById('static-site');
  const page = document.body.dataset.page || 'home';
  const phone = '(832) 316-5851';
  const email = 'thepopsybar@gmail.com';
  const address = '2717 FM 2218 Rd, Rosenberg, TX 77469, United States';
  const services = [
    ['coffee', 'Coffee & Matcha Bar', 'Espresso, matcha & handcrafted specialties', 'assets/images/coffee-latte.jpg'],
    ['mocktail-bar', 'Mocktail Bar', 'Shaken, garnished & made to order', 'assets/images/mocktail-popsy-cup.jpg'],
    ['sweet-spot', 'Dessert Experiences', 'Interactive sweets, gelato & live dessert stations', 'assets/images/dessert-crepe-roses.jpg'],
    ['live-food-stations', 'Live Food Stations', 'Made-to-order experiences prepared for your guests.', 'assets/images/food-tacos.jpg'],
    ['corporate-catering', 'Corporate & Brand Activations', 'Elevated service designed for high-volume events.', 'assets/images/corporate-brand-bar.jpg'],
    ['experience-catering', 'Curated Experiences', 'Perfume, Flower & Lip Bars made personal', 'assets/images/experience-perfume-bar.jpg']
  ];
  const nav = [['index.html', 'Home', 'home'], ['services.html', 'Services', 'services'], ...services.map((item) => [item[0] + '.html', item[1], item[0]])];
  const galleries = {
    home: [['gallery-crepe-chocolate', 'Chocolate drizzled crepe on a scalloped plate'], ['gallery-flower-wall', 'Floral installation at a baby shower'], ['gallery-mocktail-garnish', 'Mocktail being garnished in a Popsy Bar cup'], ['gallery-wedding-bar', 'Bride and groom at The Popsy Bar'], ['gallery-latte-hand', 'Latte art in a Popsy Bar cup'], ['gallery-tablescape', 'Bridal shower tablescape with florals'], ['gallery-food-plate', 'Tacos and fries plated for guests'], ['gallery-couple-stage', 'Couple on a floral stage with cake']],
    coffee: [['gallery-latte-hand', 'Latte art served at the coffee bar'], ['gallery-iced-coffee-duo', 'Custom labelled iced coffees'], ['gallery-iced-latte-sign', 'Iced latte at a baby shower']],
    'mocktail-bar': [['gallery-mocktail-garnish', 'Mocktail being garnished'], ['gallery-mocktail-mint', 'Mint and flower garnished mocktail'], ['gallery-guests-drinks', 'Guests enjoying mocktails']],
    'sweet-spot': [['gallery-crepe-chocolate', 'Chocolate crepe with whipped cream'], ['gallery-crepe-strawberry', 'Caramel crepe with strawberry'], ['gallery-crepe-menu', 'Crepe bar menu display']],
    'live-food-stations': [['gallery-food-station', 'Guest at a live food station'], ['gallery-food-plate', 'Tacos and fries plated'], ['gallery-place-setting', 'Styled place setting']],
    'corporate-catering': [['gallery-hall-umbrella', 'Umbrella cart at a large event'], ['gallery-place-setting', 'Event table styling'], ['gallery-food-station', 'Guests served at a live station']],
    'experience-catering': [['gallery-flower-wall', 'Floral wall activation'], ['gallery-candle-table', 'Candlelit welcome table'], ['gallery-couple-stage', 'Floral stage moment']]
  };

  function gallery(key, eyebrow, title) {
    const items = galleries[key];
    if (!items) return '';
    return `<section class="section gallery-section${items.length > 3 ? ' gallery-section-wide' : ''}"><div class="container"><div class="section-header"><span class="eyebrow">${eyebrow}</span><h2>${title}</h2></div><div class="gallery-grid">${items.map((item) => `<figure class="gallery-item"><img src="assets/images/${item[0]}.jpg" alt="${item[1]}" loading="lazy"><figcaption>${item[1]}</figcaption></figure>`).join('')}</div></div></section>`;
  }

  function marquee() {
    const words = ['Coffee & Matcha', 'Mocktail Bar', 'Crepe Bar', 'Live Stations', 'Perfume Bar', 'Flower Station', 'Brand Activations', 'Weddings'];
    const row = words.map((word) => `<span>${word}</span><i>&#10022;</i>`).join('');
    return `<div class="marquee" aria-hidden="true"><div class="marquee-track">${row}${row}</div></div>`;
  }

  function logo() {
    return '<img class="brand-logo-image" src="WhatsApp Image 2026-09-23 at 4.01.36 AM (1).jpeg" alt="The Popsy Bar - Elevated Guest Experiences"><span class="brand-name">The Popsy Bar</span>';
  }

  function header() {
    return `<div id="preloader" class="preloader" aria-live="polite"><div class="preloader-inner"><div class="preloader-logo"><img src="WhatsApp Image 2026-09-23 at 4.01.36 AM (1).jpeg" alt="The Popsy Bar"></div><div class="preloader-text-wrap"><h2>Welcome to The Popsy Bar</h2><p>Premium Catering, Coffee & Event Experiences</p></div><div class="loader-bar"><span></span></div><div class="loader-label">Loading...</div></div></div>
      <div class="page-shell"><div class="topbar"><div class="container topbar-inner"><div class="topbar-left">Elevated Mobile Bars and Guest Experience</div><span>Serving Greater Houston Area, Texas</span><div class="topbar-links"><a href="tel:${phone}">Call or Text ${phone}</a><a href="mailto:${email}">${email}</a></div></div></div>
      <header class="site-header"><div class="container nav-wrap"><a class="brand" href="index.html" aria-label="The Popsy Bar home">${logo()}</a><nav class="main-nav" aria-label="Main navigation">${nav.map((item) => `<a href="${item[0]}" class="nav-link ${page === item[2] ? 'active' : ''}">${item[1]}</a>`).join('')}</nav><a class="btn btn-primary nav-cta" href="mailto:${email}">Book an Event</a><button class="nav-toggle" type="button" aria-label="Open menu" aria-expanded="false"><span></span><span></span></button></div></header>`;
  }

  function footer() {
    return `<footer class="site-footer"><div class="container footer-grid"><div><a class="brand footer-brand" href="index.html">${logo()}</a><p>Luxury catering and guest experience design crafted for inspired events, meaningful gatherings, and unforgettable hospitality.</p></div><div><h4>Explore</h4><ul class="footer-links"><li><a href="services.html">Services</a></li><li><a href="corporate-catering.html">Corporate Catering & Large Events</a></li><li><a href="experience-catering.html">Experience Catering</a></li><li><a href="sweet-spot.html">The Sweet Spot</a></li></ul></div><div><h4>Contact</h4><ul class="footer-links"><li><a href="tel:${phone}">${phone}</a></li><li><a href="mailto:${email}">${email}</a></li></ul></div></div><div class="footer-bottom"><div class="container"><span>&copy; <span id="year"></span> The Popsy Bar</span><span class="footer-credit"><span class="credit-dot"></span> Design by Muhammad Saud Javed</span></div></div></footer></div>`;
  }

  function cards() {
    return `<div class="grid-3">${services.map((item) => `<article class="card"><div class="card-media"><img src="${item[3]}" alt="${item[1]}"></div><div class="card-body"><span class="tag">Experience</span><h3>${item[1]}</h3><p>${item[2]}</p><a href="${item[0]}.html" class="btn btn-secondary">Explore experience</a></div></article>`).join('')}</div>`;
  }

  function home() {
    return `<main><section class="hero"><video class="hero-video" autoplay muted loop playsinline poster="assets/images/hero-baby-shower-bar.jpg"><source src="WhatsApp Video 2026-09-25 at 3.06.10 AM.mp4" type="video/mp4"></video><div class="hero-overlay"></div><div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">Crafted for unforgettable moments</span><h1>Luxury eats, live experiences, and elevated hospitality.</h1><p>From signature coffee bars to large-scale catering and immersive event activations, The Popsy Bar designs elevated guest experiences that make every gathering feel extraordinary.</p><div class="hero-actions"><a href="services.html" class="btn btn-primary">Explore Services</a><a href="experience-catering.html" class="btn btn-secondary">View Experience Catering</a></div><div class="stat-strip"><div class="stat-item"><strong>600+</strong><span>Guests served</span></div><div class="stat-item"><strong>150+</strong><span>Events delivered</span></div><div class="stat-item"><strong>12+</strong><span>Signature concepts</span></div><div class="stat-item"><strong>24/7</strong><span>Service mindset</span></div></div></div><div class="hero-visual" aria-label="Event experience carousel"><div class="hero-slide hero-slide-1"><img src="assets/images/hero-baby-shower-bar.jpg" alt="Mobile bar with fringed umbrella at a baby shower"></div><div class="hero-slide hero-slide-2"><img src="assets/images/hero-crepe-cart.jpg" alt="Crepe bar cart at a bridal shower"></div><div class="hero-slide hero-slide-3"><img src="assets/images/hero-coffee-cart.jpg" alt="Outdoor coffee cart at a birthday party"></div></div></div></section>${marquee()}<section class="section section-ivory"><div class="container"><div class="section-header"><span class="eyebrow">Our services</span><h2>Premium event experiences tailored to your vision.</h2></div>${cards()}</div></section><section class="section promo-band section-sand"><div class="container"><h3>Need a tailored event concept? We build premium moments that guests remember.</h3><a href="mailto:${email}" class="btn btn-primary">Request a proposal</a></div></section><section class="section section-blush"><div class="container dual-grid"><div class="photo-panel"><img src="assets/images/about-guests.jpg" alt="Guests enjoying drinks at The Popsy Bar"></div><div><span class="eyebrow">About our event catering</span><h2>Catering designed for elevated gatherings.</h2><p>Our event catering experiences blend culinary excellence, visual storytelling, and polished service to create moments guests talk about long after the final toast.</p><ul class="feature-list"><li><span class="icon">&#10003;</span><div><strong>Luxury menu curation</strong><p>Curated experiences built around cultural flavors, premium ingredients, and striking presentation.</p></div></li><li><span class="icon">&#10003;</span><div><strong>Guest-first execution</strong><p>Thoughtful service flow, live stations, and polished hospitality for smooth, memorable events.</p></div></li><li><span class="icon">&#10003;</span><div><strong>Brand and event enhancement</strong><p>Custom branded moments, activations, and elevated touches that reinforce your identity and goals.</p></div></li></ul></div></div></section>${gallery('home', 'Moments we\'ve served', 'Real events. Real guests. <em>Beautifully remembered.</em>')}<section class="section section-ivory-soft"><div class="container"><div class="section-header"><span class="eyebrow">Perfect for event types</span><h2>Gatherings made <em>memorable.</em></h2></div><div class="grid-4">${['Corporate','Weddings','Private Parties','Social Events'].map((name, i) => `<div class="mini-card"><div class="num">0${i + 1}</div><h3>${name}</h3><p>Memorable, elegant food experiences with guest-first hospitality.</p></div>`).join('')}</div></div></section><section class="section contact-cta section-dark-panel"><div class="container contact-panel"><div class="contact-copy"><span class="eyebrow">Let's build your event</span><h2>Leave us a message</h2><p>Book your event today, request a quote, or contact us for availability and custom packages.</p></div><div class="contact-boxes"><div class="contact-box"><span class="contact-icon">&#9742;</span><div><strong>Call Us Today</strong><a href="tel:${phone}">${phone}</a></div></div><div class="contact-box"><span class="contact-icon">&#9993;</span><div><strong>Email</strong><a href="mailto:${email}">${email}</a></div></div><div class="contact-box"><span class="contact-icon">&#9906;</span><div><strong>Location</strong><span>${address}</span></div></div></div></div></section></main>`;
  }

  const details = {
    'corporate-catering': ['Corporate Events & Brand Activations', 'Corporate Catering', 'Thoughtful hospitality for executive gatherings, company celebrations, and large-scale events that demand polished service, consistency, and unforgettable presentation.', 'assets/images/gallery-hall-stations.jpg', ['Large-scale guest counts and beyond', 'Executive seating, buffet, and live station setups', 'Fast, polished service teams and event flow planning', 'Flexible menus tailored to brand and guest profile']],
    'coffee': ['Coffee', 'Beverage experiences', 'Our coffee experiences are designed to feel premium, warm, and memorable - perfect for morning events, networking moments, and elevated post-meal service.', 'assets/images/coffee-iced-latte.jpg', ['Espresso bar setups', 'Cold brew and specialty pours', 'Barista-led guest service', 'Signature coffee pairings']],
    'mocktail-bar': ['Mocktail Bar & Ethnic Fusion Blends', 'Zero-proof luxury', 'Our mocktail bar blends vibrant flavor, global inspiration, and polished presentation into crafted zero-proof beverages.', 'assets/images/mocktail-pink.jpg', ['Fruit-forward and herb-infused blends', 'Ethnic-inspired flavor profiles', 'Fresh garnishes and elevated glass styling', 'Creative presentation for all guest categories']],
    'sweet-spot': ['The Sweet Spot', 'Dessert artistry', 'The Sweet Spot brings elevated dessert presentation, indulgent flavor, and visual wow-factor to every event.', 'assets/images/dessert-crepe-bar.jpg', ['Luxury dessert bars', 'High-impact visual styling and themed setups', 'Custom dessert accents for events and brands', 'Immersive visual moments']],
    'experience-catering': ['Experience Catering', 'Experiential moments', 'We transform events into interactive stories through sensory activations, personalized guest experiences, and premium branded concepts.', 'assets/images/experience-floral.jpg', ['Customized perfume bar', 'Signature OUD Bar', 'Flower station and floral activation moments', 'Lip bar and personalized guest touches']]
  };

  function detail(key) {
    const item = details[key];
    return `<main><section class="service-page-hero"><div class="container"><div><span class="eyebrow">${item[1]}</span><h1>${item[0]}</h1><p>${item[2]}</p><div class="hero-actions"><a href="services.html" class="btn btn-secondary">Back to Services</a></div></div><img src="${item[3]}" alt="${item[0]}"></div></section><section class="section"><div class="container detail-grid"><div class="info-box"><h3>Signature service</h3><ul>${item[4].map((point) => `<li>${point}</li>`).join('')}</ul></div><div class="info-box"><h3>Built for unforgettable events</h3><p>Every detail is curated around your guests, your story, and the energy you want your event to create.</p><ul><li>Luxury private parties</li><li>Brand launches and activations</li><li>High-touch social gatherings</li><li>Memorable event storytelling</li></ul></div></div></section>${gallery(key, 'From our events', 'A closer <em>look.</em>')}</main>`;
  }

  function serviceIndex() {
    return `<main><section class="service-page-hero"><div class="container"><div><span class="eyebrow">Services</span><h1>Elevated hospitality for every kind of gathering.</h1><p>Our catering is built around memorable guest experiences, premium presentation, and thoughtful service.</p><div class="hero-actions"><a href="corporate-catering.html" class="btn btn-primary">Corporate Catering</a><a href="live-food-stations.html" class="btn btn-secondary">Live Food Stations</a></div></div><img src="assets/images/services-cart.jpg" alt="The Popsy Bar cart styled for a bridal shower"></div></section><section class="section"><div class="container">${cards()}</div></section>${gallery('home', 'Moments we\'ve served', 'Real events. <em>Real guests.</em>')}</main>`;
  }

  const content = page === 'home' ? home() : page === 'services' ? serviceIndex() : page === 'live-food-stations' ? `<main><section class="service-page-hero"><div class="container"><div><span class="eyebrow">Live culinary experiences</span><h1>Live Food Stations</h1><p>Immersive food experiences with flavor-forward presentation that entertain, excite, and energize guests.</p><a href="services.html" class="btn btn-secondary">Back to Services</a></div><img src="assets/images/food-station-plates.jpg" alt="Tacos and fries plated at a live food station"></div></section><section class="section"><div class="container">${cards()}</div></section>${gallery('live-food-stations', 'From our events', 'A closer <em>look.</em>')}</main>` : detail(page);
  root.innerHTML = header() + content + footer();
  root.innerHTML = root.innerHTML
    .replaceAll('Luxury Mobile Coffee, Beverage & Dessert Experiences', 'Serving Greater Houston Area, Texas')
    .replaceAll('Corporate Catering & Large Events', 'Corporate Events & Brand Activations')
    .replaceAll('Corporate Catering', 'Corporate Events')
    .replaceAll('Experience Catering', 'Curated Experiences')
    .replaceAll('View Experience Catering', 'Explore Curated Experiences');
  const topbarLabel = document.querySelector('.topbar-inner > span');
  if (topbarLabel) topbarLabel.textContent = 'Serving Greater Houston Area, Texas';
  const preloaderDescription = document.querySelector('.preloader-text-wrap p');
  if (preloaderDescription) preloaderDescription.textContent = 'Elevated Mobile Bars & Event Experiences';
  const bookingButton = document.querySelector('.nav-cta');
  if (bookingButton) {
    bookingButton.href = '#event-contact';
    bookingButton.addEventListener('click', (event) => {
      event.preventDefault();
      const contactTarget = document.querySelector('#event-contact');
      if (contactTarget) {
        contactTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        window.location.href = 'index.html#event-contact';
      }
    });
  }
  document.querySelectorAll('.footer-links a').forEach((link) => {
    if (link.textContent.includes('Corporate Catering')) link.textContent = 'Corporate Events & Brand Activations';
    if (link.textContent.includes('Experience Catering')) link.textContent = 'Curated Experiences';
  });
  const contactIcons = [
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 3.5 9 3l2 4.7-1.7 1.4a15.3 15.3 0 0 0 5.6 5.6l1.4-1.7 4.7 2-.5 2.4a2 2 0 0 1-2.2 1.6A16.8 16.8 0 0 1 5 6a2 2 0 0 1 1.6-2.5Z"/></svg>',
    '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>',
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>'
  ];
  document.querySelectorAll('.contact-icon').forEach((icon, index) => {
    icon.innerHTML = contactIcons[index] || contactIcons[0];
  });

  if (page === 'home') {
    const heroCopy = document.querySelector('.hero-copy');
    const heroDescription = heroCopy && heroCopy.querySelector('p');
    const heroSecondary = heroCopy && heroCopy.querySelector('.btn-secondary');
    const serviceHeader = document.querySelector('.section-ivory .section-header');
    const aboutSection = document.querySelector('.section-blush');
    const aboutEyebrow = aboutSection && aboutSection.querySelector('.eyebrow');
    const aboutTitle = aboutSection && aboutSection.querySelector('h2');
    const aboutDescription = aboutSection && aboutSection.querySelector('h2 + p');
    const contactCopy = document.querySelector('.contact-copy');
    const contactTitle = contactCopy && contactCopy.querySelector('h2');
    const contactDescription = contactCopy && contactCopy.querySelector('p');
    const aboutFeatureTitle = aboutSection && aboutSection.querySelector('.feature-list strong');

    if (heroDescription) heroDescription.textContent = 'Elevated mobile bars, interactive experiences & live stations designed for unforgettable events.';
    if (heroSecondary) heroSecondary.textContent = 'Explore Curated Experiences';
    if (serviceHeader) {
      serviceHeader.querySelector('.eyebrow').textContent = 'Our signature experiences';
      serviceHeader.querySelector('h2').innerHTML = 'Designed for moments that <em>deserve more.</em>';
      const intro = serviceHeader.querySelector('p') || serviceHeader.appendChild(document.createElement('p'));
      intro.textContent = 'From weddings and private celebrations to corporate events and large-scale productions, our experiences are designed around your vision.';
    }
    if (aboutEyebrow) aboutEyebrow.textContent = 'Made for your moment';
    if (aboutTitle) aboutTitle.innerHTML = 'More than catering.<br>It’s an experience.';
    if (aboutDescription) aboutDescription.textContent = 'From handcrafted drinks and live stations to personalized guest experiences, every detail is thoughtfully designed around your event.';
    if (aboutFeatureTitle) aboutFeatureTitle.textContent = 'Thoughtfully served. Beautifully remembered.';
    if (contactTitle) contactTitle.innerHTML = 'Bring us your vision.<br>We’ll create the experience.';
    if (contactDescription) contactDescription.textContent = 'Weddings, corporate events, brand activations, launches and milestone celebrations. Tell us what you’re imagining and we’ll bring the experience to life.';

    const contactSection = document.createElement('section');
    contactSection.id = 'event-contact';
    contactSection.className = 'section section-cream event-contact-section';
    contactSection.innerHTML = `<div class="container"><div class="form-showcase"><div class="form-header"><span class="eyebrow">Start your experience</span><h3>Tell us what you’re imagining.</h3><p>Share a few details and we’ll shape the right experience for your event.</p></div><form class="newsletter-form event-contact-form"><div class="field-row"><label><span>Your name</span><input name="name" type="text" placeholder="Your full name" required></label><label><span>Email address</span><input name="email" type="email" placeholder="you@example.com" required></label></div><div class="field-row"><label><span>Event type</span><input name="event" type="text" placeholder="Wedding, corporate event, launch..." required></label><label><span>Event date</span><input name="date" type="date"></label></div><label><span>Tell us about your vision</span><textarea name="message" placeholder="Tell us about your guests, location, and the experience you have in mind." required></textarea></label><button class="btn btn-primary submit-btn" type="submit">Send Event Inquiry</button></form></div></div>`;
    document.querySelector('main').appendChild(contactSection);
    contactSection.querySelector('form').addEventListener('submit', (event) => {
      event.preventDefault();
      const formData = new FormData(event.currentTarget);
      const subject = encodeURIComponent(`Event inquiry from ${formData.get('name')}`);
      const body = encodeURIComponent(`Name: ${formData.get('name')}\nEmail: ${formData.get('email')}\nEvent type: ${formData.get('event')}\nEvent date: ${formData.get('date') || 'Not specified'}\n\nVision:\n${formData.get('message')}`);
      window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
    });
  }
})();