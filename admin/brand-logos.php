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
    if (isset($_POST['add_logos_bulk'])) {
        // Bulk upload logic
        $brand_names = array_map('trim', preg_split('/[\r\n,]+/', $_POST['brand_names']));
        $brand_names = array_filter($brand_names, fn($v) => $v !== '');
        $brand_names = array_values($brand_names);
        $errors = [];
        $uploaded = 0;
        if (!isset($_FILES['logos']) || !count($_FILES['logos']['name'])) {
            $errors[] = "At least one logo image is required.";
        } else {
            $total = count($_FILES['logos']['name']);
            for ($i = 0; $i < $total; $i++) {
                $brand_name = $brand_names[$i] ?? pathinfo($_FILES['logos']['name'][$i], PATHINFO_FILENAME);
                $logo_file = [
                    'name' => $_FILES['logos']['name'][$i],
                    'type' => $_FILES['logos']['type'][$i],
                    'tmp_name' => $_FILES['logos']['tmp_name'][$i],
                    'error' => $_FILES['logos']['error'][$i],
                    'size' => $_FILES['logos']['size'][$i],
                ];
                if (empty($brand_name)) {
                    $errors[] = "Brand name is required for logo #".($i+1);
                    continue;
                }
                if ($logo_file['error'] !== 0) {
                    $errors[] = "Error uploading logo for $brand_name.";
                    continue;
                }
                $logo_path = uploadFile($logo_file, '../uploads/brand-logos/');
                if (!$logo_path) {
                    $errors[] = "Error uploading logo for $brand_name.";
                    continue;
                }
                $logo_path = substr($logo_path, 3); // Remove leading '../'
                // Insert into DB
                if (isDatabasePostgres()) {
                    $query = "INSERT INTO brand_logos (brand_name, logo_path) VALUES ('$brand_name', '$logo_path')";
                    $result = runQuery($query);
                    if ($result) $uploaded++;
                } else {
                    $query = "INSERT INTO brand_logos (brand_name, logo_path) VALUES (?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ss", $brand_name, $logo_path);
                    if (mysqli_stmt_execute($stmt)) $uploaded++;
                    mysqli_stmt_close($stmt);
                }
            }
        }
        if ($uploaded > 0) {
            showAlert("$uploaded brand logo(s) added successfully");
            redirect('brand-logos.php');
        }
    } elseif (isset($_POST['add_logo']) || isset($_POST['update_logo'])) {
        $brand_name = clean($_POST['brand_name']);
        
        // Validate inputs
        $errors = [];
        if (empty($brand_name)) {
            $errors[] = "Brand name is required";
        }
        
        // Handle logo upload
        $logo_path = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $logo_path = uploadFile($_FILES['logo'], '../uploads/brand-logos/');
            
            if (!$logo_path) {
                $errors[] = "Error uploading logo. Please try again.";
            } else {
                $logo_path = substr($logo_path, 3); // Remove the leading '../'
            }
        } elseif (isset($_POST['update_logo']) && empty($_FILES['logo']['name'])) {
            // Keep existing logo for update
            $logo_path = $_POST['current_logo'];
        } elseif (isset($_POST['add_logo'])) {
            $errors[] = "Logo image is required";
        }
        
        if (empty($errors)) {
            if (isset($_POST['add_logo'])) {
                // Add new brand logo
                if (isDatabasePostgres()) {
                    $query = "INSERT INTO brand_logos (brand_name, logo_path) VALUES ('$brand_name', '$logo_path')";
                    $result = runQuery($query);
                    
                    if ($result) {
                        showAlert("Brand logo added successfully");
                        redirect('brand-logos.php');
                    } else {
                        $errors[] = "Error adding brand logo";
                    }
                } else {
                    // MySQL with prepared statements
                    $query = "INSERT INTO brand_logos (brand_name, logo_path) VALUES (?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ss", $brand_name, $logo_path);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        showAlert("Brand logo added successfully");
                        redirect('brand-logos.php');
                    } else {
                        $errors[] = "Error adding brand logo: " . mysqli_error($conn);
                    }
                    
                    mysqli_stmt_close($stmt);
                }
            } elseif (isset($_POST['update_logo'])) {
                // Update existing brand logo
                $logo_id = (int)$_POST['logo_id'];
                
                if (isDatabasePostgres()) {
                    $query = "UPDATE brand_logos SET brand_name = '$brand_name', logo_path = '$logo_path' WHERE id = $logo_id";
                    $result = runQuery($query);
                    
                    if ($result) {
                        showAlert("Brand logo updated successfully");
                        redirect('brand-logos.php');
                    } else {
                        $errors[] = "Error updating brand logo";
                    }
                } else {
                    // MySQL with prepared statements
                    $query = "UPDATE brand_logos SET brand_name = ?, logo_path = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssi", $brand_name, $logo_path, $logo_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        showAlert("Brand logo updated successfully");
                        redirect('brand-logos.php');
                    } else {
                        $errors[] = "Error updating brand logo: " . mysqli_error($conn);
                    }
                    
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } elseif (isset($_POST['delete_logo'])) {
        // Delete brand logo
        $logo_id = (int)$_POST['logo_id'];
        
        if (isDatabasePostgres()) {
            // Get the logo path first to delete the file
            $query = "SELECT logo_path FROM brand_logos WHERE id = $logo_id";
            $result = runQuery($query);
            
            if ($result && getNumRows($result) > 0) {
                $row = fetchRow($result);
                $logo_path = $row['logo_path'];
                
                // Delete the file
                if (!empty($logo_path)) {
                    deleteFile('../' . $logo_path);
                }
                
                // Delete from database
                $query = "DELETE FROM brand_logos WHERE id = $logo_id";
                $result = runQuery($query);
                
                if ($result) {
                    showAlert("Brand logo deleted successfully");
                    redirect('brand-logos.php');
                } else {
                    $errors[] = "Error deleting brand logo";
                }
            } else {
                $errors[] = "Brand logo not found";
            }
        } else {
            // MySQL with prepared statements
            // Get the logo path first to delete the file
            $query = "SELECT logo_path FROM brand_logos WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $logo_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $logo_path);
            
            if (mysqli_stmt_fetch($stmt)) {
                mysqli_stmt_close($stmt);
                
                // Delete the file
                if (!empty($logo_path)) {
                    deleteFile('../' . $logo_path);
                }
                
                // Delete from database
                $query = "DELETE FROM brand_logos WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $logo_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    showAlert("Brand logo deleted successfully");
                    redirect('brand-logos.php');
                } else {
                    $errors[] = "Error deleting brand logo: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $errors[] = "Brand logo not found";
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Get brand logo for editing
$logo = null;
if ($action === 'edit' && $id > 0) {
    if (isDatabasePostgres()) {
        // Get logo details from database
        $query = "SELECT * FROM brand_logos WHERE id = $id";
        $result = runQuery($query);
        
        if ($result && getNumRows($result) > 0) {
            $logo = fetchRow($result);
        } else {
            showAlert("Brand logo not found", "error");
            redirect('brand-logos.php');
        }
    } else {
        // MySQL with prepared statements
        $query = "SELECT * FROM brand_logos WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $logo = mysqli_fetch_assoc($result);
        } else {
            showAlert("Brand logo not found", "error");
            redirect('brand-logos.php');
        }
        
        mysqli_stmt_close($stmt);
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-[#2B2B2A]">
            <?php echo $action === 'add' ? 'Add New Brand Logo' : ($action === 'edit' ? 'Edit Brand Logo' : 'Manage Brand Logos'); ?>
        </h1>
        
        <?php if ($action === 'list'): ?>
            <a href="brand-logos.php?action=add" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Add New Logo
            </a>
        <?php else: ?>
            <a href="brand-logos.php" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Back to Logos
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
        <!-- Brand Logos List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                <?php
                $logos = [];
                
                if (isDatabasePostgres()) {
                    $query = "SELECT * FROM brand_logos ORDER BY id DESC";
                    $result = runQuery($query);
                    
                    if ($result && getNumRows($result) > 0) {
                        $logos = fetchAllRows($result);
                    }
                } else {
                    $query = "SELECT * FROM brand_logos ORDER BY id DESC";
                    $result = mysqli_query($conn, $query);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        $logos = [];
                        while ($row = mysqli_fetch_assoc($result)) {
                            $logos[] = $row;
                        }
                    }
                }
                
                if (!empty($logos)):
                    foreach ($logos as $logo):
                ?>
                    <div class="bg-gray-50 rounded-lg p-4 flex flex-col items-center justify-center shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="h-16 flex items-center justify-center mb-4">
                            <img src="../<?php echo $logo['logo_path']; ?>" alt="<?php echo $logo['brand_name']; ?>" class="max-h-16">
                        </div>
                        <h3 class="text-sm font-medium text-[#2B2B2A] text-center mb-3 truncate w-full"><?php echo $logo['brand_name']; ?></h3>
                        <div class="flex space-x-2">
                            <a href="brand-logos.php?action=edit&id=<?php echo $logo['id']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="brand-logos.php" class="inline" onsubmit="return confirm('Are you sure you want to delete this brand logo?');">
                                <input type="hidden" name="logo_id" value="<?php echo $logo['id']; ?>">
                                <button type="submit" name="delete_logo" class="text-red-500 hover:text-red-700" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php
                    endforeach;
                else:
                ?>
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500 mb-4">No brand logos found</p>
                        <a href="brand-logos.php?action=add" class="btn-primary">
                            <i class="fas fa-plus mr-2"></i> Add Brand Logo
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-[#2B2B2A] mb-4">About Brand Logos</h2>
            <p class="text-gray-600 mb-4">
                Brand logos are displayed in the "Brands We've Worked With" section on the homepage and the "Brands That Trust Us" section on the portfolio page.
                These logos help establish credibility and showcase your client relationships.
            </p>
            <p class="text-gray-600">
                <strong>Recommended logo specifications:</strong> Transparent PNG or SVG format, square or landscape orientation, maximum width of 200px.
            </p>
        </div>
    <?php elseif ($action === 'edit'): ?>
        <!-- Brand Logo Edit Form (single logo) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" action="brand-logos.php" enctype="multipart/form-data">
                <input type="hidden" name="logo_id" value="<?php echo $logo['id']; ?>">
                <input type="hidden" name="current_logo" value="<?php echo $logo['logo_path']; ?>">
                <div class="mb-6">
                    <label for="brand_name" class="block text-[#2B2B2A] font-medium mb-2">Brand Name *</label>
                    <input type="text" name="brand_name" id="brand_name" class="form-input" value="<?php echo isset($logo['brand_name']) ? $logo['brand_name'] : ''; ?>" required>
                </div>
                <div class="mb-6">
                    <label for="logo" class="block text-[#2B2B2A] font-medium mb-2">
                        Logo Image (Leave empty to keep current logo)
                    </label>
                    <input type="file" name="logo" id="logo" class="form-input file-input" data-preview="logo-preview" accept="image/*">
                    <div id="logo-preview" class="mt-3">
                        <?php if (isset($logo['logo_path']) && !empty($logo['logo_path'])): ?>
                            <img src="../<?php echo $logo['logo_path']; ?>" alt="Current Logo" class="max-h-20 rounded">
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-gray-500 mt-3">
                        For best results, use a transparent PNG or SVG format. The logo will be displayed at a maximum height of 64px.
                    </p>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="window.location='brand-logos.php'" class="mr-4 px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-300">Cancel</button>
                    <button type="submit" name="update_logo" class="px-6 py-2 bg-[#F44B12] text-white rounded-md hover:bg-[#d43e0f] transition duration-300">Update Brand Logo</button>
                </div>
            </form>
        </div>
    <?php elseif ($action === 'add'): ?>
        <!-- Multi Brand Logo Upload Form (only for add) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" action="brand-logos.php" enctype="multipart/form-data">
                <div class="mb-6">
                    <label for="brand_names" class="block text-[#2B2B2A] font-medium mb-2">Brand Names <span class="text-gray-500">(comma or line separated, in order of logos)</span> *</label>
                    <textarea name="brand_names" id="brand_names" class="form-input" rows="2" placeholder="e.g. Nike, Adidas, Puma" required></textarea>
                    <p class="text-sm text-gray-500 mt-2">Enter one brand name per logo, in the same order as selected files. If left blank for a file, the filename will be used as the brand name.</p>
                </div>
                <div class="mb-6">
                    <label for="logos" class="block text-[#2B2B2A] font-medium mb-2">Logo Images *</label>
                    <input type="file" name="logos[]" id="logos" class="form-input file-input" accept="image/*" multiple required>
                    <div id="logo-preview" class="mt-3 flex flex-wrap gap-4"></div>
                    <p class="text-sm text-gray-500 mt-3">You can select multiple logos at once. For best results, use transparent PNG or SVG format. Each logo will be displayed at a maximum height of 64px.</p>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="window.location='brand-logos.php'" class="mr-4 px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-300">Cancel</button>
                    <button type="submit" name="add_logos_bulk" class="px-6 py-2 bg-[#F44B12] text-white rounded-md hover:bg-[#d43e0f] transition duration-300">Add Brand Logos</button>
                </div>
            </form>
        </div>
        <script>
        // Preview selected images
        document.getElementById('logos').addEventListener('change', function(e) {
            const preview = document.getElementById('logo-preview');
            preview.innerHTML = '';
            Array.from(e.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    const img = document.createElement('img');
                    img.src = ev.target.result;
                    img.className = 'max-h-20 rounded shadow';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
        </script>
    <?php endif; ?>
</div>

<script src="../assets/js/admin.js"></script>

<?php include 'includes/footer.php'; ?>
