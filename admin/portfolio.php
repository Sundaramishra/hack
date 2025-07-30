<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Sorting
$sortOptions = [
    'created_at_desc' => 'Newest First',
    'created_at_asc'  => 'Oldest First',
    'title_asc'       => 'Title A-Z',
    'title_desc'      => 'Title Z-A'
];
$selectedSort = $_GET['sort'] ?? 'created_at_desc';

switch ($selectedSort) {
    case 'created_at_asc':  $orderBy = "created_at ASC"; break;
    case 'title_asc':       $orderBy = "title ASC"; break;
    case 'title_desc':      $orderBy = "title DESC"; break;
    default:                $orderBy = "created_at DESC";
}

// Helper for dynamic uploads
function handleExtraUploads($field, $dir, $current = []) {
    $uploaded = [];
    if (!empty($_FILES[$field]['name'])) {
        foreach ($_FILES[$field]['name'] as $i => $name) {
            if (!empty($name) && $_FILES[$field]['error'][$i] == 0) {
                $file = [
                    'name' => $name,
                    'type' => $_FILES[$field]['type'][$i],
                    'tmp_name' => $_FILES[$field]['tmp_name'][$i],
                    'error' => $_FILES[$field]['error'][$i],
                    'size' => $_FILES[$field]['size'][$i]
                ];
                $path = uploadFile($file, $dir);
                if ($path) $uploaded[] = substr($path, 3);
            }
        }
    }
    if (!empty($current)) {
        foreach ($current as $curr) {
            if (!empty($curr)) $uploaded[] = $curr;
        }
    }
    return $uploaded;
}

