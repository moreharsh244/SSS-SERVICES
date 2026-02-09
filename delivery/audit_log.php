<?php
include('header.php');
include '../admin/conn.php';
include 'helpers.php';
ensure_delivery_tables($con);

$username = $_SESSION['username'] ?? '';
$res = mysqli_query($con, "SELECT * FROM delivery_audit_logs WHERE agent_username='".mysqli_real_escape_string($con,$username)."' ORDER BY created_at DESC LIMIT 200");
?>

<div class="col-12 col-lg-10 mx-auto">
    <div class="delivery-hero mb-4 fade-in">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">Audit Log</h2>
                <div class="text-muted">Recent actions for your account.</div>
            </div>
            <div class="stat-pill"><i class="bi bi-clock-history"></i> Latest 200</div>
        </div>
    </div>
    <div class="delivery-card p-0">
        <div class="card-header bg-danger text-white">Audit Log</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle table-delivery">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($res && mysqli_num_rows($res)>0): ?>
                            <?php while($r = mysqli_fetch_assoc($res)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['action']); ?></td>
                                    <td><?php echo htmlspecialchars($r['details']); ?></td>
                                    <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center">No logs yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
