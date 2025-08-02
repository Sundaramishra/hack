<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php include 'includes/header.php'; ?>

  <!-- FONTS & BASE STYLES -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
  :root {
    --orange: #F44B12;
    --orange-grad: linear-gradient(90deg, #F88F54 0%, #F44B12 100%);
    --dark: #2B2B2A;
    --glass-bg: linear-gradient(120deg, #232323 62%, #232323 100%);
    --white-bg: #fff;
    --glass-card: rgba(255,255,255,0.10);
    --glass-border: rgba(244,75,18,0.38);
    --radius-main: 38px;
    --brand-dot: #FFD6C0;
  }
  body { background: #fff; color: var(--dark); margin:0; }

  /* HERO SECTION */
  .hero-section {
    background: #fff;
    position: relative;
    min-height: 320px;
    z-index: 1;
    padding: 0 0 12px 0;
    text-align: center;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
  }
  .hero-halftone {
    position: absolute;
    pointer-events: none;
    z-index: 0;
    opacity: 0.46;
  }
  .hero-halftone.topright {
    right: 0; top: 0;
    width: min(29vw, 210px);
    height: min(22vw, 120px);
    transform: scale(-1, -1) rotate(-2deg);
  }
  .hero-halftone.bottomleft {
    left: 0; bottom: 0;
    width: min(32vw, 220px);
    height: min(24vw, 140px);
    transform: rotate(2deg);
  }
  .hero-video {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 0;
    margin-top: 3vw;
    z-index: 2;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 24px 0 rgba(0,0,0,0.07);
  }
  .hero-video video {
    width: 380px;
    max-width: 90vw;
    min-width: 170px;
    height: auto;
    border: none;
    background: none;
    display: block;
    margin: 0 auto;
    border-radius: 8px;
    animation: logoPop .9s cubic-bezier(.21,1.09,.39,1.01);
  }
  @keyframes logoPop {
    0% { opacity: 0; transform: scale(0.95);}
    95% { opacity: 1; transform: scale(1.04);}
    100% { opacity: 1; transform: scale(1);}
  }
  .hero-quote {
    font-family: 'Montserrat', Arial, sans-serif;
    font-size: clamp(1.35rem, 2.4vw, 2.45rem);
    font-weight: 800;
    text-align: center;
    margin: 2.2vw 0 42px 0;
    letter-spacing: -1px;
    z-index: 3;
    color: #222;
    position: relative;
    line-height: 1.18;
    width: 100%;
    padding-left: 2vw;
    padding-right: 2vw;
  }
  .hero-quote .smart,
  .hero-quote .loud,
  .hero-quote .real {
    color: #F44B12;
    font-weight: 900;
  }
  .hero-quote .real { color: #222; }
  </style>

  <section class="hero-section relative overflow-hidden pt-4 pb-2">
    <!-- Halftone SVG (top right) -->
    <div class="hero-halftone topright" aria-hidden="true">
      <svg width="100%" height="100%" viewBox="0 0 210 120" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <pattern id="halftonePatternTR" x="0" y="0" width="14" height="14" patternUnits="userSpaceOnUse">
            <circle cx="2" cy="2" r="1.4" fill="#F44B12" />
            <circle cx="8" cy="8" r="1.1" fill="#F44B12" />
          </pattern>
        </defs>
        <rect width="210" height="120" fill="url(#halftonePatternTR)" />
      </svg>
    </div>
    <!-- Halftone SVG (bottom left) -->
    <div class="hero-halftone bottomleft" aria-hidden="true">
      <svg width="100%" height="100%" viewBox="0 0 220 140" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <pattern id="halftonePatternBL" x="0" y="0" width="16" height="16" patternUnits="userSpaceOnUse">
            <circle cx="3" cy="3" r="1.55" fill="#F88F54" />
            <circle cx="10" cy="10" r="1.2" fill="#F88F54" />
          </pattern>
        </defs>
        <rect width="220" height="140" fill="url(#halftonePatternBL)" />
      </svg>
    </div>
    <div class="hero-video">
      <video autoplay loop muted playsinline style="width:380px;max-width:93vw;background:#fff;border-radius:13px;">
        <source src="uploads/hero-reels/animation.mp4" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </div>
    <div class="hero-quote" style="margin-top:2.2vw;">
      <span class="smart">Smart</span> strategy. <span class="loud">Loud</span> creativity. <span class="real">Real</span> results.
    </div>
  </section>

  <!-- OUR EXPERTISE SECTION -->
  <section class="expertise-section section-overlap relative mb-12">
    <style>
      .expertise-section {
        background: var(--glass-bg);
        max-width: 98vw;
        width: 90vw;
        margin: 0 auto 2.9rem auto;
        padding: 3rem 0 2.4rem 0;
        position: relative;
        overflow: visible;
        box-shadow: 0 8px 32px 0 rgba(44,44,44,0.09);
        border-radius: 38px;
        box-sizing: border-box;
      }
      .expertise-section .glow {
        position: absolute;
        border-radius: 50%;
        opacity: 0.32;
        z-index: 0;
        animation: glowFade 4s infinite alternate;
        filter: blur(45px);
      }
      .expertise-section .glow.left {
        width: 210px; height: 210px;
        left: -80px; bottom: -80px;
        background: radial-gradient(circle, var(--orange) 0%, transparent 70%);
        animation-delay: 0s;
      }
      .expertise-section .glow.right {
        width: 170px; height: 170px;
        right: -60px; top: -60px;
        background: radial-gradient(circle, #fff 0%, transparent 80%);
        animation-delay: 1.6s;
      }
      @keyframes glowFade { 0% { opacity: 0.22; } 100% { opacity: 0.47; } }
      .section-heading {
        display: inline-block;
        font-family: 'Montserrat', Arial, sans-serif;
        font-weight: 900;
        font-size: clamp(1.5rem, 2.7vw, 2.7rem);
        padding: 18px 54px;
        border: 2.5px dashed var(--orange);
        border-radius: 22px;
        background: linear-gradient(90deg,#ffb067 0%,#ff6e2a 60%,#fff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 48px;
        margin-top: 0;
        box-shadow: 2px 2px 16px rgba(244, 75, 18, 0.18);
        text-align: center;
        letter-spacing: -1px;
        position: relative;
        animation: headingPop 1.1s cubic-bezier(.22,1.08,.29,1.01);
      }
      @keyframes headingPop {
        0% { opacity:0; transform: scale(0.92); }
        80% { opacity:1; transform: scale(1.08); }
        100% { opacity:1; transform: scale(1); }
      }
      .expertise-list {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2.2rem 2.2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        box-sizing: border-box;
      }
      @media (max-width: 1024px) {
        .expertise-list {
          grid-template-columns: repeat(2, 1fr);
          gap: 1.2rem 1.2rem;
          max-width: 700px;
          padding: 0 1rem;
        }
      }
      @media (max-width: 600px) {
        html, body {
          width: 100vw;
          max-width: 100vw;
          overflow-x: hidden !important;
        }
        .expertise-section {
          width: 100vw !important;
          max-width: 100vw !important;
          min-width: 100vw !important;
          margin-left: 0 !important;
          margin-right: 0 !important;
          border-radius: 0 !important;
          padding-left: 0 !important;
          padding-right: 0 !important;
          box-sizing: border-box;
        }
        .expertise-list {
          width: 100vw !important;
          max-width: 100vw !important;
          min-width: 100vw !important;
          margin-left: 0 !important;
          margin-right: 0 !important;
          padding-left: 0 !important;
          padding-right: 0 !important;
          grid-template-columns: 1fr 1fr;
          gap: 0.7rem 0.7rem;
          box-sizing: border-box;
        }
      }
      .glass-card {
        background: linear-gradient(135deg,rgba(255,255,255,0.10) 60%,rgba(255,110,42,0.13) 100%);
        border: 2.2px solid rgba(244,75,18,0.35);
        border-radius: 22px;
        min-width: 0;
        min-height: 180px;
        max-width: 100%;
        width: 100%;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-start;
        padding: 1.6rem 1.3rem 1.6rem 1.3rem;
        margin: 0 auto;
        box-shadow: 0 4px 24px 0 rgba(34,34,34,0.09), 0 2px 16px 0 rgba(255,110,42,0.08);
        overflow: hidden;
        backdrop-filter: blur(11px) saturate(1.05);
        -webkit-backdrop-filter: blur(11px) saturate(1.05);
        z-index: 2;
        transition: box-shadow 0.25s, border 0.18s, transform 0.18s, background 0.28s, height 0.18s, padding 0.18s;
        animation: glassFadeIn 0.75s cubic-bezier(.22,1.08,.29,1.01) both;
        opacity: 0;
        cursor: pointer;
        position: relative;
        will-change: transform, box-shadow, opacity;
        animation-delay: var(--delay, 0s);
      }
      @keyframes glassFadeIn {
        0% { opacity:0; filter: blur(15px) brightness(0.82);}
        80% { opacity:.6; filter: blur(1.5px) brightness(1.04);}
        100% { opacity:1; filter: none;}
      }
      .glass-card:hover {
        box-shadow: 0 12px 40px 0 rgba(255,110,42,0.13), 0 7px 36px 0 rgba(34,34,34,0.09), 0 0 0 5px #F44B1288 inset;
        border: 2.6px solid #F44B12;
        background: linear-gradient(120deg,rgba(255,255,255,0.16) 40%,rgba(255,110,42,0.11) 100%);
        transform: translateY(-5px) scale(1.025) rotate(-1deg);
      }
      .glass-card .icon {
        width: 44px; height: 44px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px 0 #F44B1240;
        font-size: 1.7rem;
        color: #F44B12;
        margin-bottom: 0.7em;
        margin-top: -0.2em;
        z-index: 3;
        animation: iconPop 1.2s cubic-bezier(.22,1.08,.29,1.01);
      }
      @keyframes iconPop {
        0% { transform: scale(0.7); opacity: 0; }
        80% { transform: scale(1.08); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
      }
      .glass-card .title {
        font-family: 'Montserrat', Arial, sans-serif;
        font-size: 1.14rem;
        font-weight: 900;
        margin-bottom: 0.7em;
        text-align: left;
        background: linear-gradient(90deg,#ffb067 0%,#ff6e2a 60%,#fff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-fill-color: transparent;
        text-shadow: 0 2.5px 18px rgba(255,110,42,0.09);
        display: inline-block;
        line-height: 1.1;
        letter-spacing: 0.01em;
        text-transform: capitalize;
      }
      .glass-card ul {
        margin: 0;
        padding: 0;
        list-style: none;
        color: #ededed;
        font-size: 1.04rem;
        font-weight: 400;
        opacity: 0.99;
        text-align: left;
        line-height: 1.7;
        word-break: break-word;
      }
      .glass-card li {
        margin-bottom: 0.17em;
        line-height: 1.4;
        font-weight: 400;
        letter-spacing: 0.01em;
        text-shadow: 0 1.5px 12px rgba(255,255,255,0.08);
        opacity: 0.96;
        transition: color 0.2s;
      }
      .glass-card:hover li {
        color: #fff;
        text-shadow: 0 2px 8px var(--orange), 0 1.5px 12px #fff4;
      }
      .glass-card::before {
        content: "";
        position: absolute;
        top: 8px; left: 50%; transform: translateX(-50%);
        width: 92%;
        height: 18px;
        background: linear-gradient(90deg,rgba(255,255,255,0.30) 0%,rgba(255,255,255,0.08) 100%);
        border-radius: 40px;
        opacity: 0.11;
        pointer-events: none;
        z-index: 1;
        transition: opacity .35s;
      }
      .glass-card:hover::before {
        opacity: 0.21;
        animation: glassShine 1.1s linear;
      }
      @keyframes glassShine {
        0% { left: 50%; opacity:0.15; }
        20% { opacity:0.40; }
        100% { left: 120%; opacity:0.04;}
      }
      @media (min-width: 700px) {
        .glass-card {
          animation: glassFadeIn 0.8s cubic-bezier(.22,1.08,.29,1.01) both, floatCard 3.6s infinite alternate cubic-bezier(.6,0,.4,1);
        }
        @keyframes floatCard {
          0%   { transform: translateY(0) scale(1);}
          50%  { transform: translateY(-6px) scale(1.01);}
          100% { transform: translateY(0) scale(1);}
        }
      }
    </style>
    <div class="glow left"></div>
    <div class="glow right"></div>
    <div class="text-center mb-5">
      <div class="section-heading">
        <span class="heading-bg"></span>
      <span class="heading-text">Our <span style="color:var(--orange); font-family:'Montserrat', Arial, sans-serif;">Expertise</span></span>
      </div>
    </div>
    <div class="expertise-list">
      <?php
      // Fetch expertise/services from the database only
      $expertise = getServices(); // Make sure this function returns an array of ['title'=>..., 'description'=>...]
      $icons = [
        '<svg width="28" height="28" fill="none" stroke="#F44B12" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>',
        '<svg width="28" height="28" fill="none" stroke="#F44B12" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4"/></svg>',
        '<svg width="28" height="28" fill="none" stroke="#F44B12" stroke-width="2" viewBox="0 0 24 24"><polygon points="12,2 22,22 2,22"/></svg>',
        '<svg width="28" height="28" fill="none" stroke="#F44B12" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M2 12h20"/></svg>',
        '<svg width="28" height="28" fill="none" stroke="#F44B12" stroke-width="2" viewBox="0 0 24 24"><ellipse cx="12" cy="12" rx="10" ry="6"/></svg>',
        '<svg width="28" height="28" fill="none" stroke="#F44B12" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12h20M12 2v20"/></svg>',
        '<svg width="28" height="28" fill="none" stroke="#F44B12" stroke-width="2" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="3"/></svg>',
        '<svg width="28" height="28" fill="none" stroke="#F44B12" stroke-width="2" viewBox="0 0 24 24"><polygon points="12,2 19,21 5,21"/></svg>',
      ];
      if (!empty($expertise)) {
        foreach(array_slice($expertise,0,4) as $idx => $exp) {
          $delay = 0.08 * $idx;
          $icon = $icons[$idx % count($icons)];
          echo '<div class="glass-card" style="--delay:'.$delay.'s">';
          echo '<div class="icon">'.$icon.'</div>';
          echo '<div class="title">'.htmlspecialchars($exp['title']).'</div>';
          echo '<ul>';
          $points = preg_split('/\r\n|\r|\n/', $exp['description']);
          foreach($points as $p) {
            if(trim($p)!=="") echo '<li>'.htmlspecialchars($p).'</li>';
          }
          echo '</ul>';
          echo '</div>';
        }
      } else {
        echo '<div style="color:#fff;text-align:center;width:100%;font-size:1.2rem;">No expertise/services found.</div>';
      }
      ?>
    </div>
  </section>

  <!-- BRANDS SECTION (fullscreen, multi-dots animation) -->
  <!-- BRANDS SECTION (fullscreen, multi-dots animation) -->
  <section class="brands-section section-overlap my-8">
    <style>
      .brands-grid {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 1.5rem 0;
        position: relative;
        align-items: center;
      }
      .brands-row {
        display: flex;
        justify-content: center;
        gap: 3vw;
        margin-bottom: 0.9rem;
        flex-wrap: nowrap;
        width: auto;
        max-width: 100vw;
      }
      .brands-logo {
        filter: grayscale(1);
        transition: filter 0.3s, transform 0.2s, opacity 0.4s;
        max-height: 64px;
        max-width: 120px;
        object-fit: contain;
        padding: 4px 0;
        opacity: 0.72;
        animation: brandLogoFade 3s infinite alternate;
        width: auto;
        height: auto;
        background: none;
        border: none;
        display: block;
      }
      @keyframes brandLogoFade {
        0%, 100% { opacity: 0.72; }
        50% { opacity: 1; filter: none;}
      }
      .brands-logo:hover { filter: none; opacity: 1; transform: scale(1.10);}
      .brand-dot {
        width: 14px; height: 14px; border-radius: 50%; background: var(--orange,#F44B12);
        opacity: 0.17; 
        position: absolute;
        animation: fadeDot 2.4s infinite alternate, multiDotMove 10s infinite linear;
        z-index: 2;
      }
      @keyframes fadeDot { 0% { opacity: 0.13; } 100% { opacity: 0.24; } }
      @keyframes multiDotMove {
        0% { transform: scale(1) translateY(0);}
        25% { transform: scale(1.17) translateY(-22px);}
        50% { transform: scale(1.08) translateY(15px);}
        75% { transform: scale(1.12) translateY(-6px);}
        100% { transform: scale(1) translateY(0);}
      }
      .brand-dot.dot2 { left: 20vw; top: 16vh; background: #F44B12; animation-delay: .2s, 1.2s;}
      .brand-dot.dot3 { left: 70vw; top: 13vh; background: #F88F54; animation-delay: .7s, 2.1s;}
      .brand-dot.dot4 { left: 40vw; top: 18vh; background: #F44B12; animation-delay: 1.5s, 3s;}
      .brand-dot.dot5 { left: 80vw; top: 29vh; background: #FFB067; animation-delay: 2.1s, 5.5s;}
      .brand-dot.dot6 { left: 10vw; top: 31vh; background: #F88F54; animation-delay: 1.8s, 6.1s;}
      .brands-mobile { display: none; }
      .brands-desktop { display: block; }
      @media (max-width: 900px) {
        .brands-logo { max-width: 70px; max-height: 50px; }
        .brands-row { gap: 2vw; }
      }
      @media (max-width: 600px) {
        .brands-section { border-radius: 12px; padding: 1.2rem 0.2rem;}
        .brands-heading { font-size: 1.05rem; padding: 6px 4vw;}
        .brands-mobile { display: block; }
        .brands-desktop { display: none; }
        .brands-row {
          gap: 2vw;
          flex-wrap: wrap;
          width: 100vw;
          max-width: 100vw;
          margin-bottom: 0.6rem;
        }
        .brands-logo {
          max-width: 26vw;
          max-height: 36px;
          width: 26vw;
          height: auto;
          margin: 0 2vw;
          padding: 0;
        }
      }
      @media (max-width: 420px) {
        .brands-row {
          gap: 1vw;
          margin-bottom: 0.4rem;
        }
        .brands-logo {
          max-width: 29vw;
          max-height: 30px;
          margin: 0 1vw;
        }
      }
    </style>
    <div class="text-center mb-8">
    <div class="brands-heading" style="font-family:'Montserrat', Arial, sans-serif;">Brands We've <span style="color:var(--orange); font-family:'Montserrat', Arial, sans-serif;">Worked With</span></div>
    </div>
    <div class="brands-grid">
      <?php
      $brandLogos = getBrandLogos();
      if (!empty($brandLogos)) {
        // Desktop: 3-4-3 pattern, Mobile: 2-2-2 pattern
        $desktopPattern = [3,4,3];
        $mobilePattern = [2,2,2];
        $k = 0;
        
        // Desktop layout
        echo '<div class="brands-desktop">';
        while ($k < count($brandLogos)) {
          foreach($desktopPattern as $row) {
            if ($k >= count($brandLogos)) break;
            echo '<div class="brands-row">';
            for ($j = 0; $j < $row && $k < count($brandLogos); $j++, $k++) {
              $logo = $brandLogos[$k];
              echo '<img src="'.htmlspecialchars($logo['logo_path']).'" alt="'.htmlspecialchars($logo['brand_name']).'" class="brands-logo">';
            }
            echo '</div>';
          }
        }
        echo '</div>';
        
        // Mobile layout
        $k = 0;
        echo '<div class="brands-mobile">';
        while ($k < count($brandLogos)) {
          foreach($mobilePattern as $row) {
            if ($k >= count($brandLogos)) break;
            echo '<div class="brands-row">';
            for ($j = 0; $j < $row && $k < count($brandLogos); $j++, $k++) {
              $logo = $brandLogos[$k];
              echo '<img src="'.htmlspecialchars($logo['logo_path']).'" alt="'.htmlspecialchars($logo['brand_name']).'" class="brands-logo">';
            }
            echo '</div>';
          }
        }
        echo '</div>';
      } else {
        echo '<div style="color:#666; text-align:center; padding:40px;">No brand logos found. Please add brand logos from admin dashboard.</div>';
      }
      ?>
      <!-- Multi Dot Animation -->
      <div class="brand-dot" style="left:12vw;top:8vh;"></div>
      <div class="brand-dot dot2"></div>
      <div class="brand-dot dot3"></div>
      <div class="brand-dot dot4"></div>
      <div class="brand-dot dot5"></div>
      <div class="brand-dot dot6"></div>
    </div>
  </section>
<section class="featured-section section-overlap relative">
  <style>
    .featured-3d-heading {
      text-align: center;
      padding: 40px 0 20px 0;
    }
    .featured-3d-heading-text {
      font-family: 'Montserrat', Arial, sans-serif;
      font-size: 2.5rem;
      font-weight: 900;
      color: #F44B12;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    .featured-caption {
      text-align: center;
      padding: 0 20px 30px 20px;
      min-height: 80px;
    }
    .featured-caption__title {
      font-size: 1.8rem;
      font-weight: 700;
      color: #fff;
      margin-bottom: 8px;
      transition: opacity 0.3s ease;
    }
    .featured-caption__sub {
      font-size: 1.1rem;
      color: #ccc;
      transition: opacity 0.3s ease;
    }
    .fade-out { opacity: 0; }
    .fade-in { opacity: 1; }
    
    .featured-3d-slider-area {
      position: relative;
      height: 500px;
      overflow: visible;
      perspective: 1200px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .featured-3d-slider {
      position: relative;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .featured-3d-slider-track {
      position: relative;
      width: 300px;
      height: 400px;
      transform-style: preserve-3d;
    }
    .featured-3d-slide {
      position: absolute;
      width: 280px;
      height: 380px;
      background: linear-gradient(135deg, #2a2a2a, #1f1f1f);
      border-radius: 20px;
      overflow: hidden;
      transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      cursor: pointer;
    }
    .featured-3d-slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 20px;
    }
    
    /* Positioning classes */
    .featured-3d-slide.is-center {
      transform: translateZ(0) scale(1.1);
      z-index: 5;
      box-shadow: 0 20px 50px rgba(244, 75, 18, 0.4);
    }
    .featured-3d-slide.is-left1 {
      transform: translateX(-200px) translateZ(-100px) rotateY(25deg) scale(0.9);
      z-index: 3;
    }
    .featured-3d-slide.is-left2 {
      transform: translateX(-350px) translateZ(-200px) rotateY(35deg) scale(0.8);
      z-index: 2;
      opacity: 0.7;
    }
    .featured-3d-slide.is-right1 {
      transform: translateX(200px) translateZ(-100px) rotateY(-25deg) scale(0.9);
      z-index: 3;
    }
    .featured-3d-slide.is-right2 {
      transform: translateX(350px) translateZ(-200px) rotateY(-35deg) scale(0.8);
      z-index: 2;
      opacity: 0.7;
    }
    .featured-3d-slide.is-hidden {
      opacity: 0;
      pointer-events: none;
    }
    
    .featured-3d-arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 50px;
      height: 50px;
      background: linear-gradient(135deg, #F44B12, #ff8a50);
      border: none;
      border-radius: 50%;
      color: white;
      cursor: pointer;
      z-index: 10;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .featured-3d-arrow:hover {
      transform: translateY(-50%) scale(1.1);
      box-shadow: 0 5px 20px rgba(244, 75, 18, 0.5);
    }
    .featured-3d-arrow.left {
      left: 20px;
    }
    .featured-3d-arrow.right {
      right: 20px;
    }
    .featured-3d-arrow svg {
      width: 24px;
      height: 24px;
    }
    
    @media (max-width: 768px) {
      .featured-3d-slider-area {
        height: 400px;
      }
      .featured-3d-slider-track {
        width: 250px;
        height: 320px;
      }
      .featured-3d-slide {
        width: 230px;
        height: 300px;
      }
      .featured-3d-slide.is-left1, .featured-3d-slide.is-right1 {
        transform: translateX(-150px) translateZ(-80px) rotateY(25deg) scale(0.85);
      }
      .featured-3d-slide.is-left2, .featured-3d-slide.is-right2 {
        transform: translateX(-250px) translateZ(-150px) rotateY(35deg) scale(0.75);
      }
      .featured-3d-slide.is-right1 {
        transform: translateX(150px) translateZ(-80px) rotateY(-25deg) scale(0.85);
      }
      .featured-3d-slide.is-right2 {
        transform: translateX(250px) translateZ(-150px) rotateY(-35deg) scale(0.75);
      }
    }
  </style>

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
          // Get top 5 portfolio items from database
          $portfolioItems = getPortfolioItems();
          $slides = [];
          
          // If portfolio items exist, use them (limit to 5)
          if (!empty($portfolioItems)) {
            $portfolioItems = array_slice($portfolioItems, 0, 5);
            foreach ($portfolioItems as $item) {
              $slides[] = [
                'image_path' => htmlspecialchars($item['thumbnail'] ?? ''),
                'title' => htmlspecialchars($item['title'] ?? ''),
                'subtitle' => htmlspecialchars($item['brand_name'] ?? '') . ' • ' . htmlspecialchars(mb_strimwidth(strip_tags($item['description'] ?? ''), 0, 50, '...'))
              ];
            }
          }
          
          // If no slides in database, use demo data as fallback
          if (empty($slides)) {
            $slides = [
              [ 'image_path'=>'uploads/featured/slide1.jpg', 'title'=>'Bold Brand Reveal',    'subtitle'=>'Launch Teaser • Motion + Sound Design' ],
              [ 'image_path'=>'uploads/featured/slide2.jpg', 'title'=>'Summer Drop Film',     'subtitle'=>'Fashion Promo • Color‑graded & Cutdowns' ],
              [ 'image_path'=>'uploads/featured/slide3.jpg', 'title'=>'App Intro Sequence',   'subtitle'=>'UI Animations • 3D Transitions' ],
              [ 'image_path'=>'uploads/featured/slide4.jpg', 'title'=>'Product Hero Loop',    'subtitle'=>'CGI Packshot • Realistic Lighting' ],
              [ 'image_path'=>'uploads/featured/slide5.jpg', 'title'=>'Festival Opener',      'subtitle'=>'Kinetic Type • Beat‑Synced Edits' ],
            ];
          }
          
          // Render slides
          for ($i=0; $i<count($slides); $i++) {
            $s = $slides[$i];
            $img = htmlspecialchars($s['image_path']);
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
    // ***** SLIDER DATA FROM DATABASE *****
    const SLIDES_DATA = [
      <?php
        // Output the same slides data for JavaScript
        $jsSlides = [];
        foreach ($slides as $slide) {
          $jsSlides[] = sprintf(
            "{img:'%s', title:'%s', sub:'%s'}",
            addslashes($slide['image_path']),
            addslashes($slide['title']),
            addslashes($slide['subtitle'])
          );
        }
        echo implode(",\n      ", $jsSlides);
      ?>
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
        captionTitle.classList.remove('fade-out'); captionSub.classList.remove('fade-in');
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

  <!-- Remove navbar logo glow -->
  <style>
  .navbar-logo-glow, .navbar .logo-glow, .logo-glow {
    display: none !important;
    box-shadow: none !important;
    filter: none !important;
    background: none !important;
  }
  .navbar-logo, .navbar .logo, .header-logo, .site-logo {
    box-shadow: none !important;
    filter: none !important;
    background: none !important;
  }
  </style>