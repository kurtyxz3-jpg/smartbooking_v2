(() => {
  /* ------------------------------------------------------------
     Category auto-scroll
     CSS performs the animation. This script improves accessibility
     by pausing when the user focuses a category link.
     ------------------------------------------------------------ */
  const marquee = document.querySelector('.category-marquee');
  const marqueeTrack = document.getElementById('categoryMarqueeTrack');

  marquee?.addEventListener('mouseenter', () => {
    if (marqueeTrack) marqueeTrack.style.animationPlayState = 'paused';
  });

  marquee?.addEventListener('mouseleave', () => {
    if (marqueeTrack) marqueeTrack.style.animationPlayState = 'running';
  });

  marquee?.addEventListener('focusin', () => {
    if (marqueeTrack) marqueeTrack.style.animationPlayState = 'paused';
  });

  marquee?.addEventListener('focusout', () => {
    if (marqueeTrack) marqueeTrack.style.animationPlayState = 'running';
  });

  /* ------------------------------------------------------------
     Three-offer promotion popup
     - Opens after 1.2 seconds
     - Automatically rotates every 6 seconds
     - "Do not show again today" stores a 24-hour dismissal
     ------------------------------------------------------------ */
  const modal = document.getElementById('promotionModal');

  if (!modal) return;

  const slides = [...modal.querySelectorAll('[data-promotion-slide]')];
  const copies = [...modal.querySelectorAll('[data-promotion-copy]')];
  const dots = [...modal.querySelectorAll('[data-promotion-dot]')];
  const previous = modal.querySelector('[data-promotion-prev]');
  const next = modal.querySelector('[data-promotion-next]');
  const hideToday = document.getElementById('promotionHideToday');

  let current = 0;
  let timer = null;

  function showPromotion(index) {
    current = (index + slides.length) % slides.length;

    slides.forEach((slide, slideIndex) => {
      slide.classList.toggle('active', slideIndex === current);
    });

    copies.forEach((copy, copyIndex) => {
      copy.classList.toggle('active', copyIndex === current);
    });

    dots.forEach((dot, dotIndex) => {
      dot.classList.toggle('active', dotIndex === current);
      dot.setAttribute(
        'aria-current',
        dotIndex === current ? 'true' : 'false'
      );
    });
  }

  function startRotation() {
    stopRotation();

    timer = window.setInterval(() => {
      showPromotion(current + 1);
    }, 6000);
  }

  function stopRotation() {
    if (timer) {
      window.clearInterval(timer);
      timer = null;
    }
  }

  function openPromotion() {
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    showPromotion(0);
    startRotation();

    window.setTimeout(() => {
      modal.querySelector('.promotion-close')?.focus();
    }, 50);
  }

  function closePromotion() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    stopRotation();

    if (hideToday?.checked) {
      localStorage.setItem(
        'nbw_promotion_hidden_until',
        String(Date.now() + 24 * 60 * 60 * 1000)
      );
    }
  }

  modal.querySelectorAll('[data-promotion-close]').forEach((button) => {
    button.addEventListener('click', closePromotion);
  });

  previous?.addEventListener('click', () => {
    showPromotion(current - 1);
    startRotation();
  });

  next?.addEventListener('click', () => {
    showPromotion(current + 1);
    startRotation();
  });

  dots.forEach((dot) => {
    dot.addEventListener('click', () => {
      showPromotion(Number(dot.dataset.promotionDot));
      startRotation();
    });
  });

  modal.addEventListener('mouseenter', stopRotation);
  modal.addEventListener('mouseleave', startRotation);

  document.addEventListener('keydown', (event) => {
    if (!modal.classList.contains('open')) return;

    if (event.key === 'Escape') {
      closePromotion();
    }

    if (event.key === 'ArrowRight') {
      showPromotion(current + 1);
      startRotation();
    }

    if (event.key === 'ArrowLeft') {
      showPromotion(current - 1);
      startRotation();
    }
  });

  const hiddenUntil = Number(
    localStorage.getItem('nbw_promotion_hidden_until') || 0
  );

  if (Date.now() >= hiddenUntil) {
    window.setTimeout(openPromotion, 1200);
  }
})();
