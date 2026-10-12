/**
 * Black Sets Theme JavaScript
 * Mobile drawer navigation, sticky header, gallery lightbox, AJAX contact form.
 */
document.addEventListener('DOMContentLoaded', function () {
  // 1. Mobile Menu Drawer
  var header = document.getElementById('header');
  var menuToggle = document.getElementById('menuToggle');
  var nav = document.getElementById('primaryNav');

  if (menuToggle && nav) {
    menuToggle.addEventListener('click', function () {
      var isOpen = document.body.classList.toggle('menu-open');
      menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    // Close menu when clicking link
    nav.addEventListener('click', function (e) {
      if (e.target.tagName === 'A') {
        document.body.classList.remove('menu-open');
        menuToggle.setAttribute('aria-expanded', 'false');
      }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.body.classList.contains('menu-open')) {
        document.body.classList.remove('menu-open');
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.focus();
      }
    });
  }

  // 2. Sticky Header scroll state
  function checkScroll() {
    if (header) {
      if (window.scrollY > 40) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    }
  }
  window.addEventListener('scroll', checkScroll, { passive: true });
  checkScroll();

  // 3. Gallery Lightbox Modal
  var lightboxLinks = document.querySelectorAll('.bsets-lightbox');
  if (lightboxLinks.length > 0) {
    var modal = document.createElement('div');
    modal.className = 'bsets-modal';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.innerHTML = '<button class="bsets-modal-close" aria-label="Close lightbox">&times;</button><img class="bsets-modal-img" alt="">';
    document.body.appendChild(modal);

    var modalImg = modal.querySelector('.bsets-modal-img');
    var modalClose = modal.querySelector('.bsets-modal-close');

    function closeModal() {
      modal.classList.remove('active');
      modalImg.removeAttribute('src');
    }

    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
      if (e.target === modal || e.target === modalClose) {
        closeModal();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('active')) {
        closeModal();
      }
    });

    lightboxLinks.forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        var fullSrc = link.getAttribute('href');
        var altText = link.getAttribute('data-caption') || '';
        modalImg.setAttribute('src', fullSrc);
        modalImg.alt = altText;
        modal.classList.add('active');
      });
    });
  }

  // 4. AJAX Form Submission
  var forms = document.querySelectorAll('.bsets-form');
  forms.forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var statusDiv = form.querySelector('.bsets-form-status');
      var submitBtn = form.querySelector('button[type="submit"]');
      var originalBtnText = submitBtn ? submitBtn.innerText : 'Send';

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerText = 'Sending...';
      }
      if (statusDiv) {
        statusDiv.className = 'bsets-form-status';
        statusDiv.innerText = '';
      }

      var formData = new FormData(form);
      var actionUrl = form.getAttribute('action') || window.location.href;

      fetch(actionUrl, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
      })
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok, data: data };
        });
      })
      .then(function (res) {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerText = originalBtnText;
        }
        if (res.ok) {
          if (statusDiv) {
            statusDiv.className = 'bsets-form-status success';
            statusDiv.innerText = res.data.message || 'Thank you! Message received.';
          }
          form.reset();
        } else {
          if (statusDiv) {
            statusDiv.className = 'bsets-form-status error';
            var msg = res.data.message || 'Please check the form for errors.';
            if (res.data.errors) {
              msg = Object.values(res.data.errors).join(' ');
            }
            statusDiv.innerText = msg;
          }
        }
      })
      .catch(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerText = originalBtnText;
        }
        // Fallback to normal form submit on network issues
        form.submit();
      });
    });
  });
});
