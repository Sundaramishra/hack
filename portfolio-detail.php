<?php include 'includes/header.php'; ?>

<style>
:root {
  --orange: #F44B12;
  --dark: #2B2B2A;
  --light-gray: #f8f9fa;
  --border-gray: #e0e0e0;
}

body {
  background: #fff;
  color: var(--dark);
  margin: 0;
  font-family: 'Montserrat', Arial, sans-serif;
}

.portfolio-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px 20px;
}

/* Portfolio Header */
.portfolio-header {
  text-align: center;
  margin-bottom: 60px;
}

.portfolio-title {
  font-size: 3.5rem;
  font-weight: 900;
  color: var(--dark);
  margin-bottom: 15px;
  letter-spacing: -1px;
}

.portfolio-subtitle {
  font-size: 1.8rem;
  font-weight: 600;
  color: var(--orange);
  margin-bottom: 0;
}

/* Client Info Section - 40vh height */
.client-section {
  height: 40vh;
  min-height: 300px;
  display: flex;
  gap: 30px;
  margin-bottom: 60px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

.client-blank-box {
  width: 30%;
  background: linear-gradient(135deg, var(--orange), #ff6a30);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.client-blank-box::before {
  content: '';
  position: absolute;
  width: 100px;
  height: 100px;
  border: 3px solid rgba(255,255,255,0.3);
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.7; }
  50% { transform: scale(1.1); opacity: 1; }
}

.client-info {
  width: 70%;
  background: #fff;
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.client-name {
  font-size: 2.5rem;
  font-weight: 900;
  color: var(--dark);
  margin-bottom: 10px;
}

.client-description {
  font-size: 1.2rem;
  color: #666;
  line-height: 1.6;
  margin-bottom: 30px;
}

.our-role {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--orange);
  margin-bottom: 15px;
}

.role-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.role-list li {
  font-size: 1rem;
  color: var(--dark);
  padding: 5px 0;
  border-bottom: 1px solid var(--border-gray);
}

.role-list li:last-child {
  border-bottom: none;
}

/* Portfolio Grid */
.portfolio-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  grid-template-rows: auto auto auto;
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

/* Grid Items */
.grid-item {
  border-radius: 15px;
  overflow: hidden;
  position: relative;
  background: var(--light-gray);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.grid-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 40px rgba(244, 75, 18, 0.2);
}

.grid-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* iPhone 5.5 inch size reference: ~375px width */
.post-large {
  grid-row: span 2;
  height: 600px; /* iPhone 5.5 inch height equivalent */
}

.post-small {
  height: 285px; /* Half of large post */
}

.story {
  grid-column: span 2;
  height: 285px;
  background: linear-gradient(45deg, var(--orange), #ff6a30);
  position: relative;
}

.story::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(244, 75, 18, 0.3));
  border-radius: 15px;
}

