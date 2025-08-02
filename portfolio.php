<?php include 'includes/header.php'; ?>

<!-- Montserrat font for perfect match -->
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
  margin: 0 auto 0 auto;
  padding: 0 40px 40px 40px;
  font-family: 'Montserrat',sans-serif;
  background: #000;
  min-height: 100vh;
}

.portfolio-header-section {
  background: #fff;
  padding: 60px 40px;
  margin: -40px -40px 40px -40px;
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

/* PORTFOLIO GRID - Proper Responsive Layout */
.portfolio-grid {
  margin: 0 auto;
  max-width: 100%;
  width: 100%;
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  grid-template-rows: 25vh 20vh 25vh 50vh;
  gap: 20px 20px;
  position: relative;
  z-index: 1;
  min-height: 120vh;
}

/* Orange glow effect behind reels (last row) */
.portfolio-grid::after {
  content: '';
  display: block;
  grid-column: 1/-1;
  grid-row: 4;
  position: absolute;
  left: 0; 
  right: 0; 
  bottom: 12px; 
  z-index: 0;
  width: 95%; 
  height: 50vh;
  margin: auto;
  background: radial-gradient(circle, #f44b124c 34%, transparent 85%);
  filter: blur(4px);
}

/* Grid Items Base Styles */
.pg-bigpost, .pg-post, .pg-story, .pg-reel {
  background: #fff;
  color: #222;
  font-weight: 900;
  border-radius: 16px;
  box-shadow: 0 6px 20px #0002;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.pg-bigpost, .pg-post, .pg-story, .pg-reel:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(244, 75, 18, 0.15);
}

/* Big Post - spans 2x2 (2 columns, 2 rows) */
.pg-bigpost {
  font-size: 2.5rem;
  letter-spacing: 1.5px;
  grid-row: 1/3;
  grid-column: 1/3;
}

/* Regular Posts */
.pg-post {
  font-size: 2rem;
  letter-spacing: 1px;
}

.pg-post.small {
  font-size: 1.25rem;
}

/* Stories - span from row 2 to row 3 */
.pg-story {
  font-size: 1.8rem;
  letter-spacing: 1px;
  grid-row: 2/4;
}

/* Reels - bigger size, same height as big post */
.pg-reel {
  font-size: 1.8rem;
  font-weight: 800;
  min-height: 180px;
}

/* Grid Positioning - 4 Rows Layout */
.pg-post.post2 { grid-row: 1; grid-column: 3; }
.pg-post.post3 { grid-row: 1; grid-column: 4; }
.pg-post.post4 { grid-row: 3; grid-column: 1; }
.pg-post.post5 { grid-row: 3; grid-column: 2; }
.pg-story.story1 { grid-row: 2/4; grid-column: 3; }
.pg-story.story2 { grid-row: 2/4; grid-column: 4; }
.pg-reel.reel1 { grid-row: 4; grid-column: 1; }
.pg-reel.reel2 { grid-row: 4; grid-column: 2; }
.pg-reel.reel3 { grid-row: 4; grid-column: 3; }
.pg-reel.reel4 { grid-row: 4; grid-column: 4; }

/* RESPONSIVE DESIGN */
@media (max-width: 900px) {
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
    max-width: 99vw;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    grid-template-rows: 160px 80px 160px;
    gap: 12px;
  }
}

@media (max-width: 670px) {
  .portfolio-wrap {
    padding: 0 15px 40px 15px;
    max-width: 100%;
  }
  
  .portfolio-grid { 
    grid-template-columns: 1fr 1fr 1fr; 
    grid-template-rows: repeat(4, 120px);
    gap: 10px;
    max-width: 100%;
    min-height: auto;
  }
  
  /* Mobile Layout - All Same Size, 11 Total Items (3x4 grid = 12 spaces, 11 items) */
  .pg-bigpost { grid-row: 1; grid-column: 1; font-size: 1.2rem; }
  .pg-post.post2 { grid-row: 1; grid-column: 2; font-size: 1.2rem; }
  .pg-post.post3 { grid-row: 1; grid-column: 3; font-size: 1.2rem; }
  .pg-post.post4 { grid-row: 2; grid-column: 1; font-size: 1.2rem; }
  .pg-post.post5 { grid-row: 2; grid-column: 2; font-size: 1.2rem; }
  .pg-story.story1 { grid-row: 2; grid-column: 3; font-size: 1.2rem; }
  .pg-story.story2 { grid-row: 3; grid-column: 1; font-size: 1.2rem; display: block; }
  .pg-reel.reel1 { grid-row: 3; grid-column: 2; font-size: 1.2rem; }
  .pg-reel.reel2 { grid-row: 3; grid-column: 3; font-size: 1.2rem; }
  .pg-reel.reel3 { grid-row: 4; grid-column: 1; font-size: 1.2rem; display: block; }
  .pg-reel.reel4 { grid-row: 4; grid-column: 2; font-size: 1.2rem; display: block; }
  /* 11th position (4,3) will be empty - that's fine for 11 items */
  
  .portfolio-grid::after {
    display: none;
  }
}

@media (max-width: 768px) {
  .portfolio-header-section {
    padding: 40px 20px;
    margin: -40px -15px 30px -15px;
    border-radius: 0 0 20px 20px;
  }
  
  .portfolio-title {
    font-size: 1.8rem;
  }
  
  .portfolio-sub {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .portfolio-wrap {
    padding: 0 10px 40px 10px;
  }
  
  .portfolio-header-section {
    padding: 30px 15px;
    margin: -10px -10px 20px -10px;
    border-radius: 0 0 15px 15px;
  }
  
  .portfolio-title {
    font-size: 1.6rem;
  }
  
  .portfolio-sub {
    font-size: 0.9rem;
  }
}
  
  .portfolio-grid {
    grid-template-columns: 1fr;
    grid-template-rows: 200px 120px 120px 120px 120px 140px;
    gap: 10px;
  }
  
  /* Single Column Layout */
  .pg-bigpost { grid-row: 1; grid-column: 1; }
  .pg-post.post2 { grid-row: 2; grid-column: 1; }
  .pg-post.post3 { grid-row: 3; grid-column: 1; }
  .pg-post.post4 { grid-row: 4; grid-column: 1; }
  .pg-post.post5 { grid-row: 5; grid-column: 1; }
  .pg-story.story1 { grid-row: 6; grid-column: 1; }
  .pg-story.story2 { display: none; }
  .pg-reel.reel1, .pg-reel.reel2, .pg-reel.reel3, .pg-reel.reel4 { display: none; }
  
  /* Remove glow on mobile */
  .portfolio-grid::after { display: none; }
  
  .pg-bigpost { font-size: 2rem; }
  .pg-post { font-size: 1.5rem; }
  .pg-post.small { font-size: 1.1rem; }
  .pg-story { font-size: 1.5rem; }
}
</style>

<div class="portfolio-wrap">
  <!-- Separate White Background Section for Title & Subtitle -->
  <section class="portfolio-header-section">
    <div class="portfolio-title">Portfolio</div>
    <div class="portfolio-sub">Vartak's Competitive Academy</div>
  </section>
  
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

  <!-- GRID -->
  <div class="portfolio-grid">
    <?php
    // Get portfolio items from database
    $portfolioItems = getPortfolioItems();
    if (!empty($portfolioItems)) {
      // Sort descending by id (newest first)
      usort($portfolioItems, function($a, $b) {
        return $b['id'] <=> $a['id'];
      });
      
      // Take first 11 items for our grid
      $gridItems = array_slice($portfolioItems, 0, 11);
      
      // Map to exact same structure as before
      $item1 = $gridItems[0] ?? null;
      $item2 = $gridItems[1] ?? null;
      $item3 = $gridItems[2] ?? null;
      $item4 = $gridItems[3] ?? null;
      $item5 = $gridItems[4] ?? null;
      $item6 = $gridItems[5] ?? null;
      $item7 = $gridItems[6] ?? null;
      $item8 = $gridItems[7] ?? null;
      $item9 = $gridItems[8] ?? null;
      $item10 = $gridItems[9] ?? null;
      $item11 = $gridItems[10] ?? null;
    }
    ?>
    
    <div class="pg-bigpost">
      <?php echo $item1 ? ((!empty($item1['thumbnail'])) ? '<img src="'.htmlspecialchars($item1['thumbnail']).'" alt="'.htmlspecialchars($item1['brand_name'] ?? 'Post').'" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">' : htmlspecialchars($item1['brand_name'] ?? 'Post')) : 'Post'; ?>
    </div>
    <div class="pg-post post2 small">
      <?php echo $item2 ? ((!empty($item2['thumbnail'])) ? '<img src="'.htmlspecialchars($item2['thumbnail']).'" alt="'.htmlspecialchars($item2['brand_name'] ?? 'Post').'" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">' : htmlspecialchars($item2['brand_name'] ?? 'Post')) : 'Post'; ?>
    </div>
    <div class="pg-post post3 small">
      <?php echo $item3 ? ((!empty($item3['thumbnail'])) ? '<img src="'.htmlspecialchars($item3['thumbnail']).'" alt="'.htmlspecialchars($item3['brand_name'] ?? 'Post').'" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">' : htmlspecialchars($item3['brand_name'] ?? 'Post')) : 'Post'; ?>
    </div>
    <div class="pg-post post4 small">
      <?php echo $item4 ? ((!empty($item4['thumbnail'])) ? '<img src="'.htmlspecialchars($item4['thumbnail']).'" alt="'.htmlspecialchars($item4['brand_name'] ?? 'Post').'" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">' : htmlspecialchars($item4['brand_name'] ?? 'Post')) : 'Post'; ?>
    </div>
    <div class="pg-post post5 small">
      <?php echo $item5 ? ((!empty($item5['thumbnail'])) ? '<img src="'.htmlspecialchars($item5['thumbnail']).'" alt="'.htmlspecialchars($item5['brand_name'] ?? 'Post').'" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">' : htmlspecialchars($item5['brand_name'] ?? 'Post')) : 'Post'; ?>
    </div>
    <div class="pg-story story1">
      <?php echo $item6 ? ((!empty($item6['thumbnail'])) ? '<img src="'.htmlspecialchars($item6['thumbnail']).'" alt="'.htmlspecialchars($item6['brand_name'] ?? 'Story').'" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">' : htmlspecialchars($item6['brand_name'] ?? 'Story')) : 'Story'; ?>
    </div>
    <div class="pg-story story2">
      <?php echo $item7 ? ((!empty($item7['thumbnail'])) ? '<img src="'.htmlspecialchars($item7['thumbnail']).'" alt="'.htmlspecialchars($item7['brand_name'] ?? 'Story').'" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">' : htmlspecialchars($item7['brand_name'] ?? 'Story')) : 'Story'; ?>
    </div>
    <div class="pg-reel reel1">
      <?php echo $item8 ? ((!empty($item8['thumbnail'])) ? '<img src="'.htmlspecialchars($item8['thumbnail']).'" alt="'.htmlspecialchars($item8['brand_name'] ?? 'Reel').'" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">' : htmlspecialchars($item8['brand_name'] ?? 'Reel')) : 'Reel'; ?>
    </div>
    <div class="pg-reel reel2">
      <?php echo $item9 ? ((!empty($item9['thumbnail'])) ? '<img src="'.htmlspecialchars($item9['thumbnail']).'" alt="'.htmlspecialchars($item9['brand_name'] ?? 'Reel').'" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">' : htmlspecialchars($item9['brand_name'] ?? 'Reel')) : 'Reel'; ?>
    </div>
    <div class="pg-reel reel3">
      <?php echo $item10 ? ((!empty($item10['thumbnail'])) ? '<img src="'.htmlspecialchars($item10['thumbnail']).'" alt="'.htmlspecialchars($item10['brand_name'] ?? 'Reel').'" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">' : htmlspecialchars($item10['brand_name'] ?? 'Reel')) : 'Reel'; ?>
    </div>
    <div class="pg-reel reel4">
      <?php echo $item11 ? ((!empty($item11['thumbnail'])) ? '<img src="'.htmlspecialchars($item11['thumbnail']).'" alt="'.htmlspecialchars($item11['brand_name'] ?? 'Reel').'" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">' : htmlspecialchars($item11['brand_name'] ?? 'Reel')) : 'Reel'; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>