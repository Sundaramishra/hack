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
    if (isset($_POST['add_member']) || isset($_POST['update_member'])) {
        $name = clean($_POST['name']);
        $position = clean($_POST['position']);
        $bio = clean($_POST['bio']);
        $linkedin = clean($_POST['linkedin']);
        $instagram = clean($_POST['instagram']);
        $facebook = isset($_POST['facebook']) ? clean($_POST['facebook']) : '';
        
        // Validate inputs
        $errors = [];
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        if (empty($position)) {
            $errors[] = "Position is required";
        }
        if (empty($bio)) {
            $errors[] = "Bio is required";
        }
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_path = uploadFile($_FILES['image'], '../uploads/team/');
            
            if (!$image_path) {
                $errors[] = "Error uploading image. Please try again.";
            } else {
                $image_path = substr($image_path, 3); // Remove the leading '../'
            }
        } elseif (isset($_POST['update_member']) && empty($_FILES['image']['name'])) {
            // Keep existing image for update
            $image_path = $_POST['current_image'];
        } elseif (isset($_POST['add_member'])) {
            $errors[] = "Image is required";
        }
        
        if (empty($errors)) {
            if (isset($_POST['add_member'])) {
                // Add new team member
                if (isDatabasePostgres()) {
                    $query = "INSERT INTO team (name, position, bio, image, linkedin, instagram, facebook) VALUES ('$name', '$position', '$bio', '$image_path', '$linkedin', '$instagram', '$facebook')";
                    $result = runQuery($query);
                    
                    if ($result) {
                        showAlert("Team member added successfully");
                        redirect('team.php');
                    } else {
                        $errors[] = "Error adding team member";
                    }
                } else {
                    // MySQL with prepared statements
                    $query = "INSERT INTO team (name, position, bio, image, linkedin, instagram, facebook) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sssssss", $name, $position, $bio, $image_path, $linkedin, $instagram, $facebook);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        showAlert("Team member added successfully");
                        redirect('team.php');
                    } else {
                        $errors[] = "Error adding team member: " . mysqli_error($conn);
                    }
                    
                    mysqli_stmt_close($stmt);
                }
            } elseif (isset($_POST['update_member'])) {
                // Update existing team member
                $member_id = (int)$_POST['member_id'];
                
                if (isDatabasePostgres()) {
                    $query = "UPDATE team SET name = '$name', position = '$position', bio = '$bio', image = '$image_path', linkedin = '$linkedin', instagram = '$instagram', facebook = '$facebook' WHERE id = $member_id";
                    $result = runQuery($query);
                    
                    if ($result) {
                        showAlert("Team member updated successfully");
                        redirect('team.php');
                    } else {
                        $errors[] = "Error updating team member";
                    }
                } else {
                    // MySQL with prepared statements
                    $query = "UPDATE team SET name = ?, position = ?, bio = ?, image = ?, linkedin = ?, instagram = ?, facebook = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sssssssi", $name, $position, $bio, $image_path, $linkedin, $instagram, $facebook, $member_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        showAlert("Team member updated successfully");
                        redirect('team.php');
                    } else {
                        $errors[] = "Error updating team member: " . mysqli_error($conn);
                    }
                    
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } elseif (isset($_POST['delete_member'])) {
        // Delete team member
        $member_id = (int)$_POST['member_id'];
        
        // Get the image path first to delete the file
        if (isDatabasePostgres()) {
            $query = "SELECT image FROM team WHERE id = $member_id";
            $result = runQuery($query);
            
            if ($result && getNumRows($result) > 0) {
                $row = fetchRow($result);
                $image_path = $row['image'];
                
                // Delete the file
                if (!empty($image_path)) {
                    deleteFile('../' . $image_path);
                }
                
                // Delete from database
                $query = "DELETE FROM team WHERE id = $member_id";
                $result = runQuery($query);
                
                if ($result) {
                    showAlert("Team member deleted successfully");
                    redirect('team.php');
                } else {
                    $errors[] = "Error deleting team member";
                }
            } else {
                $errors[] = "Team member not found";
            }
        } else {
            // MySQL with prepared statements
            $query = "SELECT image FROM team WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $member_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $image_path);
            
            if (mysqli_stmt_fetch($stmt)) {
                mysqli_stmt_close($stmt);
                
                // Delete the file
                if (!empty($image_path)) {
                    deleteFile('../' . $image_path);
                }
                
                // Delete from database
                $query = "DELETE FROM team WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $member_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    showAlert("Team member deleted successfully");
                    redirect('team.php');
                } else {
                    $errors[] = "Error deleting team member: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $errors[] = "Team member not found";
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Get team member for editing
$member = null;
if ($action === 'edit' && $id > 0) {
    $member = getTeamMember($id);
    
    if (!$member) {
        showAlert("Team member not found", "error");
        redirect('team.php');
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-[#2B2B2A]">
            <?php echo $action === 'add' ? 'Add New Team Member' : ($action === 'edit' ? 'Edit Team Member' : 'Manage Team'); ?>
        </h1>
        
        <?php if ($action === 'list'): ?>
            <a href="team.php?action=add" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Add New Member
            </a>
        <?php else: ?>
            <a href="team.php" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Back to Team
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
        <!-- Team Members List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $teamMembers = getTeamMembers();
            if (!empty($teamMembers)):
                foreach ($teamMembers as $member):
            ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="h-48 overflow-hidden">
                        <img src="../<?php echo $member['image']; ?>" alt="<?php echo $member['name']; ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-[#2B2B2A]"><?php echo $member['name']; ?></h3>
                        <p class="text-[#F44B12] font-medium mb-2"><?php echo $member['position']; ?></p>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo $member['bio']; ?></p>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex space-x-3">
                                <?php if (!empty($member['linkedin'])): ?>
                                    <a href="<?php echo $member['linkedin']; ?>" target="_blank" class="text-[#2B2B2A] hover:text-[#F44B12]">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($member['instagram'])): ?>
                                    <a href="<?php echo $member['instagram']; ?>" target="_blank" class="text-[#2B2B2A] hover:text-[#F44B12]">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($member['facebook'])): ?>
                                    <a href="<?php echo $member['facebook']; ?>" target="_blank" class="text-[#2B2B2A] hover:text-[#F44B12]">
                                        <i class="fab fa-facebook"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex space-x-2">
                                <a href="team.php?action=edit&id=<?php echo $member['id']; ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="team.php" class="inline" onsubmit="return confirm('Are you sure you want to delete this team member?');">
                                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                    <button type="submit" name="delete_member" class="btn-delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                endforeach;
            else:
            ?>
                <div class="col-span-full bg-white rounded-lg shadow-md p-6 text-center">
                    <p class="text-gray-600 mb-4">No team members found.</p>
                    <a href="team.php?action=add" class="btn-primary">Add Team Member</a>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Team Member Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" action="team.php" enctype="multipart/form-data">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                    <input type="hidden" name="current_image" value="<?php echo $member['image']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-[#2B2B2A] font-medium mb-2">Name *</label>
                        <input type="text" name="name" id="name" class="form-input" value="<?php echo isset($member['name']) ? $member['name'] : ''; ?>" required>
                    </div>
                    
                    <div>
                        <label for="position" class="block text-[#2B2B2A] font-medium mb-2">Position *</label>
                        <input type="text" name="position" id="position" class="form-input" value="<?php echo isset($member['position']) ? $member['position'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="bio" class="block text-[#2B2B2A] font-medium mb-2">Bio *</label>
                    <textarea name="bio" id="bio" rows="4" class="form-input" required><?php echo isset($member['bio']) ? $member['bio'] : ''; ?></textarea>
                </div>
                
                <div class="mb-6">
                    <label for="image" class="block text-[#2B2B2A] font-medium mb-2">
                        Profile Image <?php echo $action === 'add' ? '*' : '(Leave empty to keep current image)'; ?>
                    </label>
                    <input type="file" name="image" id="image" class="form-input file-input" data-preview="image-preview" accept="image/*" <?php echo $action === 'add' ? 'required' : ''; ?>>
                    
                    <div id="image-preview" class="mt-3">
                        <?php if (isset($member['image']) && !empty($member['image'])): ?>
                            <img src="../<?php echo $member['image']; ?>" alt="Current Image" class="max-h-40 rounded">
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="linkedin" class="block text-[#2B2B2A] font-medium mb-2">LinkedIn Profile</label>
                        <input type="url" name="linkedin" id="linkedin" class="form-input" value="<?php echo isset($member['linkedin']) ? $member['linkedin'] : ''; ?>" placeholder="https://linkedin.com/in/username">
                    </div>
                    
                    <div>
                        <label for="instagram" class="block text-[#2B2B2A] font-medium mb-2">Instagram Profile</label>
                        <input type="url" name="instagram" id="instagram" class="form-input" value="<?php echo isset($member['instagram']) ? $member['instagram'] : ''; ?>" placeholder="https://instagram.com/username">
                    </div>
                    
                    <div>
                        <label for="facebook" class="block text-[#2B2B2A] font-medium mb-2">Facebook Profile</label>
                        <input type="url" name="facebook" id="facebook" class="form-input" value="<?php echo isset($member['facebook']) ? $member['facebook'] : ''; ?>" placeholder="https://facebook.com/username">
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" onclick="window.location='team.php'" class="mr-4 px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-300">
                        Cancel
                    </button>
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_member' : 'update_member'; ?>" class="px-6 py-2 bg-[#F44B12] text-white rounded-md hover:bg-[#d43e0f] transition duration-300">
                        <?php echo $action === 'add' ? 'Add Team Member' : 'Update Team Member'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script src="../assets/js/admin.js"></script>

<?php include 'includes/footer.php'; ?>
