(() => {
  const chartBars = document.querySelectorAll('.premium-chart-bars span');

  if ('IntersectionObserver' in window) {
    chartBars.forEach((bar) => {
      bar.dataset.finalHeight = bar.style.height;
      bar.style.height = '4%';
    });

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;

          chartBars.forEach((bar) => {
            bar.style.height = bar.dataset.finalHeight || '4%';
          });

          observer.disconnect();
        });
      },
      { threshold: 0.3 }
    );

    const chart = document.querySelector('.premium-chart');
    if (chart) observer.observe(chart);
  }
})();
