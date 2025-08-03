<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Safe getter (handles empty string and null, trims input)
function safe($arr, $key, $default = '') {
    return (isset($arr[$key]) && $arr[$key] !== null && trim($arr[$key]) !== '') ? trim($arr[$key]) : $default;
}

// Include database connection first
require_once 'includes/db.php';

// Include functions first (without any output)
require_once 'includes/functions.php';

$portfolioId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Debug: Check if function exists
if (!function_exists('getPortfolioItem')) {
    die('Error: getPortfolioItem function not found in functions.php');
}

$portfolio = getPortfolioItem($portfolioId);

// Debug: Check what we got
if (!$portfolio) {
    // Show debug info instead of redirect for now
    echo "Debug Info:<br>";
    echo "Portfolio ID: " . $portfolioId . "<br>";
    echo "Portfolio data: ";
    var_dump($portfolio);
    echo "<br><a href='portfolio.php'>Go back to Portfolio</a>";
    exit;
}

// Get all fields
$title = safe($portfolio, 'title', 'Client Name');
$thumbnail = safe($portfolio, 'thumbnail');
$description = safe($portfolio, 'description');
$role = safe($portfolio, 'services_provided');
$timeline = safe($portfolio, 'timeline');
$big_post = safe($portfolio, 'photo1');
$small_posts = [
    safe($portfolio, 'photo2'),
    safe($portfolio, 'photo3'),
    safe($portfolio, 'photo4'),
];
$stories = [
    safe($portfolio, 'video_story1'),
    safe($portfolio, 'video_story2'),
];
$reels = [
    safe($portfolio, 'reel1'),
    safe($portfolio, 'reel2'),
    safe($portfolio, 'reel3'),
    safe($portfolio, 'reel4'),
];

// Prepare grid slots
$grid_slots = [
    ['type' => 'img', 'src' => $big_post,      'class' => 'pg-bigpost',    'alt' => 'Portfolio Main Post'],
    ['type' => 'img', 'src' => $small_posts[0],'class' => 'pg-post2', 'alt' => 'Portfolio Image'],
    ['type' => 'img', 'src' => $small_posts[1],'class' => 'pg-post3', 'alt' => 'Portfolio Image'],
    ['type' => 'img', 'src' => $small_posts[2],'class' => 'pg-post4', 'alt' => 'Portfolio Image'],
    ['type' => 'video', 'src' => $stories[0],  'class' => 'pg-story1',      'alt' => 'Portfolio Story'],
    ['type' => 'video', 'src' => $stories[1],  'class' => 'pg-story2',      'alt' => 'Portfolio Story'],
    ['type' => 'img', 'src' => $small_posts[0],'class' => 'pg-post5', 'alt' => 'Portfolio Image'],
    ['type' => 'video', 'src' => $reels[0],    'class' => 'pg-reel1',       'alt' => 'Portfolio Reel'],
    ['type' => 'video', 'src' => $reels[1],    'class' => 'pg-reel2',       'alt' => 'Portfolio Reel'],
    ['type' => 'video', 'src' => $reels[2],    'class' => 'pg-reel3',       'alt' => 'Portfolio Reel'],
    ['type' => 'video', 'src' => $reels[3],    'class' => 'pg-reel4',       'alt' => 'Portfolio Reel'],
];

function countMedia($slots) {
    $count = 0;
    foreach ($slots as $slot) {
        if ($slot['src']) $count++;
    }
    return $count;
}

// Now include header after redirect check
include 'includes/header.php';
?>

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

/* MAIN GRID SYSTEM */
.portfolio-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  grid-template-rows: 240px 240px 240px 480px;
  gap: 15px;
  max-width: 100%;
  position: relative;
  z-index: 1;
}

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

.pg-item img, .pg-item video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 16px;
}

/* Exact Grid Positioning & Styling */
.pg-bigpost {
  grid-column: 1/3;
  grid-row: 1/3;
  font-size: 3rem;
  border-radius: 20px;
  background: #fff;
  border: 4px solid #e1306c;
  padding: 8px;
}

.pg-post2 {
  grid-column: 3;
  grid-row: 1;
  font-size: 1.8rem;
  border-radius: 16px;
  background: #fff;
  border: 3px solid #e1306c;
  padding: 6px;
}

