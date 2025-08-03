<?php include 'includes/header.php'; ?>

<?php
// Safe getter (handles empty string and null, trims input)
function safe($arr, $key, $default = '') {
    return (isset($arr[$key]) && $arr[$key] !== null && trim($arr[$key]) !== '') ? trim($arr[$key]) : $default;
}

$portfolioId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$portfolio = getPortfolioItem($portfolioId);

if (!$portfolio) {
    header("Location: portfolio.php");
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
    ['type' => 'img', 'src' => $big_post,      'class' => 'big-post',    'alt' => 'Portfolio Main Post'],
    ['type' => 'img', 'src' => $small_posts[0],'class' => 'small-post1', 'alt' => 'Portfolio Image'],
    ['type' => 'img', 'src' => $small_posts[1],'class' => 'small-post2', 'alt' => 'Portfolio Image'],
    ['type' => 'img', 'src' => $small_posts[2],'class' => 'small-post3', 'alt' => 'Portfolio Image'],
    ['type' => 'video', 'src' => $stories[0],  'class' => 'story1',      'alt' => 'Portfolio Story'],
    ['type' => 'video', 'src' => $stories[1],  'class' => 'story2',      'alt' => 'Portfolio Story'],
    // Uncomment if you have a third story
    // ['type' => 'video', 'src' => safe($portfolio, 'video_story3'), 'class' => 'story3', 'alt' => 'Portfolio Story'],
    ['type' => 'video', 'src' => $reels[0],    'class' => 'reel1',       'alt' => 'Portfolio Reel'],
    ['type' => 'video', 'src' => $reels[1],    'class' => 'reel2',       'alt' => 'Portfolio Reel'],
    ['type' => 'video', 'src' => $reels[2],    'class' => 'reel3',       'alt' => 'Portfolio Reel'],
    ['type' => 'video', 'src' => $reels[3],    'class' => 'reel4',       'alt' => 'Portfolio Reel'],
];

function countMedia($slots) {
    $count = 0;
    foreach ($slots as $slot) {
        if ($slot['src']) $count++;
    }
    return $count;
}
?>

<style>
/* Header and card styles as before ... */

