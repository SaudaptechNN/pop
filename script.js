document.addEventListener('DOMContentLoaded', function () {
  const body = document.body;
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const finePointer = window.matchMedia('(pointer: fine)').matches;

  const yearEl = document.getElementById('year');
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }

  // Split page headlines into words for the staggered entrance.
  document.querySelectorAll('.hero-copy h1, .service-page-hero h1').forEach((heading) => {
    const words = heading.textContent.trim().split(/\s+/);
    heading.innerHTML = words.map((word, i) => `<span class="word"><span style="--i:${i}">${word}</span></span>`).join(' ');
    heading.classList.add('split');
    const wrap = heading.parentElement;
    const delayStart = 0.35 + words.length * 0.07;
    const siblings = Array.from(wrap.children);
    const headingIndex = siblings.indexOf(heading);
    siblings.forEach((el, i) => {
      if (el === heading) return;
      el.classList.add('hero-intro');
      const delay = i < headingIndex ? 0.1 : delayStart + (i - headingIndex - 1) * 0.12;
      el.style.setProperty('--d', delay.toFixed(2) + 's');
    });
  });

  // Hero slide indicators and scroll cue.
  const hero = document.querySelector('.hero');
  if (hero) {
    const visual = hero.querySelector('.hero-visual');
    if (visual) {
      const dots = document.createElement('div');
      dots.className = 'hero-dots';
      dots.innerHTML = '<span></span><span></span><span></span>';
      visual.appendChild(dots);
    }
    const cue = document.createElement('div');
    cue.className = 'scroll-cue';
    cue.textContent = 'Scroll';
    hero.appendChild(cue);
  }

  const preloader = document.getElementById('preloader');
  if (preloader) {
    setTimeout(function () {
      preloader.classList.add('hidden');
      body.classList.add('is-loaded');
    }, 2000);
  } else {
    requestAnimationFrame(() => body.classList.add('is-loaded'));
  }

  // Wrap feature images for the clip reveal and parallax.
  document.querySelectorAll('.service-page-hero .container > img, .photo-panel > img').forEach((img) => {
    const frame = document.createElement('div');
    frame.className = 'parallax-img reveal-img';
    img.parentNode.insertBefore(frame, img);
    frame.appendChild(img);
  });

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  const revealTargets = document.querySelectorAll(
    '.card, .mini-card, .info-box, .quote-card, .hero-card, .feature-list li, .newsletter-box, .promo-band h3, .promo-band .btn, .section-header, .gallery-item, .contact-copy, .contact-box, .form-showcase'
  );

  revealTargets.forEach((item) => {
    const siblingIndex = Array.prototype.indexOf.call(item.parentElement.children, item);
    item.classList.add('reveal');
    item.style.transitionDelay = (Math.min(siblingIndex, 5) * 90) + 'ms';
    // Clear the delay once revealed so hover effects respond instantly.
    item.addEventListener('transitionend', () => { item.style.transitionDelay = ''; }, { once: true });
    observer.observe(item);
  });
  // Clipped frames never report as intersecting, so they are revealed from the scroll handler by position.
  let pendingFrames = Array.from(document.querySelectorAll('.reveal-img'));

  // Scroll-driven effects: progress bar, header state, back-to-top, parallax.
  const progress = document.createElement('div');
  progress.className = 'scroll-progress';
  body.appendChild(progress);

  const toTop = document.createElement('button');
  toTop.className = 'to-top';
  toTop.type = 'button';
  toTop.setAttribute('aria-label', 'Back to top');
  toTop.innerHTML = '&#8593;';
  toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  body.appendChild(toTop);

  const header = document.querySelector('.site-header');
  const parallaxImages = Array.from(document.querySelectorAll('.parallax-img img'));
  let ticking = false;

  function onScroll() {
    const scrollY = window.scrollY;
    const max = document.documentElement.scrollHeight - window.innerHeight;
    progress.style.transform = `scaleX(${max > 0 ? scrollY / max : 0})`;
    if (header) header.classList.toggle('is-scrolled', scrollY > 40);
    toTop.classList.toggle('is-shown', scrollY > window.innerHeight * 0.8);

    if (pendingFrames.length) {
      pendingFrames = pendingFrames.filter((frame) => {
        const rect = frame.getBoundingClientRect();
        const inView = rect.top < window.innerHeight * 0.88 && rect.bottom > 0;
        if (inView) frame.classList.add('is-visible');
        return !inView;
      });
    }

    if (!reduceMotion) {
      parallaxImages.forEach((img) => {
        const rect = img.parentElement.getBoundingClientRect();
        if (rect.bottom < 0 || rect.top > window.innerHeight) return;
        const centerOffset = (rect.top + rect.height / 2 - window.innerHeight / 2) / window.innerHeight;
        img.style.setProperty('--py', (centerOffset * -50).toFixed(1) + 'px');
      });
    }
    ticking = false;
  }
  window.addEventListener('scroll', () => {
    if (!ticking) {
      requestAnimationFrame(onScroll);
      ticking = true;
    }
  }, { passive: true });
  onScroll();

  if (finePointer && !reduceMotion) {
    // Spotlight that follows the cursor across service cards.
    document.querySelectorAll('.card').forEach((card) => {
      card.addEventListener('mousemove', (event) => {
        const rect = card.getBoundingClientRect();
        card.style.setProperty('--mx', (event.clientX - rect.left) + 'px');
        card.style.setProperty('--my', (event.clientY - rect.top) + 'px');
      });
    });

    // Magnetic primary buttons.
    document.querySelectorAll('.btn-primary').forEach((btn) => {
      btn.addEventListener('mousemove', (event) => {
        const rect = btn.getBoundingClientRect();
        const x = event.clientX - rect.left - rect.width / 2;
        const y = event.clientY - rect.top - rect.height / 2;
        btn.style.transform = `translate(${x * 0.22}px, ${y * 0.3 - 3}px)`;
      });
      btn.addEventListener('mouseleave', () => { btn.style.transform = ''; });
    });
  }

  // Lightbox for gallery photos.
  const galleryItems = Array.from(document.querySelectorAll('.gallery-item'));
  if (galleryItems.length) {
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.setAttribute('role', 'dialog');
    lightbox.setAttribute('aria-modal', 'true');
    lightbox.innerHTML = '<figure><img alt=""><p></p></figure><button class="lb-close" type="button" aria-label="Close">&#10005;</button><button class="lb-prev" type="button" aria-label="Previous photo">&#8592;</button><button class="lb-next" type="button" aria-label="Next photo">&#8594;</button>';
    body.appendChild(lightbox);
    const lbImg = lightbox.querySelector('img');
    const lbCaption = lightbox.querySelector('p');
    let current = 0;

    function show(index) {
      current = (index + galleryItems.length) % galleryItems.length;
      const img = galleryItems[current].querySelector('img');
      lbImg.src = img.src;
      lbImg.alt = img.alt;
      lbCaption.textContent = img.alt;
    }
    function close() {
      lightbox.classList.remove('is-open');
      body.style.overflow = '';
    }

    galleryItems.forEach((item, index) => {
      item.tabIndex = 0;
      const open = () => {
        show(index);
        lightbox.classList.add('is-open');
        body.style.overflow = 'hidden';
      };
      item.addEventListener('click', open);
      item.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          open();
        }
      });
    });
    lightbox.querySelector('.lb-close').addEventListener('click', close);
    lightbox.querySelector('.lb-prev').addEventListener('click', () => show(current - 1));
    lightbox.querySelector('.lb-next').addEventListener('click', () => show(current + 1));
    lightbox.addEventListener('click', (event) => {
      if (event.target === lightbox) close();
    });
    document.addEventListener('keydown', (event) => {
      if (!lightbox.classList.contains('is-open')) return;
      if (event.key === 'Escape') close();
      if (event.key === 'ArrowLeft') show(current - 1);
      if (event.key === 'ArrowRight') show(current + 1);
    });
  }

  // Mobile menu.
  const navToggle = document.querySelector('.nav-toggle');
  if (navToggle) {
    const setOpen = (open) => {
      body.classList.toggle('nav-open', open);
      navToggle.setAttribute('aria-expanded', String(open));
      navToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    };
    navToggle.addEventListener('click', () => setOpen(!body.classList.contains('nav-open')));
    document.querySelectorAll('.main-nav a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') setOpen(false);
    });
  }
});
