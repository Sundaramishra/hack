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
.portfolio-title {
  text-align: center;
  color: #F44B12;
  font-family: 'Montserrat',sans-serif;
  font-weight: 800;
  font-size: 2.1rem;
  margin-top: 40px;
  margin-bottom: 5px;
  letter-spacing: .2px;
}
.portfolio-sub {
  text-align: center;
  font-size: 1.1rem;
  color: #383838;
  margin-bottom: 30px;
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
  grid-template-rows: 200px 100px 200px;
  gap: 20px 20px;
  position: relative;
  z-index: 1;
}

/* Orange glow effect behind reels (last row) */
.portfolio-grid::after {
  content: '';
  display: block;
  grid-column: 1/-1;
  grid-row: 3;
  position: absolute;
  left: 0; 
  right: 0; 
  bottom: 12px; 
  z-index: 0;
  width: 95%; 
  height: 160px;
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

/* Stories - span from row 1 to row 2 (overlap with posts) */
.pg-story {
  font-size: 1.8rem;
  letter-spacing: 1px;
  grid-row: 1/3;
}

/* Reels - bigger size, same height as big post */
.pg-reel {
  font-size: 1.8rem;
  font-weight: 800;
  min-height: 180px;
}

/* Grid Positioning */
.pg-post.post2 { grid-row: 1; grid-column: 3; }
.pg-post.post3 { grid-row: 1; grid-column: 4; }
.pg-post.post4 { grid-row: 2; grid-column: 1; }
.pg-post.post5 { grid-row: 2; grid-column: 2; }
.pg-story.story1 { grid-row: 1/3; grid-column: 3; }
.pg-story.story2 { grid-row: 1/3; grid-column: 4; }
.pg-reel.reel1 { grid-row: 3; grid-column: 1; }
.pg-reel.reel2 { grid-row: 3; grid-column: 2; }
.pg-reel.reel3 { grid-row: 3; grid-column: 3; }
.pg-reel.reel4 { grid-row: 3; grid-column: 4; }

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
    grid-template-columns: 1fr 1fr; 
    grid-template-rows: 140px 140px 120px 120px 100px;
    gap: 10px;
    max-width: 100%;
  }
  
  /* Mobile Layout - Simplified */
  .pg-bigpost { 
    grid-row: 1/3; 
    grid-column: 1/2; 
    font-size: 1.8rem;
  }
  .pg-post.post2 { grid-row: 1; grid-column: 2; font-size: 1.2rem; }
  .pg-post.post3 { grid-row: 2; grid-column: 2; font-size: 1.2rem; }
  .pg-post.post4 { grid-row: 3; grid-column: 1; font-size: 1.2rem; }
  .pg-post.post5 { grid-row: 3; grid-column: 2; font-size: 1.2rem; }
  .pg-story.story1 { grid-row: 4; grid-column: 1/3; font-size: 1.4rem; }
  .pg-story.story2 { display: none; }
  .pg-reel.reel1 { grid-row: 5; grid-column: 1; font-size: 1.1rem; }
  .pg-reel.reel2 { grid-row: 5; grid-column: 2; font-size: 1.1rem; }
  .pg-reel.reel3 { display: none; }
  .pg-reel.reel4 { display: none; }
  
  .portfolio-grid::after {
    grid-row: 5;
    height: 50px;
  }
}

@media (max-width: 480px) {
  .portfolio-wrap {
    padding: 0 10px 40px 10px;
  }
  
  .portfolio-title {
    font-size: 1.8rem;
    margin-top: 20px;
  }
  
  .portfolio-sub {
    font-size: 1rem;
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
  <div class="portfolio-title">Portfolio</div>
  <div class="portfolio-sub">Vartak's Competitive Academy</div>
  
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
    <div class="pg-bigpost">Post</div>
    <div class="pg-post post2 small">Post</div>
    <div class="pg-post post3 small">Post</div>
    <div class="pg-post post4 small">Post</div>
    <div class="pg-post post5 small">Post</div>
    <div class="pg-story story1">Story</div>
    <div class="pg-story story2">Story</div>
    <div class="pg-reel reel1">Reel</div>
    <div class="pg-reel reel2">Reel</div>
    <div class="pg-reel reel3">Reel</div>
    <div class="pg-reel reel4">Reel</div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>