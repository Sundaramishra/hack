<?php 
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php'; 
?>

<style>
:root {
    --orange: #F44B12;
    --dark: #2B2B2A;
    --glass-bg: linear-gradient(120deg, #232323 62%, #232323 100%);
}

body {
    background: #fff;
    color: var(--dark);
    margin: 0;
    font-family: 'Poppins', sans-serif;
    overflow-x: hidden;
}

/* HERO SECTION */
.hero-section {
    background: #fff;
    position: relative;
    min-height: 320px;
    padding: 0 0 12px 0;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
}

.hero-video {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 3vw;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 24px 0 rgba(0,0,0,0.07);
}

.hero-video video {
    width: 380px;
    max-width: 90vw;
    height: auto;
    border-radius: 8px;
    animation: logoPop .9s cubic-bezier(.21,1.09,.39,1.01);
}

@keyframes logoPop {
    0% { opacity: 0; transform: scale(0.95); }
    95% { opacity: 1; transform: scale(1.04); }
    100% { opacity: 1; transform: scale(1); }
}

.hero-quote {
    font-family: 'Montserrat', Arial, sans-serif;
    font-size: clamp(1.35rem, 2.4vw, 2.45rem);
    font-weight: 800;
    text-align: center;
    margin: 2.2vw 0 42px 0;
    letter-spacing: -1px;
    color: #222;
    line-height: 1.18;
    padding: 0 2vw;
}

.hero-quote .smart, .hero-quote .loud { color: #F44B12; font-weight: 900; }
.hero-quote .real { color: #222; }

/* OUR EXPERTISE SECTION */
.expertise-section {
    background: var(--glass-bg);
    width: 100vw;
    margin: 0 auto 2.9rem auto;
    padding: 5.2rem 0 3.9rem 0;
    position: relative;
    box-shadow: 0 12px 38px 0 rgba(44,44,44,0.12);
    border-radius: 56px 56px 0 0 / 52px 52px 0 0;
}

.section-heading {
    font-family: 'Montserrat', Arial, sans-serif;
    font-size: 2.3rem;
    font-weight: 900;
    text-align: center;
    margin-bottom: 3.5rem;
    animation: headingPop 0.8s cubic-bezier(.22,1.08,.29,1.01);
}

@keyframes headingPop {
    0% { opacity: 0; transform: scale(0.92); }
    80% { opacity: 1; transform: scale(1.08); }
    100% { opacity: 1; transform: scale(1); }
}

.section-heading .heading-our { color: #fff; }
.section-heading .heading-expertise { color: #F44B12; }

.expertise-list {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 3.2rem;
    max-width: 1850px;
    margin: 0 auto;
    padding: 0 1.5vw;
}

@media (max-width: 1100px) {
    .expertise-list { grid-template-columns: repeat(2, 1fr); gap: 1.8rem; max-width: 700px; }
}

@media (max-width: 600px) {
    .expertise-section { border-radius: 32px 32px 0 0; padding: 1.5rem 0 1rem 0; }
    .section-heading { font-size: 1.03rem; margin-bottom: 30px; }
    .expertise-list { 
        grid-template-columns: 1fr 1fr;
        gap: 0.7rem;
        width: 90vw;
        margin: 0 auto;
        padding: 0;
        height: 90vw;
        max-height: 90vw;
    }
}

.glass-card {
    background: rgba(255,255,255,0.07);
    border: 2.5px solid rgba(244,75,18,0.15);
    border-radius: 16px;
    min-height: 300px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 2.6rem 1.5rem;
    box-shadow: 0 10px 40px 0 rgba(44,44,44,0.07);
    transition: all 0.3s;
    animation: glassFadeIn 0.93s cubic-bezier(.22,1.08,.29,1.01) both;
    backdrop-filter: blur(12px);
    text-align: center;
}

@media (max-width: 600px) {
    .glass-card {
        padding: 0.6rem;
        min-height: unset;
        height: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
}

@keyframes glassFadeIn {
    0% { opacity: 0; filter: blur(12px); }
    100% { opacity: 1; filter: none; }
}

.glass-card:hover {
    box-shadow: 0 24px 64px 0 rgba(44,44,44,0.13);
    border: 2.5px solid #F44B12;
    background: rgba(255,255,255,0.16);
    transform: translateY(-8px) scale(1.027);
}

.glass-card .title {
    font-family: 'Montserrat', Arial, sans-serif;
    font-size: 2.12rem;
    font-weight: 900;
    margin-bottom: 1.1em;
    color: #F44B12;
    line-height: 1.09;
}

@media (max-width: 600px) {
    .glass-card .title {
        font-size: 0.95rem;
        margin-bottom: 0.18em;
        font-weight: 900;
    }
}

.glass-card ul {
    margin: 0;
    padding: 0;
    list-style: none;
    color: #fff;
    font-size: 1.13rem;
    font-weight: 500;
    text-align: center;
    line-height: 2.1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

@media (max-width: 600px) {
    .glass-card ul {
        font-size: 0.63rem;
        line-height: 1.15;
        height: 100%;
        justify-content: center;
        align-items: center;
    }
}

.glass-card li {
    margin-bottom: 0.33em;
    color: #fff;
    text-align: center;
}

@media (max-width: 600px) {
    .glass-card li {
        margin-bottom: 0.13em;
        font-size: inherit;
    }
}

/* BRANDS SECTION */
.brands-section {
    background: #fff;
    border-radius: 32px;
    min-height: 200px;
    padding: 40px 20px;
    margin: 0 auto 2.9rem auto;
    box-shadow: 0 20px 54px 0 rgba(44,44,44,0.08);
    position: relative;
}

.brands-grid {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 1.5rem 0;
    align-items: center;
}

.brands-row {
    display: flex;
    justify-content: center;
    gap: 3vw;
    margin-bottom: 0.9rem;
    flex-wrap: nowrap;
}

.brands-logo {
    filter: grayscale(1);
    transition: all 0.3s;
    max-height: 64px;
    max-width: 120px;
    object-fit: contain;
    padding: 4px 0;
    opacity: 0.72;
    animation: brandLogoFade 3s infinite alternate;
}

@keyframes brandLogoFade {
    0%, 100% { opacity: 0.72; }
    50% { opacity: 1; filter: none; }
}

.brands-logo:hover {
    filter: none;
    opacity: 1;
    transform: scale(1.10);
}

@media (max-width: 600px) {
    .brands-row {
        gap: 2vw;
        flex-wrap: wrap;
        justify-content: center;
    }
    .brands-logo {
        max-width: 40vw;
        max-height: 36px;
        margin: 0 1vw 10px 1vw;
    }
}

/* FEATURED WORK SECTION */
.featured-section {
    background: #232323;
    border-radius: 32px;
    min-height: 670px;
    padding: 40px 0;
    position: relative;
}

.featured-heading {
    text-align: center;
    margin: 36px 0 20px 0;
}

.featured-heading-text {
    color: #F44B12;
    font-family: 'Montserrat', Arial, sans-serif;
    font-weight: 900;
    font-size: 2.3rem;
    border: 2px solid #F44B12;
    padding: 10px 28px;
    background: #252525;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    box-shadow: 0 2px 12px #252525;
}

@media (max-width: 700px) {
    .featured-heading-text {
        font-size: 1.17rem;
        padding: 8px 15px;
    }
}

.featured-slider {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

.featured-slider-track {
    width: 100%;
    min-height: 430px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    perspective: 2000px;
    margin-bottom: 20px;
}

@media (max-width: 700px) {
    .featured-slider-track {
        min-height: 350px;
        gap: 10px;
    }
}

.featured-slide {
    position: relative;
    background: #252525;
    border: 3px solid #F44B12;
    cursor: pointer;
    box-shadow: 0 8px 22px rgba(0,0,0,0.4);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    transition: all 0.3s;
    width: 220px;
    height: 390px;
    filter: drop-shadow(0 6px 16px rgba(0,0,0,0.5));
}

@media (max-width: 700px) {
    .featured-slide {
        width: 128px;
        height: 220px;
    }
}

.featured-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.featured-slide--current {
    transform: scale(1.18);
    width: 310px;
    height: 470px;
    border: 3px solid #ff6a30;
    box-shadow: 0 14px 55px 0 rgba(17,17,29,0.8), 0 0px 12px 0 #F44B12;
    z-index: 10;
}

@media (max-width: 700px) {
    .featured-slide--current {
        width: 196px;
        height: 330px;
    }
}

.featured-viewall-btn {
    display: block;
    margin: 18px auto 0 auto;
    padding: 11px 22px;
    background: #252525;
    color: #fff;
    border: 2px solid #F44B12;
    font-size: 1.1rem;
    font-family: 'Montserrat', sans-serif;
    font-weight: 900;
    text-decoration: none;
    letter-spacing: 0.08em;
    transition: all 0.3s;
    cursor: pointer;
    text-align: center;
}

.featured-viewall-btn:hover {
    background: #F44B12;
    color: #fff;
    border: 2px solid #fff;
    transform: scale(1.06);
}

@media (max-width: 700px) {
    .featured-viewall-btn {
        font-size: 1rem;
        padding: 8px 16px;
    }
}
</style>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="hero-video">
        <video autoplay loop muted playsinline>
            <source src="uploads/hero-reels/animation.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    <div class="hero-quote">
        <span class="smart">Smart</span> strategy. <span class="loud">Loud</span> creativity. <span class="real">Real</span> results.
    </div>
</section>

<!-- OUR EXPERTISE SECTION -->
<section class="expertise-section">
    <div class="text-center">
        <div class="section-heading">
            <span class="heading-our">Our</span> <span class="heading-expertise">Expertise</span>
        </div>
    </div>
    <div class="expertise-list">
        <?php
        $services = getServices();
        if (!empty($services)) {
            foreach($services as $idx => $service) {
                $delay = 0.08 * $idx;
                $title = html_entity_decode($service['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                echo '<div class="glass-card" style="animation-delay:'.$delay.'s">';
                echo '<div class="title">'.clean($title).'</div>';
                echo '<ul>';
                $points = preg_split('/\r\n|\r|\n/', $service['description']);
                foreach($points as $p) {
                    if(trim($p) !== "") echo '<li>'.clean($p).'</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
        } else {
            echo '<div style="color:#fff;text-align:center;width:100%;font-size:1.15rem;">Loading expertise...</div>';
        }
        ?>
    </div>
</section>

<!-- BRANDS SECTION -->
<section class="brands-section">
    <div class="brands-grid">
        <?php
        $brandLogos = getBrandLogos();
        if (!empty($brandLogos)) {
            // 4-3-4-3 pattern
            $pattern = [4,3];
            $k = 0;
            $rowCount = 0;
            while ($k < count($brandLogos)) {
                $row = $pattern[$rowCount % 2];
                echo '<div class="brands-row">';
                for ($j = 0; $j < $row && $k < count($brandLogos); $j++, $k++) {
                    $logo = $brandLogos[$k];
                    echo '<img src="'.clean($logo['logo_path']).'" alt="'.clean($logo['brand_name']).'" class="brands-logo" onerror="this.style.display=\'none\'">';
                }
                echo '</div>';
                $rowCount++;
            }
        } else {
            echo '<div class="brands-row"><p style="text-align:center;color:#666;font-size:1.2rem;padding:20px;">Loading brands...</p></div>';
        }
        ?>
    </div>
</section>

<!-- FEATURED WORK SECTION -->
<section class="featured-section">
    <div class="featured-heading">
        <span class="featured-heading-text">Featured Work</span>
    </div>
    
    <div class="featured-slider">
        <div class="featured-slider-track" id="sliderTrack">
            <?php
            $portfolioItems = getPortfolioItems(5);
            if (!empty($portfolioItems)) {
                foreach ($portfolioItems as $idx => $item) {
                    $isActive = $idx === 2 ? 'featured-slide--current' : '';
                    echo '<div class="featured-slide '.$isActive.'" data-idx="'.$idx.'">';
                    echo '<img src="'.clean($item['thumbnail']).'" alt="'.clean($item['title']).'" onerror="this.src=\''.createFallbackImage($item['title']).'\'">';
                    echo '</div>';
                }
            } else {
                echo '<div class="featured-slide featured-slide--current">';
                echo '<div style="width:100%;height:100%;background:#252525;display:flex;align-items:center;justify-content:center;color:#F44B12;font-size:1.2rem;">Loading portfolio...</div>';
                echo '</div>';
            }
            ?>
        </div>
        
        <a href="portfolio.php" class="featured-viewall-btn">View All Portfolio</a>
    </div>
</section>

<script>
// Simple slider functionality
let currentSlide = 2;
const slides = document.querySelectorAll('.featured-slide');
const totalSlides = slides.length;

function updateSlider() {
    slides.forEach((slide, idx) => {
        slide.classList.remove('featured-slide--current');
        if (idx === currentSlide) {
            slide.classList.add('featured-slide--current');
        }
    });
}

// Auto-rotate every 4 seconds
setInterval(() => {
    if (totalSlides > 1) {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateSlider();
    }
}, 4000);

// Click to change slide
slides.forEach((slide, idx) => {
    slide.addEventListener('click', () => {
        currentSlide = idx;
        updateSlider();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
