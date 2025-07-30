<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php');
}

// Possible statuses
$validStatuses = ['pending', 'in progress', 'completed', 'not completed'];

// Handle status update or delete
$actionMsg = '';
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] === 'delete') {
        runQuery("DELETE FROM contact_form WHERE id = $id");
        $actionMsg = "Contact query deleted!";
    } elseif ($_GET['action'] === 'update' && in_array($_GET['status'], $validStatuses)) {
        $status = mysqli_real_escape_string($conn, $_GET['status']);
        runQuery("UPDATE contact_form SET status = '$status' WHERE id = $id");
        $actionMsg = "Status updated!";
    }
}

// Fetch all queries
$contactQueriesSql = "SELECT * FROM contact_form ORDER BY submitted_at DESC";
$contactQueriesResult = runQuery($contactQueriesSql);
$contactQueries = [];
if ($contactQueriesResult && getNumRows($contactQueriesResult) > 0) {
    $contactQueries = fetchAllRows($contactQueriesResult);
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-[#2B2B2A] mb-8">Contact Form Queries</h1>
    <?php if ($actionMsg): ?>
        <div class="p-2 mb-4 bg-green-100 text-green-700 rounded"><?php echo htmlspecialchars($actionMsg); ?></div>
    <?php endif; ?>
    <?php if (empty($contactQueries)): ?>
        <div class="bg-gray-100 p-4 rounded text-center">
            <p class="text-gray-600">No contact queries found.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name / Email</th>
                        <th>Industry</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contactQueries as $query): ?>
                        <tr>
                            <td><?php echo $query['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($query['name']); ?></strong><br>
                                <span class="text-xs text-gray-500"><?php echo htmlspecialchars($query['email']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($query['industry']); ?></td>
                            <td style="max-width: 250px; word-break: break-word;">
                                <?php echo nl2br(htmlspecialchars($query['message'])); ?>
                            </td>
                            <td>
                                <form method="get" action="" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $query['id']; ?>">
                                    <input type="hidden" name="action" value="update">
                                    <select name="status" onchange="this.form.submit()" class="rounded p-1 border border-gray-300">
                                        <?php foreach ($validStatuses as $statusOpt): ?>
                                            <option value="<?php echo $statusOpt; ?>" <?php if (($query['status'] ?? 'pending') === $statusOpt) echo 'selected'; ?>>
                                                <?php echo ucfirst($statusOpt); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo htmlspecialchars($query['submitted_at']); ?></td>
                            <td>
                                <a href="?action=delete&id=<?php echo $query['id']; ?>"
                                   onclick="return confirm('Are you sure you want to delete this query?')"
                                   class="text-red-600 hover:underline font-bold">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

