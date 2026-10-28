<?php ?>

<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Zonensystem SW</title>
<style>
/* Layout: Arbeitsfläche links (Bild), rechts ein schmales Instrument (Zone Mapper + Gradation + Messwerte).
   Bewusst nur ein Thema: neutrales, farbstichfreies Dunkelgrau, damit die Tonwertbeurteilung nicht verfälscht wird. */
:root{
  color-scheme: dark;
  --ground:#1d1d1d;      /* Umfeld, neutral */
  --panel:#262626;
  --line:#3a3a3a;
  --ink:#d6d6d6;
  --ink-dim:#8c8c8c;
  --pin:#e0a33a;         /* fixierte Zonengrenze */
  --hl:#f2c94c;          /* Zone-Finder-Hervorhebung */
  --warn:#e06c5a;
  --f-ui:"Arial Narrow", "Helvetica Neue", Arial, system-ui, sans-serif;
  --f-num:ui-monospace, "SFMono-Regular", Menlo, Consolas, monospace;
}
*{box-sizing:border-box}
[hidden]{display:none!important}
body{margin:0}
html,body{height:100%}
body{background:var(--ground);color:var(--ink);font-family:var(--f-ui);font-size:14px;
  padding-left:max(16px, env(safe-area-inset-left, 0px));padding-right:max(16px, env(safe-area-inset-right, 0px));
  padding-block:12px 16px;display:flex;flex-direction:column;gap:12px;min-height:100%}
