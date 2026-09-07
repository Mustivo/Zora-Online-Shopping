<?php
require_once 'includes/header.php';

$users_query = mysqli_query($conn, "SELECT * FROM users WHERE role='user' ORDER BY created_at DESC");
?>
<h2>Customers</h2>
<table class="table admin-table mt-3">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Joined</th></tr></thead>
    <tbody>
        <?php mysqli_data_seek($users_query, 0); while($u = mysqli_fetch_assoc($users_query)): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once 'includes/footer.php'; ?>
