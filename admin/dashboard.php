<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php');
}

// Get counts for dashboard
$servicesQuery = "SELECT COUNT(*) as count FROM services";
$teamQuery = "SELECT COUNT(*) as count FROM team";
$portfolioQuery = "SELECT COUNT(*) as count FROM portfolio";
$brandLogosQuery = "SELECT COUNT(*) as count FROM brand_logos";
$heroReelsQuery = "SELECT COUNT(*) as count FROM hero_reels";

$servicesResult = runQuery($servicesQuery);
$teamResult = runQuery($teamQuery);
$portfolioResult = runQuery($portfolioQuery);
$brandLogosResult = runQuery($brandLogosQuery);
$heroReelsResult = runQuery($heroReelsQuery);

$servicesCount = fetchRow($servicesResult)['count'] ?? 0;
$teamCount = fetchRow($teamResult)['count'] ?? 0;
$portfolioCount = fetchRow($portfolioResult)['count'] ?? 0;
$brandLogosCount = fetchRow($brandLogosResult)['count'] ?? 0;
$heroReelsCount = fetchRow($heroReelsResult)['count'] ?? 0;

// Get recent portfolio items
$recentPortfolioQuery = "SELECT * FROM portfolio ORDER BY id DESC LIMIT 5";
$recentPortfolioResult = runQuery($recentPortfolioQuery);
$recentPortfolioItems = [];
if ($recentPortfolioResult && getNumRows($recentPortfolioResult) > 0) {
    $recentPortfolioItems = fetchAllRows($recentPortfolioResult);
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-[#2B2B2A] mb-8">Dashboard</h1>
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
        <!-- Services Stat -->
        <div class="admin-card">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-[#2B2B2A]">Services</h3>
                <div class="w-10 h-10 bg-[#F44B12]/10 rounded-full flex items-center justify-center">
                    <i class="fas fa-briefcase text-[#F44B12]"></i>
                </div>
            </div>
            <div class="admin-stat"><?php echo $servicesCount; ?></div>
            <a href="services.php" class="text-[#F44B12] text-sm hover:underline mt-2 inline-block">
                Manage Services <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <!-- Team Stat -->
        <div class="admin-card">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-[#2B2B2A]">Team Members</h3>
                <div class="w-10 h-10 bg-[#F44B12]/10 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-[#F44B12]"></i>
                </div>
            </div>
            <div class="admin-stat"><?php echo $teamCount; ?></div>
            <a href="team.php" class="text-[#F44B12] text-sm hover:underline mt-2 inline-block">
                Manage Team <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <!-- Portfolio Stat -->
        <div class="admin-card">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-[#2B2B2A]">Portfolio Items</h3>
                <div class="w-10 h-10 bg-[#F44B12]/10 rounded-full flex items-center justify-center">
                    <i class="fas fa-images text-[#F44B12]"></i>
                </div>
            </div>
            <div class="admin-stat"><?php echo $portfolioCount; ?></div>
            <a href="portfolio.php" class="text-[#F44B12] text-sm hover:underline mt-2 inline-block">
                Manage Portfolio <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <!-- Brand Logos Stat -->
        <div class="admin-card">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-[#2B2B2A]">Brand Logos</h3>
                <div class="w-10 h-10 bg-[#F44B12]/10 rounded-full flex items-center justify-center">
                    <i class="fas fa-building text-[#F44B12]"></i>
                </div>
            </div>
            <div class="admin-stat"><?php echo $brandLogosCount; ?></div>
            <a href="brand-logos.php" class="text-[#F44B12] text-sm hover:underline mt-2 inline-block">
                Manage Logos <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <!-- Hero Reels Stat -->
        <div class="admin-card">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-[#2B2B2A]">Hero Reels</h3>
                <div class="w-10 h-10 bg-[#F44B12]/10 rounded-full flex items-center justify-center">
                    <i class="fas fa-film text-[#F44B12]"></i>
                </div>
            </div>
            <div class="admin-stat"><?php echo $heroReelsCount; ?></div>
            <a href="hero-reels.php" class="text-[#F44B12] text-sm hover:underline mt-2 inline-block">
                Manage Reels <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
    
    <!-- Recent Portfolio Items -->
    <div class="admin-card mb-8">
        <h2 class="text-xl font-bold text-[#2B2B2A] mb-6">Recent Portfolio Items</h2>
        
        <?php if (empty($recentPortfolioItems)): ?>
            <div class="bg-gray-100 p-4 rounded text-center">
                <p class="text-gray-600">No portfolio items found.</p>
                <a href="portfolio.php" class="inline-block mt-2 px-4 py-2 bg-[#F44B12] text-white rounded hover:bg-[#d43e0f] transition duration-300">
                    Add Portfolio Item
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPortfolioItems as $item): ?>
                            <tr>
                                <td class="w-16">
                                    <img src="../<?php echo $item['thumbnail']; ?>" alt="<?php echo htmlspecialchars($item['title'] ?? ''); ?>" class="w-12 h-12 object-cover rounded">
                                </td>
                                <td><?php echo htmlspecialchars($item['title'] ?? ''); ?></td>
                                <td>
                                    <a href="portfolio.php?action=edit&id=<?php echo $item['id']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="portfolio.php?action=view&id=<?php echo $item['id']; ?>" class="btn-view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 text-right">
                <a href="portfolio.php" class="text-[#F44B12] hover:underline">
                    View All Portfolio Items <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="admin-card">
            <h2 class="text-xl font-bold text-[#2B2B2A] mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-4">
                <a href="services.php?action=add" class="p-4 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300 flex flex-col items-center justify-center text-center">
                    <i class="fas fa-plus-circle text-[#F44B12] text-2xl mb-2"></i>
                    <span class="text-[#2B2B2A] font-medium">Add Service</span>
                </a>
                <a href="team.php?action=add" class="p-4 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300 flex flex-col items-center justify-center text-center">
                    <i class="fas fa-user-plus text-[#F44B12] text-2xl mb-2"></i>
                    <span class="text-[#2B2B2A] font-medium">Add Team Member</span>
                </a>
                <a href="portfolio.php?action=add" class="p-4 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300 flex flex-col items-center justify-center text-center">
                    <i class="fas fa-image text-[#F44B12] text-2xl mb-2"></i>
                    <span class="text-[#2B2B2A] font-medium">Add Portfolio Item</span>
                </a>
                <a href="brand-logos.php?action=add" class="p-4 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300 flex flex-col items-center justify-center text-center">
                    <i class="fas fa-building text-[#F44B12] text-2xl mb-2"></i>
                    <span class="text-[#2B2B2A] font-medium">Add Brand Logo</span>
                </a>
                <!-- Contact Queries Quick Action -->
                <a href="contact_queries.php" class="p-4 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300 flex flex-col items-center justify-center text-center">
                    <i class="fas fa-envelope text-[#F44B12] text-2xl mb-2"></i>
                    <span class="text-[#2B2B2A] font-medium">Contact Queries</span>
                </a>
            </div>
        </div>
        
        <div class="admin-card">
            <h2 class="text-xl font-bold text-[#2B2B2A] mb-4">Website Links</h2>
            <ul class="space-y-2">
                <li>
                    <a href="../index.php" target="_blank" class="flex items-center p-3 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300">
                        <i class="fas fa-home text-[#F44B12] mr-3"></i>
                        <span class="text-[#2B2B2A]">Homepage</span>
                        <i class="fas fa-external-link-alt ml-auto text-gray-500"></i>
                    </a>
                </li>
                <li>
                    <a href="../services.php" target="_blank" class="flex items-center p-3 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300">
                        <i class="fas fa-briefcase text-[#F44B12] mr-3"></i>
                        <span class="text-[#2B2B2A]">Services Page</span>
                        <i class="fas fa-external-link-alt ml-auto text-gray-500"></i>
                    </a>
                </li>
                <li>
                    <a href="../portfolio.php" target="_blank" class="flex items-center p-3 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300">
                        <i class="fas fa-images text-[#F44B12] mr-3"></i>
                        <span class="text-[#2B2B2A]">Portfolio Page</span>
                        <i class="fas fa-external-link-alt ml-auto text-gray-500"></i>
                    </a>
                </li>
                <li>
                    <a href="../team.php" target="_blank" class="flex items-center p-3 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300">
                        <i class="fas fa-users text-[#F44B12] mr-3"></i>
                        <span class="text-[#2B2B2A]">Team Page</span>
                        <i class="fas fa-external-link-alt ml-auto text-gray-500"></i>
                    </a>
                </li>
                <li>
                    <a href="../contact.php" target="_blank" class="flex items-center p-3 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300">
                        <i class="fas fa-envelope text-[#F44B12] mr-3"></i>
                        <span class="text-[#2B2B2A]">Contact Page</span>
                        <i class="fas fa-external-link-alt ml-auto text-gray-500"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>