header{display:flex;flex-wrap:wrap;align-items:center;gap:10px 18px}
h1{font-size:17px;font-weight:600;letter-spacing:.02em;margin:0;white-space:nowrap}
h1 span{color:var(--ink-dim);font-weight:400}
.tools{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
button,select,.file{font:500 13px/1 var(--f-ui);color:var(--ink);background:var(--panel);border:1px solid var(--line);
  border-radius:3px;padding:7px 10px;cursor:pointer;letter-spacing:.02em}
button:hover,.file:hover,select:hover{border-color:#5a5a5a}
button:focus-visible,select:focus-visible,.file:focus-within,canvas:focus-visible{outline:2px solid var(--pin);outline-offset:2px}
button:disabled{opacity:.4;cursor:default}
.file input{position:absolute;width:1px;height:1px;opacity:0}
.file{position:relative;display:inline-flex}
.seg{display:inline-flex;border:1px solid var(--line);border-radius:3px;overflow:hidden}
.seg button{border:0;border-radius:0;background:transparent;border-right:1px solid var(--line)}
.seg button:last-child{border-right:0}
.seg button[aria-pressed="true"]{background:#3a3a3a;color:#fff}
label.inl{display:inline-flex;gap:6px;align-items:center;color:var(--ink-dim);font-size:12px;text-transform:uppercase;letter-spacing:.06em}
main{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:16px;flex:1;min-height:0}
.stage{min-width:0;display:flex;flex-direction:column;gap:8px}
.frame{width:100%;height:60vh;max-width:100%;background:#151515;border:1px solid var(--line);position:relative;overflow:hidden}
#img{position:absolute;inset:0;width:100%;height:100%;object-fit:contain;display:block;cursor:crosshair}
.drop{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(20,20,20,.85);
  border:2px dashed var(--pin);font-size:16px;letter-spacing:.04em}
.meta{display:flex;flex-wrap:wrap;gap:4px 16px;font:12px/1.4 var(--f-num);color:var(--ink-dim);font-variant-numeric:tabular-nums;min-height:17px}
.meta b{color:var(--ink);font-weight:500}
aside{background:var(--panel);border:1px solid var(--line);border-radius:3px;padding:12px;display:flex;flex-direction:column;gap:12px;min-width:0}
.h{font-size:11px;text-transform:uppercase;letter-spacing:.09em;color:var(--ink-dim);display:flex;justify-content:space-between;align-items:baseline}
#mapper{width:100%;height:520px;display:block;touch-action:none;cursor:default}
#curve{width:160px;height:160px;flex:none;display:block;align-self:center}
.row{display:flex;flex-direction:column;gap:10px;align-items:stretch}
.read{font:12px/1.5 var(--f-num);font-variant-numeric:tabular-nums;min-width:0;flex:1}
.read div{display:flex;justify-content:space-between;gap:8px}
.read span:first-child{color:var(--ink-dim)}
.hint{font-size:12px;line-height:1.45;color:var(--ink-dim);margin:0}
.hint kbd{font:11px var(--f-num);border:1px solid var(--line);border-radius:2px;padding:0 3px;color:var(--ink)}
.err{color:var(--warn);font-size:13px}
@media (max-width:760px){
  main{grid-template-columns:minmax(0,1fr)}
  #mapper{height:420px}
}
@media (prefers-reduced-motion:reduce){*{transition:none!important}}
</style>
</head>
<body>

<header>
  <h1>Zonensystem <span>· 16 Zonen · SW-TIFF</span></h1>
  <div class="tools">
    <label class="file" id="fileLbl">TIFF öffnen<input id="file" type="file" accept=".tif,.tiff,image/tiff,image/png,image/jpeg"></label>
    <label class="inl" for="enc">Kodierung
      <select id="enc">
        <option value="srgb">sRGB-Gamma</option>
        <option value="g22">Gamma 2,2</option>
        <option value="g18">Gamma 1,8</option>
        <option value="lin">Linear</option>
      </select>
    </label>
    <div class="seg" role="group" aria-label="Ansicht">
      <button id="vOut" aria-pressed="true">Ergebnis</button>
      <button id="vIn" aria-pressed="false">Original</button>
      <button id="vZone" aria-pressed="false">Zone Finder</button>
    </div>
    <label class="inl" for="smooth">Glättung <input id="smooth" type="range" min="0" max="100" value="35" style="width:90px"> <span id="smoothV" style="font-family:var(--f-num);color:var(--ink);min-width:2.5em">35</span></label>
    <label class="inl" for="dither"><input id="dither" type="checkbox" checked> Dither</label>
    <button id="undo" disabled>Rückgängig</button>
    <button id="reset">Zurücksetzen</button>
    <label class="inl" for="bits">Export
      <select id="bits"><option value="16">16 bit</option><option value="8">8 bit</option></select>
    </label>
    <button id="export">TIFF speichern</button>
  </div>
</header>

<main>
  <section class="stage">
    <div class="frame" id="frame">
      <canvas id="img" width="10" height="10" aria-label="Bildvorschau"></canvas>
      <div class="drop" id="drop" hidden>TIFF hier ablegen</div>
    </div>
    <div class="meta" id="meta"></div>
    <div class="meta" id="pix"></div>
  </section>

  <aside>
    <div class="h"><span>Zone Mapper</span><span id="zmInfo">Eingang · Ausgang</span></div>
    <canvas id="mapper" tabindex="0" aria-label="Zone Mapper: Zonengrenzen ziehen"></canvas>
    <div class="row">
      <canvas id="curve" width="160" height="160" aria-label="Resultierende Gradationskurve in L*"></canvas>
      <div class="read" id="zread"></div>
    </div>
    <p class="hint">Grenze ziehen: verschiebt sie und fixiert sie (gelb). Nicht fixierte Grenzen dazwischen werden proportional gestaucht oder gedehnt. Doppelklick löst die Fixierung. Glättung rundet die Kurve an den Grenzen ab (weiche Verläufe wie Bokeh bleiben ohne Ringe), die Grenzen werden dann nicht mehr exakt getroffen. Rote Breite: Zone stark gestaucht (&lt;0,4×) oder gedehnt (&gt;2,5×). Über einer Zone schweben markiert ihre Pixel im Bild. Tastatur im Mapper: <kbd>↑</kbd><kbd>↓</kbd> bewegen, <kbd>Bild↑</kbd><kbd>Bild↓</kbd> Grenze wählen, <kbd>P</kbd> fixieren/lösen, <kbd>Strg</kbd>+<kbd>Z</kbd> rückgängig.</p>
    <p class="hint">Zonen sind in L* gleichabständig (je 6,25 L*). Alles läuft lokal im Browser, nichts wird hochgeladen. Export schreibt ein unkomprimiertes Graustufen-TIFF in der gewählten Kodierung.</p>
  </aside>
</main>

<script>
"use strict";
/* ======================================================================
   TIFF-Decoder (Graustufen-Ausgabe). Unterstützt: 8/16/32-bit Integer,
   32/64-bit Float, Kompression keine / LZW / Deflate / PackBits,
   Prädiktor 2, Streifen und Kacheln, chunky und planar, Photometric
   0/1/2 (RGB wird mit Rec.709-Gewichten im kodierten Raum gemischt).
   Gibt {w,h,data:Float32Array 0..1, info} zurück.
   ====================================================================== */
/*TIFF-BEGIN*/
function lzwDecode(src, expected){
  const out = new Uint8Array(expected);
  const prefix = new Int32Array(4096), suffix = new Uint8Array(4096), len = new Uint16Array(4096), first = new Uint8Array(4096);
  for (let i=0;i<256;i++){prefix[i]=-1;suffix[i]=i;len[i]=1;first[i]=i;}
  let next=258, width=9, old=-1, pos=0, bitPos=0;
  const nbits = src.length*8;
  const write = (code)=>{
    const L = len[code]; let p = pos + L - 1, c = code;
    if (pos + L > expected){ // abschneiden
      while (c !== -1 && p >= pos){ if (p < expected) out[p] = suffix[c]; c = prefix[c]; p--; }
    } else {
      while (c !== -1){ out[p--] = suffix[c]; c = prefix[c]; }
    }
    pos += L;
  };
  while (bitPos + width <= nbits && pos < expected){
    let code = 0;
    for (let k=0;k<width;k++){ const b = bitPos+k; code = (code<<1) | ((src[b>>3] >> (7-(b&7))) & 1); }
    bitPos += width;
    if (code === 257) break;
    if (code === 256){ next=258; width=9; old=-1; continue; }
    if (old === -1){ if (code>255) break; write(code); old=code; continue; }
    if (code < next){
      write(code);
      if (next<4096){ prefix[next]=old; suffix[next]=first[code]; len[next]=len[old]+1; first[next]=first[old]; next++; }
    } else if (code === next){
      if (next<4096){ prefix[next]=old; suffix[next]=first[old]; len[next]=len[old]+1; first[next]=first[old]; next++; }
      write(code);
    } else break; // defekter Strom
    old = code;
    if (next >= (1<<width)-1 && width<12) width++;
  }
  return out;
}
function packbitsDecode(src, expected){
  const out = new Uint8Array(expected); let i=0, o=0;
  while (i<src.length && o<expected){
    const n = (src[i++]<<24)>>24;
    if (n>=0){ for (let k=0;k<=n && o<expected;k++) out[o++]=src[i++]; }
    else if (n!==-128){ const v=src[i++]; for (let k=0;k<1-n && o<expected;k++) out[o++]=v; }
  }
  return out;
}
async function inflate(src, expected){
  const ds = new DecompressionStream('deflate');
  const ab = await new Response(new Blob([src]).stream().pipeThrough(ds)).arrayBuffer();
  const out = new Uint8Array(expected); out.set(new Uint8Array(ab).subarray(0, expected)); return out;
}
async function decodeTIFF(buf){
  const dv = new DataView(buf), u8 = new Uint8Array(buf);
  const bom = dv.getUint16(0);
  let le; if (bom===0x4949) le=true; else if (bom===0x4D4D) le=false; else throw new Error('Keine TIFF-Datei (Byte-Order-Kennung fehlt).');
  const magic = dv.getUint16(2,le);
  if (magic===43) throw new Error('BigTIFF wird nicht unterstützt.');
  if (magic!==42) throw new Error('Keine gültige TIFF-Kennung.');
  const SZ={1:1,2:1,3:2,4:4,5:8,6:1,7:1,8:2,9:4,10:8,11:4,12:8};
  const ifds=[]; let off=dv.getUint32(4,le); const seen=new Set();
  while (off && off < buf.byteLength-2 && ifds.length<64 && !seen.has(off)){
    seen.add(off);
    const n=dv.getUint16(off,le); const t={};
    for (let i=0;i<n;i++){
      const e=off+2+i*12, tag=dv.getUint16(e,le), type=dv.getUint16(e+2,le), cnt=dv.getUint32(e+4,le);
      const s=SZ[type]; if (!s || type===2) continue;
      const p = s*cnt<=4 ? e+8 : dv.getUint32(e+8,le);
      if (p + s*cnt > buf.byteLength) continue;
      const a=new Array(cnt);
      for (let j=0;j<cnt;j++){
        const q=p+j*s;
        a[j] = type===3?dv.getUint16(q,le): type===4?dv.getUint32(q,le): type===1||type===7?u8[q]:
               type===8?dv.getInt16(q,le): type===9?dv.getInt32(q,le): type===11?dv.getFloat32(q,le):
               type===12?dv.getFloat64(q,le): type===5?dv.getUint32(q,le)/(dv.getUint32(q+4,le)||1):
               type===10?dv.getInt32(q,le)/(dv.getInt32(q+4,le)||1): type===6?((u8[q]<<24)>>24):0;
      }
      t[tag]=a;
    }
    ifds.push(t); off=dv.getUint32(off+2+n*12,le);
  }
  let t=null, best=-1;
  for (const d of ifds){ if (!d[256]||!d[257]||!(d[273]||d[324])) continue; const a=d[256][0]*d[257][0]; if (a>best){best=a;t=d;} }
  if (!t) throw new Error('Kein Bild im TIFF gefunden.');
  const w=t[256][0], h=t[257][0];
  const bpsArr=t[258]||[1], bps=bpsArr[0], spp=(t[277]||[1])[0], comp=(t[259]||[1])[0];
  const photo=(t[262]||[1])[0], pred=(t[317]||[1])[0], planar=(t[284]||[1])[0], sfmt=(t[339]||[1])[0];
  if (![8,16,32,64].includes(bps)) throw new Error('Bittiefe '+bps+' wird nicht unterstützt (nur 8, 16, 32, 64).');
  if (photo===3) throw new Error('Palettenbilder werden nicht unterstützt.');
  if (![0,1,2].includes(photo)) throw new Error('Photometric '+photo+' wird nicht unterstützt (nur Graustufen oder RGB).');
  if (![1,5,8,32946,32773].includes(comp)) throw new Error('Kompression '+comp+(comp===7?' (JPEG)':'')+' wird nicht unterstützt.');
  if (pred===3) throw new Error('Gleitkomma-Prädiktor (3) wird nicht unterstützt.');
  const isFloat = sfmt===3;
  if (isFloat && bps<32) throw new Error('16-bit-Gleitkomma wird nicht unterstützt.');
  const weights = (photo===2 && spp>=3) ? [0.2126,0.7152,0.0722] : [1];
  const tiled = !!t[324];
  const offs = tiled?t[324]:t[273], cnts = tiled?(t[325]||[]):(t[279]||[]);
  const tw = tiled?t[322][0]:w, th = tiled?t[323][0]:Math.min((t[278]||[h])[0], h);
  const bpsB = bps/8, sppSeg = planar===2?1:spp;
  const segsPerPlane = planar===2 ? Math.floor(offs.length/spp) : offs.length;
  const across = Math.ceil(w/tw);
  const gray = new Float32Array(w*h);
  let fmax = 0;
  for (let si=0; si<offs.length; si++){
    const plane = planar===2 ? Math.floor(si/segsPerPlane) : 0;
    if (planar===2 && plane>=weights.length) break;
    const local = si % segsPerPlane;
    const tx = tiled ? (local%across)*tw : 0;
    const ty = tiled ? Math.floor(local/across)*th : local*th;
    if (ty>=h) continue;
    const segH = tiled ? th : Math.min(th, h-ty);
    const rowBytes = tw*sppSeg*bpsB, expected = rowBytes*segH;
    const o = offs[si], c = cnts[si]!==undefined ? cnts[si] : expected;
    const raw = u8.subarray(o, Math.min(o+c, u8.length));
    let d;
    if (comp===1){ d=new Uint8Array(expected); d.set(raw.subarray(0,expected)); }
    else if (comp===5) d=lzwDecode(raw, expected);
    else if (comp===32773) d=packbitsDecode(raw, expected);
    else d=await inflate(raw, expected);
    const sd = new DataView(d.buffer, d.byteOffset, d.byteLength);
    if (pred===2){
      for (let y=0;y<segH;y++){
        const r=y*rowBytes;
        if (bps===8){ for (let i=sppSeg;i<tw*sppSeg;i++) d[r+i]=(d[r+i]+d[r+i-sppSeg])&255; }
        else if (bps===16){ for (let i=sppSeg;i<tw*sppSeg;i++){ const p=r+i*2; sd.setUint16(p,(sd.getUint16(p,le)+sd.getUint16(p-sppSeg*2,le))&65535,le);} }
        else if (bps===32){ for (let i=sppSeg;i<tw*sppSeg;i++){ const p=r+i*4; sd.setUint32(p,(sd.getUint32(p,le)+sd.getUint32(p-sppSeg*4,le))>>>0,le);} }
      }
    }
    const read = bps===8 ? (p)=>d[p]/255
      : bps===16 ? (sfmt===2 ? (p)=>(sd.getInt16(p*2,le)+32768)/65535 : (p)=>sd.getUint16(p*2,le)/65535)
      : bps===32 ? (isFloat ? (p)=>sd.getFloat32(p*4,le) : sfmt===2 ? (p)=>(sd.getInt32(p*4,le)+2147483648)/4294967295 : (p)=>sd.getUint32(p*4,le)/4294967295)
      : (p)=>sd.getFloat64(p*8,le);
    const ns = planar===2 ? 1 : Math.min(weights.length, spp);
    for (let y=0;y<segH;y++){
      const Y=ty+y; if (Y>=h) break;
      for (let x=0;x<tw;x++){
        const X=tx+x; if (X>=w) break;
        const base=(y*tw+x)*sppSeg; let v=0;
        if (planar===2) v = weights[plane]*read(base);
        else for (let s=0;s<ns;s++) v += weights[s]*read(base+s);
        const idx=Y*w+X; gray[idx]+=v;
        if (isFloat && planar!==2 && v>fmax) fmax=v;
      }
    }
  }
  if (isFloat){
    if (planar===2) for (let i=0;i<gray.length;i++) if (gray[i]>fmax) fmax=gray[i];
    const k = fmax>1 ? 1/fmax : 1;
    for (let i=0;i<gray.length;i++){ let v=gray[i]*k; gray[i]= v<0?0:v>1?1:v; }
  }
  if (photo===0) for (let i=0;i<gray.length;i++) gray[i]=1-gray[i];
  const cname={1:'unkomprimiert',5:'LZW',8:'Deflate',32946:'Deflate',32773:'PackBits'}[comp];
  const info = `${bps}${isFloat?'-bit float':' bit'} · ${spp===1?'1 Kanal':spp+' Kanäle'}${photo===2?' (RGB→Luminanz)':''}${photo===0?' (WhiteIsZero)':''} · ${cname}${pred===2?' + Prädiktor':''} · ${le?'II':'MM'}${tiled?' · gekachelt':''}`;
  return {w,h,data:gray,bits:isFloat?16:Math.min(bps,16),info};
}
/*TIFF-END*/

/* ======================================================================
   Tonwert-Mathematik
   ====================================================================== */
const N = 16;                                   // Zonen
const Lstar = y => y<=216/24389 ? y*24389/27 : 116*Math.cbrt(y)-16;
const invL  = L => L>8 ? Math.pow((L+16)/116,3) : L*27/24389;
const ENC = {
  srgb:{dec:v=> v<=0.04045? v/12.92 : Math.pow((v+0.055)/1.055,2.4), enc:v=> v<=0.0031308? v*12.92 : 1.055*Math.pow(v,1/2.4)-0.055},
  g22: {dec:v=>Math.pow(v,2.2), enc:v=>Math.pow(v,1/2.2)},
  g18: {dec:v=>Math.pow(v,1.8), enc:v=>Math.pow(v,1/1.8)},
  lin: {dec:v=>v, enc:v=>v}
};
const srgbF = lin => 255*ENC.srgb.enc(Math.min(1,Math.max(0,lin)));          // ungerundet, für Dither
const srgb8 = lin => Math.round(srgbF(lin));
const zoneGray = z => srgb8(invL(z/(N-1)*100));          // Anzeige-Grau einer Zone (0 schwarz … 15 weiß)
const toneGray = t => srgb8(invL(Math.min(1,Math.max(0,t))*100));

/* Zustand */
const S = {
  src:null, prev:null, name:'beispiel', info:'', enc:'srgb', view:'out',
  B:Float64Array.from({length:N+1},(_,i)=>i/N), pinned:Array.from({length:N+1},(_,i)=>i===0||i===N),
  sel:8, hover:-1, undo:[], smooth:0.35, dither:true, noise:null, dq:null,
  tIn:new Float32Array(65536), inZone:new Uint8Array(65536), dispIn:new Float32Array(65536),
  out16:new Uint16Array(65536), dispOut:new Float32Array(65536), outZone:new Uint8Array(65536),
  histIn:new Float64Array(N), histOut:new Float64Array(N), total:1
};

function buildInputLUT(){
  const e=ENC[S.enc];
  for (let c=0;c<65536;c++){
    const lin=e.dec(c/65535), t=Lstar(lin)/100;
    S.tIn[c]=t; S.inZone[c]=Math.min(N-1, Math.floor(t*N)); S.dispIn[c]=srgbF(lin);
  }
}
/* Monotone kubische Interpolation (Fritsch–Carlson) durch (i/16, B_i) */
function splineFn(){
  const B=S.B, n=N, h=1/n, d=new Float64Array(n), m=new Float64Array(n+1);
  for (let i=0;i<n;i++) d[i]=(B[i+1]-B[i])/h;
  m[0]=d[0]; m[n]=d[n-1];
  for (let i=1;i<n;i++) m[i] = d[i-1]*d[i]<=0 ? 0 : (d[i-1]+d[i])/2;
  for (let i=0;i<n;i++){
    if (d[i]===0){ m[i]=0; m[i+1]=0; continue; }
    const a=m[i]/d[i], b=m[i+1]/d[i], s=a*a+b*b;
    if (s>9){ const tau=3/Math.sqrt(s); m[i]=tau*a*d[i]; m[i+1]=tau*b*d[i]; }
  }
  return t=>{
    if (t<=0) return B[0]; if (t>=1) return B[n];
    let i=Math.min(n-1, Math.floor(t*n)); const u=(t-i*h)/h, u2=u*u, u3=u2*u;
    return (2*u3-3*u2+1)*B[i] + (u3-2*u2+u)*h*m[i] + (-2*u3+3*u2)*B[i+1] + (u3-u2)*h*m[i+1];
  };
}
/* Geglättete Kurve: Spline auf feinem Raster, Gauß-Faltung mit punktgespiegelten Rändern.
   Faltung einer monotonen Funktion mit positivem Kern bleibt monoton; Endpunkte bleiben fest.
   Entfernt Knicke der Steigung an den Zonengrenzen, die in weichen Verläufen (Bokeh, Himmel) als Ringe sichtbar werden. */
function curveFn(){
  const sp=splineFn();
  if (S.smooth<=0) return sp;
  const M=2048, sig=S.smooth*0.75/N*M, R=Math.ceil(3*sig);
  const f=new Float64Array(M+1), g=new Float64Array(M+1), k=new Float64Array(R+1);
  for (let i=0;i<=M;i++) f[i]=sp(i/M);
  let ks=0; for (let j=0;j<=R;j++){ k[j]=Math.exp(-j*j/(2*sig*sig)); ks+= j?2*k[j]:k[j]; }
  const at=i=> i<0 ? 2*f[0]-f[-i] : i>M ? 2*f[M]-f[2*M-i] : f[i];
  for (let i=0;i<=M;i++){ let a=k[0]*at(i); for (let j=1;j<=R;j++) a+=k[j]*(at(i-j)+at(i+j)); g[i]=a/ks; }
  return t=>{ if (t<=0) return g[0]; if (t>=1) return g[M]; const x=t*M, i=Math.floor(x), u=x-i; return g[i]+(g[i+1]-g[i])*u; };
}
const isIdentity=()=>S.B.every((v,i)=>v===i/N);
function buildOutputLUT(){
  const f=curveFn(), e=ENC[S.enc];
  for (let c=0;c<65536;c++){
    let to=f(S.tIn[c]); to = to<0?0:to>1?1:to;
    const lin=invL(to*100);
    S.out16[c]=Math.round(65535*Math.min(1,Math.max(0,e.enc(lin))));
    S.dispOut[c]=srgbF(lin);
    S.outZone[c]=Math.min(N-1, Math.floor(to*N));
  }
}
function histograms(){
  S.histIn.fill(0); S.histOut.fill(0);
  if (!S.prev) return;
  const d=S.prev.data;
  for (let i=0;i<d.length;i++){ const v=d[i]; S.histIn[S.inZone[v]]++; S.histOut[S.outZone[v]]++; }
  S.total=d.length;
}

/* ======================================================================
   Bild laden / Vorschau
   ====================================================================== */
const MAXP = 1600;
function setSource(w,h,float01,name,info,bits){
  const data=new Uint16Array(w*h);
  for (let i=0;i<data.length;i++){ const v=float01[i]; data[i]= v<=0?0 : v>=1?65535 : Math.round(v*65535); }
  S.src={w,h,data,bits}; S.name=name; S.info=info;
  const k=Math.max(1, Math.ceil(Math.max(w,h)/MAXP));
  const pw=Math.floor(w/k)||1, ph=Math.floor(h/k)||1, pd=new Uint16Array(pw*ph);
  if (k===1) pd.set(data);
  else for (let y=0;y<ph;y++) for (let x=0;x<pw;x++){
    let s=0; for (let j=0;j<k;j++){ const r=(y*k+j)*w+x*k; for (let i=0;i<k;i++) s+=data[r+i]; }
    pd[y*pw+x]=Math.round(s/(k*k));
  }
  S.prev={w:pw,h:ph,data:pd,k};
  makeNoise();
  const cv=$('img'); cv.width=pw; cv.height=ph; imgCtx=cv.getContext('2d'); imgData=imgCtx.createImageData(pw,ph);
  $('meta').innerHTML = `<span><b>${esc(name)}</b></span><span>${w} × ${h} px</span><span>${esc(info)}</span>${k>1?`<span>Vorschau 1:${k}</span>`:''}`;
  histograms(); fitFrame(); renderAll();
}
function sampleImage(){
  // Synthetisches 16-bit-Beispiel: Himmel, Kugel unter Streiflicht, dunkler Grund, Stufenkeil der 16 Zonen.
  const w=1500,h=1000,a=new Float32Array(w*h), e=ENC.srgb;
  let seed=7; const rnd=()=>((seed=(seed*1103515245+12345)&0x7fffffff)/0x7fffffff);
  const cx=560,cy=430,R=230, Lx=-0.55,Ly=-0.6,Lz=0.58;
  for (let y=0;y<h;y++) for (let x=0;x<w;x++){
    let L;
    if (y>=870){ const z=Math.min(15,Math.floor(x/w*16)); L=(z+0.5)/16*100; }
    else if (y<560){ L=88-28*(y/560) - 6*Math.sin(x/220+y/90)*Math.exp(-y/300); }
    else { L=24 - 10*((y-560)/310) + 6*Math.sin(x/37)*Math.sin(y/23) + (rnd()-0.5)*4; }
    const dx=(x-cx)/R, dy=(y-cy)/R, rr=dx*dx+dy*dy;
    if (rr<1){ const dz=Math.sqrt(1-rr); const lam=Math.max(0,dx*Lx+dy*Ly+dz*Lz); const spec=Math.pow(Math.max(0,lam),40);
      L=4+80*lam+14*spec + (rnd()-0.5)*0.6; }
    else { // Schlagschatten
      const sx=(x-(cx+260))/(R*1.25), sy=(y-(cy+235))/(R*0.32);
      if (y>560 && y<870 && sx*sx+sy*sy<1) L*=0.45;
    }
    a[y*w+x]=e.enc(invL(Math.max(0,Math.min(100,L))));
  }
  setSource(w,h,a,'Beispielbild (synthetisch)','16 bit · 1 Kanal · erzeugt · unten: Stufenkeil Zone 0–15',16);
}
async function loadFile(file){
  showErr('');
  try{
    const buf=await file.arrayBuffer();
    const head=new Uint8Array(buf,0,Math.min(4,buf.byteLength));
    const isTiff=(head[0]===0x49&&head[1]===0x49)||(head[0]===0x4D&&head[1]===0x4D);
    if (isTiff){
      $('meta').textContent='Dekodiere …';
      await new Promise(r=>setTimeout(r,20));
      const r=await decodeTIFF(buf);
      setSource(r.w,r.h,r.data,file.name,r.info,r.bits);
    } else {
      const bmp=await createImageBitmap(new Blob([buf]));
      const c=document.createElement('canvas'); c.width=bmp.width; c.height=bmp.height;
      const x=c.getContext('2d'); x.drawImage(bmp,0,0); const d=x.getImageData(0,0,c.width,c.height).data;
      const g=new Float32Array(c.width*c.height);
      for (let i=0;i<g.length;i++) g[i]=(0.2126*d[i*4]+0.7152*d[i*4+1]+0.0722*d[i*4+2])/255;
      setSource(c.width,c.height,g,file.name,'8 bit · kein TIFF · RGB→Luminanz',8);
    }
  }catch(err){ showErr(err.message||String(err)); }
}

/* ======================================================================
   Darstellung
   ====================================================================== */
const $=id=>document.getElementById(id);
const esc=s=>String(s).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
let imgCtx=null, imgData=null, raf=0;
function showErr(m){ const p=$('pix'); p.innerHTML = m ? `<span class="err">${esc(m)}</span>` : ''; }
/* Dither: TPDF ±1 Stufe für die 8-bit-Anzeige; bei 8-bit-Quellen zusätzlich gleichverteilte
   Entquantisierung ±½ Quellstufe vor der Kurve, damit gedehnte Zonen nicht in Stufen zerfallen. */
function makeNoise(){
  const n=S.prev.w*S.prev.h; S.noise=new Float32Array(n); S.dq=new Int16Array(n);
  let seed=12345; const r=()=>((seed=(seed*1664525+1013904223)>>>0)/4294967296);
  for (let i=0;i<n;i++){ S.noise[i]=r()+r()-1; S.dq[i]=Math.round((r()-0.5)*257); }
}
function renderImage(){
  if (!S.prev) return;
  const d=S.prev.data, px=imgData.data, view=S.view, hz=S.hover;
  const lut = view==='in' ? S.dispIn : S.dispOut;
  const zg = new Uint8Array(N); for (let z=0;z<N;z++) zg[z]=zoneGray(z);
  // Original und unveränderte Kurve: ohne Rauschen, pixelgleich zur Quelle
  const ident=isIdentity(), dith=S.dither && view!=='in' && !ident, deq=dith && S.src && S.src.bits===8, nz=S.noise, dq=S.dq;
  for (let i=0,j=0;i<d.length;i++,j+=4){
    let v=d[i]; if (deq){ v+=dq[i]; v=v<0?0:v>65535?65535:v; }
    let g;
    if (view==='zone') g=zg[S.outZone[v]];
    else { g=lut[v]+(dith?nz[i]:0)+0.5; g=g<0?0:g>255?255:g|0; }
    if (hz>=0 && S.inZone[v]===hz){ px[j]=242; px[j+1]=201; px[j+2]=76; }
    else { px[j]=g; px[j+1]=g; px[j+2]=g; }
    px[j+3]=255;
  }
  imgCtx.putImageData(imgData,0,0);
}
/* Rahmen exakt auf das Seitenverhältnis des Bildes bringen: Bild füllt ihn vollständig */
function fitFrame(){
  if (!S.prev) return;
  const f=$('frame'), avail=f.parentElement.clientWidth;
  const maxH = window.innerWidth<=760 ? window.innerHeight*0.6 : Math.max(240, window.innerHeight-150);
  const sc=Math.min(avail/S.prev.w, maxH/S.prev.h);
  f.style.width=Math.floor(S.prev.w*sc)+'px'; f.style.height=Math.floor(S.prev.h*sc)+'px';
}
function renderAll(){ cancelAnimationFrame(raf); raf=requestAnimationFrame(()=>{ renderImage(); drawMapper(); drawCurve(); readout(); }); }

/* Geometrie des Mappers */
let G=null;
function mapperGeom(){
  const cv=$('mapper'), r=cv.getBoundingClientRect(), dpr=window.devicePixelRatio||1;
  const W=r.width, H=r.height;
  if (cv.width!==Math.round(W*dpr)||cv.height!==Math.round(H*dpr)){ cv.width=Math.round(W*dpr); cv.height=Math.round(H*dpr); }
  const top=10, bot=10, hw=Math.max(30,W*0.15);
  G={W,H,dpr,top,bot,
    hiL:0, hiR:hw, inL:hw+6, inR:hw+6+W*0.13, outL:W*0.6-W*0.13/2, outR:W*0.6+W*0.13/2,
    grip:W*0.6+W*0.13/2+10, hoL:W-hw, hoR:W,
    y:t=>top+(1-t)*(H-top-bot), t:y=>1-(y-top)/(H-top-bot)};
  G.outL=Math.max(G.outL, G.inR+30); G.outR=G.outL+(G.inR-G.inL); G.grip=G.outR+10;
  return G;
}
function drawMapper(){
  const g=mapperGeom(), cv=$('mapper'), x=cv.getContext('2d'), B=S.B;
  x.setTransform(g.dpr,0,0,g.dpr,0,0); x.clearRect(0,0,g.W,g.H);
  const css=getComputedStyle(document.documentElement);
  const ink=css.getPropertyValue('--ink').trim(), dim=css.getPropertyValue('--ink-dim').trim(), pin=css.getPropertyValue('--pin').trim(), hl=css.getPropertyValue('--hl').trim(), line=css.getPropertyValue('--line').trim();
  const maxIn=Math.max(...S.histIn,1), maxOut=Math.max(...S.histOut,1);
  for (let i=0;i<N;i++){
    const yi0=g.y(i/N), yi1=g.y((i+1)/N), yo0=g.y(B[i]), yo1=g.y(B[i+1]);
    const gi=zoneGray(i), go=toneGray((B[i]+B[i+1])/2);
    // Verbindung (Trapez) Eingang → Ausgang
    const grad=x.createLinearGradient(g.inR,0,g.outL,0);
    grad.addColorStop(0,`rgb(${gi},${gi},${gi})`); grad.addColorStop(1,`rgb(${go},${go},${go})`);
    x.fillStyle=grad; x.globalAlpha=0.55;
    x.beginPath(); x.moveTo(g.inR,yi1); x.lineTo(g.outL,yo1); x.lineTo(g.outL,yo0); x.lineTo(g.inR,yi0); x.closePath(); x.fill();
    x.globalAlpha=1;
    x.fillStyle=`rgb(${gi},${gi},${gi})`; x.fillRect(g.inL,yi1,g.inR-g.inL,yi0-yi1);
    x.fillStyle=`rgb(${go},${go},${go})`; x.fillRect(g.outL,yo1,g.outR-g.outL,Math.max(0.5,yo0-yo1));
    // Zonen-Nummer
    x.font=`500 10px ${css.getPropertyValue('--f-num')}`; x.textAlign='center'; x.textBaseline='middle';
    x.fillStyle = gi>120 ? '#111' : '#ddd'; x.fillText(String(i),(g.inL+g.inR)/2,(yi0+yi1)/2);
    if (yo0-yo1>11){ x.fillStyle = go>120 ? '#111' : '#ddd'; x.fillText(String(i),(g.outL+g.outR)/2,(yo0+yo1)/2); }
    // Histogramme
    const bwI=(g.hiR-g.hiL-2)*(S.histIn[i]/maxIn), bwO=(g.hoR-g.hoL-2)*(S.histOut[i]/maxOut);
    x.fillStyle = i===S.hover ? hl : dim; x.globalAlpha=0.8;
    x.fillRect(g.hiR-bwI, yi1+1, bwI, yi0-yi1-2);
    const yz1=g.y((i+1)/N), yz0=g.y(i/N);
    x.fillStyle=dim; x.fillRect(g.hoL+2, yz1+1, bwO, yz0-yz1-2);
    x.globalAlpha=1;
    if (i===S.hover){ x.strokeStyle=hl; x.lineWidth=2; x.strokeRect(g.inL+1,yi1+1,g.inR-g.inL-2,yi0-yi1-2); x.strokeRect(g.outL+1,yo1+1,g.outR-g.outL-2,Math.max(1,yo0-yo1-2)); }
  }
  // Rahmen
  x.strokeStyle=line; x.lineWidth=1;
  x.strokeRect(g.inL+.5,g.y(1)+.5,g.inR-g.inL-1,g.y(0)-g.y(1)-1); x.strokeRect(g.outL+.5,g.y(1)+.5,g.outR-g.outL-1,g.y(0)-g.y(1)-1);
  // Griffe
  for (let k=1;k<N;k++){
    const y=g.y(B[k]);
    x.strokeStyle = S.pinned[k] ? pin : 'rgba(214,214,214,.55)'; x.lineWidth = k===S.sel?2:1;
    x.beginPath(); x.moveTo(g.outL-4,y); x.lineTo(g.grip,y); x.stroke();
    x.beginPath(); x.arc(g.grip+4,y,4.2,0,Math.PI*2);
    if (S.pinned[k]){ x.fillStyle=pin; x.fill(); } else { x.fillStyle='#262626'; x.fill(); x.strokeStyle=ink; x.lineWidth=1; x.stroke(); }
    if (k===S.sel && document.activeElement===cv){ x.strokeStyle=pin; x.lineWidth=1; x.beginPath(); x.arc(g.grip+4,y,7,0,Math.PI*2); x.stroke(); }
  }
  x.fillStyle=dim; x.font=`500 9.5px ${css.getPropertyValue('--f-ui')}`; x.textAlign='center'; x.textBaseline='alphabetic';
}
function drawCurve(){
  const cv=$('curve'), x=cv.getContext('2d'), s=cv.width, p=8, sz=s-2*p;
  x.clearRect(0,0,s,s);
  x.fillStyle='#1d1d1d'; x.fillRect(p,p,sz,sz);
  x.strokeStyle='#333'; x.lineWidth=1;
  for (let i=0;i<=N;i+=4){ const v=p+sz*i/N; x.beginPath(); x.moveTo(v+.5,p); x.lineTo(v+.5,p+sz); x.moveTo(p,v+.5); x.lineTo(p+sz,v+.5); x.stroke(); }
  x.strokeStyle='#555'; x.beginPath(); x.moveTo(p,p+sz); x.lineTo(p+sz,p); x.stroke();
  const f=curveFn(); x.strokeStyle='#e8e8e8'; x.lineWidth=1.6; x.beginPath();
  for (let i=0;i<=sz;i++){ const t=i/sz, y=f(t); i?x.lineTo(p+i,p+sz*(1-y)):x.moveTo(p+i,p+sz*(1-y)); }
  x.stroke();
  x.fillStyle='#e0a33a';
  for (let k=1;k<N;k++) if (S.pinned[k]){ x.beginPath(); x.arc(p+sz*k/N, p+sz*(1-S.B[k]), 2.4, 0, 7); x.fill(); }
  x.fillStyle='#8c8c8c'; x.font="10px ui-monospace, Menlo, Consolas, monospace"; x.fillText('L* ein →',p+2,s-1);
}
function readout(){
  const k=S.sel, z=S.hover>=0?S.hover:Math.min(N-1,Math.max(0,k-1));
  const pct=v=>(100*v/(S.total||1)).toFixed(1).replace('.',',');
  const L=v=>(v*100).toFixed(1).replace('.',',');
  const span = (S.B[z+1]-S.B[z])*N;
  $('zread').innerHTML =
    `<div><span>Grenze ${k}</span><span>${L(k/N)} → ${L(S.B[k])}</span></div>`+
    `<div><span>Zone ${z}</span><span>${pct(S.histIn[z])} %</span></div>`+
    `<div><span>Breite</span><span style="color:${span<0.4||span>2.5?'var(--warn)':'inherit'}">${span.toFixed(2).replace('.',',')}×</span></div>`+
    `<div><span>Fixiert</span><span>${S.pinned.slice(1,N).filter(Boolean).length} / 15</span></div>`+
    `<div><span>Kodierung</span><span>${$('enc').selectedOptions[0].text}</span></div>`;
  $('zmInfo').textContent = S.hover>=0 ? `Zone ${S.hover}: ${pct(S.histIn[S.hover])} % → Zone ${Math.min(N-1,Math.floor(((S.B[S.hover]+S.B[S.hover+1])/2)*N))}` : 'Eingang · Ausgang';
}

/* ======================================================================
   Interaktion Zone Mapper
   ====================================================================== */
function pushUndo(){ S.undo.push({B:Float64Array.from(S.B),p:S.pinned.slice()}); if (S.undo.length>100) S.undo.shift(); $('undo').disabled=false; }
function undo(){ const u=S.undo.pop(); if (!u) return; S.B=u.B; S.pinned=u.p; $('undo').disabled=!S.undo.length; update(); }
function update(){ buildOutputLUT(); histograms(); renderAll(); }
const EPS=0.2/N;   // kleinste Zonenbreite: 0,2× der Ausgangsbreite (verhindert Plateaus)
function moveBoundary(k, v, B0){
  let a=k-1; while (!S.pinned[a]) a--;
  let b=k+1; while (!S.pinned[b]) b++;
  v=Math.min(B0[b]-(b-k)*EPS, Math.max(B0[a]+(k-a)*EPS, v));
  const B=S.B; B[k]=v;
  const sa=(B0[k]-B0[a])||1e-9, sb=(B0[b]-B0[k])||1e-9;
  for (let j=a+1;j<k;j++) B[j]=B0[a]+(B0[j]-B0[a])*(v-B0[a])/sa;
  for (let j=k+1;j<b;j++) B[j]=B0[b]-(B0[b]-B0[j])*(B0[b]-v)/sb;
}
let drag=null;
function hitHandle(px,py){
  if (!G || px<G.outL-6) return -1;
  let best=-1, bd=11;
  for (let k=1;k<N;k++){ const d=Math.abs(G.y(S.B[k])-py); if (d<bd){bd=d;best=k;} }
  return best;
}
function zoneAt(px,py){
  if (!G) return -1;
  const t=G.t(py); if (t<0||t>1) return -1;
  if (px<=G.inR+ (G.outL-G.inR)/2) return Math.min(N-1,Math.floor(t*N));
  for (let i=0;i<N;i++) if (t>=S.B[i]&&t<=S.B[i+1]) return i;
  return -1;
}
const mp=$('mapper');
mp.addEventListener('pointerdown',e=>{
  const r=mp.getBoundingClientRect(), px=e.clientX-r.left, py=e.clientY-r.top;
  const k=hitHandle(px,py); if (k<0) return;
  e.preventDefault(); mp.setPointerCapture(e.pointerId);
  pushUndo(); S.sel=k; S.pinned[k]=true;
  drag={k,B0:Float64Array.from(S.B),y0:py,v0:S.B[k]};
  renderAll();
});
mp.addEventListener('pointermove',e=>{
  const r=mp.getBoundingClientRect(), px=e.clientX-r.left, py=e.clientY-r.top;
  if (drag){
    const v=drag.v0 + (G.t(py)-G.t(drag.y0));
    moveBoundary(drag.k, v, drag.B0); update(); return;
  }
  mp.style.cursor = hitHandle(px,py)>=0 ? 'ns-resize' : 'default';
  const z = hitHandle(px,py)>=0 ? -1 : zoneAt(px,py);
  if (z!==S.hover){ S.hover=z; renderAll(); }
});
const endDrag=()=>{ if (drag){ const same=S.B[drag.k]===drag.v0; drag=null; if (same && S.undo.length){ /* reine Fixierung bleibt als Schritt */ } } };
mp.addEventListener('pointerup',endDrag); mp.addEventListener('pointercancel',endDrag);
mp.addEventListener('pointerleave',()=>{ if (!drag && S.hover!==-1){ S.hover=-1; renderAll(); } });
mp.addEventListener('dblclick',e=>{
  const r=mp.getBoundingClientRect(); const k=hitHandle(e.clientX-r.left,e.clientY-r.top);
  if (k>0){ pushUndo(); S.pinned[k]=false; S.sel=k; update(); }
});
mp.addEventListener('keydown',e=>{
  const k=S.sel;
  if (e.key==='ArrowUp'||e.key==='ArrowDown'){
    e.preventDefault(); pushUndo(); S.pinned[k]=true;
    const step=(e.shiftKey?0.02:0.004)*(e.key==='ArrowUp'?1:-1);
    moveBoundary(k, S.B[k]+step, Float64Array.from(S.B)); update();
  } else if (e.key==='PageUp'){ e.preventDefault(); S.sel=Math.min(N-1,k+1); renderAll(); }
  else if (e.key==='PageDown'){ e.preventDefault(); S.sel=Math.max(1,k-1); renderAll(); }
  else if (e.key==='p'||e.key==='P'){ pushUndo(); S.pinned[k]=!S.pinned[k]; renderAll(); }
});
mp.addEventListener('focus',renderAll); mp.addEventListener('blur',renderAll);
document.addEventListener('keydown',e=>{ if ((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='z'){ e.preventDefault(); undo(); } });

/* Pixel-Messung im Bild */
$('img').addEventListener('pointermove',e=>{
  if (!S.prev) return;
  const cv=$('img'), r=cv.getBoundingClientRect();
  // object-fit: contain -> tatsächlich gezeichnete Bildfläche bestimmen
  const sc=Math.min(r.width/S.prev.w, r.height/S.prev.h), dw=S.prev.w*sc, dh=S.prev.h*sc;
  const ox=r.left+(r.width-dw)/2, oy=r.top+(r.height-dh)/2;
  const x=Math.floor((e.clientX-ox)/sc), y=Math.floor((e.clientY-oy)/sc);
  if (x<0||y<0||x>=S.prev.w||y>=S.prev.h) return;
  const v=S.prev.data[y*S.prev.w+x], bits=S.src.bits, raw=bits===8?Math.round(v/257):v;
  const tin=S.tIn[v]*100, tout=Lstar(ENC[S.enc].dec(S.out16[v]/65535));
  const f=n=>n.toFixed(1).replace('.',',');
  $('pix').innerHTML=`<span>x ${x*S.prev.k} · y ${y*S.prev.k}</span><span>Wert <b>${raw}</b> / ${bits===8?255:65535}</span><span>L* <b>${f(tin)}</b> → <b>${f(tout)}</b></span><span>Zone <b>${S.inZone[v]}</b> → <b>${S.outZone[v]}</b></span>${S.prev.k>1?'<span>(Mittel '+S.prev.k+'×'+S.prev.k+')</span>':''}`;
});

/* Steuerung */
$('file').addEventListener('change',e=>{ const f=e.target.files[0]; if (f) loadFile(f); e.target.value=''; });
const fr=$('frame');
['dragenter','dragover'].forEach(t=>fr.addEventListener(t,e=>{ e.preventDefault(); $('drop').hidden=false; }));
['dragleave','drop'].forEach(t=>fr.addEventListener(t,e=>{ e.preventDefault(); $('drop').hidden=true; if (t==='drop'&&e.dataTransfer.files[0]) loadFile(e.dataTransfer.files[0]); }));
$('enc').addEventListener('change',e=>{ S.enc=e.target.value; buildInputLUT(); update(); });
const views={vOut:'out',vIn:'in',vZone:'zone'};
Object.keys(views).forEach(id=>$(id).addEventListener('click',()=>{ S.view=views[id]; Object.keys(views).forEach(j=>$(j).setAttribute('aria-pressed',String(j===id))); renderAll(); }));
$('reset').addEventListener('click',()=>{
  pushUndo();
  S.B=Float64Array.from({length:N+1},(_,i)=>i/N); S.pinned=Array.from({length:N+1},(_,i)=>i===0||i===N);
  S.sel=8; S.hover=-1; drag=null;
  S.smooth=0.35; $('smooth').value=35; $('smoothV').textContent='35';
  S.dither=true; $('dither').checked=true;
  S.view='out'; Object.keys(views).forEach(j=>$(j).setAttribute('aria-pressed',String(j==='vOut')));
  update();
});
$('undo').addEventListener('click',undo);
$('smooth').addEventListener('input',e=>{ S.smooth=e.target.value/100; $('smoothV').textContent=e.target.value; update(); });
$('dither').addEventListener('change',e=>{ S.dither=e.target.checked; renderAll(); });
window.addEventListener('resize',()=>{ fitFrame(); renderAll(); });

/* Export als TIFF (unkomprimiert, Graustufen, Little Endian) */
function writeTIFF(w,h,bits,src16,lut,dith,deq){
  let seed=987654321; const r=()=>((seed=(seed*1664525+1013904223)>>>0)/4294967296);
  const inp=i=>{ let v=src16[i]; if (deq){ v+=Math.round((r()-0.5)*257); v=v<0?0:v>65535?65535:v; } return v; };
  const bpp=bits/8, dataLen=w*h*bpp, nTags=12, ifdLen=2+nTags*12+4, xres=8+ifdLen, yres=xres+8, dataOff=yres+8;
  const buf=new ArrayBuffer(dataOff+dataLen), dv=new DataView(buf);
  dv.setUint16(0,0x4949); dv.setUint16(2,42,true); dv.setUint32(4,8,true);
  let p=8; dv.setUint16(p,nTags,true); p+=2;
  const tag=(id,type,cnt,val)=>{ dv.setUint16(p,id,true); dv.setUint16(p+2,type,true); dv.setUint32(p+4,cnt,true);
    if (type===3&&cnt===1) dv.setUint16(p+8,val,true); else dv.setUint32(p+8,val,true); p+=12; };
  tag(256,4,1,w); tag(257,4,1,h); tag(258,3,1,bits); tag(259,3,1,1); tag(262,3,1,1); tag(273,4,1,dataOff);
  tag(277,3,1,1); tag(278,4,1,h); tag(279,4,1,dataLen); tag(282,5,1,xres); tag(283,5,1,yres); tag(296,3,1,2);
  dv.setUint32(p,0,true);
  dv.setUint32(xres,300,true); dv.setUint32(xres+4,1,true); dv.setUint32(yres,300,true); dv.setUint32(yres+4,1,true);
  if (bits===16){ const o=new DataView(buf,dataOff); for (let i=0;i<src16.length;i++) o.setUint16(i*2,lut[inp(i)],true); }
  else { const o=new Uint8Array(buf,dataOff); for (let i=0;i<src16.length;i++){ let g=lut[inp(i)]/257+(dith?r()+r()-1:0)+0.5; o[i]=g<0?0:g>255?255:g|0; } }
  return new Blob([buf],{type:'image/tiff'});
}
$('export').addEventListener('click',()=>{
  if (!S.src) return;
  const bits=+$('bits').value, id=isIdentity(), blob=writeTIFF(S.src.w,S.src.h,bits,S.src.data,S.out16,S.dither&&!id,S.dither&&!id&&S.src.bits===8);
  const base=S.name.replace(/\.(tiff?|png|jpe?g)$/i,'').replace(/[^\w.-]+/g,'_')+'_zonen';
  const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=base+'.tif';
  document.body.appendChild(a); a.click(); a.remove(); setTimeout(()=>URL.revokeObjectURL(a.href),4000);
});

/* Start */
buildInputLUT(); buildOutputLUT(); sampleImage();
</script>
</body>
</html>
