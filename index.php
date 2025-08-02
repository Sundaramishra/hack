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
      max-width: 100vw;
      width: 100vw;
      margin: 0 auto 2.9rem auto;
      padding: 5.2rem 0 3.9rem 0;
      position: relative;
      overflow: visible;
      box-shadow: 0 12px 38px 0 rgba(44,44,44,0.12);
      border-radius: 56px 56px 0 0 / 52px 52px 0 0;
      box-sizing: border-box;
    }
   
      @media (max-width: 600px) {
  .section-heading {
    font-size: 1.03rem !important;
    padding: 7px 8px !important;
    border-radius: 7px !important;
    margin-bottom: 30px !important;
    line-height: 1.25 !important;
    min-height: 3.4em !important; /* Ensure enough height for descenders */
    /* Fix vertical alignment if needed: */
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    word-break: break-word;
    overflow-wrap: break-word;
  }
}
    }
    @keyframes headingPop {
      0% { opacity:0; transform: scale(0.92);}
      80% { opacity:1; transform: scale(1.08);}
      100% { opacity:1; transform: scale(1);}
    }
    .section-heading .heading-our {
      color: #fff;
    }
    .section-heading .heading-expertise {
      color: #F44B12;
    }
    .expertise-list {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 3.2rem 3.2rem;
      max-width: 1850px;
      margin: 0 auto;
      padding: 0 1.5vw;
      box-sizing: border-box;
    }
    @media (max-width: 1500px) {
      .expertise-list { max-width: 1400px; }
    }
    @media (max-width: 1200px) {
      .expertise-list { gap: 2.2rem 2.2rem; max-width: 1000px;}
      .section-heading { font-size: 2.05rem; padding: 10px 28px;}
    }
    @media (max-width: 1100px) {
      .expertise-list { grid-template-columns: repeat(2, 1fr); gap: 1.8rem 1.8rem; max-width: 700px;}
    }
    @media (max-width: 800px) {
      .expertise-list { grid-template-columns: 1fr; max-width: 96vw; gap: 1.2rem 0.8rem; padding: 0 1vw;}
      .section-heading { font-size: 1.25rem; padding: 6px 12px; border-radius: 8px;}
    }
    @media (max-width: 600px) {
      .expertise-section {
        border-radius: 32px 32px 0 0 / 30px 30px 0 0 !important;
        padding-top: 1.5rem !important;
        padding-bottom: 1rem !important;
      }
      .expertise-list {
        width: 90vw !important;
        max-width: 90vw !important;
        min-width: 90vw !important;
        margin: 0 auto !important;
        padding: 0 !important;
        grid-template-columns: 1fr !important;
        grid-template-rows: repeat(4, 1fr) !important;
        gap: 1rem !important;
        box-sizing: border-box;
        height: auto !important;
        min-height: auto !important;
        max-height: none !important;
      }
      .glass-card {
        padding: 1rem !important;
        font-size: 0.80rem !important;
        min-height: 120px !important;
        min-width: unset !important;
        height: auto !important;
        width: 100% !important;
        border-radius: 8px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        align-items: center !important;
        background: rgba(255,255,255,0.07) !important;
        border: 2.5px solid rgba(244,75,18,0.15) !important;
        box-shadow: 0 10px 40px 0 rgba(44,44,44,0.07) !important;
      }
      .glass-card .title {
        font-size: 0.95rem !important;
        margin-bottom: 0.18em !important;
        background: transparent !important;
        color: #F44B12 !important;
        padding: 0 !important;
        font-weight: 900 !important;
        width: 100% !important;
        white-space: normal !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        line-height: 1.2;
      }
      .glass-card ul {
        font-size: 0.75rem !important;
        line-height: 1.3 !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        word-break: break-word !important;
        white-space: normal !important;
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important; /* <<< Center contents vertically */
        align-items: center !important;      /* <<< Center contents horizontally */
        text-align: center !important;       /* <<< Center text */
      }
      .glass-card li {
        margin-bottom: 0.13em !important;
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        max-width: 100% !important;
        font-size: inherit !important;
        color: #fff !important;
        background: transparent !important;
        text-align: center !important;
      }
      .glass-card li:last-child {
        margin-bottom: 0 !important;
      }
    }
    .glass-card {
      background: rgba(255,255,255,0.07);
      border: 2.5px solid rgba(244,75,18,0.15);
      border-radius: 16px;
      min-width: 0;
      min-height: 300px;
      max-width: 100%;
      width: 100%;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      padding: 2.6rem 1.5rem 2.6rem 1.5rem;
      margin: 0 auto;
      box-shadow: 0 10px 40px 0 rgba(44,44,44,0.07);
      overflow: hidden;
      z-index: 2;
      transition: box-shadow 0.22s, border 0.12s, transform 0.14s, background 0.15s, height 0.13s, padding 0.13s;
      animation: glassFadeIn 0.93s cubic-bezier(.22,1.08,.29,1.01) both;
      opacity: 0;
      cursor: pointer;
      position: relative;
      will-change: transform, box-shadow, opacity;
      animation-delay: var(--delay, 0s);
      text-align: center;
      font-size: 1.05rem;
      backdrop-filter: blur(12px) saturate(1.37) brightness(1.13);
      -webkit-backdrop-filter: blur(12px) saturate(1.37) brightness(1.13);
      border-radius: 16px !important;
    }
    @keyframes glassFadeIn {
      0% { opacity:0; filter: blur(12px) brightness(0.82);}
      80% { opacity:.6; filter: blur(2px) brightness(1.04);}
      100% { opacity:1; filter: none;}
    }
    .glass-card:hover {
      box-shadow: 0 24px 64px 0 rgba(44,44,44,0.13), 0 0 0 6px #F44B1210;
      border: 2.5px solid #F44B12;
      background: rgba(255,255,255,0.16);
      transform: translateY(-8px) scale(1.027) rotate(-0.5deg);
    }
    .glass-card .title {
      font-family: 'Montserrat', Arial, sans-serif;
      font-size: 2.12rem;
      font-weight: 900;
      margin-bottom: 1.1em;
      text-align: center;
      color: #F44B12;
      background: transparent;
      text-shadow: none;
      display: inline-block;
      line-height: 1.09;
      letter-spacing: 0.018em;
      text-transform: capitalize;
      width: 100%;
      word-break: break-word;
      overflow-wrap: break-word;
      hyphens: auto;
      backdrop-filter: blur(0.5px) brightness(1.08);
      -webkit-backdrop-filter: blur(0.5px) brightness(1.08);
    }
    .glass-card ul {
      margin: 0;
      padding: 0;
      list-style: none;
      color: #fff;
      font-size: 1.13rem;
      font-weight: 500;
      opacity: 0.99;
      text-align: center;
      line-height: 2.1;
      word-break: break-word;
      width: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }
    .glass-card li {
      margin-bottom: 0.33em;
      line-height: 1.7;
      font-weight: 500;
      letter-spacing: 0.01em;
      text-shadow: none;
      opacity: 0.95;
      transition: color 0.15s;
      color: #fff;
      background: transparent;
      text-align: center;
    }
    .glass-card li:last-child {
      margin-bottom: 0;
    }
  </style>
  <div class="text-center mb-5">
    <div class="section-heading">
        <span class="heading-our">Our</span>
        <span class="heading-expertise">Expertise</span>
    </div>
  </div>
  <div class="expertise-list">
    <?php
    // Fetch expertise/services from the database only
    $expertise = getServices(); // Should return an array of ['title'=>..., 'description'=>...]
    if (!empty($expertise)) {
      foreach(array_slice($expertise,0,4) as $idx => $exp) {
        $delay = 0.08 * $idx;
        // Fix SQL encoded chars (&amp; etc) in title for display
        $title = html_entity_decode($exp['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        echo '<div class="glass-card" style="--delay:'.$delay.'s">';
        echo '<div class="title">'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</div>';
        echo '<ul>';
        $points = preg_split('/\r\n|\r|\n/', $exp['description']);
        foreach($points as $p) {
          if(trim($p)!=="") echo '<li>'.htmlspecialchars($p).'</li>';
        }
        echo '</ul>';
        echo '</div>';
      }
    } else {
      echo '<div style="color:#fff;text-align:center;width:100%;font-size:1.15rem;">No expertise/services found.</div>';
    }
    ?>
  </div>
</section>

  <!-- BRANDS SECTION (fullscreen, multi-dots animation) -->
 <section class="brands-section section-overlap my-8" style="position:relative; margin-top:-3.8rem; z-index:11;">
  <style>
    .brands-section {
      background: #fff;
      /* Big squircle/rounded-rectangle top, smaller bottom, strong overlap illusion */
      border-radius: 64px 64px 22px 22px / 56px 56px 16px 16px;
      max-width: 100vw;
      width: 100vw;
      margin: 0 auto 2.9rem auto;
      padding: 3.2rem 0 2.1rem 0;
      position: relative;
      box-shadow: 0 20px 54px 0 rgba(44,44,44,0.08), 0 12px 38px 0 rgba(44,44,44,0.14);
      overflow: visible;
      z-index: 11;
    }
    .brands-heading {
      font-family:'Montserrat', Arial, sans-serif;
      font-size: 2.1rem;
      font-weight: 900;
      border-radius: 10px;
      padding: 7px 26px;
      margin-bottom: 1.5rem;
      background: none;
      display: inline-block;
      box-shadow: 1px 1px 18px rgba(244, 75, 18, 0.08);
      color: #111;
      border: 1.6px dashed #111;
      letter-spacing: -1px;
    }
    .brands-grid {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 2.1rem 0;
      position: relative;
      align-items: center;
    }
    .brands-row {
      display: flex;
      justify-content: center;
      gap: 4vw;
      margin-bottom: 1.25rem;
      flex-wrap: nowrap;
      width: 100vw;
      max-width: 100vw;
      padding: 0 1vw;
    }
    .brands-logo {
      filter: grayscale(1);
      transition: filter 0.3s, transform 0.2s, opacity 0.4s;
      max-height: 100px;
      max-width: 180px;
      min-width: 70px;
      object-fit: contain;
      padding: 8px 0;
      opacity: 0.80;
      animation: brandLogoFade 3s infinite alternate;
      width: 15vw;
      height: auto;
      background: none;
      border: none;
      display: block;
    }
    @keyframes brandLogoFade {
      0%, 100% { opacity: 0.80; }
      50% { opacity: 1; filter: none;}
    }
    .brands-logo:hover { filter: none; opacity: 1; transform: scale(1.11);}
    .brand-dot {
      width: 16px; height: 16px; border-radius: 50%; background: var(--orange,#F44B12);
      opacity: 0.13; 
      position: absolute;
      animation: fadeDot 2.4s infinite alternate, multiDotMove 10s infinite linear;
      z-index: 2;
    }
    @keyframes fadeDot { 0% { opacity: 0.10; } 100% { opacity: 0.22; } }
    @keyframes multiDotMove {
      0% { transform: scale(1) translateY(0);}
      25% { transform: scale(1.17) translateY(-30px);}
      50% { transform: scale(1.08) translateY(20px);}
      75% { transform: scale(1.12) translateY(-9px);}
      100% { transform: scale(1) translateY(0);}
    }
    .brand-dot.dot2 { left: 20vw; top: 13vh; background: #F44B12; animation-delay: .2s, 1.2s;}
    .brand-dot.dot3 { left: 70vw; top: 11vh; background: #F88F54; animation-delay: .7s, 2.1s;}
    .brand-dot.dot4 { left: 40vw; top: 15vh; background: #F44B12; animation-delay: 1.5s, 3s;}
    .brand-dot.dot5 { left: 80vw; top: 23vh; background: #FFB067; animation-delay: 2.1s, 5.5s;}
    .brand-dot.dot6 { left: 10vw; top: 27vh; background: #F88F54; animation-delay: 1.8s, 6.1s;}
    @media (max-width: 1200px) {
      .brands-logo {
        max-width: 108px;
        max-height: 60px;
        width: 19vw;
        min-width: 58px;
      }
      .brands-row { gap: 2.4vw;}
      .brands-section { border-radius: 38px 38px 14px 14px / 32px 32px 8px 8px;}
    }
    @media (max-width: 700px) {
      .brands-logo {
        max-width: 32vw;
        min-width: 16vw;
        max-height: 38px;
        width: 32vw;
        padding: 0;
      }
      .brands-row { gap: 1vw; margin-bottom: 1rem;}
      .brands-section { border-radius: 22px 22px 0 0 / 18px 18px 0 0;}
      .brands-grid { gap: 1.1rem 0;}
      .brands-heading { font-size: 1.18rem; padding: 6px 7vw;}
    }
    @media (max-width: 420px) {
      .brands-heading { font-size: 1.07rem; padding: 5px 4vw;}
      .brands-logo { max-width: 44vw; min-width: 12vw; }
      .brands-row { gap: 0.6vw; margin-bottom: 0.7rem; }
    }
  </style>
  <div class="text-center mb-8">
    <div class="brands-heading">Brands We've <span style="color:#111; font-family:'Montserrat', Arial, sans-serif; font-weight:900;">Worked With</span></div>
  </div>
  <div class="brands-grid">
    <?php
    $brandLogos = getBrandLogos();
    
    // Debug output
    if (empty($brandLogos)) {
      echo '<div style="text-align:center; color:#F44B12; padding:20px;">Loading brands...</div>';
    } else {
      // Pattern: 4,3,4,3,4,3,...
      $pattern = [4,3];
      $k = 0;
      $rowCount = 0;
      while ($k < count($brandLogos)) {
        $row = $pattern[$rowCount % 2];
        echo '<div class="brands-row">';
        for ($j = 0; $j < $row && $k < count($brandLogos); $j++, $k++) {
          $logo = $brandLogos[$k];
          $logoPath = htmlspecialchars($logo['logo_path']);
          $brandName = htmlspecialchars($logo['brand_name']);
          echo '<img src="'.$logoPath.'" alt="'.$brandName.'" class="brands-logo" onerror="console.log(\'Failed to load:\', this.src);">';
        }
        echo '</div>';
        $rowCount++;
      }
    }
    ?>
  </div>
</section>
  <!-- Responsive Featured Work Section: Modern Carousel for Web Designer/Animator -->
 <!-- Responsive Featured Work Section: Modern Carousel with Pro-Level Colors, Layout, and Button Design -->
<section class="featured-section section-overlap relative" style="background:#232323; border-radius:32px; overflow:hidden; min-height:670px; padding:0;">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet">
<style>
  :root {
    --main-dark: #232323;
    --main-accent: #F44B12;
    --main-accent-dark: #cc390d;
    --main-accent-light: #ff6a30;
    --main-light: #fff;
    --main-grey: #252525;
    --main-btn-bg: #252525;
    --main-btn-bg-hover: #F44B12;
    --main-btn-text: #fff;
    --main-btn-border: #F44B12;
  }
  .featured-3d-heading {
    display: flex; justify-content: center; align-items: center;
    margin-top: 36px; margin-bottom: 0.2em;
  }
  .featured-3d-heading-text {
    color: var(--main-accent); font-family: 'Montserrat', Arial, sans-serif; font-weight: 900;
    font-size: 2.3rem; border: 2px solid var(--main-accent); border-radius: 0;
    padding: 10px 28px; background: var(--main-btn-bg); display: inline-block;
    letter-spacing: 0.09em; text-align: center; text-transform: uppercase;
    position: relative;
    box-shadow: 0 2px 12px var(--main-grey);
    text-shadow: 0 2px 6px var(--main-accent-light);
  }
  @media (max-width:700px) {
    .featured-3d-heading-text { font-size: 1.17rem; padding: 8px 15px; }
  }
  .featured-3d-slider-area {
    width: 100vw; max-width: 1840px; margin: 0 auto;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    overflow-x: hidden; box-sizing: border-box;
    padding-bottom: 18px;
  }
  .featured-3d-slider { width: 100%; position: relative; display: flex; flex-direction: column; align-items: center; }
  .featured-3d-arrows {
    display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 42px;
    width: 100%; margin: 0 auto 12px auto;
    position: absolute; top: 54%; left: 0; right: 0; z-index: 20;
    pointer-events: none;
  }
  .featured-3d-arrow {
    width: 60px; height: 60px;
    background: var(--main-btn-bg); border-radius: 50%; border: 3px solid var(--main-btn-border);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; opacity: 1; font-size: 1.45rem;
    transition: box-shadow 0.18s, background 0.12s, opacity 0.12s, border 0.12s;
    outline: none;
    box-shadow: none;
    pointer-events: auto;
    position: relative;
    top: 0;
  }
  .featured-3d-arrow:active,
  .featured-3d-arrow:focus,
  .featured-3d-arrow:hover {
    background: var(--main-accent); color: var(--main-light);
    border: 3px solid var(--main-light);
    box-shadow: 0 2px 18px var(--main-accent-dark);
  }
  .featured-3d-arrow svg {
    width: 1.7em; height: 1.7em; color: var(--main-accent); pointer-events: none; display: block;
    transition: color 0.12s;
  }
  .featured-3d-arrow:active svg, .featured-3d-arrow:focus svg, .featured-3d-arrow:hover svg {
    color: var(--main-light);
  }
  .featured-3d-slider-track {
    width: 100%; min-height: 430px; height: 430px; /* More height for better vertical space */
    display: flex; align-items: center; justify-content: center; gap: 0;
    perspective: 2000px; pointer-events: none; position: relative; margin-bottom: 0;
    transition: height 0.3s;
  }
  .featured-3d-slide {
    position: relative; margin: 0 0px;
    background: var(--main-grey);
    border-radius: 0; /* Sharp edges */
    border: 3px solid var(--main-accent);
    overflow: visible;
    cursor: pointer;
    box-shadow: 0 8px 22px #0006, 0 2px 7px 0 #F44B120a;
    opacity: 0;
    pointer-events: none;
    display: flex; align-items: flex-end; justify-content: center;
    transition:
      width 0.3s cubic-bezier(.22,1,.36,1),
      height 0.3s cubic-bezier(.22,1,.36,1),
      transform 0.38s cubic-bezier(.66,-0.01,.32,1.02),
      box-shadow 0.3s cubic-bezier(.22,1,.36,1),
      opacity 0.18s cubic-bezier(.22,1,.36,1),
      z-index 0.2s;
    will-change: transform, opacity, width, height, box-shadow;
    backface-visibility: hidden;
    width: 220px; height: 390px;
    min-width: 220px; max-width: 220px;
    min-height: 390px; max-height: 390px;
    filter: drop-shadow(0 6px 16px #0008);
  }
  .featured-3d-slide img {
    width: 100%; height: 100%;
    object-fit: cover;
    border-radius: 0;
    display: block; z-index: 1; position: relative;
    pointer-events: none; border: none; box-shadow: none;
    user-select: none;
  }
  .featured-3d-slide--farleft {
    opacity: 1; pointer-events: none; z-index: 1;
    transform: translateX(-325px) scale(0.83) rotateY(55deg);
    filter: brightness(0.65) blur(0.5px) grayscale(0.33);
    box-shadow: 0 2px 7px #1118;
  }
  .featured-3d-slide--left {
    opacity: 1; pointer-events: none; z-index: 3;
    transform: translateX(-160px) scale(0.94) rotateY(32deg);
    filter: brightness(0.85) blur(0.08px);
    box-shadow: 0 4px 14px #0008;
  }
  .featured-3d-slide--current {
    opacity: 1 !important; pointer-events: auto; z-index: 10;
    transform: scale(1.18) rotateY(0deg); width: 310px; height: 470px;
    min-width:310px; max-width:310px; min-height:470px; max-height:470px;
    border: 3px solid var(--main-accent-light);
    box-shadow: 0 14px 55px 0 #111d, 0 0px 12px 0 var(--main-accent), 0 0 0 7px #fff3 inset;
    transition: box-shadow 0.2s, transform 0.2s;
    background: linear-gradient(135deg, #232323 70%, #222 110%);
  }
  .featured-3d-slide--right {
    opacity: 1; pointer-events: none; z-index: 3;
    transform: translateX(160px) scale(0.94) rotateY(-32deg);
    filter: brightness(0.85) blur(0.08px);
    box-shadow: 0 4px 14px #0008;
  }
  .featured-3d-slide--farright {
    opacity: 1; pointer-events: none; z-index: 1;
    transform: translateX(325px) scale(0.83) rotateY(-55deg);
    filter: brightness(0.65) blur(0.5px) grayscale(0.33);
    box-shadow: 0 2px 7px #1118;
  }
  .featured-3d-slide--edge {
    /* At the start/end, cards snap straight and big */
    transform: scale(1.10) rotateY(0deg);
    opacity: 1 !important;
    pointer-events: auto !important;
    box-shadow: 0 10px 32px var(--main-accent-dark);
    border: 3px solid var(--main-accent-light);
    background: linear-gradient(135deg, #232323 70%, #222 110%);
  }
  .featured-3d-slide:not(.featured-3d-slide--farleft):not(.featured-3d-slide--left):not(.featured-3d-slide--current):not(.featured-3d-slide--right):not(.featured-3d-slide--farright):not(.featured-3d-slide--edge) {
    opacity: 0 !important; pointer-events: none !important;
    width: 0 !important; min-width: 0 !important; height: 0 !important; min-height:0 !important; max-height:0 !important;
  }
  .featured-3d-slide-title-overlay {
    position: absolute; left: 0; right: 0; bottom: 0; text-align: center;
    font-family: 'Montserrat', Arial, sans-serif; font-size: 1.13rem; font-weight: 900;
    color: var(--main-light); text-shadow: 0 2px 8px #F44B12b0;
    letter-spacing: 0.05em; background: linear-gradient(0deg, #222c 85%, transparent 100%);
    padding: 14px 6px 10px 6px; border-bottom-left-radius: 0; border-bottom-right-radius: 0;
    opacity: 1; z-index: 2; pointer-events: none; animation: fadein .4s cubic-bezier(.22,1,.29,1.01);
    display: none; user-select: none;
    font-variant: small-caps;
    font-family: 'Montserrat', Arial, sans-serif;
  }
  .featured-3d-slide--current .featured-3d-slide-title-overlay,
  .featured-3d-slide--edge .featured-3d-slide-title-overlay { display: block; }
  @keyframes fadein { from { opacity: 0; transform: translateY(16px);} to { opacity: 1; transform: translateY(0);}
  }
  .featured-3d-viewall-btn {
    display: block; margin: 18px auto 0 auto; padding: 11px 22px;
    background: var(--main-btn-bg); color: var(--main-btn-text); border-radius: 0; border: 2px solid var(--main-btn-border); font-size: 1.1rem;
    font-family: 'Montserrat',sans-serif; font-weight: 900;
    box-shadow: none; text-decoration: none; letter-spacing: 0.08em;
    transition: background 0.14s, color 0.14s, border 0.13s, transform 0.11s; cursor: pointer; opacity: 0.98;
    outline: none; max-width: 155px; min-width: 72px; text-align: center;
  }
  .featured-3d-viewall-btn:hover,
  .featured-3d-viewall-btn:focus {
    background: var(--main-accent); color: var(--main-light);
    border: 2px solid var(--main-light);
    transform: scale(1.06);
  }
  /* Mobile: bigger cards, sharper edges, better arrangement */
  @media (max-width:700px) {
    .featured-3d-slider-area { max-width: 100vw; }
    .featured-3d-slider { min-height: 120px; }
    .featured-3d-arrows {
      gap: 22px;
      top: 54%;
      left: 0; right: 0;
      margin: 0;
      position: absolute;
      z-index: 25;
    }
    .featured-3d-slider-track {
      min-height: 350px; height: 350px; flex-direction: row; align-items: center; justify-content: center; gap: 0;
      perspective: 1200px;
      margin-bottom: 0;
    }
    .featured-3d-slide {
      margin: 0 0px;
      width: 128px !important; max-width: 128px !important; min-width: 128px;
      height: 220px !important; min-height: 220px; max-height: 220px;
    }
    .featured-3d-slide--current,
    .featured-3d-slide--edge {
      width: 196px !important; max-width: 196px !important; min-width: 196px;
      height: 330px !important; min-height: 330px; max-height: 330px;
    }
    .featured-3d-viewall-btn { font-size: 1rem; padding: 8px 16px; max-width: 130px; border-radius: 0; }
    .featured-3d-slide-title-overlay { font-size: 1rem; padding: 10px 2px 10px 2px; border-radius: 0;}
  }
</style>
<div class="featured-3d-heading">
  <span class="featured-3d-heading-text">Featured Work</span>
</div>
<div class="featured-3d-slider-area" style="position:relative;">
  <div class="featured-3d-slider" style="position:relative;">
    <div class="featured-3d-arrows">
      <button class="featured-3d-arrow left" type="button" id="featured3dArrowLeft" aria-label="Previous">
        <svg viewBox="0 0 24 24" fill="none"><path d="M15.5 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <button class="featured-3d-arrow right" type="button" id="featured3dArrowRight" aria-label="Next">
        <svg viewBox="0 0 24 24" fill="none"><path d="M8.5 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
    </div>
    <div class="featured-3d-slider-track" id="featured3dSliderTrack">
      <?php
        $portfolioItems = getPortfolioItems();
        $slides = [];
        
        if (!empty($portfolioItems)) {
          // Limit to 5 items only
          $portfolioItems = array_slice($portfolioItems, 0, 5);
          
          foreach ($portfolioItems as $item) {
            $img = htmlspecialchars($item['thumbnail'] ?? '');
            $title = htmlspecialchars($item['brand_name'] ?? ''); // Use brand_name as title
            $brand = htmlspecialchars($item['brand_name'] ?? '');
            $desc = htmlspecialchars($item['description'] ?? '');
            $descShort = mb_strimwidth(strip_tags($desc), 0, 68, '...');
            $portfolioUrl = "portfolio-detail.php?id=".(isset($item['id']) ? intval($item['id']) : 0);
            $slides[] = [
              'img' => $img,
              'title' => $title,
              'brand' => $brand,
              'desc' => $descShort,
              'url' => $portfolioUrl
            ];
          }
        } else {
          // Fallback slides if no database data
          $slides = [
            ['img' => 'uploads/portfolio/thumbnails/portfolio1.jpg', 'title' => 'Project 1', 'brand' => '', 'desc' => 'Creative project', 'url' => '#'],
            ['img' => 'uploads/portfolio/thumbnails/portfolio2.jpg', 'title' => 'Project 2', 'brand' => '', 'desc' => 'Creative project', 'url' => '#'],
            ['img' => 'uploads/portfolio/thumbnails/portfolio3.jpg', 'title' => 'Project 3', 'brand' => '', 'desc' => 'Creative project', 'url' => '#'],
            ['img' => 'uploads/portfolio/thumbnails/portfolio4.jpg', 'title' => 'Project 4', 'brand' => '', 'desc' => 'Creative project', 'url' => '#'],
            ['img' => 'uploads/portfolio/thumbnails/portfolio5.jpg', 'title' => 'Project 5', 'brand' => '', 'desc' => 'Creative project', 'url' => '#']
          ];
        }
        
        $total = count($slides);
        for ($i = 0; $i < $total; $i++) {
          $slide = $slides[$i];
          echo '<div class="featured-3d-slide" data-slide-idx="'.$i.'" tabindex="0" onclick="window.location.href=\''.$slide['url'].'\'" onkeydown="if(event.key===\'Enter\'){window.location.href=\''.$slide['url'].'\'}">';
          echo '<img src="'.$slide['img'].'" alt="'.$slide['title'].'" onerror="this.src=\'data:image/svg+xml;base64,'.base64_encode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"300\" height=\"400\" viewBox=\"0 0 300 400\"><rect width=\"300\" height=\"400\" fill=\"#252525\"/><text x=\"150\" y=\"200\" text-anchor=\"middle\" fill=\"#F44B12\" font-family=\"Arial\" font-size=\"16\">'.$slide['title'].'</text></svg>').'\'">';
          echo '<div class="featured-3d-slide-title-overlay" style="display:none"></div>';
          echo '</div>';
        }
      ?>
    </div>
    <a href="portfolio-detail.php" class="featured-3d-viewall-btn">View All Portfolio</a>
  </div>
</div>
<script>
  const slides = Array.from(document.querySelectorAll('.featured-3d-slide'));
  let currentIdx = 0;
  const total = slides.length;
  const slideData = [
    <?php foreach ($slides as $slide) {
      echo json_encode($slide).",";
    } ?>
  ];

  function getLoopIdx(i) {
    return ((i % total) + total) % total;
  }

  function update3dSlider() {
    slides.forEach((slide, idx) => {
      slide.classList.remove(
        'featured-3d-slide--current',
        'featured-3d-slide--left',
        'featured-3d-slide--right',
        'featured-3d-slide--farleft',
        'featured-3d-slide--farright',
        'featured-3d-slide--edge'
      );
      slide.style.opacity = "0";
      slide.style.pointerEvents = "none";
      slide.setAttribute('aria-current', 'false');
      const overlay = slide.querySelector('.featured-3d-slide-title-overlay');
      if (overlay) overlay.style.display = "none";
    });

    if (total > 0) {
      // Edge logic for first, second, last, second last cards
      if (
        currentIdx === 0 ||
        currentIdx === 1 ||
        currentIdx === total - 1 ||
        currentIdx === total - 2
      ) {
        slides[currentIdx].classList.add('featured-3d-slide--edge');
        slides[currentIdx].style.opacity = "1";
        slides[currentIdx].style.pointerEvents = "auto";
        slides[currentIdx].setAttribute('aria-current', 'true');
        const overlay = slides[currentIdx].querySelector('.featured-3d-slide-title-overlay');
        if (overlay) {
          overlay.textContent = slideData[currentIdx].title;
          overlay.style.display = "";
        }
        let indexes = [];
        if (currentIdx === 0) {
          indexes = [currentIdx, getLoopIdx(currentIdx+1), getLoopIdx(currentIdx+2), getLoopIdx(currentIdx+3), getLoopIdx(currentIdx+4)];
        } else if (currentIdx === 1) {
          indexes = [getLoopIdx(currentIdx-1), currentIdx, getLoopIdx(currentIdx+1), getLoopIdx(currentIdx+2), getLoopIdx(currentIdx+3)];
        } else if (currentIdx === total-2) {
          indexes = [getLoopIdx(currentIdx-3), getLoopIdx(currentIdx-2), getLoopIdx(currentIdx-1), currentIdx, getLoopIdx(currentIdx+1)];
        } else if (currentIdx === total-1) {
          indexes = [getLoopIdx(currentIdx-4), getLoopIdx(currentIdx-3), getLoopIdx(currentIdx-2), getLoopIdx(currentIdx-1), currentIdx];
        }
        for (let i = 0; i < indexes.length; i++) {
          if (indexes[i] !== currentIdx && slides[indexes[i]]) {
            if (i === 0) slides[indexes[i]].classList.add('featured-3d-slide--farleft');
            if (i === 1) slides[indexes[i]].classList.add('featured-3d-slide--left');
            if (i === 2) slides[indexes[i]].classList.add('featured-3d-slide--right');
            if (i === 3) slides[indexes[i]].classList.add('featured-3d-slide--farright');
            slides[indexes[i]].style.opacity = "1";
          }
        }
      } else {
        // Normal coverflow: 5 cards
        const farleft   = getLoopIdx(currentIdx - 2);
        const left      = getLoopIdx(currentIdx - 1);
        const center    = getLoopIdx(currentIdx);
        const right     = getLoopIdx(currentIdx + 1);
        const farright  = getLoopIdx(currentIdx + 2);

        slides[farleft].classList.add('featured-3d-slide--farleft');
        slides[farleft].style.opacity = "1";
        slides[left].classList.add('featured-3d-slide--left');
        slides[left].style.opacity = "1";
        slides[center].classList.add('featured-3d-slide--current');
        slides[center].style.opacity = "1";
        slides[center].style.pointerEvents = "auto";
        slides[center].setAttribute('aria-current', 'true');
        const overlay = slides[center].querySelector('.featured-3d-slide-title-overlay');
        if (overlay) {
          overlay.textContent = slideData[center].title;
          overlay.style.display = "";
        }
        slides[right].classList.add('featured-3d-slide--right');
        slides[right].style.opacity = "1";
        slides[farright].classList.add('featured-3d-slide--farright');
        slides[farright].style.opacity = "1";
      }
    }
  }

  function featured3dPrev() {
    currentIdx = (currentIdx - 1 + total) % total;
    update3dSlider();
  }
  function featured3dNext() {
    currentIdx = (currentIdx + 1) % total;
    update3dSlider();
  }
  document.getElementById('featured3dArrowLeft').addEventListener('click', featured3dPrev);
  document.getElementById('featured3dArrowRight').addEventListener('click', featured3dNext);

  (() => {
    let startX = 0;
    let dragging = false;
    const track = document.getElementById('featured3dSliderTrack');
    if (!track) return;
    track.addEventListener('touchstart', e => {
      startX = e.touches[0].clientX;
      dragging = true;
    });
    track.addEventListener('touchend', e => {
      if (!dragging) return;
      dragging = false;
      let endX = e.changedTouches[0].clientX;
      if (endX - startX > 30) featured3dPrev();
      else if (startX - endX > 30) featured3dNext();
    });
  })();
  window.addEventListener('DOMContentLoaded', update3dSlider);
  window.addEventListener('resize', update3dSlider);
  window.addEventListener('keydown', function(e){
    if (e.key === 'ArrowLeft') featured3dPrev();
    if (e.key === 'ArrowRight') featured3dNext();
  });
  setInterval(() => featured3dNext(), 5000);
</script>
</section>
  <?php include 'includes/footer.php'; ?>

 