<?php include 'includes/header.php'; ?>

<!-- Montserrat font -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap" rel="stylesheet">

<style>
body {
  background: #000 !important;
  margin: 0;
  padding: 0;
}

.portfolio-wrap {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px 40px 40px;
  font-family: 'Montserrat',sans-serif;
  background: #000;
  min-height: 100vh;
}

/* WHITE HEADER SECTION */
.portfolio-header-section {
  background: #fff;
  padding: 60px 0;
  margin: 0 0 40px 0;
  width: 100vw;
  margin-left: calc(-50vw + 50%);
  border-radius: 0 0 30px 30px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.portfolio-title {
  color: #F44B12;
  font-family: 'Montserrat',sans-serif;
  font-weight: 800;
  font-size: 2.1rem;
  margin-bottom: 5px;
  letter-spacing: .2px;
}

.portfolio-sub {
  font-size: 1.1rem;
  color: #383838;
  margin-bottom: 0;
}

/* CLIENT INFO SECTION */
.portfolio-topflex {
  display: flex;
  min-height: 40vh;
  width: 100%;
  border-radius: 30px 30px 0 0;
  background: linear-gradient(120deg, #2d2421 65%, #f44b1237 100%);
  box-shadow: 0 9px 26px #0001;
  margin-bottom: 32px;
  position: relative;
  align-items: stretch;
  gap: 0;
}

.portfolio-blankbox {
  flex: 0 0 30%;
  min-width: 120px;
  background: #232322;
  border-radius: 13px;
  margin: 32px 0 32px 36px;
  box-shadow: 0 2px 15px 0 #0002, 0 0px 2px #fff1 inset;
  border: 1.7px solid #fff2;
  min-height: 140px;
  max-width: 230px;
}

.portfolio-clientinfo {
  flex: 1 1 0;
  padding: 32px 18px 10px 36px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.portfolio-clientinfo .client-label {
  color: #F44B12;
  font-size: 1rem;
  font-weight: bold;
  margin-bottom: 0px;
}

.portfolio-clientinfo .client-title {
  color: #fff;
  font-size: 1.16rem;
  font-weight: bold;
  letter-spacing: .6px;
  margin-bottom: 8px;
}

.portfolio-clientinfo .portfolio-role-label {
  color: #F44B12;
  font-weight: bold;
}

.portfolio-clientinfo .portfolio-role-list {
  display: flex;
  flex-wrap: wrap;
  gap: 23px;
  margin-top: 4px;
}

.portfolio-clientinfo .portfolio-role-list span {
  font-size: .97rem;
  color: #fff;
  font-weight: 600;
}

/* 4x4 GRID LAYOUT */
.portfolio-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  grid-template-rows: 240px 240px 240px 480px;
  gap: 15px;
  max-width: 100%;
  position: relative;
  z-index: 1;
}

/* Orange glow effect */
.portfolio-grid::after {
  content: '';
  position: absolute;
  grid-column: 1/-1;
  grid-row: 4;
  width: 100%;
  height: 480px;
  background: radial-gradient(circle, #f44b124c 34%, transparent 85%);
  filter: blur(6px);
  z-index: 0;
  pointer-events: none;
}

/* Base styles for all grid items */
.pg-item {
  background: #fff;
  color: #222;
  font-weight: 900;
  border-radius: 16px;
  box-shadow: 0 6px 20px #0002;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
  position: relative;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  overflow: hidden;
}

.pg-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(244, 75, 18, 0.15);
}

.pg-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 16px;
}

/* EXACT GRID POSITIONING */

/* Big Post - spans 2x2 (columns 1-2, rows 1-2) - Instagram Post Style */
.pg-bigpost {
  grid-column: 1/3;
  grid-row: 1/3;
  font-size: 3rem;
  border-radius: 20px;
  background: #fff;
  border: 4px solid #e1306c;
  padding: 8px;
}

/* Post2 - Column 3, Row 1 - Instagram Post Style */
.pg-post2 {
  grid-column: 3;
  grid-row: 1;
  font-size: 1.8rem;
  border-radius: 16px;
  background: #fff;
  border: 3px solid #e1306c;
  padding: 6px;
}

/* Post3 - Column 4, Row 1 - Instagram Post Style */
.pg-post3 {
  grid-column: 4;
  grid-row: 1;
  font-size: 1.8rem;
  border-radius: 16px;
  background: #fff;
  border: 3px solid #e1306c;
  padding: 6px;
}

/* Story1 - Column 3, Row 2-3 - Instagram Story Style (Big Size) */
.pg-story1 {
  grid-column: 3;
  grid-row: 2/4;
  font-size: 2rem;
  border-radius: 20px;
  background: #fff;
  border: 4px solid #833ab4;
  position: relative;
  padding: 8px;
}

.pg-story1::before {
  content: '';
  position: absolute;
  top: 12px;
  left: 12px;
  right: 12px;
  bottom: 12px;
  border: 2px solid rgba(131,58,180,0.3);
  border-radius: 16px;
  pointer-events: none;
}

/* Story2 - Column 4, Row 2-3 - Instagram Story Style (Big Size) */
.pg-story2 {
  grid-column: 4;
  grid-row: 2/4;
  font-size: 2rem;
  border-radius: 20px;
  background: #fff;
  border: 4px solid #833ab4;
  position: relative;
  padding: 8px;
}

.pg-story2::before {
  content: '';
  position: absolute;
  top: 12px;
  left: 12px;
  right: 12px;
  bottom: 12px;
  border: 2px solid rgba(131,58,180,0.3);
  border-radius: 16px;
  pointer-events: none;
}

/* Post4 - Column 1, Row 3 - Instagram Post Style */
.pg-post4 {
  grid-column: 1;
  grid-row: 3;
  font-size: 1.8rem;
  border-radius: 16px;
  background: #fff;
  border: 3px solid #e1306c;
  padding: 6px;
}

/* Post5 - Column 2, Row 3 - Instagram Post Style */
.pg-post5 {
  grid-column: 2;
  grid-row: 3;
  font-size: 1.8rem;
  border-radius: 16px;
  background: #fff;
  border: 3px solid #e1306c;
  padding: 6px;
}

/* Reel1 - Column 1, Row 4 - Instagram Reel Style (Big Post Height) */
.pg-reel1 {
  grid-column: 1;
  grid-row: 4;
  font-size: 1.8rem;
  border-radius: 24px;
  background: #fff;
  border: 4px solid #000;
  position: relative;
  padding: 8px;
}

.pg-reel1::after {
  content: '▶';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 3rem;
  color: #000;
  text-shadow: 0 0 10px rgba(0,0,0,0.3);
  pointer-events: none;
}

/* Reel2 - Column 2, Row 4 - Instagram Reel Style (Big Post Height) */
.pg-reel2 {
  grid-column: 2;
  grid-row: 4;
  font-size: 1.8rem;
  border-radius: 24px;
  background: #fff;
  border: 4px solid #000;
  position: relative;
  padding: 8px;
}

.pg-reel2::after {
  content: '▶';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 3rem;
  color: #000;
  text-shadow: 0 0 10px rgba(0,0,0,0.3);
  pointer-events: none;
}

/* Reel3 - Column 3, Row 4 - Instagram Reel Style (Big Post Height) */
.pg-reel3 {
  grid-column: 3;
  grid-row: 4;
  font-size: 1.8rem;
  border-radius: 24px;
  background: #fff;
  border: 4px solid #000;
  position: relative;
  padding: 8px;
}

.pg-reel3::after {
  content: '▶';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 3rem;
  color: #000;
  text-shadow: 0 0 10px rgba(0,0,0,0.3);
  pointer-events: none;
}

/* Reel4 - Column 4, Row 4 - Instagram Reel Style (Big Post Height) */
.pg-reel4 {
  grid-column: 4;
  grid-row: 4;
  font-size: 1.8rem;
  border-radius: 24px;
  background: #fff;
  border: 4px solid #000;
  position: relative;
  padding: 8px;
}

.pg-reel4::after {
  content: '▶';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 3rem;
  color: #000;
  text-shadow: 0 0 10px rgba(0,0,0,0.3);
  pointer-events: none;
}

/* RESPONSIVE DESIGN */
@media (max-width: 768px) {
  .portfolio-wrap {
    padding: 0 15px 40px 15px;
  }
  
  .portfolio-header-section {
    padding: 40px 20px;
    border-radius: 0 0 20px 20px;
  }
  
  .portfolio-title {
    font-size: 1.8rem;
  }
  
  .portfolio-sub {
    font-size: 1rem;
  }
  
  .portfolio-topflex {
    flex-direction: column;
    min-height: unset;
    border-radius: 22px;
  }
  
  .portfolio-blankbox {
    margin: 22px auto 12px auto;
  }
  
  .portfolio-clientinfo {
    padding: 8px 9px;
  }
  
  .portfolio-grid {
    grid-template-columns: 1fr;
    grid-template-rows: repeat(11, 70vh);
    gap: 10px;
  }
  
  /* Mobile positioning - All 11 items in single column stack */
  .pg-bigpost { grid-column: 1; grid-row: 1; font-size: 1.5rem; }
  .pg-post2 { grid-column: 1; grid-row: 2; font-size: 1.2rem; }
  .pg-post3 { grid-column: 1; grid-row: 3; font-size: 1.2rem; }
  .pg-story1 { grid-column: 1; grid-row: 4; font-size: 1.2rem; }
  .pg-story2 { grid-column: 1; grid-row: 5; font-size: 1.2rem; }
  .pg-post4 { grid-column: 1; grid-row: 6; font-size: 1.2rem; }
  .pg-post5 { grid-column: 1; grid-row: 7; font-size: 1.2rem; }
  .pg-reel1 { grid-column: 1; grid-row: 8; font-size: 1.2rem; }
  .pg-reel2 { grid-column: 1; grid-row: 9; font-size: 1.2rem; }
  .pg-reel3 { grid-column: 1; grid-row: 10; font-size: 1.2rem; }
  .pg-reel4 { grid-column: 1; grid-row: 11; font-size: 1.2rem; }
  
  .portfolio-grid::after {
    display: none;
  }
}

@media (max-width: 480px) {
  .portfolio-header-section {
    padding: 30px 15px;
    border-radius: 0 0 15px 15px;
  }
  
  .portfolio-title {
    font-size: 1.6rem;
  }
  
  .portfolio-sub {
    font-size: 0.9rem;
  }
}
</style>

<div class="portfolio-wrap">
  <!-- White Header Section -->
  <section class="portfolio-header-section">
    <div class="portfolio-title">Portfolio</div>
    <div class="portfolio-sub">Vartak's Competitive Academy</div>
  </section>
  
  <!-- Client Info Section -->
  <div class="portfolio-topflex">
    <div class="portfolio-blankbox"></div>
    <div class="portfolio-clientinfo">
      <div class="client-label">Client</div>
      <div class="client-title">VARTAK'S COMPETITIVE ACADEMY</div>
      <div style="margin-bottom:6px;">
        <span class="portfolio-role-label">Our Role</span>
        <div class="portfolio-role-list" style="margin-top:4px;">
          <span>SOCIAL MEDIA MANAGEMENT</span>
          <span>DESIGNING</span>
          <span>INSTITUTE AWARENESS THROUGH ADS</span>
        </div>
      </div>
    </div>
  </div>

  <!-- 4x4 GRID -->
  <div class="portfolio-grid">
    <?php
    // Safe getter function (same as portfolio-detail.php)
    function safe($arr, $key, $default = '') {
        return (isset($arr[$key]) && $arr[$key] !== null && trim($arr[$key]) !== '') ? trim($arr[$key]) : $default;
    }
    
    // Get portfolio items from database
    $portfolioItems = getPortfolioItems();
    if (!empty($portfolioItems)) {
      // Sort descending by id (newest first)
      usort($portfolioItems, function($a, $b) {
        return $b['id'] <=> $a['id'];
      });
      
      // Take first 11 items for our grid
      $gridItems = array_slice($portfolioItems, 0, 11);
    } else {
      $gridItems = [];
    }
    
    // Create grid slots with proper media types (same logic as portfolio-detail.php)
    $grid_slots = [];
    foreach ($gridItems as $index => $item) {
      if (!$item) continue;
      
      switch($index) {
        case 0: // Big Post
          $media_src = safe($item, 'photo1') ?: safe($item, 'thumbnail');
          $grid_slots[] = ['type' => 'img', 'src' => $media_src, 'class' => 'pg-bigpost', 'alt' => 'Portfolio Main Post'];
          break;
        case 1: // Post2
          $media_src = safe($item, 'photo2') ?: safe($item, 'thumbnail');
          $grid_slots[] = ['type' => 'img', 'src' => $media_src, 'class' => 'pg-post2', 'alt' => 'Portfolio Image'];
          break;
        case 2: // Post3
          $media_src = safe($item, 'photo3') ?: safe($item, 'thumbnail');
          $grid_slots[] = ['type' => 'img', 'src' => $media_src, 'class' => 'pg-post3', 'alt' => 'Portfolio Image'];
          break;
        case 3: // Story1
          $media_src = safe($item, 'video_story1');
          $media_type = $media_src ? 'video' : 'img';
          if (!$media_src) $media_src = safe($item, 'thumbnail');
          $grid_slots[] = ['type' => $media_type, 'src' => $media_src, 'class' => 'pg-story1', 'alt' => 'Portfolio Story'];
          break;
        case 4: // Story2
          $media_src = safe($item, 'video_story2');
          $media_type = $media_src ? 'video' : 'img';
          if (!$media_src) $media_src = safe($item, 'thumbnail');
          $grid_slots[] = ['type' => $media_type, 'src' => $media_src, 'class' => 'pg-story2', 'alt' => 'Portfolio Story'];
          break;
        case 5: // Post4
          $media_src = safe($item, 'photo4') ?: safe($item, 'thumbnail');
          $grid_slots[] = ['type' => 'img', 'src' => $media_src, 'class' => 'pg-post4', 'alt' => 'Portfolio Image'];
          break;
        case 6: // Post5 (using photo1 from next item or thumbnail)
          $media_src = safe($item, 'photo1') ?: safe($item, 'thumbnail');
          $grid_slots[] = ['type' => 'img', 'src' => $media_src, 'class' => 'pg-post5', 'alt' => 'Portfolio Image'];
          break;
        case 7: // Reel1
          $media_src = safe($item, 'reel1');
          $media_type = $media_src ? 'video' : 'img';
          if (!$media_src) $media_src = safe($item, 'thumbnail');
          $grid_slots[] = ['type' => $media_type, 'src' => $media_src, 'class' => 'pg-reel1', 'alt' => 'Portfolio Reel'];
          break;
        case 8: // Reel2
          $media_src = safe($item, 'reel2');
          $media_type = $media_src ? 'video' : 'img';
          if (!$media_src) $media_src = safe($item, 'thumbnail');
          $grid_slots[] = ['type' => $media_type, 'src' => $media_src, 'class' => 'pg-reel2', 'alt' => 'Portfolio Reel'];
          break;
        case 9: // Reel3
          $media_src = safe($item, 'reel3');
          $media_type = $media_src ? 'video' : 'img';
          if (!$media_src) $media_src = safe($item, 'thumbnail');
          $grid_slots[] = ['type' => $media_type, 'src' => $media_src, 'class' => 'pg-reel3', 'alt' => 'Portfolio Reel'];
          break;
        case 10: // Reel4
          $media_src = safe($item, 'reel4');
          $media_type = $media_src ? 'video' : 'img';
          if (!$media_src) $media_src = safe($item, 'thumbnail');
          $grid_slots[] = ['type' => $media_type, 'src' => $media_src, 'class' => 'pg-reel4', 'alt' => 'Portfolio Reel'];
          break;
      }
    }
    
    // Render grid slots
    foreach ($grid_slots as $slot) {
      if (!$slot['src']) continue;
      
      $src = htmlspecialchars($slot['src']);
      $class = htmlspecialchars($slot['class']);
      $alt = htmlspecialchars($slot['alt']);
      
      echo '<div class="pg-item ' . $class . '">';
      if ($slot['type'] === 'img') {
        echo '<img src="' . $src . '" alt="' . $alt . '" loading="lazy" />';
      } elseif ($slot['type'] === 'video') {
        echo '<video autoplay muted loop playsinline preload="metadata" aria-label="' . $alt . '">';
        echo '<source src="' . $src . '" type="video/mp4">';
        echo '</video>';
      }
      echo '</div>';
    }
    
    // Fill remaining slots if less than 11 items
    $remaining_slots = 11 - count($grid_slots);
    $slot_classes = ['pg-bigpost', 'pg-post2', 'pg-post3', 'pg-story1', 'pg-story2', 'pg-post4', 'pg-post5', 'pg-reel1', 'pg-reel2', 'pg-reel3', 'pg-reel4'];
    
    for ($i = count($grid_slots); $i < 11; $i++) {
      echo '<div class="pg-item ' . $slot_classes[$i] . '">';
      echo '<div style="color: #666; font-size: 1rem;">No Media</div>';
      echo '</div>';
    }
    ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>