// Smooth scroll pour liens ancre
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e){
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if(target){ target.scrollIntoView({ behavior:'smooth' }); }
  });
});

// Fade animation au chargement
window.addEventListener('DOMContentLoaded', ()=>{
  document.querySelectorAll('.page-fade').forEach(el => {
    el.style.opacity = 1;
  });
});
