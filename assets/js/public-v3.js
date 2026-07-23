
(() => {
 const menu=document.getElementById('menuButton'),nav=document.getElementById('siteNav');
 menu?.addEventListener('click',()=>{const open=nav?.classList.toggle('open')??false;menu.setAttribute('aria-expanded',String(open));});
 if('IntersectionObserver'in window){const ob=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');ob.unobserve(e.target)}}),{threshold:.1});document.querySelectorAll('.reveal').forEach(x=>ob.observe(x));}else document.querySelectorAll('.reveal').forEach(x=>x.classList.add('visible'));
 const grid=document.getElementById('galleryGrid'),g=window.NBW_GALLERIES||{};
 document.querySelectorAll('[data-gallery-filter]').forEach(b=>b.addEventListener('click',()=>{document.querySelectorAll('[data-gallery-filter]').forEach(x=>x.classList.remove('active'));b.classList.add('active');if(grid)grid.innerHTML=(g[b.dataset.galleryFilter]||[]).slice(0,18).map(src=>`<button class="gallery-item"><img loading="lazy" src="${src}" alt="Salon gallery"></button>`).join('');}));
 const box=document.getElementById('lightbox'),pic=document.getElementById('lightboxImage');
 document.addEventListener('click',e=>{const t=e.target.closest('[data-lightbox-src],.gallery-item');if(!t||!box||!pic)return;pic.src=t.dataset.lightboxSrc||t.querySelector('img')?.src||'';box.classList.add('open');box.setAttribute('aria-hidden','false');});
 const close=()=>{box?.classList.remove('open');box?.setAttribute('aria-hidden','true')};document.getElementById('lightboxClose')?.addEventListener('click',close);box?.addEventListener('click',e=>{if(e.target===box)close()});
 const search=document.getElementById('serviceSearch'),cards=[...document.querySelectorAll('.catalog-card')],none=document.getElementById('noResults');
 search?.addEventListener('input',()=>{const q=search.value.toLowerCase();let shown=0;cards.forEach(c=>{const ok=c.dataset.search.includes(q);c.hidden=!ok;if(ok)shown++});if(none)none.hidden=shown>0});
 document.getElementById('filterToggle')?.addEventListener('click',()=>document.getElementById('catalogFilters')?.classList.toggle('open'));
})();
