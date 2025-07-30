<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php');
}

// Get action from URL
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_reel']) || isset($_POST['update_reel'])) {
        $title = clean($_POST['title']);
        $description = clean($_POST['description']);
        
        // Validate inputs
        $errors = [];
        if (empty($title)) {
            $errors[] = "Title is required";
        }
        
        // Handle video upload
        $video_path = '';
        if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
            $video_path = uploadFile($_FILES['video'], '../uploads/hero-reels/');
            
            if (!$video_path) {
                $errors[] = "Error uploading video. Please try again.";
            } else {
                $video_path = substr($video_path, 3); // Remove the leading '../'
            }
        } elseif (isset($_POST['update_reel']) && empty($_FILES['video']['name'])) {
            // Keep existing video for update
            $video_path = $_POST['current_video'];
        } elseif (isset($_POST['add_reel'])) {
            $errors[] = "Video is required";
        }
        
        if (empty($errors)) {
            if (isset($_POST['add_reel'])) {
                // Add new hero reel
                if (isDatabasePostgres()) {
                    $query = "INSERT INTO hero_reels (title, description, video_path) VALUES ('$title', '$description', '$video_path')";
                    $result = runQuery($query);
                    
                    if ($result) {
                        showAlert("Hero reel added successfully");
                        redirect('hero-reels.php');
                    } else {
                        $errors[] = "Error adding hero reel";
                    }
                } else {
                    // MySQL with prepared statements
                    $query = "INSERT INTO hero_reels (title, description, video_path) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sss", $title, $description, $video_path);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        showAlert("Hero reel added successfully");
                        redirect('hero-reels.php');
                    } else {
                        $errors[] = "Error adding hero reel: " . mysqli_error($conn);
                    }
                    
                    mysqli_stmt_close($stmt);
                }
            } elseif (isset($_POST['update_reel'])) {
                // Update existing hero reel
                $reel_id = (int)$_POST['reel_id'];
                
                if (isDatabasePostgres()) {
                    $query = "UPDATE hero_reels SET title = '$title', description = '$description', video_path = '$video_path' WHERE id = $reel_id";
                    $result = runQuery($query);
                    
                    if ($result) {
                        showAlert("Hero reel updated successfully");
                        redirect('hero-reels.php');
                    } else {
                        $errors[] = "Error updating hero reel";
                    }
                } else {
                    // MySQL with prepared statements
                    $query = "UPDATE hero_reels SET title = ?, description = ?, video_path = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sssi", $title, $description, $video_path, $reel_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        showAlert("Hero reel updated successfully");
                        redirect('hero-reels.php');
                    } else {
                        $errors[] = "Error updating hero reel: " . mysqli_error($conn);
                    }
                    
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } elseif (isset($_POST['delete_reel'])) {
        // Delete hero reel
        $reel_id = (int)$_POST['reel_id'];
        
        if (isDatabasePostgres()) {
            // Get the video path first to delete the file
            $query = "SELECT video_path FROM hero_reels WHERE id = $reel_id";
            $result = runQuery($query);
            
            if ($result && getNumRows($result) > 0) {
                $row = fetchRow($result);
                $video_path = $row['video_path'];
                
                // Delete the file
                if (!empty($video_path)) {
                    deleteFile('../' . $video_path);
                }
                
                // Delete from database
                $query = "DELETE FROM hero_reels WHERE id = $reel_id";
                $result = runQuery($query);
                
                if ($result) {
                    showAlert("Hero reel deleted successfully");
                    redirect('hero-reels.php');
                } else {
                    $errors[] = "Error deleting hero reel";
                }
            } else {
                $errors[] = "Hero reel not found";
            }
        } else {
            // MySQL with prepared statements
            // Get the video path first to delete the file
            $query = "SELECT video_path FROM hero_reels WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $reel_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $video_path);
            
            if (mysqli_stmt_fetch($stmt)) {
                mysqli_stmt_close($stmt);
                
                // Delete the file
                if (!empty($video_path)) {
                    deleteFile('../' . $video_path);
                }
                
                // Delete from database
                $query = "DELETE FROM hero_reels WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $reel_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    showAlert("Hero reel deleted successfully");
                    redirect('hero-reels.php');
                } else {
                    $errors[] = "Error deleting hero reel: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $errors[] = "Hero reel not found";
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Get hero reel for editing
$reel = null;
if ($action === 'edit' && $id > 0) {
    $reel = getHeroReel($id);
    
    if (!$reel) {
        showAlert("Hero reel not found", "error");
        redirect('hero-reels.php');
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-[#2B2B2A]">
            <?php echo $action === 'add' ? 'Add New Hero Reel' : ($action === 'edit' ? 'Edit Hero Reel' : 'Manage Hero Reels'); ?>
        </h1>
        
        <?php if ($action === 'list'): ?>
            <a href="hero-reels.php?action=add" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Add New Reel
            </a>
        <?php else: ?>
            <a href="hero-reels.php" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Back to Reels
            </a>
        <?php endif; ?>
    </div>
    
    <?php displayAlert(); ?>
    
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Please fix the following errors:</p>
            <ul class="list-disc ml-8">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if ($action === 'list'): ?>
        <!-- Hero Reels List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $heroReels = getHeroReels();
            if (!empty($heroReels)):
                foreach ($heroReels as $reel):
            ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="h-48 relative">
                        <?php if (pathinfo($reel['video_path'], PATHINFO_EXTENSION) === 'mp4' || pathinfo($reel['video_path'], PATHINFO_EXTENSION) === 'webm'): ?>
                            <div class="video-container">
                                <video muted loop class="w-full h-full object-cover">
                                    <source src="../<?php echo $reel['video_path']; ?>" type="video/<?php echo pathinfo($reel['video_path'], PATHINFO_EXTENSION); ?>">
                                    Your browser does not support the video tag.
                                </video>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <i class="fas fa-play-circle text-white text-4xl opacity-70 hover:opacity-100 transition-opacity duration-300"></i>
                                </div>
                            </div>
                        <?php else: ?>
                            <img src="../<?php echo $reel['video_path']; ?>" alt="<?php echo $reel['title']; ?>" class="w-full h-full object-cover">
                        <?php endif; ?>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-[#2B2B2A] mb-2"><?php echo $reel['title']; ?></h3>
                        <?php if (!empty($reel['description'])): ?>
                            <p class="text-gray-600 text-sm mb-4"><?php echo $reel['description']; ?></p>
                        <?php endif; ?>
                        
                        <div class="flex justify-end space-x-2">
                            <a href="hero-reels.php?action=edit&id=<?php echo $reel['id']; ?>" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="hero-reels.php" class="inline" onsubmit="return confirm('Are you sure you want to delete this hero reel?');">
                                <input type="hidden" name="reel_id" value="<?php echo $reel['id']; ?>">
                                <button type="submit" name="delete_reel" class="btn-delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php
                endforeach;
            else:
            ?>
                <div class="col-span-full bg-white rounded-lg shadow-md p-6 text-center">
                    <p class="text-gray-600 mb-4">No hero reels found.</p>
                    <a href="hero-reels.php?action=add" class="btn-primary">Add Hero Reel</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-[#2B2B2A] mb-4">About Hero Reels</h2>
            <p class="text-gray-600 mb-4">
                Hero reels are displayed in the hero section of the homepage. The most recently added reel will be shown by default.
                These reels should be high-quality videos or images that showcase your best work and capture visitors' attention.
            </p>
            <p class="text-gray-600">
                <strong>Recommended video specifications:</strong> MP4 or WebM format, 16:9 aspect ratio, maximum file size of 10MB.
            </p>
        </div>
    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Hero Reel Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" action="hero-reels.php" enctype="multipart/form-data">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="reel_id" value="<?php echo $reel['id']; ?>">
                    <input type="hidden" name="current_video" value="<?php echo $reel['video_path']; ?>">
                <?php endif; ?>
                
                <div class="mb-6">
                    <label for="title" class="block text-[#2B2B2A] font-medium mb-2">Reel Title *</label>
                    <input type="text" name="title" id="title" class="form-input" value="<?php echo isset($reel['title']) ? $reel['title'] : ''; ?>" required>
                </div>
                
                <div class="mb-6">
                    <label for="description" class="block text-[#2B2B2A] font-medium mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-input"><?php echo isset($reel['description']) ? $reel['description'] : ''; ?></textarea>
                    <p class="text-sm text-gray-500 mt-1">Optional brief description of the reel</p>
                </div>
                
                <div class="mb-6">
                    <label for="video" class="block text-[#2B2B2A] font-medium mb-2">
                        Video File <?php echo $action === 'add' ? '*' : '(Leave empty to keep current video)'; ?>
                    </label>
                    <input type="file" name="video" id="video" class="form-input file-input" data-preview="video-preview" accept="video/*,image/*" <?php echo $action === 'add' ? 'required' : ''; ?>>
                    
                    <div id="video-preview" class="mt-3">
                        <?php if (isset($reel['video_path']) && !empty($reel['video_path'])): ?>
                            <?php if (pathinfo($reel['video_path'], PATHINFO_EXTENSION) === 'mp4' || pathinfo($reel['video_path'], PATHINFO_EXTENSION) === 'webm'): ?>
                                <video controls class="max-h-40 rounded">
                                    <source src="../<?php echo $reel['video_path']; ?>" type="video/<?php echo pathinfo($reel['video_path'], PATHINFO_EXTENSION); ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <img src="../<?php echo $reel['video_path']; ?>" alt="Current Image" class="max-h-40 rounded">
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-sm text-gray-500 mt-3">
                        You can upload either a video (MP4 or WebM format recommended) or an image file.
                        For best results, use a 16:9 aspect ratio and keep the file size under 10MB.
                    </p>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" onclick="window.location='hero-reels.php'" class="mr-4 px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-300">
                        Cancel
                    </button>
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_reel' : 'update_reel'; ?>" class="px-6 py-2 bg-[#F44B12] text-white rounded-md hover:bg-[#d43e0f] transition duration-300">
                        <?php echo $action === 'add' ? 'Add Hero Reel' : 'Update Hero Reel'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script src="../assets/js/admin.js"></script>

<?php include 'includes/footer.php'; ?>
