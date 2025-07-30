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
                    $title = !empty($item['brand_name']) ? $item['brand_name'] : (!empty($item['title']) ? $item['title'] : 'Portfolio Item');
                    $description = !empty($item['description']) ? substr(strip_tags($item['description']), 0, 100) . '...' : '';
                    
                    echo '<div class="portfolio-card bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105 hover:shadow-xl group">';
                    echo '<a href="portfolio-detail.php?id=' . $item['id'] . '" class="block">';
                    
                    // Image section
                    echo '<div class="aspect-video bg-gray-200 overflow-hidden">';
                    if (!empty($item['thumbnail'])) {
                        echo '<img src="' . htmlspecialchars($item['thumbnail']) . '" alt="' . htmlspecialchars($title) . '" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">';
                    } else {
                        echo '<div class="w-full h-full flex items-center justify-center text-gray-400"><i class="fas fa-image text-4xl"></i></div>';
                    }
                    echo '</div>';
                    
                    // Content section
                    echo '<div class="p-4">';
                    echo '<h3 class="text-lg font-bold text-[#F44B12] mb-2 line-clamp-2" style="font-family: \'Montserrat\', sans-serif;">' . htmlspecialchars($title) . '</h3>';
                    if ($description) {
                        echo '<p class="text-gray-600 text-sm line-clamp-3">' . htmlspecialchars($description) . '</p>';
                    }
                    echo '</div>';
                    
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                // Fallback: One placeholder card if no items
                echo '<div class="portfolio-card bg-white rounded-xl shadow-lg overflow-hidden">';
                echo '<div class="aspect-video bg-gray-200 flex items-center justify-center">';
                echo '<div class="text-center text-gray-400">';
                echo '<i class="fas fa-image text-4xl mb-2"></i>';
                echo '<p class="text-sm">No portfolio items available</p>';
                echo '</div>';
                echo '</div>';
                echo '<div class="p-4">';
                echo '<h3 class="text-lg font-bold text-[#F44B12] mb-2" style="font-family: \'Montserrat\', sans-serif;">Coming Soon</h3>';
                echo '<p class="text-gray-600 text-sm">Portfolio items will be displayed here.</p>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Portfolio Card Styles -->
<style>
.portfolio-card {
    transition: all 0.3s ease;
}

.portfolio-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(244, 75, 18, 0.15);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media (max-width: 640px) {
    .portfolio-card {
        margin-bottom: 1rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>