.portfolio-header-bg {
    background: linear-gradient(120deg, #fff 0%, #fff 100%);
    position: relative;
    min-height: 220px;
    border-bottom-left-radius: 64px;
    border-bottom-right-radius: 64px;
    box-shadow: 0 8px 30px 0 rgba(44,44,44,0.08);
    overflow: hidden;
}
.portfolio-header-bg .dot-bg {
    position: absolute;
    right: 0;
    top: 0;
    width: 540px;
    height: 100%;
    background: url('/dot-bg.svg') no-repeat right top;
    opacity: 0.25;
    pointer-events: none;
}
.portfolio-header-content {
    max-width: 780px;
    margin: 0 auto;
    padding: 64px 24px 32px 24px;
    text-align: center;
    position: relative;
    z-index: 1;
}
@media (max-width: 768px) {
    .portfolio-header-bg {
        min-height: 150px;
        border-bottom-left-radius: 32px;
        border-bottom-right-radius: 32px;
    }
    .portfolio-header-content {
        padding: 32px 12px 16px 12px;
    }
}
.portfolio-title {
    font-size: 2.7rem;
    font-weight: 900;
    color: #F44B12;
    font-family: 'Playfair Display', serif;
    margin-bottom: 4px;
    letter-spacing: 0.5px;
}
.portfolio-subtitle {
    font-size: 2rem;
    font-weight: 400;
    color: #2B2B2A;
    font-family: 'Montserrat', sans-serif;
    margin-bottom: 0;
}
@media (max-width: 768px) {
    .portfolio-title { font-size: 2rem; }
    .portfolio-subtitle { font-size: 1.2rem; }
}
.portfolio-details-bg {
    background: linear-gradient(135deg, #232221 85%, #F44B12 110%);
    border-radius: 48px 48px 0 0;
    margin-top: -48px;
    min-height: 400px;
    padding-top: 0;
    position: relative;
    z-index: 5;
    box-shadow: 0 8px 40px 0 rgba(44,44,44,0.19);
}
@media (max-width: 1024px) {
    .portfolio-details-bg { border-radius: 32px 32px 0 0; }
}
@media (max-width: 640px) {
    .portfolio-details-bg { border-radius: 18px 18px 0 0; }
}
.portfolio-details-card {
    background: rgba(35, 34, 33, 0.96);
    border-radius: 28px;
    box-shadow: 0 8px 36px 0 rgba(44,44,44,0.08);
    padding: 36px 42px;
    display: flex;
    gap: 32px;
    align-items: flex-start;
    border: 1.5px solid rgba(244, 75, 18, 0.09);
    margin: 0 auto;
    max-width: 1080px;
    position: relative;
    top: -56px;
}
@media (max-width: 1024px) {
    .portfolio-details-card {
        flex-direction: column;
        align-items: stretch;
        padding: 28px 16px;
        top: -24px;
    }
}
@media (max-width: 640px) {
    .portfolio-details-card {
        padding: 18px 6px;
        top: -12px;
    }
}
.portfolio-details-thumb {
    width: 300px;
    min-width: 200px;
    max-width: 340px;
    flex-shrink: 0;
}
.portfolio-details-thumb img {
    width: 100%;
    height: 220px;
    max-height: 260px;
    object-fit: cover;
    background: #fff;
    border-radius: 18px;
    border: 3.5px solid #F44B12;
    box-shadow: 0 4px 18px 0 rgba(44,44,44,0.08);
}
.portfolio-details-thumb .placeholder {
    width: 100%;
    height: 220px;
    background: #444;
    color: #bdbdbd;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    border: 2.5px solid #F44B12;
    font-size: 2.4rem;
}
@media (max-width: 768px) {
    .portfolio-details-thumb, .portfolio-details-thumb img, .portfolio-details-thumb .placeholder {
        height: 140px;
        min-width: 130px;
        font-size: 1.3rem;
    }
}
.portfolio-details-info {
    flex: 1;
    min-width: 0;
}
.portfolio-details-info .client-label {
    color: #F44B12;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 2px;
}
.portfolio-details-info .client-title {
    color: #fff;
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 8px;
    font-family: 'Montserrat', sans-serif;
}
.portfolio-details-info .client-desc {
    color: #e0e0e0;
    font-size: 1.05rem;
    margin-bottom: 18px;
    font-family: 'Montserrat', sans-serif;
}
.portfolio-details-info .role-label {
    color: #F44B12;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 2px;
}
.portfolio-details-info .roles-list {
    color: #fff;
    font-size: 1.05rem;
    margin-bottom: 8px;
    font-family: 'Montserrat', sans-serif;
    display: flex;
    flex-wrap: wrap;
    gap: 8px 14px;
}
.portfolio-details-info .roles-list span {
    background: transparent;
    font-weight: 700;
    position: relative;
}
.portfolio-details-info .roles-list span:after {
    content: "•";
    font-weight: 900;
    color: #F44B12;
    margin: 0 12px;
    display: inline-block;
}
.portfolio-details-info .roles-list span:last-child:after { display: none; }
.portfolio-details-info .timeline-label {
    color: #bdbdbd;
    font-size: .98rem;
    margin-top: 8px;
}
.portfolio-details-info .timeline-value {
    color: #fff;
    font-weight: 600;
    margin-left: 4px;
}

/* COLLAGE GRID */
.portfolio-grid-main {
    max-width: 1080px;
    margin: 0 auto;
    padding: 0 0 46px 0;
}
.portfolio-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-template-rows: repeat(5, 1fr);
    gap: 32px 32px; /* Matches the close look in info image */
    width: 100%;
    margin-top: 40px;
    margin-bottom: 40px;
}
.portfolio-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 24px 0 rgba(0,0,0,0.13);
    display: flex;
    align-items: stretch;
    justify-content: stretch;
    overflow: hidden;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box;
    border: none;
}
.portfolio-card img, .portfolio-card video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: 0;
    border: none;
}
/* --- COLLAGE GRID AREAS --- */
.portfolio-card.big-post {
    grid-column: 1 / span 2;
    grid-row: 1 / span 2;
    aspect-ratio: 1/1;
}
.portfolio-card.small-post1 {
    grid-column: 3 / span 1;
    grid-row: 1 / span 1;
    aspect-ratio: 1/1;
}
.portfolio-card.small-post2 {
    grid-column: 4 / span 1;
    grid-row: 1 / span 1;
    aspect-ratio: 1/1;
}
.portfolio-card.small-post3 {
    grid-column: 3 / span 1;
    grid-row: 2 / span 1;
    aspect-ratio: 1/1;
}
.portfolio-card.story1 {
    grid-column: 4 / span 1;
    grid-row: 2 / span 1;
    aspect-ratio: 1/1;
}
.portfolio-card.story2 {
    grid-column: 3 / span 1;
    grid-row: 3 / span 1;
    aspect-ratio: 1/1;
}
.portfolio-card.story3 {
    grid-column: 4 / span 1;
    grid-row: 3 / span 1;
    aspect-ratio: 1/1;
}
.portfolio-card.reel1 {
    grid-column: 1 / span 1;
    grid-row: 3 / span 2;
    aspect-ratio: 9/16;
}
.portfolio-card.reel2 {
    grid-column: 2 / span 1;
    grid-row: 3 / span 2;
    aspect-ratio: 9/16;
}
.portfolio-card.reel3 {
    grid-column: 3 / span 1;
    grid-row: 4 / span 2;
    aspect-ratio: 9/16;
}
.portfolio-card.reel4 {
    grid-column: 4 / span 1;
    grid-row: 4 / span 2;
    aspect-ratio: 9/16;
}
/* --- END COLLAGE GRID AREAS --- */

