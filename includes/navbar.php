<!-- Premium announcement banner -->
<head>
    <style>
        .w {
            width: 100%;
        }
    </style>
</head>

<nav class="bg-white backdrop-filter backdrop-blur-lg bg-opacity-80 shadow-xl w z-50 border-b border-gray-100">
<?php
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$current_page = basename($request_uri);

// Better logic for determining current page
if (empty($current_page) || $current_page == '/' || $current_page == '') {
    $current_page = 'index.php';
} elseif (!strpos($current_page, '.php')) {
    // If no .php extension, assume it's index
    $current_page = 'index.php';
}

$pages = [
    'index.php' => 'Home',
    'services.php' => 'Expertise',
    'portfolio.php' => 'Portfolio',
    'team.php' => 'Team',
    'contact.php' => 'Contact'
];

// Debug - remove this after testing
 echo "<!-- Current page: " . $current_page . " -->";
// echo "<!-- Request URI: " . $request_uri . " -->";
?>
  
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center h-20 relative">
        
        <!-- Left: Logo (No glow) -->
        <div class="shrink-0 flex items-center justify-start">
            <a href="index.php" class="relative">
                <div class="relative px-2 py-1">
                    <img src="<?php echo 'includes/logo.svg'; ?>" alt="Logo" class="h-16 w-auto transition-transform duration-300 transform hover:scale-110">
                </div>
            </a>
        </div>

        <!-- Center: Navigation Links -->
        <div class="absolute left-1/2 transform -translate-x-1/2 hidden sm:flex sm:space-x-8">
            <?php foreach ($pages as $page => $label): ?>
                <?php
                $active = ($current_page == $page)
                    ? 'text-[#F44B12] border-[#F44B12]'
                    : 'text-[#2B2B2A] border-transparent hover:text-[#F44B12] hover:border-[#F44B12]';
                ?>
                <a href="<?= $page ?>" class="<?= $active ?> inline-flex items-center px-2 pt-1 border-b-2 text-sm font-semibold tracking-wide transition-all duration-300 transform hover:-translate-y-1"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <!-- Right: Hamburger Icon -->
        <div class="ml-auto flex items-center sm:hidden">
            <button id="mobile-menu-button" type="button" class="inline-flex items-center justify-center p-2 rounded-full text-[#2B2B2A] hover:text-white hover:bg-[#F44B12] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#F44B12] transition-all duration-300">
                <span class="sr-only">Open main menu</span>
                <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Premium Mobile menu -->
<div id="mobile-menu" class="hidden sm:hidden backdrop-filter backdrop-blur-lg bg-white/90 border-b border-gray-100 shadow-2xl rounded-b-2xl absolute top-20 left-0 w-full z-[9999]">
    <div class="pt-4 pb-3 space-y-1 px-4">
        <?php foreach ($pages as $page => $label): ?>
            <?php
            $active = ($current_page == $page)
                ? 'text-white bg-gradient-to-r from-[#F44B12] to-orange-500 font-bold shadow-md'
                : 'text-[#2B2B2A] bg-white/80 hover:bg-gray-100';
            ?>
            <a href="<?= $page ?>" class="block px-6 py-3 <?= $active ?> rounded-xl text-base font-medium transition-all duration-300 mb-2 flex items-center justify-between">
                <span><?= $label ?></span>
                <?php if ($current_page == $page): ?>
                    <i class="fas fa-check-circle text-white"></i>
                <?php else: ?>
                    <i class="fas fa-chevron-right text-[#F44B12] opacity-70"></i>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const menuButton = document.getElementById("mobile-menu-button");
        const mobileMenu = document.getElementById("mobile-menu");

        menuButton.addEventListener("click", function () {
            mobileMenu.classList.toggle("hidden");
        });

        // Close mobile menu when clicking outside
        document.addEventListener("click", function(event) {
            if (!menuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add("hidden");
            }
        });

        // Close mobile menu when clicking on a link
        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add("hidden");
            });
        });
    });
</script>
</nav>