.reel {
  height: 285px;
  background: linear-gradient(135deg, #2a2a2a, #1a1a1a);
  position: relative;
  overflow: hidden;
}

.reel::after {
  content: '';
  position: absolute;
  inset: -20px;
  background: radial-gradient(circle, var(--orange), transparent 70%);
  opacity: 0.3;
  z-index: -1;
  filter: blur(20px);
}

.reel-overlay {
  position: absolute;
  bottom: 15px;
  left: 15px;
  right: 15px;
  color: white;
  font-size: 0.9rem;
  font-weight: 600;
}

/* Grid Layout */
.grid-item:nth-child(1) { /* Large Post */
  grid-column: 1;
  grid-row: 1 / span 2;
}

.grid-item:nth-child(2) { /* Small Post 1 */
  grid-column: 2;
  grid-row: 1;
}

.grid-item:nth-child(3) { /* Small Post 2 */
  grid-column: 3;
  grid-row: 1;
}

.grid-item:nth-child(4) { /* Small Post 3 */
  grid-column: 2;
  grid-row: 2;
}

.grid-item:nth-child(5) { /* Small Post 4 */
  grid-column: 3;
  grid-row: 2;
}

.grid-item:nth-child(6) { /* Story */
  grid-column: 1 / span 2;
  grid-row: 3;
}

.grid-item:nth-child(7) { /* Reel 1 */
  grid-column: 3;
  grid-row: 3;
}

.grid-item:nth-child(8) { /* Story 2 */
  grid-column: 1 / span 2;
  grid-row: 4;
}

.grid-item:nth-child(9) { /* Reel 2 */
  grid-column: 3;
  grid-row: 4;
}

.grid-item:nth-child(10) { /* Reel 3 */
  grid-column: 1;
  grid-row: 5;
}

.grid-item:nth-child(11) { /* Reel 4 */
  grid-column: 2;
  grid-row: 5;
}

.grid-item:nth-child(12) { /* Reel 5 */
  grid-column: 3;
  grid-row: 5;
}

/* Content Labels */
.content-label {
  position: absolute;
  top: 15px;
  left: 15px;
  background: rgba(0,0,0,0.7);
  color: white;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.label-post { background: var(--orange); }
.label-story { background: #e91e63; }
.label-reel { background: #9c27b0; }

/* Responsive Design */
@media (max-width: 1200px) {
  .portfolio-container {
    padding: 30px 15px;
  }
  
  .portfolio-title {
    font-size: 2.8rem;
  }
  
  .portfolio-subtitle {
    font-size: 1.5rem;
  }
}

@media (max-width: 968px) {
  .client-section {
    flex-direction: column;
    height: auto;
    min-height: auto;
  }
  
  .client-blank-box {
    width: 100%;
    height: 200px;
  }
  
  .client-info {
    width: 100%;
    padding: 30px;
  }
  
  .client-name {
    font-size: 2rem;
  }
  
  .portfolio-grid {
    grid-template-columns: 1fr 1fr;
    gap: 15px;
  }
  
  .post-large {
    height: 500px;
  }
  
  .post-small {
    height: 235px;
  }
  
  .story {
    height: 235px;
  }
  
  .reel {
    height: 235px;
  }
  
  /* Tablet Grid Layout */
  .grid-item:nth-child(1) { /* Large Post */
    grid-column: 1;
    grid-row: 1 / span 2;
  }
  
  .grid-item:nth-child(2) { /* Small Post 1 */
    grid-column: 2;
    grid-row: 1;
  }
  
  .grid-item:nth-child(3) { /* Small Post 2 */
    grid-column: 2;
    grid-row: 2;
  }
  
  .grid-item:nth-child(4) { /* Story */
    grid-column: 1 / span 2;
    grid-row: 3;
  }
  
  .grid-item:nth-child(5) { /* Small Post 3 */
    grid-column: 1;
    grid-row: 4;
  }
  
  .grid-item:nth-child(6) { /* Small Post 4 */
    grid-column: 2;
    grid-row: 4;
  }
  
  .grid-item:nth-child(7) { /* Reel 1 */
    grid-column: 1;
    grid-row: 5;
  }
  
  .grid-item:nth-child(8) { /* Reel 2 */
    grid-column: 2;
    grid-row: 5;
  }
  
  .grid-item:nth-child(9) { /* Story 2 */
    grid-column: 1 / span 2;
    grid-row: 6;
  }
  
  .grid-item:nth-child(10) { /* Reel 3 */
    grid-column: 1;
    grid-row: 7;
  }
  
  .grid-item:nth-child(11) { /* Reel 4 */
    grid-column: 2;
    grid-row: 7;
  }
  
  .grid-item:nth-child(12) { /* Reel 5 */
    grid-column: 1 / span 2;
    grid-row: 8;
  }
}

@media (max-width: 768px) {
  .portfolio-title {
    font-size: 2.2rem;
  }
  
  .portfolio-subtitle {
    font-size: 1.3rem;
  }
  
  .client-info {
    padding: 25px;
  }
  
  .client-name {
    font-size: 1.8rem;
  }
  
  .portfolio-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .post-large {
    height: 400px;
  }
  
  .post-small {
    height: 200px;
  }
  
  .story {
    height: 180px;
  }
  
  .reel {
    height: 200px;
  }
  
  /* Mobile Grid Layout - Single Column */
  .grid-item:nth-child(n) {
    grid-column: 1;
    grid-row: auto;
  }
}

@media (max-width: 480px) {
  .portfolio-container {
    padding: 20px 10px;
  }
  
  .portfolio-title {
    font-size: 1.8rem;
  }
  
  .portfolio-subtitle {
    font-size: 1.1rem;
  }
  
  .client-info {
    padding: 20px;
  }
  
  .client-name {
    font-size: 1.5rem;
  }
  
  .client-description {
    font-size: 1rem;
  }
  
  .post-large {
    height: 350px;
  }
  
  .post-small {
    height: 180px;
  }
  
  .story {
    height: 150px;
  }
  
  .reel {
    height: 180px;
  }
}
</style>

<div class="portfolio-container">
  <!-- Portfolio Header -->
  <div class="portfolio-header">
    <h1 class="portfolio-title">Portfolio</h1>
    <h2 class="portfolio-subtitle">Vartak's Competitive Academy</h2>
  </div>

  <!-- Client Info Section -->
  <div class="client-section">
    <div class="client-blank-box"></div>
    <div class="client-info">
      <h3 class="client-name">Vartak's Competitive Academy</h3>
      <p class="client-description">
        A leading educational institution focused on competitive exam preparation. 
        We helped them establish a strong digital presence and create engaging content 
        that resonates with students and parents alike.
      </p>
      <div class="our-role">Our Role:</div>
      <ul class="role-list">
        <li>Brand Identity Design</li>
        <li>Social Media Management</li>
        <li>Content Creation & Strategy</li>
        <li>Digital Marketing Campaigns</li>
        <li>Video Production & Editing</li>
      </ul>
    </div>
  </div>

  <!-- Portfolio Grid -->
  <div class="portfolio-grid">
    
    <!-- Large Post (iPhone 5.5 inch size) -->
    <div class="grid-item post-large">
      <div class="content-label label-post">Post</div>
      <img src="uploads/portfolio/thumbnails/project1.webp" alt="Main Post" onerror="this.style.background='linear-gradient(135deg, #f0f0f0, #e0e0e0)'; this.style.display='flex'; this.style.alignItems='center'; this.style.justifyContent='center'; this.innerHTML='<div style=\'color:#999; font-size:1.2rem; font-weight:600;\'>Main Post</div>';">
    </div>

    <!-- Small Post 1 -->
    <div class="grid-item post-small">
      <div class="content-label label-post">Post</div>
      <img src="uploads/portfolio/thumbnails/project2.webp" alt="Post 2" onerror="this.style.background='linear-gradient(135deg, #f0f0f0, #e0e0e0)'; this.style.display='flex'; this.style.alignItems='center'; this.style.justifyContent='center'; this.innerHTML='<div style=\'color:#999; font-size:1rem; font-weight:600;\'>Post 2</div>';">
    </div>

    <!-- Small Post 2 -->
    <div class="grid-item post-small">
      <div class="content-label label-post">Post</div>
      <img src="uploads/portfolio/thumbnails/project3.webp" alt="Post 3" onerror="this.style.background='linear-gradient(135deg, #f0f0f0, #e0e0e0)'; this.style.display='flex'; this.style.alignItems='center'; this.style.justifyContent='center'; this.innerHTML='<div style=\'color:#999; font-size:1rem; font-weight:600;\'>Post 3</div>';">
    </div>

    <!-- Small Post 3 -->
    <div class="grid-item post-small">
      <div class="content-label label-post">Post</div>
      <img src="uploads/portfolio/thumbnails/project4.webp" alt="Post 4" onerror="this.style.background='linear-gradient(135deg, #f0f0f0, #e0e0e0)'; this.style.display='flex'; this.style.alignItems='center'; this.style.justifyContent='center'; this.innerHTML='<div style=\'color:#999; font-size:1rem; font-weight:600;\'>Post 4</div>';">
    </div>

    <!-- Small Post 4 -->
    <div class="grid-item post-small">
      <div class="content-label label-post">Post</div>
      <img src="uploads/portfolio/thumbnails/project5.webp" alt="Post 5" onerror="this.style.background='linear-gradient(135deg, #f0f0f0, #e0e0e0)'; this.style.display='flex'; this.style.alignItems='center'; this.style.justifyContent='center'; this.innerHTML='<div style=\'color:#999; font-size:1rem; font-weight:600;\'>Post 5</div>';">
    </div>

    <!-- Story 1 -->
    <div class="grid-item story">
      <div class="content-label label-story">Story</div>
      <div class="reel-overlay">Educational Stories</div>
    </div>

    <!-- Reel 1 -->
    <div class="grid-item reel">
      <div class="content-label label-reel">Reel</div>
      <div class="reel-overlay">Study Tips Reel</div>
    </div>

    <!-- Story 2 -->
    <div class="grid-item story">
      <div class="content-label label-story">Story</div>
      <div class="reel-overlay">Success Stories</div>
    </div>

    <!-- Reel 2 -->
    <div class="grid-item reel">
      <div class="content-label label-reel">Reel</div>
      <div class="reel-overlay">Motivation Reel</div>
    </div>

    <!-- Reel 3 -->
    <div class="grid-item reel">
      <div class="content-label label-reel">Reel</div>
      <div class="reel-overlay">Campus Tour</div>
    </div>

    <!-- Reel 4 -->
    <div class="grid-item reel">
      <div class="content-label label-reel">Reel</div>
      <div class="reel-overlay">Results Reel</div>
    </div>

    <!-- Reel 5 -->
    <div class="grid-item reel">
      <div class="content-label label-reel">Reel</div>
      <div class="reel-overlay">Faculty Intro</div>
    </div>

  </div>
</div>

<?php include 'includes/footer.php'; ?>