/* Responsive collage stacking */
@media (max-width: 1024px) {
    .portfolio-grid {
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: repeat(10, 1fr);
        gap: 24px 16px;
    }
    .portfolio-card.big-post    { grid-column: 1 / span 2; grid-row: 1 / span 2;}
    .portfolio-card.small-post1 { grid-column: 1; grid-row: 3;}
    .portfolio-card.small-post2 { grid-column: 2; grid-row: 3;}
    .portfolio-card.small-post3 { grid-column: 1; grid-row: 4;}
    .portfolio-card.story1      { grid-column: 2; grid-row: 4;}
    .portfolio-card.story2      { grid-column: 1; grid-row: 5;}
    .portfolio-card.story3      { grid-column: 2; grid-row: 5;}
    .portfolio-card.reel1       { grid-column: 1; grid-row: 6;}
    .portfolio-card.reel2       { grid-column: 2; grid-row: 6;}
    .portfolio-card.reel3       { grid-column: 1; grid-row: 7;}
    .portfolio-card.reel4       { grid-column: 2; grid-row: 7;}
}
@media (max-width:600px) {
    .portfolio-grid {
        grid-template-columns: 1fr;
        grid-template-rows: repeat(12, 1fr);
        gap: 14px 0;
    }
    .portfolio-card.big-post    { grid-column: 1; grid-row: 1;}
    .portfolio-card.small-post1 { grid-column: 1; grid-row: 2;}
    .portfolio-card.small-post2 { grid-column: 1; grid-row: 3;}
    .portfolio-card.small-post3 { grid-column: 1; grid-row: 4;}
    .portfolio-card.story1      { grid-column: 1; grid-row: 5;}
    .portfolio-card.story2      { grid-column: 1; grid-row: 6;}
    .portfolio-card.story3      { grid-column: 1; grid-row: 7;}
    .portfolio-card.reel1       { grid-column: 1; grid-row: 8;}
    .portfolio-card.reel2       { grid-column: 1; grid-row: 9;}
    .portfolio-card.reel3       { grid-column: 1; grid-row: 10;}
    .portfolio-card.reel4       { grid-column: 1; grid-row: 11;}
}

/* v9 improvements */
.portfolio-grid > .portfolio-card:empty { display: none !important; }
.no-media-message {
    color: #fff;
    background: rgba(44,44,44,0.18);
    border-radius: 14px;
    padding: 32px 8px;
    text-align: center;
    font-size: 1.14rem;
    grid-column: 1/-1;
    margin: 14px 0 0 0;
}
@media (max-width: 600px) {
    .no-media-message {
        font-size: 1rem;
        padding: 22px 2px;
    }
}
@media (prefers-reduced-motion: reduce) {
    video[autoplay] {
        animation: none !important;
        transition: none !important;
    }
}
</style>

<section class="portfolio-header-bg">
    <div class="dot-bg"></div>
    <div class="portfolio-header-content">
        <div class="portfolio-title">Portfolio</div>
        <div class="portfolio-subtitle">
            <?php echo htmlspecialchars($title); ?>
        </div>
    </div>
</section>

<section class="portfolio-details-bg">
    <div class="portfolio-details-card">
        <div class="portfolio-details-thumb">
            <?php if ($thumbnail): ?>
                <img src="../<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy" />
            <?php else: ?>
                <div class="placeholder">No Image</div>
            <?php endif; ?>
        </div>
        <div class="portfolio-details-info">
            <div class="client-label">Client</div>
            <div class="client-title"><?php echo htmlspecialchars($title); ?></div>
            <div class="client-desc"><?php echo nl2br(htmlspecialchars($description)); ?></div>
            <div class="role-label">Our Role</div>
            <div class="roles-list">
                <?php
                $services = array_filter(array_map('trim', explode(',', $role)));
                foreach ($services as $service) {
                    echo '<span>' . htmlspecialchars($service) . '</span>';
                }
                ?>
            </div>
            <?php if ($timeline): ?>
            <div class="timeline-label">Timeline:<span class="timeline-value"> <?php echo htmlspecialchars($timeline); ?></span></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="portfolio-grid-main">
        <div class="portfolio-grid">
            <?php
            $mediaPrinted = false;
            foreach ($grid_slots as $slot) {
                if (!$slot['src']) continue;
                $mediaPrinted = true;
                $src = htmlspecialchars($slot['src']);
                $class = htmlspecialchars($slot['class']);
                $alt = htmlspecialchars($slot['alt']);
                if ($slot['type'] === 'img') {
                    echo "<div class=\"portfolio-card $class\"><img src=\"../$src\" alt=\"$alt\" loading=\"lazy\" /></div>";
                } elseif ($slot['type'] === 'video') {
                    echo "<div class=\"portfolio-card $class\"><video autoplay muted loop playsinline preload=\"metadata\" aria-label=\"$alt\"><source src=\"../$src\" type=\"video/mp4\"></video></div>";
                }
            }
            if (!$mediaPrinted) {
                echo '<div class="no-media-message">No media to display for this project.</div>';
            }
            ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>