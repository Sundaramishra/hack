<?php include 'includes/header.php'; ?>

<!-- Portfolio Hero Section as per Image 2 -->
<section class="relative pt-24 pb-12 bg-white overflow-hidden" style="border-bottom-left-radius: 40px; border-bottom-right-radius: 40px;">
    <!-- Dotted background (replace with your image or CSS as needed) -->
    <div class="absolute inset-0 pointer-events-none select-none" style="
        background: url('images/dots-top.png') repeat-x top left;
        opacity: 0.2;
        height: 200px;
    "></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center">
            <h2 class="text-4xl md:text-5xl font-extrabold text-[#F44B12] mb-2" style="font-family:'Montserrat',sans-serif;">
                Portfolio
            </h2>
            <p class="text-2xl md:text-3xl text-[#222] mb-10" style="font-family:'Montserrat',sans-serif;">
                Discover how we make brands move.
            </p>
        </div>
    </div>
</section>

<!-- Portfolio Gallery Section with Premium Black/Orange Gradient -->
<section class="relative px-2 pb-8 pt-0" style="background: radial-gradient(ellipse at bottom right, #F44B12 0%, transparent 50%), radial-gradient(ellipse at bottom left, #F44B12 0%, transparent 40%), linear-gradient(135deg, #242322 80%, #F44B12 120%); border-radius: 60px 60px 0 0;">
    <div class="container mx-auto px-4 pt-0 pb-20">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8 lg:gap-10 pt-10 justify-items-center">
            <?php
            // Get and sort portfolio items as before (descending, newest first)
            $portfolioItems = getPortfolioItems();
            if (!empty($portfolioItems)) {
                // Sort descending by id (or created_at if needed, adjust as per your data)
                usort($portfolioItems, function($a, $b) {
                    // If you have a 'created_at', use that instead of 'id'
                    return $b['id'] <=> $a['id'];
                });
                foreach ($portfolioItems as $item) {
                    echo '<a href="portfolio-detail.php?id=' . $item['id'] . '" class="rounded-xl bg-[#292929] border border-gray-400/40 shadow-lg aspect-video flex items-center justify-center transition-transform duration-300 hover:scale-105 overflow-hidden group" style="min-height: 180px;">';
                    if (!empty($item['thumbnail'])) {
                        echo '<img src="' . htmlspecialchars($item['thumbnail']) . '" alt="' . htmlspecialchars(isset($item['brand_name']) && $item['brand_name'] !== null && $item['brand_name'] !== '' ? $item['brand_name'] : (isset($item['title']) ? $item['title'] : 'Portfolio Item')) . '" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">';
                    } else {
                        echo '<div class="w-full h-full flex items-center justify-center text-gray-500"><i class="fas fa-image text-3xl"></i></div>';
                    }
                    echo '</a>';
                }
            } else {
                // Fallback: One placeholder card if no items
                echo '<div class="rounded-xl bg-[#292929] border border-gray-400/40 shadow-lg aspect-video flex items-center justify-center" style="min-height: 180px;">';
                echo '<div class="w-full h-full flex items-center justify-center text-gray-500"><i class="fas fa-image text-3xl"></i></div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>



<?php include 'includes/footer.php'; ?>