/*
  main.js
  Meghna Studio — all JS in one place, kept simple
*/

// ── cursor dot ──
var dot = document.getElementById('dot');
if (dot) {
  document.addEventListener('mousemove', function(e) {
    dot.style.left = e.clientX + 'px';
    dot.style.top  = e.clientY + 'px';
  });
}

// ── navbar gets dark background after scrolling a bit ──
var nav = document.getElementById('nav');
if (nav) {
  window.addEventListener('scroll', function() {
    if (window.scrollY > 50) {
      nav.classList.add('dark');
    } else {
      nav.classList.remove('dark');
    }
  }, { passive: true });
}

// ── hero slideshow (home page only) ──
var slides = document.querySelectorAll('.slide');
var dotBtns = document.querySelectorAll('.hero-dots button');
var current = 0;
var timer;

function showSlide(n) {
  slides[current].classList.remove('on');
  dotBtns[current].classList.remove('on');
  current = (n + slides.length) % slides.length;
  slides[current].classList.add('on');
  dotBtns[current].classList.add('on');
}

if (slides.length > 0) {
  dotBtns.forEach(function(btn, i) {
    btn.addEventListener('click', function() {
      clearInterval(timer);
      showSlide(i);
      timer = setInterval(function() { showSlide(current + 1); }, 5000);
    });
  });

  // pause when mouse is on the hero
  var heroEl = document.querySelector('.hero');
  if (heroEl) {
    heroEl.addEventListener('mouseenter', function() { clearInterval(timer); });
    heroEl.addEventListener('mouseleave', function() {
      timer = setInterval(function() { showSlide(current + 1); }, 5000);
    });
  }

  timer = setInterval(function() { showSlide(current + 1); }, 5000);
}

// ── scroll reveal — elements fade up when they come into view ──
var revealItems = document.querySelectorAll(
  '.offer-card, .review-card, .pkg-card, .stat-box, .step, .addon-row, .gear-tag'
);

revealItems.forEach(function(el, i) {
  el.style.opacity = '0';
  el.style.transform = 'translateY(20px)';
  el.style.transition = 'opacity 0.5s ease ' + (i % 4) * 0.08 + 's, transform 0.5s ease ' + (i % 4) * 0.08 + 's';
});

var revealObserver = new IntersectionObserver(function(entries) {
  entries.forEach(function(entry) {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });

revealItems.forEach(function(el) { revealObserver.observe(el); });

// ── gallery ──
var photoCells = document.querySelectorAll('.photo-cell');

// ── lightbox ──
var lb       = document.getElementById('lightbox');
var lbImg    = document.getElementById('lb-img');
var lbClose  = document.getElementById('lb-close');
var lbPrev   = document.getElementById('lb-prev');
var lbNext   = document.getElementById('lb-next');
var lbCounter= document.getElementById('lb-counter');
var allImgs  = [];
var lbIdx    = 0;

if (lb) {
  photoCells.forEach(function(cell) {
    cell.addEventListener('click', function() {
      allImgs = Array.from(photoCells)
        .filter(function(c) { return c.style.opacity !== '0.08'; })
        .map(function(c) { return { src: c.dataset.src, cat: c.querySelector('span').textContent }; });

      lbIdx = allImgs.findIndex(function(img) { return img.src === cell.dataset.src; });
      openLb(lbIdx);
    });
  });

  function openLb(i) {
    lbIdx = (i + allImgs.length) % allImgs.length;
    lbImg.src = allImgs[lbIdx].src;
    lb.scrollTop = 0;
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
    if (lbCounter) lbCounter.textContent = (lbIdx + 1) + ' / ' + allImgs.length;
  }

  function closeLb() {
    lb.classList.remove('open');
    document.body.style.overflow = '';
    lbImg.src = '';
  }

  lbClose.addEventListener('click', closeLb);
  if (lbPrev) lbPrev.addEventListener('click', function() { openLb(lbIdx - 1); });
  if (lbNext) lbNext.addEventListener('click', function() { openLb(lbIdx + 1); });

  lb.addEventListener('click', function(e) {
    if (e.target === lb) closeLb();
  });

  document.addEventListener('keydown', function(e) {
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape')     closeLb();
    if (e.key === 'ArrowRight') openLb(lbIdx + 1);
    if (e.key === 'ArrowLeft')  openLb(lbIdx - 1);
  });
}

// ── contact form — basic check before sending ──
var submitBtn = document.getElementById('submit-btn');
if (submitBtn) {
  submitBtn.addEventListener('click', function() {
    var requiredFields = [
      document.getElementById('contact-name'),
      document.getElementById('contact-phone'),
      document.getElementById('contact-email')
    ];
    var ok = true;

    // Reset error classes for all fields
    document.querySelectorAll('.field input, .field textarea').forEach(f => f.classList.remove('err'));

    requiredFields.forEach(function(f) {
      if (!f.value.trim()) {
        f.classList.add('err');
        ok = false;
        f.addEventListener('input', function() { f.classList.remove('err'); }, { once: true });
      }
    });

    if (!ok) return;

    var label = submitBtn.querySelector('span');
    var originalText = label.textContent;
    label.textContent = 'Sending...';
    submitBtn.disabled = true;

    // Collect data
    var formData = {
      name: document.getElementById('contact-name').value,
      phone: document.getElementById('contact-phone').value,
      email: document.getElementById('contact-email').value,
      event_type: document.getElementById('contact-event-type').value,
      event_date: document.getElementById('contact-event-date').value,
      location: document.getElementById('contact-location').value,
      message: document.getElementById('contact-message').value
    };

    fetch('submit_enquiry.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        label.textContent = '✓ Sent! I\'ll reply soon.';
        submitBtn.classList.add('sent');
        // Clear fields
        fields.forEach(f => f.value = '');
        document.getElementById('contact-event-type').value = '';
      } else {
        label.textContent = 'Error: ' + data.message;
        submitBtn.disabled = false;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      label.textContent = 'Error sending message.';
      submitBtn.disabled = false;
    })
    .finally(() => {
      setTimeout(function() {
        if (submitBtn.classList.contains('sent')) {
          label.textContent = originalText;
          submitBtn.classList.remove('sent');
        }
        submitBtn.disabled = false;
      }, 5000);
    });
  });
}

// ── form field label highlight on focus ──
document.querySelectorAll('.field input, .field select, .field textarea').forEach(function(el) {
  var wrap = el.closest('.field');
  if (!wrap) return;
  el.addEventListener('focus',  function() { wrap.classList.add('focused'); });
  el.addEventListener('blur',   function() { wrap.classList.remove('focused'); });
});
