<?php
require_once 'includes/header.php';
<<<<<<< HEAD

$query = mysqli_query($conn, "SELECT * FROM support_tickets ORDER BY created_at DESC");
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title"><i class="fas fa-headset me-2 text-accent"></i> Support Tickets</h2>
</div>

<div class="admin-card p-4">
    <table class="table admin-table align-middle">
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Customer</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($query) == 0): ?>
            <tr><td colspan="6" class="text-center text-muted py-5">
                <i class="fas fa-check-circle fa-3x text-success opacity-50 mb-3 d-block"></i>
                No active support tickets! You're all caught up.
            </td></tr>
            <?php else: while($row = mysqli_fetch_assoc($query)): 
                $status_class = $row['status'] == 'open' ? 'bg-danger' : ($row['status'] == 'in_progress' ? 'bg-warning' : 'bg-success');
            ?>
            <tr>
                <td class="text-muted fw-bold">#TK-<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></td>
                <td>
                    <div class="fw-bold"><?= htmlspecialchars($row['customer_name']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($row['customer_email']) ?></small>
                </td>
                <td class="fw-bold text-dark"><?= htmlspecialchars($row['subject']) ?></td>
                <td><span class="badge <?= $status_class ?> text-uppercase"><?= str_replace('_', ' ', $row['status']) ?></span></td>
                <td class="text-muted"><?= date('M d, H:i', strtotime($row['created_at'])) ?></td>
                <td>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick='viewTicket(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8") ?>)'>View / Reply</button>
                        <form action="actions.php" method="POST" onsubmit="return customConfirm(event, 'Delete this ticket?');">
                            <input type="hidden" name="action" value="delete_ticket">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<!-- Ticket Modal -->
<div class="modal fade" id="ticketModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="background:var(--bg2); color:var(--text)">
      <form action="actions.php" method="POST">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="modal_subject">Ticket Subject</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="action" value="update_ticket">
            <input type="hidden" name="id" id="ticket_id">
            
            <div class="mb-4 p-3 rounded" style="background: var(--bg3);">
                <div class="fw-bold text-accent mb-2" id="modal_customer"></div>
                <div id="modal_message" class="text-muted" style="white-space: pre-wrap;"></div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Update Status</label>
                <select name="status" id="ticket_status" class="form-select">
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Send Email Reply (coming soon)</label>
                <textarea class="form-control" rows="3" placeholder="Type your reply here..." disabled></textarea>
            </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Status</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function viewTicket(ticket) {
    document.getElementById('ticket_id').value = ticket.id;
    document.getElementById('modal_subject').innerText = ticket.subject;
    document.getElementById('modal_customer').innerText = ticket.customer_name + ' (' + ticket.customer_email + ')';
    document.getElementById('modal_message').innerText = ticket.message;
    document.getElementById('ticket_status').value = ticket.status;
    new bootstrap.Modal(document.getElementById('ticketModal')).show();
}
</script>
=======
?>
<div class="admin-header d-flex justify-content-between align-items-center mb-4">
    <h2 class="admin-page-title">Customer Support</h2>
</div>

<div class="admin-card p-5 text-center mt-4">
    <i class="fas fa-tools fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
    <h3 class="mt-3">Under Construction</h3>
    <p class="text-muted">The Customer Support module is currently being built. Check back soon!</p>
</div>
>>>>>>> cce12f54b13cc026fb7227be0113b7f5b024d444
<?php require_once 'includes/footer.php'; ?>
