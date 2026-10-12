/**
 * Black Sets Theme JavaScript
 * Phone menu, header scroll state and progress bar, hero slideshow, reveal-on-scroll micro animations,
 * gallery lightbox and AJAX forms. Everything is visible without JavaScript.
 */
document.addEventListener('DOMContentLoaded', function () {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var body = document.body;

  // 1. Phone menu
  var header = document.getElementById('header');
  var menuToggle = document.getElementById('menuToggle');
  var nav = document.getElementById('primaryNav');

  function setMenu(open) {
    body.classList.toggle('menu-open', open);
    menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  if (menuToggle && nav) {
    nav.querySelectorAll('.menu > li').forEach(function (li, i) {
      li.style.setProperty('--i', String(i));
    });
    menuToggle.addEventListener('click', function () {
      setMenu(!body.classList.contains('menu-open'));
    });
    nav.addEventListener('click', function (e) {
      if (e.target.closest('a')) { setMenu(false); }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && body.classList.contains('menu-open')) {
        setMenu(false);
        menuToggle.focus();
      }
    });
    // Leaving phone width with the menu open must not leave the page locked.
    window.matchMedia('(min-width: 861px)').addEventListener('change', function (mq) {
      if (mq.matches) { setMenu(false); }
    });
  }

  // 2. Header scroll state and reading progress
  var ticking = false;
  function onScroll() {
    var y = window.scrollY;
    if (header) { header.classList.toggle('scrolled', y > 40); }
    var max = document.documentElement.scrollHeight - window.innerHeight;
    document.documentElement.style.setProperty('--progress', max > 0 ? Math.min(1, y / max).toFixed(4) : '0');
    ticking = false;
  }
  window.addEventListener('scroll', function () {
    if (!ticking) { ticking = true; window.requestAnimationFrame(onScroll); }
  }, { passive: true });
  onScroll();

  // 3. Hero: title rises line by line (lines are the <br> breaks), background slideshow
  document.querySelectorAll('.bsets-page-hero .bsets-title').forEach(function (title) {
    var lines = title.innerHTML.split(/<br\s*\/?>/i).map(function (s) { return s.trim(); }).filter(Boolean);
    title.innerHTML = lines.map(function (line, i) {
      return '<span class="line" style="--i:' + i + '"><span>' + line + '</span></span>';
    }).join('');
  });

  document.querySelectorAll('.bsets-home-hero').forEach(function (hero) {
    var slides = Array.prototype.filter.call(hero.children, function (el) { return el.classList.contains('bsets-hero-bg'); });
    if (slides.length < 2) { return; }
    hero.classList.add('has-slides');
    var current = 0;
    slides[0].classList.add('is-active');
    if (reduce) { return; }
    window.setInterval(function () {
      if (document.hidden) { return; }
      slides[current].classList.remove('is-active');
      current = (current + 1) % slides.length;
      slides[current].classList.add('is-active');
    }, 6500);
  });

  // 4. Landscape key art sits on a blurred copy of itself inside the portrait poster frame
  document.querySelectorAll('.bsets-project-poster.is-landscape img').forEach(function (img) {
    img.parentElement.style.setProperty('--poster', 'url("' + (img.currentSrc || img.src).replace(/"/g, '%22') + '")');
  });

  // 5. Reveal on scroll, staggered between siblings
  var revealSelector = [
    '.bsets-section-head', '.bsets-intro .wp-block-column', '.bsets-video-wrap', '.bsets-card', '.bsets-post-card',
    '.bsets-logos .bsets-logo', '.bsets-gallery-item', '.bsets-step-card', '.bsets-render-img', '.bsets-project-card',
    '.bsets-contact-split > .wp-block-column', '.bsets-faq-cta', '.bsets-empty-notice', '.footer-col'
  ].join(',');
  var items = Array.prototype.slice.call(document.querySelectorAll(revealSelector));
  items.forEach(function (el) { el.setAttribute('data-reveal', ''); });
  function show(el) { el.classList.add('in'); }
  if (reduce || !('IntersectionObserver' in window)) {
    items.forEach(show);
  } else {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) { show(entry.target); io.unobserve(entry.target); }
      });
    }, { rootMargin: '0px 0px -6% 0px', threshold: 0.05 });
    items.forEach(function (el) {
      var siblings = Array.prototype.filter.call(el.parentElement.children, function (c) { return c.hasAttribute('data-reveal'); });
      var index = siblings.indexOf(el);
      if (index > 0) { el.style.setProperty('--d', String(Math.min(index % 4, 3))); }
      io.observe(el);
    });
    // Safety net for fast flings and late layout shifts: anything already scrolled past is shown.
    var sweep = 0;
    window.addEventListener('scroll', function () {
      window.clearTimeout(sweep);
      sweep = window.setTimeout(function () {
        items.forEach(function (el) {
          if (!el.classList.contains('in') && el.getBoundingClientRect().top < window.innerHeight) {
            show(el);
            io.unobserve(el);
          }
        });
      }, 120);
    }, { passive: true });
  }

  // 6. Gallery lightbox with previous / next
  var lightboxLinks = Array.prototype.slice.call(document.querySelectorAll('.bsets-lightbox'));
  if (lightboxLinks.length > 0) {
    var modal = document.createElement('div');
    modal.className = 'bsets-modal';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-label', 'Image viewer');
    modal.innerHTML = '<button class="bsets-modal-close" aria-label="Close">&times;</button>' +
      '<button class="bsets-modal-prev" aria-label="Previous image">&#8249;</button>' +
      '<img class="bsets-modal-img" alt="">' +
      '<button class="bsets-modal-next" aria-label="Next image">&#8250;</button>';
    body.appendChild(modal);

    var modalImg = modal.querySelector('.bsets-modal-img');
    var index = 0;
    var opener = null;

    function openAt(i) {
      index = (i + lightboxLinks.length) % lightboxLinks.length;
      var link = lightboxLinks[index];
      modalImg.src = link.getAttribute('href');
      modalImg.alt = link.getAttribute('data-caption') || '';
      modal.classList.add('active');
    }
    function closeModal() {
      modal.classList.remove('active');
      modalImg.removeAttribute('src');
      if (opener) { opener.focus(); }
    }

    modal.querySelector('.bsets-modal-close').addEventListener('click', closeModal);
    modal.querySelector('.bsets-modal-prev').addEventListener('click', function () { openAt(index - 1); });
    modal.querySelector('.bsets-modal-next').addEventListener('click', function () { openAt(index + 1); });
    modal.addEventListener('click', function (e) {
      if (e.target === modal) { closeModal(); }
    });
    document.addEventListener('keydown', function (e) {
      if (!modal.classList.contains('active')) { return; }
      if (e.key === 'Escape') { closeModal(); }
      if (e.key === 'ArrowLeft') { openAt(index - 1); }
      if (e.key === 'ArrowRight') { openAt(index + 1); }
    });
    lightboxLinks.forEach(function (link, i) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        opener = link;
        openAt(i);
        modal.querySelector('.bsets-modal-close').focus();
      });
    });
  }

  // 7. AJAX form submission (falls back to a normal post on network errors)
  document.querySelectorAll('.bsets-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var statusDiv = form.querySelector('.bsets-form-status');
      var submitBtn = form.querySelector('button[type="submit"]');
      var originalBtnText = submitBtn ? submitBtn.textContent : '';

      if (submitBtn) { submitBtn.disabled = true; }
      if (statusDiv) {
        statusDiv.className = 'bsets-form-status';
        statusDiv.textContent = '';
      }

      fetch(form.getAttribute('action') || window.location.href, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: new FormData(form)
      })
        .then(function (res) {
          return res.json().then(function (data) { return { ok: res.ok, data: data }; });
        })
        .then(function (res) {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
          }
          if (!statusDiv) { return; }
          if (res.ok) {
            statusDiv.className = 'bsets-form-status success';
            statusDiv.textContent = res.data.message || 'Thanks for submitting!';
            form.reset();
          } else {
            statusDiv.className = 'bsets-form-status error';
            statusDiv.textContent = res.data.errors ? Object.values(res.data.errors).join(' ') : (res.data.message || 'Please check the form for errors.');
          }
        })
        .catch(function () {
          if (submitBtn) { submitBtn.disabled = false; }
          form.submit();
        });
    });
  });
});
