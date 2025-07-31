<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php include 'includes/header.php'; ?>

<!-- FONTS -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">

<style>
  :root{
    --orange:#F44B12;           /* base orange */
    --orange-deep:#D64209;      /* darker orange (glow color) */
    --orange-heat:#B23607;      /* deepest accent */
    --dark:#1c1c1c;
    --white:#fff;
    --captionW: clamp(280px, 42vw, 560px);

    /* Coverflow tuning */
    --c-persp: 2000px;
    --c-gap-1: 180px;
    --c-gap-2: 320px;
    --c-rot-1: 26deg;
    --c-rot-2: 42deg;
    --c-scale-ctr: 1.06;
    --c-scale-1: .92;
    --c-scale-2: .82;
  }

  *{box-sizing:border-box}
  body{margin:0;background:#fff;color:#2B2B2A;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial}

  /* =============== HERO =============== */
  .hero-section{background:#fff;position:relative;min-height:320px;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;overflow:hidden}
  .hero-video{display:flex;justify-content:center;align-items:center;margin-top:3vw;background:#fff;border-radius:18px;box-shadow:0 2px 24px rgba(0,0,0,.07)}
  .hero-video video{width:380px;max-width:93vw;height:auto;border-radius:13px;display:block}
  .hero-quote{font-family:'Montserrat',Arial,sans-serif;font-weight:800;letter-spacing:-.5px;color:#222;margin:2.2vw 0 42px;font-size:clamp(1.35rem,2.4vw,2.45rem);padding:0 2vw;text-align:center}
  .hero-quote .smart,.hero-quote .loud,.hero-quote .real{color:var(--orange);font-weight:900}
  .hero-quote .real{color:#222}

  /* =============== EXPERTISE (DARK + DARK-ORANGE GLOW) =============== */
  .expertise-section{
    position:relative; isolation:isolate; overflow:hidden;
    background:
      radial-gradient(1200px 420px at 100% 0%, rgba(214,66,9,.12) 0%, transparent 60%),
      radial-gradient(900px 520px at 0% 100%, rgba(214,66,9,.12) 0%, transparent 62%),
      linear-gradient(120deg, #222 0%, #1b1b1b 100%);
    border-radius:56px 56px 0 0 / 52px 52px 0 0;
    width:100vw; margin:0 auto 2.9rem; padding:5.2rem 0 3.9rem;
    box-shadow:0 24px 64px rgba(0,0,0,.25);
  }
  .expertise-section::before{
    content:""; position:absolute; inset:-20%; z-index:0; pointer-events:none;
    background:
      radial-gradient(60% 60% at 90% 5%, rgba(214,66,9,.35) 0%, rgba(214,66,9,0) 60%),
      radial-gradient(70% 60% at 10% 95%, rgba(178,54,7,.32) 0%, rgba(178,54,7,0) 62%),
      radial-gradient(100% 100% at 50% 50%, rgba(255,255,255,.05) 0%, transparent 70%);
    filter: blur(22px);
  }
  .expertise-section::after{
    content:""; position:absolute; inset:0; z-index:0; pointer-events:none;
    background: radial-gradient(120% 120% at 50% 60%, rgba(0,0,0,.35) 0%, rgba(0,0,0,.0) 60%);
    mix-blend-mode:multiply;
  }
  .section-heading{display:inline-block;font-family:'Montserrat',Arial,sans-serif;font-weight:900;font-size:2.2rem;padding:10px 28px;border-radius:12px;position:relative;z-index:1}
  .section-heading .heading-our{color:#fff}
  .section-heading .heading-expertise{color:var(--orange)}
  .expertise-list{position:relative;z-index:1;display:grid;grid-template-columns:repeat(4,1fr);gap:3.2rem;max-width:1850px;margin:0 auto;padding:0 1.5vw}
  @media (max-width:1200px){.expertise-list{gap:2.2rem;max-width:1000px}}
  @media (max-width:1100px){.expertise-list{grid-template-columns:repeat(2,1fr);gap:1.8rem;max-width:700px}}
  @media (max-width:800px){.expertise-list{grid-template-columns:1fr;max-width:96vw;gap:1.2rem;padding:0 1vw}}

  .glass-card{
    background: rgba(255,255,255,.06);
    border: 2px solid rgba(214,66,9,.22);
    border-radius:16px; min-height:300px; width:100%;
    display:flex; flex-direction:column; align-items:center; justify-content:flex-start;
    padding:2.6rem 1.5rem; text-align:center; color:#fff;
    box-shadow: 0 10px 40px rgba(0,0,0,.18), inset 0 0 0 1px rgba(255,255,255,.045);
    backdrop-filter: blur(12px) saturate(1.2);
  }
  .glass-card .title{font-family:'Montserrat',Arial,sans-serif;font-size:2.06rem;font-weight:900;margin-bottom:1.1em;color:var(--orange)}
  .glass-card ul{list-style:none;margin:0;padding:0;color:#fff;font-size:1.08rem;line-height:2.0}
  .glass-card li{margin-bottom:.33em}
  @media (max-width:600px){
    .expertise-section{border-radius:32px 32px 0 0 / 30px 30px 0 0;padding-top:1.5rem;padding-bottom:1rem}
    .expertise-list{width:90vw;max-width:90vw;margin:0 auto;padding:0;grid-template-columns:1fr 1fr;gap:.7rem;aspect-ratio:1/1;height:90vw}
    .glass-card{padding:.6rem;border-radius:10px}
    .glass-card .title{font-size:1rem;margin-bottom:.25em}
    .glass-card ul{font-size:.68rem;line-height:1.18}
    .glass-card li{margin-bottom:.12em}
  }

  /* =============== BRANDS (Mobile: 2 logos/row) =============== */
  .brands-section{
    background:#fff;border-radius:64px 64px 22px 22px / 56px 56px 16px 16px;
    width:100vw;margin:0 auto 2.9rem;padding:3.2rem 0 2.1rem;
    position:relative;box-shadow:0 20px 54px rgba(44,44,44,.08),0 12px 38px rgba(44,44,44,.14);
  }
  .brands-heading{
    font-family:'Montserrat',Arial,sans-serif;font-size:2.1rem;font-weight:900;border-radius:10px;padding:7px 26px;background:none;display:inline-block;color:#111;border:1.6px dashed #111;letter-spacing:-1px
  }
  .brands-grid{width:100%;display:flex;flex-direction:column;gap:2.1rem;align-items:center}
  .brands-row{display:flex;justify-content:center;gap:4vw;margin-bottom:1.25rem;flex-wrap:nowrap;width:100vw;padding:0 1vw}
  .brands-logo{filter:grayscale(1);transition:filter .3s,transform .2s,opacity .4s;max-height:96px;max-width:180px;min-width:70px;width:15vw;object-fit:contain;padding:2px 0;opacity:.85}
  .brands-logo:hover{filter:none;opacity:1;transform:scale(1.06)}
  @media (max-width:768px){
    .brands-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px 10px;justify-items:center;align-items:center;padding:0 12px}
    .brands-logo{width:100%;max-width:100%;max-height:44px;min-width:0;padding:0;margin:0 auto}
  }

  /* =============== FEATURED (DARK-ORANGE GLOW + 5 CARD CENTER) =============== */
  .featured-section{
    background:
      radial-gradient(1200px 520px at 100% 0%, rgba(214,66,9,.14) 0%, transparent 60%),
      radial-gradient(1000px 520px at 0% 100%, rgba(178,54,7,.14) 0%, transparent 62%),
      linear-gradient(180deg, #232323 0%, #191919 100%);
    border-radius:32px; overflow:hidden; min-height:670px; padding:0; position:relative; isolation:isolate;
    box-shadow: 0 28px 80px rgba(0,0,0,.3);
  }
  .featured-section::before{
    content:""; position:absolute; inset:-20%; z-index:0; pointer-events:none;
    background:
      radial-gradient(60% 60% at 90% 6%, rgba(214,66,9,.38) 0%, rgba(214,66,9,0) 60%),
      radial-gradient(70% 60% at 10% 94%, rgba(178,54,7,.36) 0%, rgba(178,54,7,0) 62%);
    filter: blur(26px);
  }
  .featured-section::after{
    content:""; position:absolute; inset:0; z-index:0; pointer-events:none;
    background: radial-gradient(120% 120% at 50% 55%, rgba(0,0,0,.42) 0%, rgba(0,0,0,.0) 60%);
    mix-blend-mode:multiply;
  }

  .featured-3d-heading{position:relative;z-index:1;display:flex;justify-content:center;align-items:center;margin:36px 0 .6em}
  .featured-3d-heading-text{
    color:var(--orange);font-family:'Montserrat',Arial,sans-serif;font-weight:900;text-transform:uppercase;letter-spacing:.09em;
    border:2px solid var(--orange-deep);background:#252525;padding:10px 28px;
    box-shadow:0 6px 24px rgba(0,0,0,.4), 0 0 32px rgba(214,66,9,.22) inset
  }

  /* Center caption */
  .featured-caption{
    position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); z-index:2;
    text-align:center; width:var(--captionW); pointer-events:none; color:#fff;
    text-shadow:0 2px 14px rgba(0,0,0,.45);
  }
  .featured-caption__title{
    font-family:'Montserrat',Arial,sans-serif;font-weight:900;font-size:clamp(1.2rem,3.6vw,3rem);line-height:1.05;letter-spacing:.02em;
  }
  .featured-caption__sub{margin-top:.45em;opacity:.95;font-size:clamp(.9rem,1.6vw,1.1rem)}
  .fade-in{animation:fcIn .32s ease forwards} .fade-out{animation:fcOut .12s ease forwards}
  @keyframes fcIn{from{opacity:0;transform:translate(-50%,-42%)}to{opacity:1;transform:translate(-50%,-50%)}}
  @keyframes fcOut{from{opacity:1}to{opacity:0}}

  /* Slider shell */
  .featured-3d-slider-area{position:relative;z-index:1;width:100%;max-width:1840px;margin:0 auto;display:flex;justify-content:center;align-items:center;overflow:hidden}
  .featured-3d-slider{width:100%;position:relative;display:flex;justify-content:center;align-items:center}
  .featured-3d-slider-track{width:100%;height:500px;display:flex;justify-content:center;align-items:center;position:relative;perspective:var(--c-persp)}

  /* Arrows */
  .featured-3d-arrow{
    position:absolute;top:50%;transform:translateY(-50%);
    width:58px;height:58px;border-radius:50%;border:3px solid var(--orange-deep);background:#2b2b2b;z-index:5;
    display:flex;align-items:center;justify-content:center;cursor:pointer;
    box-shadow:0 8px 24px rgba(0,0,0,.35);
    transition: background .16s, border-color .16s, transform .08s;
  }
  .featured-3d-arrow.left{left:24px}
  .featured-3d-arrow.right{right:24px}
  .featured-3d-arrow:hover{background:var(--orange-deep);border-color:#fff}
  .featured-3d-arrow:active{transform:translateY(-50%) scale(.96)}
  .featured-3d-arrow svg{width:1.6em;height:1.6em;color:var(--orange-deep)}
  .featured-3d-arrow:hover svg{color:#fff}

  /* Slides */
  .featured-3d-slide{
    position:absolute; top:50%; left:50%;
    width:320px; height:480px; max-width:32vw; max-height:48vw;
    transform:translate(-50%,-50%);
    border:3px solid #2d2d2d; border-radius:10px; overflow:hidden;
    background:linear-gradient(180deg,#2a2a2a,#242424);
    box-shadow: 0 14px 32px rgba(0,0,0,.45), 0 2px 10px rgba(214,66,9,.10);
    transition: transform .48s cubic-bezier(.22,1,.36,1), opacity .22s, filter .22s, box-shadow .24s, border-color .22s;
    will-change: transform, opacity;
    opacity:0; pointer-events:none;
  }
  .featured-3d-slide img{width:100%;height:100%;object-fit:cover;display:block}
  .featured-3d-slide.is-hidden{opacity:0}

  /* EXACT 5 positions */
  .featured-3d-slide.is-center{
    opacity:1; pointer-events:auto; z-index:10;
    transform: translate(-50%,-50%) translateX(0) scale(var(--c-scale-ctr)) rotateY(0deg);
    border-color: rgba(214,66,9,.92);
    box-shadow: 0 26px 80px rgba(0,0,0,.58), 0 0 0 8px rgba(255,255,255,.06) inset, 0 0 42px rgba(214,66,9,.22);
    filter:none;
  }
  .featured-3d-slide.is-left1{
    opacity:1; z-index:4;
    transform: translate(-50%,-50%) translateX(calc(-1 * var(--c-gap-1))) scale(var(--c-scale-1)) rotateY(var(--c-rot-1));
    filter:brightness(.9);
  }
  .featured-3d-slide.is-right1{
    opacity:1; z-index:4;
    transform: translate(-50%,-50%) translateX(var(--c-gap-1)) scale(var(--c-scale-1)) rotateY(calc(-1 * var(--c-rot-1)));
    filter:brightness(.9);
  }
  .featured-3d-slide.is-left2{
    opacity:1; z-index:2;
    transform: translate(-50%,-50%) translateX(calc(-1 * var(--c-gap-2))) scale(var(--c-scale-2)) rotateY(var(--c-rot-2));
    filter:brightness(.76) grayscale(.08) blur(.2px);
  }
  .featured-3d-slide.is-right2{
    opacity:1; z-index:2;
    transform: translate(-50%,-50%) translateX(var(--c-gap-2)) scale(var(--c-scale-2)) rotateY(calc(-1 * var(--c-rot-2)));
    filter:brightness(.76) grayscale(.08) blur(.2px);
  }

  /* Mobile — still 5 visible, compact */
  @media (max-width:700px){
    :root{
      --c-persp:1400px;
      --c-gap-1: 110px;
      --c-gap-2: 200px;
      --c-rot-1: 22deg;
      --c-rot-2: 36deg;
      --c-scale-ctr: 1.03;
      --c-scale-1: .9;
      --c-scale-2: .78;
    }
    .featured-3d-slider-track{height:360px}
    .featured-3d-slide{width:176px;height:260px;border-width:2px;border-radius:8px}
    .featured-3d-arrow{width:48px;height:48px}
    .featured-3d-arrow.left{left:10px} .featured-3d-arrow.right{right:10px}
    .featured-caption{width:min(78vw,560px)}
    .featured-caption__title{font-size:clamp(1.04rem,6.2vw,1.65rem)}
    .featured-caption__sub{font-size:clamp(.84rem,3.2vw,.96rem)}
  }
  @media (max-width:380px){
    :root{ --c-gap-1: 92px; --c-gap-2: 170px; }
    .featured-3d-slide{width:152px;height:222px}
  }
</style>

<!-- ==================== HERO ==================== -->
<section class="hero-section relative overflow-hidden pt-4 pb-2">
  <div class="hero-video">
    <video autoplay loop muted playsinline>
      <source src="uploads/hero-reels/animation.mp4" type="video/mp4">
      Your browser does not support the video tag.
    </video>
  </div>
  <div class="hero-quote" style="margin-top:2.2vw;">
    <span class="smart">Smart</span> strategy. <span class="loud">Loud</span> creativity. <span class="real">Real</span> results.
  </div>
</section>

<!-- ==================== OUR EXPERTISE ==================== -->
<section class="expertise-section section-overlap relative mb-12">
  <div class="text-center mb-5">
    <div class="section-heading">
      <span class="heading-our">Our</span>
      <span class="heading-expertise">Expertise</span>
    </div>
  </div>
  <div class="expertise-list">
    <?php
      $expertise = getServices(); // ['title'=>..., 'description'=>...]
      if (!empty($expertise)) {
        foreach(array_slice($expertise,0,4) as $idx => $exp) {
          $title = html_entity_decode($exp['title'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
          echo '<div class="glass-card">';
            echo '<div class="title">'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</div>';
            echo '<ul>';
              $points = preg_split('/\r\n|\r|\n/', $exp['description'] ?? '');
              foreach($points as $p) { if(trim($p)!=="") echo '<li>'.htmlspecialchars($p).'</li>'; }
            echo '</ul>';
          echo '</div>';
        }
      } else {
        echo '<div style="color:#fff;text-align:center;width:100%;font-size:1.15rem;">No expertise/services found.</div>';
      }
    ?>
  </div>
</section>

<!-- ==================== BRANDS (2-per-row on mobile) ==================== -->
<section class="brands-section section-overlap my-8" style="position:relative; margin-top:-3.8rem; z-index:11;">
  <div class="text-center mb-8">
    <div class="brands-heading">Brands We've <span style="font-weight:900;">Worked With</span></div>
  </div>
  <div class="brands-grid">
    <?php
      $brandLogos = getBrandLogos();
      $pattern = [3,4]; $k=0; $rowCount=0;
      while ($k < count($brandLogos)) {
        $row = $pattern[$rowCount % 2];
        echo '<div class="brands-row">';
        for ($j=0; $j<$row && $k<count($brandLogos); $j++,$k++) {
          $logo = $brandLogos[$k];
          echo '<img src="'.htmlspecialchars($logo['logo_path']).'" alt="'.htmlspecialchars($logo['brand_name']).'" class="brands-logo">';
        }
        echo '</div>';
        $rowCount++;
      }
    ?>
  </div>
</section>

<!-- ==================== FEATURED (5 demo slides + center caption) ==================== -->
<section class="featured-section section-overlap relative">
  <div class="featured-3d-heading">
    <span class="featured-3d-heading-text">Featured Work</span>
  </div>

  <!-- Center caption that updates with current slide -->
  <div class="featured-caption" aria-live="polite" aria-atomic="true">
    <h2 class="featured-caption__title"></h2>
    <p class="featured-caption__sub"></p>
  </div>

  <div class="featured-3d-slider-area">
    <div class="featured-3d-slider">
      <button class="featured-3d-arrow left" type="button" id="featured3dArrowLeft" aria-label="Previous">
        <svg viewBox="0 0 24 24" fill="none"><path d="M15.5 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <button class="featured-3d-arrow right" type="button" id="featured3dArrowRight" aria-label="Next">
        <svg viewBox="0 0 24 24" fill="none"><path d="M8.5 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>

      <div class="featured-3d-slider-track" id="featured3dSliderTrack">
        <?php
          // ***** DEMO: EXACTLY 5 SLIDES *****
          $slides = [
            [ 'img'=>'uploads/featured/slide1.jpg', 'title'=>'Bold Brand Reveal',    'sub'=>'Launch Teaser • Motion + Sound Design' ],
            [ 'img'=>'uploads/featured/slide2.jpg', 'title'=>'Summer Drop Film',     'sub'=>'Fashion Promo • Color‑graded & Cutdowns' ],
            [ 'img'=>'uploads/featured/slide3.jpg', 'title'=>'App Intro Sequence',   'sub'=>'UI Animations • 3D Transitions' ],
            [ 'img'=>'uploads/featured/slide4.jpg', 'title'=>'Product Hero Loop',    'sub'=>'CGI Packshot • Realistic Lighting' ],
            [ 'img'=>'uploads/featured/slide5.jpg', 'title'=>'Festival Opener',      'sub'=>'Kinetic Type • Beat‑Synced Edits' ],
          ];
          // render five slides
          for ($i=0; $i<count($slides); $i++) {
            $s = $slides[$i];
            $img = htmlspecialchars($s['img']);
            $title = htmlspecialchars($s['title']);
            echo '<div class="featured-3d-slide" data-slide-idx="'.$i.'">';
              // fallback gradient if image missing
              echo '<img src="'.$img.'" alt="'.$title.'" onerror="this.style.display=\'none\'; this.parentElement.style.background=\'linear-gradient(135deg,#2a2a2a,#1f1f1f)\';">';
            echo '</div>';
          }
        ?>
      </div>
    </div>
  </div>

  <script>
    // ***** DEMO: EXACTLY 5 SLIDES (same order as PHP above) *****
    const SLIDES_DATA = [
      {img:'uploads/featured/slide1.jpg', title:'Bold Brand Reveal',  sub:'Launch Teaser • Motion + Sound Design'},
      {img:'uploads/featured/slide2.jpg', title:'Summer Drop Film',   sub:'Fashion Promo • Color‑graded & Cutdowns'},
      {img:'uploads/featured/slide3.jpg', title:'App Intro Sequence', sub:'UI Animations • 3D Transitions'},
      {img:'uploads/featured/slide4.jpg', title:'Product Hero Loop',  sub:'CGI Packshot • Realistic Lighting'},
      {img:'uploads/featured/slide5.jpg', title:'Festival Opener',    sub:'Kinetic Type • Beat‑Synced Edits'}
    ];

    const track = document.getElementById('featured3dSliderTrack');
    const slidesEls = Array.from(track.querySelectorAll('.featured-3d-slide'));
    const captionTitle = document.querySelector('.featured-caption__title');
    const captionSub   = document.querySelector('.featured-caption__sub');

    let current = 0;
    let autoplayTimer = null;
    const AUTOPLAY_MS = 5000;

    function relPos(i, total){
      const rel = ((i - current) % total + total) % total;
      const half = Math.floor(total/2);
      return rel > half ? rel - total : rel;
    }

    function applyPositions(){
      const total = slidesEls.length;
      slidesEls.forEach(el=>{
        el.className='featured-3d-slide';
        el.style.opacity='0';
        el.style.pointerEvents='none';
        el.setAttribute('aria-current','false');
      });
      if(!total) return;

      const show = [-2,-1,0,1,2]; // five visible always
      slidesEls.forEach((el,i)=>{
        const d = relPos(i,total);
        if(show.includes(d)){
          el.style.opacity='1';
          el.style.pointerEvents='auto';
          if(d===0){ el.classList.add('is-center'); el.setAttribute('aria-current','true'); }
          else if(d===-1) el.classList.add('is-left1');
          else if(d===-2) el.classList.add('is-left2');
          else if(d=== 1) el.classList.add('is-right1');
          else if(d=== 2) el.classList.add('is-right2');
        } else {
          el.classList.add('is-hidden');
        }
      });

      const data = SLIDES_DATA[current] || {title:'', sub:''};
      updateCaption(data.title, data.sub);
    }

    function updateCaption(title, sub){
      captionTitle.classList.add('fade-out'); captionSub.classList.add('fade-out');
      setTimeout(()=>{
        captionTitle.textContent = title || '';
        captionSub.textContent   = sub || '';
        captionTitle.classList.remove('fade-out'); captionSub.classList.remove('fade-out');
        captionTitle.classList.add('fade-in'); captionSub.classList.add('fade-in');
        setTimeout(()=>{ captionTitle.classList.remove('fade-in'); captionSub.classList.remove('fade-in'); }, 320);
      }, 120);
    }

    function next(){ current=(current+1)%slidesEls.length; applyPositions(); }
    function prev(){ current=(current-1+slidesEls.length)%slidesEls.length; applyPositions(); }

    function startAuto(){ stopAuto(); autoplayTimer=setInterval(next, AUTOPLAY_MS); }
    function stopAuto(){ if(autoplayTimer){ clearInterval(autoplayTimer); autoplayTimer=null; } }

    document.getElementById('featured3dArrowRight').addEventListener('click', ()=>{ next(); startAuto(); });
    document.getElementById('featured3dArrowLeft').addEventListener('click',  ()=>{ prev(); startAuto(); });

    // keyboard
    window.addEventListener('keydown', (e)=>{ if(e.key==='ArrowRight'){ next(); startAuto(); } if(e.key==='ArrowLeft'){ prev(); startAuto(); } });

    // touch swipe
    (function(){
      let sx=0, sy=0, dx=0, dy=0;
      track.addEventListener('touchstart', (e)=>{ if(!e.touches[0])return; sx=e.touches[0].clientX; sy=e.touches[0].clientY; stopAuto(); }, {passive:true});
      track.addEventListener('touchmove',  (e)=>{ if(!e.touches[0])return; dx=e.touches[0].clientX-sx; dy=e.touches[0].clientY-sy; }, {passive:true});
      track.addEventListener('touchend',   ()=>{ if(Math.abs(dx)>30 && Math.abs(dx)>Math.abs(dy)){ (dx<0)?next():prev(); } dx=dy=0; startAuto(); });
    })();

    window.addEventListener('load', ()=>{ applyPositions(); startAuto(); });
    window.addEventListener('resize', applyPositions);
  </script>
</section>

<?php include 'includes/footer.php'; ?>
