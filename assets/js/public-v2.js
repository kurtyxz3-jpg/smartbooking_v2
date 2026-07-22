(() => {
  const header = document.getElementById('siteHeader');
  const menuToggle = document.getElementById('menuToggle');
  const siteNav = document.getElementById('siteNav');

  const updateHeader = () => {
    header?.classList.toggle('is-scrolled', window.scrollY > 20);
  };

  updateHeader();
  window.addEventListener('scroll', updateHeader, { passive: true });

  menuToggle?.addEventListener('click', () => {
    const open = siteNav?.classList.toggle('is-open') ?? false;
    menuToggle.classList.toggle('is-open', open);
    menuToggle.setAttribute('aria-expanded', String(open));
  });

  siteNav?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      siteNav.classList.remove('is-open');
      menuToggle?.classList.remove('is-open');
      menuToggle?.setAttribute('aria-expanded', 'false');
    });
  });

  const reveals = document.querySelectorAll('.reveal');

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;

          const delay = Number(entry.target.dataset.delay || 0);
          window.setTimeout(() => entry.target.classList.add('is-visible'), delay);
          observer.unobserve(entry.target);
        });
      },
      { threshold: 0.14 }
    );

    reveals.forEach((element) => observer.observe(element));
  } else {
    reveals.forEach((element) => element.classList.add('is-visible'));
  }

  document.querySelectorAll('.v2-faq-item button').forEach((button) => {
    button.addEventListener('click', () => {
      const item = button.closest('.v2-faq-item');
      const answer = item?.querySelector('.v2-faq-answer');
      const isOpen = item?.classList.toggle('is-open') ?? false;

      button.setAttribute('aria-expanded', String(isOpen));

      if (answer) {
        answer.style.maxHeight = isOpen ? `${answer.scrollHeight}px` : '0px';
      }
    });
  });

  const tiltCard = document.querySelector('[data-tilt]');

  if (tiltCard && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    tiltCard.addEventListener('mousemove', (event) => {
      const bounds = tiltCard.getBoundingClientRect();
      const x = (event.clientX - bounds.left) / bounds.width - 0.5;
      const y = (event.clientY - bounds.top) / bounds.height - 0.5;

      tiltCard.style.transform =
        `perspective(900px) rotateX(${y * -5}deg) rotateY(${x * 6}deg)`;
    });

    tiltCard.addEventListener('mouseleave', () => {
      tiltCard.style.transform = 'perspective(900px) rotateX(0deg) rotateY(0deg)';
    });
  }

  document.querySelectorAll('[data-demo-alert]').forEach((button) => {
    button.addEventListener('click', () => {
      alert('This interface is ready. The complete booking and payment workflow will be connected during the customer module phase.');
    });
  });
})();