// Handle add/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete portfolio
    if (isset($_POST['delete_portfolio']) && isset($_POST['portfolio_id'])) {
        $portfolio_id = (int)$_POST['portfolio_id'];
        $delQuery = "SELECT thumbnail, photo1, photo2, photo3, photo4, photo5, video_story1, video_story2, reel1, reel2, reel3, reel4, extra_images, extra_videos FROM portfolio WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delQuery);
        mysqli_stmt_bind_param($stmt, "i", $portfolio_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        foreach (['thumbnail', 'photo1', 'photo2', 'photo3', 'photo4', 'photo5', 'video_story1', 'video_story2', 'reel1', 'reel2', 'reel3', 'reel4'] as $f) {
            if (!empty($row[$f])) @unlink('../' . $row[$f]);
        }
        if (!empty($row['extra_images'])) foreach (json_decode($row['extra_images'], true) as $img) { @unlink('../' . $img); }
        if (!empty($row['extra_videos'])) foreach (json_decode($row['extra_videos'], true) as $vid) { @unlink('../' . $vid); }
        $stmt = mysqli_prepare($conn, "DELETE FROM portfolio WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $portfolio_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        showAlert("Portfolio item deleted successfully");
        redirect('portfolio.php');
    }

    function handleUpload($field, $dir, $current = '') {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $path = uploadFile($_FILES[$field], $dir);
            if ($path) return substr($path, 3);
            return '';
        }
        return $current;
    }

    $title = clean($_POST['title'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $role = clean($_POST['role'] ?? '');
    $timeline = clean($_POST['timeline'] ?? '');
    $thumbnail = handleUpload('thumbnail', '../uploads/portfolio/thumbnails/', $_POST['current_thumbnail'] ?? '');

    $photo1 = handleUpload('photo1', '../uploads/portfolio/images/', $_POST['current_photo1'] ?? '');
    $photo2 = handleUpload('photo2', '../uploads/portfolio/images/', $_POST['current_photo2'] ?? '');
    $photo3 = handleUpload('photo3', '../uploads/portfolio/images/', $_POST['current_photo3'] ?? '');
    $photo4 = handleUpload('photo4', '../uploads/portfolio/images/', $_POST['current_photo4'] ?? '');
    $photo5 = handleUpload('photo5', '../uploads/portfolio/images/', $_POST['current_photo5'] ?? '');

    $video_story1 = $_POST['video_story1_path'] ?? ($_POST['current_video_story1'] ?? '');
    $video_story2 = $_POST['video_story2_path'] ?? ($_POST['current_video_story2'] ?? '');
    $reel1 = $_POST['reel1_path'] ?? ($_POST['current_reel1'] ?? '');
    $reel2 = $_POST['reel2_path'] ?? ($_POST['current_reel2'] ?? '');
    $reel3 = $_POST['reel3_path'] ?? ($_POST['current_reel3'] ?? '');
    $reel4 = $_POST['reel4_path'] ?? ($_POST['current_reel4'] ?? '');

    // Dynamic images/videos
    $extra_images = handleExtraUploads('extra_images', '../uploads/portfolio/images/', $_POST['current_extra_images'] ?? []);
    $extra_videos = handleExtraUploads('extra_videos', '../uploads/portfolio/videos/', $_POST['current_extra_videos'] ?? []);
    $json_extra_images = !empty($extra_images) ? json_encode($extra_images) : null;
    $json_extra_videos = !empty($extra_videos) ? json_encode($extra_videos) : null;

    if (isset($_POST['add_portfolio'])) {
        $query = "INSERT INTO portfolio (title, description, services_provided, timeline, thumbnail, photo1, photo2, photo3, photo4, photo5, video_story1, video_story2, reel1, reel2, reel3, reel4, extra_images, extra_videos)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssssssssssssss",
            $title, $description, $role, $timeline, $thumbnail,
            $photo1, $photo2, $photo3, $photo4, $photo5,
            $video_story1, $video_story2,
            $reel1, $reel2, $reel3, $reel4,
            $json_extra_images, $json_extra_videos
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        showAlert("Portfolio item added successfully");
        redirect('portfolio.php');
    } elseif (isset($_POST['update_portfolio'])) {
        $portfolio_id = (int)$_POST['portfolio_id'];
        $query = "UPDATE portfolio SET title=?, description=?, services_provided=?, timeline=?, thumbnail=?, photo1=?, photo2=?, photo3=?, photo4=?, photo5=?, video_story1=?, video_story2=?, reel1=?, reel2=?, reel3=?, reel4=?, extra_images=?, extra_videos=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssssssssssssssi",
            $title, $description, $role, $timeline, $thumbnail,
            $photo1, $photo2, $photo3, $photo4, $photo5,
            $video_story1, $video_story2,
            $reel1, $reel2, $reel3, $reel4,
            $json_extra_images, $json_extra_videos, $portfolio_id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        showAlert("Portfolio item updated successfully");
        redirect('portfolio.php');
    }
}

// Load for edit/view
$portfolio = null;
if (($action === 'edit' || $action === 'view') && $id > 0) {
    $query = "SELECT * FROM portfolio WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $portfolio = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$portfolio) {
        showAlert("Portfolio item not found", "error");
        redirect('portfolio.php');
    }
}

include 'includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/resumablejs@1/resumable.js"></script>

<div class="container mx-auto px-2 sm:px-4 py-8">
    <?php if ($action === 'list'): ?>
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 md:gap-0">
        <h1 class="text-2xl font-bold">Portfolio</h1>
        <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
            <form method="get" action="portfolio.php" class="w-full sm:w-auto mr-0 sm:mr-4">
                <select name="sort" onchange="this.form.submit()" class="form-input w-full sm:w-auto">
                    <?php foreach ($sortOptions as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php if ($selectedSort === $key) echo 'selected'; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="portfolio.php?action=add" class="btn btn-primary w-full sm:w-auto">+ Add Portfolio</a>
        </div>
    </div>
    <?php displayAlert(); ?>
    <?php
    $portfolios = [];
    $sql = "SELECT * FROM portfolio ORDER BY $orderBy";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $portfolios[] = $row;
    }
    ?>
    <?php if (count($portfolios)): ?>
    <div class="overflow-x-auto">
    <table class="table-auto w-full bg-white shadow rounded mb-6 text-sm md:text-base">
        <thead>
            <tr>
                <th>Thumbnail</th>
                <th>Title</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($portfolios as $item): ?>
            <tr>
                <td>
                    <?php if ($item['thumbnail']): ?>
                        <img src="../<?php echo $item['thumbnail']; ?>" alt="Thumbnail" class="h-12 w-12 object-cover rounded mx-auto">
                    <?php endif; ?>
                </td>
                <td><?php echo html_entity_decode($item['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></td>
                <td><?php echo html_entity_decode($item['services_provided'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></td>
                <td><?php echo date('d M Y', strtotime($item['created_at'])); ?></td>
                <td>
                    <div class="flex flex-col sm:flex-row gap-2 justify-center items-center">
                        <a href="portfolio.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-secondary w-full sm:w-auto">Edit</a>
                        <a href="portfolio.php?action=view&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline w-full sm:w-auto">View</a>
                        <form method="POST" action="portfolio.php" style="display:inline;" onsubmit="return confirm('Delete this portfolio?');">
                            <input type="hidden" name="portfolio_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" name="delete_portfolio" class="btn btn-sm btn-danger w-full sm:w-auto">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php else: ?>
        <div class="bg-white p-8 rounded shadow text-center text-gray-600">No portfolio items found.</div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 md:gap-0">
        <h1 class="text-3xl font-bold"><?php echo $action === 'add' ? 'Add Portfolio' : 'Edit Portfolio'; ?></h1>
    </div>
    <?php displayAlert(); ?>
    <form method="POST" action="portfolio.php" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="portfolio_id" value="<?php echo $portfolio['id']; ?>">
            <?php foreach(['thumbnail','photo1','photo2','photo3','photo4','photo5','video_story1','video_story2','reel1','reel2','reel3','reel4'] as $f): ?>
                <input type="hidden" name="current_<?php echo $f; ?>" value="<?php echo $portfolio[$f] ?? ''; ?>">
            <?php endforeach; ?>
        <?php endif; ?>

        <div>
            <label>Thumbnail</label>
            <input type="file" name="thumbnail" accept="image/*,.webp,.heic" class="form-input">
            <?php if (!empty($portfolio['thumbnail'])): ?>
                <img src="../<?php echo $portfolio['thumbnail']; ?>" class="max-h-40 mt-2">
            <?php endif; ?>
        </div>
        <div>
            <label>Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($portfolio['title'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>" class="form-input">
        </div>
        <div>
            <label>Description</label>
            <textarea name="description" rows="4" class="form-input"><?php echo htmlspecialchars($portfolio['description'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></textarea>
        </div>
        <div>
            <label>Role</label>
            <input type="text" name="role" value="<?php echo htmlspecialchars($portfolio['services_provided'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>" class="form-input">
        </div>
        <div>
            <label>Timeline</label>
            <input type="text" name="timeline" value="<?php echo htmlspecialchars($portfolio['timeline'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>" class="form-input">
        </div>
        <div>
            <label>Big Post (Image)</label>
            <input type="file" name="photo1" accept="image/*,.webp,.heic" class="form-input">
            <?php if (!empty($portfolio['photo1'])): ?>
                <img src="../<?php echo $portfolio['photo1']; ?>" class="max-h-32 mt-2">
            <?php endif; ?>
        </div>
        <div><label>Small Post 1</label><input type="file" name="photo2" accept="image/*,.webp,.heic" class="form-input">
            <?php if (!empty($portfolio['photo2'])): ?><img src="../<?php echo $portfolio['photo2']; ?>" class="max-h-32 mt-2"><?php endif; ?></div>
        <div><label>Small Post 2</label><input type="file" name="photo3" accept="image/*,.webp,.heic" class="form-input">
            <?php if (!empty($portfolio['photo3'])): ?><img src="../<?php echo $portfolio['photo3']; ?>" class="max-h-32 mt-2"><?php endif; ?></div>
        <div><label>Small Post 3</label><input type="file" name="photo4" accept="image/*,.webp,.heic" class="form-input">
            <?php if (!empty($portfolio['photo4'])): ?><img src="../<?php echo $portfolio['photo4']; ?>" class="max-h-32 mt-2"><?php endif; ?></div>
        <div><label>Small Post 4</label><input type="file" name="photo5" accept="image/*,.webp,.heic" class="form-input">
            <?php if (!empty($portfolio['photo5'])): ?><img src="../<?php echo $portfolio['photo5']; ?>" class="max-h-32 mt-2"><?php endif; ?></div>

        <!-- Dynamic Extra Images -->
        <div class="col-span-2">
            <label class="block font-semibold mb-2">More Images</label>
            <button type="button" onclick="addMoreImageField()" class="btn btn-sm btn-secondary mb-2">Add More Images</button>
            <div id="extra-images-container"></div>
        </div>
        <!-- Dynamic Extra Videos -->
        <div class="col-span-2">
            <label class="block font-semibold mb-2">More Videos</label>
            <button type="button" onclick="addMoreVideoField()" class="btn btn-sm btn-secondary mb-2">Add More Videos</button>
            <div id="extra-videos-container"></div>
        </div>

        <?php foreach (['video_story1' => 'Story 1', 'video_story2' => 'Story 2', 'reel1' => 'Reel 1', 'reel2' => 'Reel 2', 'reel3' => 'Reel 3', 'reel4' => 'Reel 4'] as $field => $label): ?>
        <div>
            <label><?php echo $label; ?> (Image/Video, Chunked)</label>
            <div id="<?php echo $field; ?>_upload_container">
                <button id="browse<?php echo ucfirst($field); ?>" type="button" class="form-input mb-2">Select File</button>
                <div id="progress-bar-<?php echo $field; ?>" style="width:100%; height:10px; background:#eee;">
                    <div id="progress-<?php echo $field; ?>" style="height:100%; width:0%; background:#F44B12;"></div>
                </div>
                <input type="hidden" name="<?php echo $field; ?>_path" id="<?php echo $field; ?>_path" value="<?php echo $portfolio[$field] ?? ''; ?>">
                <span id="upload-status-<?php echo $field; ?>" class="text-xs text-gray-600"></span>
                <?php if (!empty($portfolio[$field])): ?>
                    <?php $ext = strtolower(pathinfo($portfolio[$field], PATHINFO_EXTENSION)); ?>
                    <?php if (in_array($ext, ['jpg','jpeg','png','webp','gif','bmp','svg','heic'])): ?>
                        <img src="../<?php echo $portfolio[$field]; ?>" class="max-h-32 mt-2 rounded border">
                    <?php else: ?>
                        <video controls class="max-h-32 mt-2"><source src="../<?php echo $portfolio[$field]; ?>"></video>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="col-span-full flex justify-end mt-4">
            <button type="submit" name="<?php echo $action === 'add' ? 'add_portfolio' : 'update_portfolio'; ?>" class="btn-primary">
                <?php echo $action === 'add' ? 'Add Portfolio Item' : 'Update Portfolio Item'; ?>
            </button>
        </div>
    </form>
    <script>
    function addMoreImageField(value = '') {
        const container = document.getElementById('extra-images-container');
        const div = document.createElement('div');
        div.className = 'extra-image-field mb-2';
        div.innerHTML = `
            <input type="file" name="extra_images[]" accept="image/*,.webp,.heic" class="form-input inline w-auto" style="display:inline-block;width:auto;">
            ${value ? `<img src="../${value}" class="inline max-h-16 ml-2">` : ''}
            <button type="button" onclick="this.parentNode.remove()" class="btn btn-xs btn-danger ml-2">Remove</button>
            <input type="hidden" name="current_extra_images[]" value="${value}">
        `;
        container.appendChild(div);
    }
    function addMoreVideoField(value = '') {
        const container = document.getElementById('extra-videos-container');
        const div = document.createElement('div');
        div.className = 'extra-video-field mb-2';
        div.innerHTML = `
            <input type="file" name="extra_videos[]" accept="video/*" class="form-input inline w-auto" style="display:inline-block;width:auto;">
            ${value ? `<video controls class="inline max-h-16 ml-2"><source src="../${value}"></video>` : ''}
            <button type="button" onclick="this.parentNode.remove()" class="btn btn-xs btn-danger ml-2">Remove</button>
            <input type="hidden" name="current_extra_videos[]" value="${value}">
        `;
        container.appendChild(div);
    }
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($action === 'edit' && !empty($portfolio['extra_images'])):
            foreach (json_decode($portfolio['extra_images'], true) ?: [] as $img): ?>
                addMoreImageField("<?php echo addslashes($img); ?>");
        <?php endforeach; endif; ?>
        <?php if ($action === 'edit' && !empty($portfolio['extra_videos'])):
            foreach (json_decode($portfolio['extra_videos'], true) ?: [] as $vid): ?>
                addMoreVideoField("<?php echo addslashes($vid); ?>");
        <?php endforeach; endif; ?>
    });
    </script>
    <?php endif; ?>

    <?php if ($action === 'view' && $portfolio): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-10">
        <div class="flex flex-col md:flex-row">
            <div class="md:w-1/3 w-full p-6">
                <?php if (!empty($portfolio['thumbnail'])): ?>
                    <img src="../<?php echo $portfolio['thumbnail']; ?>" class="w-full object-cover rounded mb-4 max-h-60">
                <?php endif; ?>
                <h2 class="text-xl font-bold mt-4 break-words"><?php echo html_entity_decode($portfolio['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></h2>
                <div class="text-gray-700 mt-2 break-words"><?php echo html_entity_decode($portfolio['services_provided'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></div>
                <div class="text-sm text-gray-500 mt-1"><?php echo $portfolio['timeline']; ?></div>
            </div>
            <div class="md:w-2/3 w-full p-6">
                <div class="mb-4">
                    <div class="font-semibold">Description:</div>
                    <div class="text-gray-700 break-words"><?php echo nl2br(html_entity_decode($portfolio['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                    <div class="col-span-1 sm:col-span-2 md:col-span-3 mb-2 font-semibold">Big Post</div>
                    <?php if (!empty($portfolio['photo1'])): ?>
                    <div class="col-span-1 sm:col-span-2 md:col-span-3">
                        <img src="../<?php echo $portfolio['photo1']; ?>" class="w-full max-h-80 object-cover rounded mb-2">
                    </div>
                    <?php endif; ?>
                    <div class="col-span-1 sm:col-span-2 md:col-span-3 mb-2 font-semibold">Small Posts</div>
                    <?php for ($i = 2; $i <= 5; $i++): if (!empty($portfolio['photo'.$i])): ?>
                        <div>
                            <img src="../<?php echo $portfolio['photo'.$i]; ?>" class="w-full h-32 object-cover rounded">
                        </div>
                    <?php endif; endfor; ?>
                    <?php if (!empty($portfolio['extra_images'])):
                        foreach (json_decode($portfolio['extra_images'], true) as $img): ?>
                            <div>
                                <img src="../<?php echo $img; ?>" class="w-full h-32 object-cover rounded border border-gray-200">
                            </div>
                    <?php endforeach; endif; ?>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div class="col-span-1 sm:col-span-2 font-semibold mb-2">Stories</div>
                    <?php for ($i = 1; $i <= 2; $i++): if (!empty($portfolio['video_story'.$i])): ?>
                        <div>
                            <video controls class="w-full h-32 rounded">
                                <source src="../<?php echo $portfolio['video_story'.$i]; ?>">
                            </video>
                        </div>
                    <?php endif; endfor; ?>
                    <?php if (!empty($portfolio['extra_videos'])):
                        foreach (json_decode($portfolio['extra_videos'], true) as $vid): ?>
                            <div>
                                <video controls class="w-full h-32 rounded border border-gray-200">
                                    <source src="../<?php echo $vid; ?>">
                                </video>
                            </div>
                    <?php endforeach; endif; ?>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="col-span-2 md:col-span-4 font-semibold mb-2">Reels</div>
                    <?php for ($i = 1; $i <= 4; $i++): if (!empty($portfolio['reel'.$i])): ?>
                        <div>
                            <video controls class="w-full h-32 rounded">
                                <source src="../<?php echo $portfolio['reel'.$i]; ?>">
                            </video>
                        </div>
                    <?php endif; endfor; ?>
                </div>
            </div>
        </div>
    </div>
    <form method="POST" action="portfolio.php" onsubmit="return confirm('Delete this portfolio item?');" class="mt-4 text-right">
        <input type="hidden" name="portfolio_id" value="<?php echo $portfolio['id']; ?>">
        <button type="submit" name="delete_portfolio" class="btn btn-danger">Delete Portfolio Item</button>
    </form>
    <?php endif; ?>
</div>

<script>
function setupResumableUpload(buttonId, progressBarId, progressId, inputId, statusId, uploadTarget) {
    var r = new Resumable({
        target: uploadTarget,
        chunkSize: 5*1024*1024,
        simultaneousUploads: 1,
        testChunks: false,
        throttleProgressCallbacks: 1,
        fileType: ['mp4', 'mov', 'avi', 'webm', 'mkv', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg', 'heic']
    });

    r.assignBrowse(document.getElementById(buttonId));

    r.on('fileAdded', function(file) {
        r.upload();
        document.getElementById(statusId).innerText = 'Uploading...';
    });
    r.on('fileProgress', function(file) {
        var progress = Math.floor(file.progress() * 100);
        document.getElementById(progressId).style.width = progress + '%';
    });
    r.on('fileSuccess', function(file, response) {
        var resp = JSON.parse(response);
        if (resp.success) {
            document.getElementById(inputId).value = resp.path;
            document.getElementById(statusId).innerText = 'Upload complete!';
        } else {
            document.getElementById(statusId).innerText = 'Upload failed: ' + (resp.error || '');
        }
    });
    r.on('fileError', function(file, message) {
        document.getElementById(statusId).innerText = 'Upload error: ' + message;
    });
}

document.addEventListener("DOMContentLoaded", function() {
    <?php foreach (['video_story1', 'video_story2', 'reel1', 'reel2', 'reel3', 'reel4'] as $field): ?>
    setupResumableUpload(
        'browse<?php echo ucfirst($field); ?>',
        'progress-bar-<?php echo $field; ?>',
        'progress-<?php echo $field; ?>',
        '<?php echo $field; ?>_path',
        'upload-status-<?php echo $field; ?>',
        'video_upload_handler.php'
    );
    <?php endforeach; ?>
});
</script>

<?php include 'includes/footer.php'; ?>