/* ===========================
   Suman Mehta Portfolio JS
   =========================== */

// ===== THEME TOGGLE =====
const html = document.documentElement;
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');

const savedTheme = localStorage.getItem('theme') || 'dark';
html.setAttribute('data-theme', savedTheme);
updateThemeIcon(savedTheme);

themeToggle.addEventListener('click', () => {
  const current = html.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
  updateThemeIcon(next);
});

function updateThemeIcon(theme) {
  themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

// ===== HAMBURGER MENU =====
const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');

hamburger.addEventListener('click', () => {
  navLinks.classList.toggle('open');
  hamburger.classList.toggle('active');
});

// Close on link click
navLinks.querySelectorAll('a').forEach(link => {
  link.addEventListener('click', () => {
    navLinks.classList.remove('open');
    hamburger.classList.remove('active');
  });
});

// ===== NAVBAR ACTIVE LINK + SCROLL EFFECT =====
const sections = document.querySelectorAll('section[id]');
const navItems = document.querySelectorAll('.nav-links a');

window.addEventListener('scroll', () => {
  const scrollY = window.scrollY;

  // Back to top
  const btt = document.getElementById('backToTop');
  btt.classList.toggle('visible', scrollY > 400);

  // Active nav
  sections.forEach(section => {
    const top = section.offsetTop - 100;
    const bottom = top + section.offsetHeight;
    if (scrollY >= top && scrollY < bottom) {
      navItems.forEach(a => a.classList.remove('active'));
      const active = document.querySelector(`.nav-links a[href="#${section.id}"]`);
      if (active) active.classList.add('active');
    }
  });
});

// ===== BACK TO TOP =====
document.getElementById('backToTop').addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

// ===== TYPEWRITER =====
const phrases = [
  'Full Stack PHP Developer',
  'Laravel Specialist',
  'WordPress Expert',
  'React & Vue Developer',
  'WHMCS Customizer',
  'Digital Problem Solver',
];
let phraseIdx = 0;
let charIdx = 0;
let isDeleting = false;
const typeEl = document.getElementById('typewriter');

function type() {
  const current = phrases[phraseIdx];
  const text = isDeleting
    ? current.substring(0, charIdx - 1)
    : current.substring(0, charIdx + 1);

  typeEl.innerHTML = text + '<span class="cursor">|</span>';

  if (!isDeleting) charIdx++;
  else charIdx--;

  let speed = isDeleting ? 50 : 80;

  if (!isDeleting && charIdx === current.length) {
    speed = 2000;
    isDeleting = true;
  } else if (isDeleting && charIdx === 0) {
    isDeleting = false;
    phraseIdx = (phraseIdx + 1) % phrases.length;
    speed = 400;
  }

  setTimeout(type, speed);
}
type();

// ===== COUNTER ANIMATION =====
function animateCounters() {
  document.querySelectorAll('.stat-num').forEach(el => {
    const target = parseInt(el.getAttribute('data-count'), 10);
    let current = 0;
    const step = target / 60;
    const interval = setInterval(() => {
      current += step;
      if (current >= target) {
        el.textContent = target;
        clearInterval(interval);
      } else {
        el.textContent = Math.floor(current);
      }
    }, 25);
  });
}

// Trigger on hero visible
const heroObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { animateCounters(); heroObserver.disconnect(); } });
}, { threshold: 0.3 });
const heroSection = document.getElementById('hero');
if (heroSection) heroObserver.observe(heroSection);

// ===== SKILL BARS ANIMATION =====
const skillObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.querySelectorAll('.skill-fill').forEach(bar => {
        bar.style.width = bar.getAttribute('data-width') + '%';
      });
    }
  });
}, { threshold: 0.2 });
document.querySelectorAll('.skills-panel').forEach(p => skillObserver.observe(p));

// ===== SKILLS TABS =====
const tabs = document.querySelectorAll('.skill-tab');
const panels = document.querySelectorAll('.skills-panel');

tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    tabs.forEach(t => t.classList.remove('active'));
    panels.forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    const target = document.getElementById('tab-' + tab.dataset.tab);
    if (target) {
      target.classList.add('active');
      // Trigger skill bar animation
      target.querySelectorAll('.skill-fill').forEach(bar => {
        bar.style.width = '0';
        setTimeout(() => { bar.style.width = bar.getAttribute('data-width') + '%'; }, 50);
      });
    }
  });
});

// ===== PROJECT FILTER =====
const filterBtns = document.querySelectorAll('.filter-btn');
const projectCards = document.querySelectorAll('.project-card');

filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    filterBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filter = btn.dataset.filter;
    projectCards.forEach(card => {
      if (filter === 'all' || card.dataset.category === filter) {
        card.classList.remove('hidden');
        card.style.animation = 'fadeIn 0.4s ease forwards';
      } else {
        card.classList.add('hidden');
      }
    });
  });
});

// ===== TESTIMONIALS SLIDER =====
const track = document.getElementById('testimonialTrack');
const dotsContainer = document.getElementById('sliderDots');
const cards = track ? Array.from(track.querySelectorAll('.testimonial-card')) : [];
let currentSlide = 0;
let slidesPerView = window.innerWidth < 768 ? 1 : 2;
const totalSlides = Math.ceil(cards.length / slidesPerView);

