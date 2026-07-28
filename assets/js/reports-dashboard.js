(function(){
 'use strict';
 const data=window.SB_REPORT_DATA||{daily:[],statuses:[]};
 const colors=['#b8892e','#5b3421','#7c5ac7','#2f8f68','#c85a54','#d98728'];
 document.querySelectorAll('[data-report-tab]').forEach(btn=>btn.addEventListener('click',()=>{
  document.querySelectorAll('[data-report-tab]').forEach(x=>x.classList.remove('active'));
  document.querySelectorAll('[data-report-panel]').forEach(x=>x.classList.remove('active'));
  btn.classList.add('active');document.querySelector(`[data-report-panel="${btn.dataset.reportTab}"]`)?.classList.add('active');
 }));
 document.querySelector('[data-print-report]')?.addEventListener('click',()=>window.print());
 const toggle=document.querySelector('[data-export-toggle]'),pop=document.querySelector('.sb-export-popover');
 toggle?.addEventListener('click',()=>pop.hidden=!pop.hidden);
 document.addEventListener('click',e=>{if(pop&&!e.target.closest('.sb-export-menu'))pop.hidden=true;});
 const donut=document.getElementById('sbStatusDonut'),legend=document.getElementById('sbStatusLegend');
 if(donut&&data.statuses.length){
  const total=data.statuses.reduce((n,x)=>n+Number(x.value||0),0)||1;let start=0,stops=[];
  data.statuses.forEach((x,i)=>{const end=start+Number(x.value||0)/total*100;stops.push(`${colors[i%colors.length]} ${start}% ${end}%`);start=end;
   const row=document.createElement('p');row.innerHTML=`<i style="background:${colors[i%colors.length]}"></i><span>${String(x.label).replaceAll('_',' ')}</span><strong>${x.value}</strong>`;legend?.appendChild(row);});
  donut.style.background=`conic-gradient(${stops.join(',')})`;
 }
 const canvas=document.getElementById('sbRevenueChart');if(!canvas)return;
 function draw(){
  const ratio=window.devicePixelRatio||1,w=canvas.clientWidth||700,h=280,p={l:50,r:18,t:18,b:44};
  canvas.width=w*ratio;canvas.height=h*ratio;const c=canvas.getContext('2d');c.scale(ratio,ratio);c.clearRect(0,0,w,h);
  const rows=data.daily,cw=w-p.l-p.r,ch=h-p.t-p.b;
  if(!rows.length){c.fillStyle='#81766d';c.font='14px sans-serif';c.fillText('No report data for this date range.',p.l,h/2);return;}
  const maxR=Math.max(...rows.map(x=>Number(x.revenue||0)),1),maxB=Math.max(...rows.map(x=>Number(x.bookings||0)),1);
  c.font='11px sans-serif';for(let i=0;i<=4;i++){const y=p.t+ch*i/4;c.strokeStyle='rgba(91,52,33,.12)';c.beginPath();c.moveTo(p.l,y);c.lineTo(w-p.r,y);c.stroke();c.fillStyle='#81766d';c.fillText(`₱${Math.round(maxR*(4-i)/4).toLocaleString()}`,2,y+4);}
  const step=cw/rows.length,bw=Math.max(3,Math.min(22,step*.55));
  rows.forEach((x,i)=>{const px=p.l+step*i+(step-bw)/2,bh=Number(x.revenue||0)/maxR*ch;c.fillStyle='#b8892e';c.fillRect(px,p.t+ch-bh,bw,bh);});
  c.strokeStyle='#5b3421';c.lineWidth=2.5;c.beginPath();rows.forEach((x,i)=>{const px=p.l+step*i+step/2,py=p.t+ch-Number(x.bookings||0)/maxB*ch;i?c.lineTo(px,py):c.moveTo(px,py);});c.stroke();
  const every=Math.max(1,Math.ceil(rows.length/7));rows.forEach((x,i)=>{if(i%every)return;c.save();c.translate(p.l+step*i+step/2,h-8);c.rotate(-.45);c.fillStyle='#81766d';c.fillText(new Date(`${x.label}T00:00:00`).toLocaleDateString(undefined,{month:'short',day:'numeric'}),0,0);c.restore();});
 }
 draw();window.addEventListener('resize',draw);
})();
