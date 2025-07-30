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
    if (isset($_POST['add_service']) || isset($_POST['update_service'])) {
        $title = clean($_POST['title']);
        $icon = clean($_POST['icon']);
        $description = clean($_POST['description']);
        $sub_services = isset($_POST['sub_services']) ? clean($_POST['sub_services']) : '';
        
        // Validate inputs
        $errors = [];
        if (empty($title)) {
            $errors[] = "Title is required";
        }
        if (empty($icon)) {
            $errors[] = "Icon is required";
        }
        if (empty($description)) {
            $errors[] = "Description is required";
        }
        
        if (empty($errors)) {
            if (isset($_POST['add_service'])) {
                // Add new service
                if (isDatabasePostgres()) {
                    $query = "INSERT INTO services (title, icon, description, sub_services) VALUES ('$title', '$icon', '$description', '$sub_services')";
                    $result = runQuery($query);
                    
                    if ($result) {
                        showAlert("Service added successfully");
                        redirect('services.php');
                    } else {
                        $errors[] = "Error adding service";
                    }
                } else {
                    // MySQL with prepared statements
                    $query = "INSERT INTO services (title, icon, description, sub_services) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssss", $title, $icon, $description, $sub_services);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        showAlert("Service added successfully");
                        redirect('services.php');
                    } else {
                        $errors[] = "Error adding service: " . mysqli_error($conn);
                    }
                    
                    mysqli_stmt_close($stmt);
                }
            } elseif (isset($_POST['update_service'])) {
                // Update existing service
                $service_id = (int)$_POST['service_id'];
                
                if (isDatabasePostgres()) {
                    $query = "UPDATE services SET title = '$title', icon = '$icon', description = '$description', sub_services = '$sub_services' WHERE id = $service_id";
                    $result = runQuery($query);
                    
                    if ($result) {
                        showAlert("Service updated successfully");
                        redirect('services.php');
                    } else {
                        $errors[] = "Error updating service";
                    }
                } else {
                    // MySQL with prepared statements
                    $query = "UPDATE services SET title = ?, icon = ?, description = ?, sub_services = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssssi", $title, $icon, $description, $sub_services, $service_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        showAlert("Service updated successfully");
                        redirect('services.php');
                    } else {
                        $errors[] = "Error updating service: " . mysqli_error($conn);
                    }
                    
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } elseif (isset($_POST['delete_service'])) {
        // Delete service
        $service_id = (int)$_POST['service_id'];
        
        if (isDatabasePostgres()) {
            $query = "DELETE FROM services WHERE id = $service_id";
            $result = runQuery($query);
            
            if ($result) {
                showAlert("Service deleted successfully");
                redirect('services.php');
            } else {
                $errors[] = "Error deleting service";
            }
        } else {
            // MySQL with prepared statements
            $query = "DELETE FROM services WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $service_id);
            
            if (mysqli_stmt_execute($stmt)) {
                showAlert("Service deleted successfully");
                redirect('services.php');
            } else {
                $errors[] = "Error deleting service: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

// Get service for editing
$service = null;
if ($action === 'edit' && $id > 0) {
    $service = getService($id);
    
    if (!$service) {
        showAlert("Service not found", "error");
        redirect('services.php');
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-[#2B2B2A]">
            <?php echo $action === 'add' ? 'Add New Service' : ($action === 'edit' ? 'Edit Service' : 'Manage Services'); ?>
        </h1>
        
        <?php if ($action === 'list'): ?>
            <a href="services.php?action=add" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Add New Service
            </a>
        <?php else: ?>
            <a href="services.php" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Back to Services
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
        <!-- Services List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="w-16">Icon</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th class="w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $services = getServices();
                        if (!empty($services)):
                            foreach ($services as $service):
                        ?>
                            <tr>
                                <td class="text-center">
                                    <i class="<?php echo $service['icon']; ?> text-[#F44B12] text-2xl"></i>
                                </td>
                                <td><?php echo $service['title']; ?></td>
                                <td><?php echo substr($service['description'], 0, 100) . (strlen($service['description']) > 100 ? '...' : ''); ?></td>
                                <td>
                                    <div class="flex space-x-2">
                                        <a href="services.php?action=edit&id=<?php echo $service['id']; ?>" class="btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="services.php" class="inline" onsubmit="return confirm('Are you sure you want to delete this service?');">
                                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                            <button type="submit" name="delete_service" class="btn-delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">No services found. <a href="services.php?action=add" class="text-[#F44B12] hover:underline">Add a service</a>.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Service Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" action="services.php">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                <?php endif; ?>
                
                <div class="mb-6">
                    <label for="title" class="block text-[#2B2B2A] font-medium mb-2">Service Title *</label>
                    <input type="text" name="title" id="title" class="form-input" value="<?php echo isset($service['title']) ? $service['title'] : ''; ?>" required>
                </div>
                
                <div class="mb-6">
                    <label for="icon" class="block text-[#2B2B2A] font-medium mb-2">Icon Class (FontAwesome) *</label>
                    <div class="flex">
                        <input type="text" name="icon" id="icon" class="form-input" value="<?php echo isset($service['icon']) ? $service['icon'] : ''; ?>" required>
                        <div class="ml-4 flex items-center">
                            <span class="preview-icon text-[#F44B12] text-2xl">
                                <?php if (isset($service['icon']) && !empty($service['icon'])): ?>
                                    <i class="<?php echo $service['icon']; ?>"></i>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        Example: fas fa-paint-brush, fas fa-video, fas fa-hashtag, fas fa-cube
                    </p>
                </div>
                
                <div class="mb-6">
    <label for="description" class="block text-[#2B2B2A] font-medium mb-2">Description *</label>
    <textarea name="description" id="description" rows="4" class="form-input"><?php echo isset($service['description']) ? htmlspecialchars($service['description']) : ''; ?></textarea>
</div>

                
                <div class="mb-6">
                    <label for="sub_services" class="block text-[#2B2B2A] font-medium mb-2">Sub-Services</label>
                    <textarea name="sub_services" id="sub_services" rows="3" class="form-input" placeholder="Enter each sub-service on a new line"><?php echo isset($service['sub_services']) ? $service['sub_services'] : ''; ?></textarea>
                    <p class="text-sm text-gray-500 mt-1">
                        Enter each sub-service on a new line. These will be displayed as bullet points.
                    </p>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" onclick="window.location='services.php'" class="mr-4 px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-300">
                        Cancel
                    </button>
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_service' : 'update_service'; ?>" class="px-6 py-2 bg-[#F44B12] text-white rounded-md hover:bg-[#d43e0f] transition duration-300">
                        <?php echo $action === 'add' ? 'Add Service' : 'Update Service'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
    // Icon preview
    document.addEventListener('DOMContentLoaded', function() {
        const iconInput = document.getElementById('icon');
        const previewIcon = document.querySelector('.preview-icon');
        
        if (iconInput && previewIcon) {
            iconInput.addEventListener('input', function() {
                const iconClass = this.value.trim();
                if (iconClass) {
                    previewIcon.innerHTML = `<i class="${iconClass}"></i>`;
                } else {
                    previewIcon.innerHTML = '';
                }
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