.pg-post3 {
  grid-column: 4;
  grid-row: 1;
  font-size: 1.8rem;
  border-radius: 16px;
  background: #fff;
  border: 3px solid #e1306c;
  padding: 6px;
}

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

.pg-post4 {
  grid-column: 1;
  grid-row: 3;
  font-size: 1.8rem;
  border-radius: 16px;
  background: #fff;
  border: 3px solid #e1306c;
  padding: 6px;
}

.pg-post5 {
  grid-column: 2;
  grid-row: 3;
  font-size: 1.8rem;
  border-radius: 16px;
  background: #fff;
  border: 3px solid #e1306c;
  padding: 6px;
}

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

/* Tablet Responsive - Same as Desktop */
@media (max-width: 1024px) {
  .portfolio-grid {
    grid-template-columns: 1fr 1fr 1fr 1fr;
    grid-template-rows: 240px 240px 240px 480px;
    gap: 15px;
  }
  
  /* Keep all desktop positioning for tablet */
  .pg-bigpost { grid-column: 1/3; grid-row: 1/3; }
  .pg-post2 { grid-column: 3; grid-row: 1; }
  .pg-post3 { grid-column: 4; grid-row: 1; }
  .pg-story1 { grid-column: 3; grid-row: 2/4; }
  .pg-story2 { grid-column: 4; grid-row: 2/4; }
  .pg-post4 { grid-column: 1; grid-row: 3; }
  .pg-post5 { grid-column: 2; grid-row: 3; }
  .pg-reel1 { grid-column: 1; grid-row: 4; }
  .pg-reel2 { grid-column: 2; grid-row: 4; }
  .pg-reel3 { grid-column: 3; grid-row: 4; }
  .pg-reel4 { grid-column: 4; grid-row: 4; }
}

/* Mobile Responsive - 1 Column × 11 Rows */
@media (max-width: 768px) {
  .portfolio-header-section {
    padding: 40px 20px;
    margin-left: calc(-50vw + 50%);
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
    grid-template-rows: 70vh 70vh 70vh 90vh 90vh 70vh 70vh 90vh 90vh 90vh 90vh;
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
    <div class="portfolio-sub"><?php echo htmlspecialchars($title); ?></div>
  </section>
  
  <!-- Client Info Section -->
  <div class="portfolio-topflex">
    <div class="portfolio-blankbox">
      <?php if ($thumbnail): ?>
        <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; border-radius: 13px;" />
      <?php endif; ?>
    </div>
    <div class="portfolio-clientinfo">
      <div class="client-label">Client</div>
      <div class="client-title"><?php echo htmlspecialchars($title); ?></div>
      <?php if ($description): ?>
      <div style="color: #e0e0e0; font-size: 1rem; margin-bottom: 18px; font-family: 'Montserrat', sans-serif;">
        <?php echo nl2br(htmlspecialchars($description)); ?>
      </div>
      <?php endif; ?>
      <div style="margin-bottom:6px;">
        <span class="portfolio-role-label">Our Role</span>
        <div class="portfolio-role-list" style="margin-top:4px;">
          <?php
          $services = array_filter(array_map('trim', explode(',', $role)));
          foreach ($services as $service) {
              echo '<span>' . htmlspecialchars($service) . '</span>';
          }
          ?>
        </div>
      </div>
      <?php if ($timeline): ?>
      <div style="color: #bdbdbd; font-size: .98rem; margin-top: 8px;">
        Timeline:<span style="color: #fff; font-weight: 600; margin-left: 4px;"> <?php echo htmlspecialchars($timeline); ?></span>
      </div>
      <?php endif; ?>
    </div>
  </div>

      <!-- 4x4 GRID -->
    <div class="portfolio-grid">
      <?php
      // Render grid slots using new logic
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
      
      // Check if no media to display
      if (countMedia($grid_slots) === 0) {
        echo '<div style="color: #fff; background: rgba(44,44,44,0.18); border-radius: 14px; padding: 32px 8px; text-align: center; font-size: 1.14rem; grid-column: 1/-1; margin: 14px 0 0 0;">No media to display for this project.</div>';
      }
      ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>