function buildDots() {
  if (!dotsContainer) return;
  dotsContainer.innerHTML = '';
  for (let i = 0; i < totalSlides; i++) {
    const dot = document.createElement('button');
    dot.className = 'slider-dot' + (i === 0 ? ' active' : '');
    dot.setAttribute('aria-label', `Slide ${i + 1}`);
    dot.addEventListener('click', () => goTo(i));
    dotsContainer.appendChild(dot);
  }
}

function goTo(idx) {
  currentSlide = Math.max(0, Math.min(idx, totalSlides - 1));
  const offset = currentSlide * (100 / slidesPerView) * slidesPerView;
  if (track) track.style.transform = `translateX(-${currentSlide * 100 / slidesPerView * slidesPerView}%)`;
  // simpler approach:
  const cardWidth = cards[0] ? cards[0].offsetWidth + 24 : 0;
  if (track) track.style.transform = `translateX(-${currentSlide * cardWidth * slidesPerView}px)`;
  dotsContainer.querySelectorAll('.slider-dot').forEach((d, i) => d.classList.toggle('active', i === currentSlide));
}

document.getElementById('sliderPrev')?.addEventListener('click', () => goTo(currentSlide - 1));
document.getElementById('sliderNext')?.addEventListener('click', () => goTo(currentSlide + 1));

buildDots();
// Auto-slide
setInterval(() => goTo((currentSlide + 1) % totalSlides), 5000);

// ===== SCROLL REVEAL =====
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

// Add reveal classes to elements
document.querySelectorAll('.section-header').forEach(el => { el.classList.add('reveal'); revealObserver.observe(el); });
document.querySelectorAll('.service-card').forEach((el, i) => {
  el.classList.add('reveal');
  el.style.transitionDelay = `${i * 0.08}s`;
  revealObserver.observe(el);
});
document.querySelectorAll('.skill-card').forEach((el, i) => {
  el.classList.add('reveal');
  el.style.transitionDelay = `${i * 0.06}s`;
  revealObserver.observe(el);
});
document.querySelectorAll('.project-card').forEach((el, i) => {
  el.classList.add('reveal');
  el.style.transitionDelay = `${i * 0.08}s`;
  revealObserver.observe(el);
});
document.querySelectorAll('.timeline-card').forEach((el, i) => {
  el.classList.add('reveal-right');
  el.style.transitionDelay = `${i * 0.15}s`;
  revealObserver.observe(el);
});
document.querySelectorAll('.about-image-col').forEach(el => { el.classList.add('reveal-left'); revealObserver.observe(el); });
document.querySelectorAll('.about-text-col').forEach(el => { el.classList.add('reveal-right'); revealObserver.observe(el); });
document.querySelectorAll('.contact-info').forEach(el => { el.classList.add('reveal-left'); revealObserver.observe(el); });
document.querySelectorAll('.contact-form').forEach(el => { el.classList.add('reveal-right'); revealObserver.observe(el); });

// ===== CONTACT FORM VALIDATION =====
const form = document.getElementById('contactForm');
if (form) {
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    let valid = true;

    const name = form.querySelector('[name="name"]');
    const email = form.querySelector('[name="email"]');
    const message = form.querySelector('[name="message"]');
    const nameErr = document.getElementById('nameErr');
    const emailErr = document.getElementById('emailErr');
    const msgErr = document.getElementById('msgErr');

    // Reset
    [nameErr, emailErr, msgErr].forEach(el => { if (el) el.textContent = ''; });
    [name, email, message].forEach(el => el.style.borderColor = '');

    if (!name.value.trim()) {
      nameErr.textContent = 'Name is required.';
      name.style.borderColor = '#ef4444';
      valid = false;
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email.value.trim() || !emailRegex.test(email.value)) {
      emailErr.textContent = 'Valid email is required.';
      email.style.borderColor = '#ef4444';
      valid = false;
    }
    if (!message.value.trim() || message.value.trim().length < 10) {
      msgErr.textContent = 'Message must be at least 10 characters.';
      message.style.borderColor = '#ef4444';
      valid = false;
    }

    if (valid) {
      const btn = document.getElementById('formSubmitBtn');
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
      btn.disabled = true;

      // Simulate send (replace with actual fetch/ajax)
      setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
        btn.disabled = false;
        const success = document.getElementById('formSuccess');
        success.classList.add('show');
        form.reset();
        setTimeout(() => success.classList.remove('show'), 5000);
      }, 1500);
    }
  });
}

// ===== SMOOTH SCROLL FOR ANCHOR LINKS =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      e.preventDefault();
      const offset = 80;
      window.scrollTo({ top: target.offsetTop - offset, behavior: 'smooth' });
    }
  });
});

// ===== PARALLAX BLOBS (subtle) =====
window.addEventListener('mousemove', (e) => {
  const blob1 = document.querySelector('.blob-1');
  const blob2 = document.querySelector('.blob-2');
  if (!blob1 || !blob2) return;
  const x = (e.clientX / window.innerWidth - 0.5) * 20;
  const y = (e.clientY / window.innerHeight - 0.5) * 20;
  blob1.style.transform = `translate(${x}px, ${y}px)`;
  blob2.style.transform = `translate(${-x}px, ${-y}px)`;
});
