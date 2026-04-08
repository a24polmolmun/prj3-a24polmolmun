const fs = require('fs');
const path = require('path');

const files = [
  'app/pages/admin/dashboard.vue',
  'app/pages/admin/informes.vue',
  'app/pages/admin/esdeveniments/index.vue',
  'app/pages/admin/esdeveniments/[id].vue',
  'app/components/AdminSidebar.vue'
];

files.forEach(file => {
  const p = path.join(__dirname, file);
  if (!fs.existsSync(p)) return;
  let content = fs.readFileSync(p, 'utf8');

  // Wrapper removal of min-h-screen and dark backgrounds
  content = content.replace(/class=\"flex min-h-screen bg-slate-950 text-white/g, 'class="flex flex-1 text-slate-900');
  
  // Sidebar specifics
  content = content.replace(/w-72 bg-slate-900 border-r border-white\/10 flex flex-col h-screen sticky top-0/g, 'w-72 bg-white border-r border-slate-200 flex flex-col h-screen');
  content = content.replace(/bg-accent text-black shadow-\[0_10px_20px_-5px_rgba\(255,222,0,0\.3\)\]/g, 'bg-slate-100 text-slate-900 shadow-sm');
  
  // Replace dark component containers with white backgrounds
  content = content.replace(/bg-slate-900(\/50)?/g, 'bg-white shadow-sm');
  content = content.replace(/bg-slate-950/g, 'bg-slate-50');
  content = content.replace(/text-white\/40/g, 'text-slate-500');
  content = content.replace(/text-white\/30/g, 'text-slate-500');
  content = content.replace(/text-white\/20/g, 'text-slate-400');
  content = content.replace(/text-white\/10/g, 'text-slate-300');
  content = content.replace(/text-white\/70/g, 'text-slate-700');
  content = content.replace(/text-white/g, 'text-slate-900');
  content = content.replace(/border-white\/(5|10|20)/g, 'border-slate-200');
  content = content.replace(/bg-white\/5/g, 'bg-slate-100');
  content = content.replace(/bg-white\/10/g, 'bg-slate-200');
  content = content.replace(/hover:bg-white\/5/g, 'hover:bg-slate-100');
  content = content.replace(/hover:text-white/g, 'hover:text-slate-900');
  
  fs.writeFileSync(p, content);
});
