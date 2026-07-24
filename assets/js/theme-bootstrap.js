(() => {
  const allowed = new Set(['default', 'light', 'dark']);
  const cookieMatch = document.cookie.match(/(?:^|;\s*)nbw_theme=([^;]+)/);
  const cookieTheme = cookieMatch ? decodeURIComponent(cookieMatch[1]) : '';
  const storedTheme = localStorage.getItem('nbw_theme') || '';
  const theme = allowed.has(cookieTheme)
    ? cookieTheme
    : allowed.has(storedTheme)
      ? storedTheme
      : document.documentElement.dataset.theme || 'default';

  document.documentElement.dataset.theme = theme;
})();
