// Smooth scroll pour liens ancre
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) { target.scrollIntoView({ behavior: 'smooth' }); }
  });
});

// Lien actif dans la navigation
function setActiveNav() {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-link').forEach(link => {
    if (link.getAttribute('href') === currentPage) {
      link.classList.add('active');
    }
  });
}

// Menu hamburger (injecté dynamiquement pour ne pas modifier tous les fichiers HTML)
function setupHamburger() {
  const nav = document.querySelector('nav');
  const ul = nav.querySelector('ul');

  const btn = document.createElement('button');
  btn.className = 'hamburger';
  btn.setAttribute('aria-label', 'Ouvrir le menu');
  btn.innerHTML = '<span></span><span></span><span></span>';
  nav.insertBefore(btn, ul);

  btn.addEventListener('click', () => {
    const isOpen = nav.classList.toggle('nav-open');
    btn.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
    btn.classList.toggle('active');
  });

  // Ferme le menu quand on clique sur un lien (mobile)
  ul.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      nav.classList.remove('nav-open');
      btn.classList.remove('active');
      btn.setAttribute('aria-label', 'Ouvrir le menu');
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  setActiveNav();
  setupHamburger();
});
