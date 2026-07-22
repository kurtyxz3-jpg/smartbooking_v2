(() => {
  const cards = document.querySelectorAll('.customer-panel, .customer-stat-grid article');

  if (!('IntersectionObserver' in window)) return;

  cards.forEach((card) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 520ms ease, transform 520ms ease';
  });

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        observer.unobserve(entry.target);
      });
    },
    { threshold: 0.1 }
  );

  cards.forEach((card) => observer.observe(card));
})();
