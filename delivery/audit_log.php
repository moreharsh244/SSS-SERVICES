<?php
include('header.php');
include '../admin/conn.php';
include 'helpers.php';
ensure_delivery_tables($con);

$username = $_SESSION['username'] ?? '';
$res = mysqli_query($con, "SELECT * FROM delivery_audit_logs WHERE agent_username='".mysqli_real_escape_string($con,$username)."' ORDER BY created_at DESC LIMIT 200");
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    :root {
        /* Purple Gradient to match Admin/User portals */
        --primary-grad: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); 
        --primary-solid: #6366f1;
        --card-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.15);
        --text-dark: #0f172a;
        --text-muted: #64748b;
    }

    /* Hero Section */
    .audit-hero {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(255, 255, 255, 0.8);
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .audit-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; bottom: 0; width: 6px;
        background: var(--primary-grad);
    }

    .hero-title {
        font-weight: 800;
        font-size: 1.8rem;
        color: #0f172a;
        letter-spacing: -0.5px;
    }

    .stat-pill {
        background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
        color: #7c3aed;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        border: 1px solid #e9d5ff;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Table Container */
    .table-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        border: 1px solid #e2e8f0;
        padding: 0;
    }

    .panel-header {
        padding: 20px 25px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .panel-icon {
        width: 36px; height: 36px;
        background: #f3e8ff;
        color: #7c3aed;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
    }

    /* Custom Table */
    .custom-table { margin-bottom: 0; }
    
    .custom-table thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 15px 25px;
        border-bottom: 1px solid #e2e8f0;
    }

    .custom-table tbody td {
        padding: 16px 25px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.9rem;
    }

    .custom-table tr:last-child td { border-bottom: none; }
    .custom-table tr:hover { background-color: #f1f5f9; }

    /* Action Badges */
    .badge-log {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .bg-action-blue { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; } /* General */
    .bg-action-green { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; } /* Login/Success */
    .bg-action-orange { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; } /* Updates */
    .bg-action-red { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; } /* Deletes/Errors */

</style>

<div class="container">
    <div class="col-12 col-lg-10 mx-auto">
        
        <div class="audit-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="hero-title mb-1">Audit Log</h2>
                <div class="text-muted">Track your recent account activity</div>
            </div>
            <div class="stat-pill">
                <i class="bi bi-clock-history"></i> Latest 200
            </div>
        </div>

        <div class="table-card">
            <div class="panel-header">
                <div class="panel-icon"><i class="bi bi-journal-text"></i></div>
                <h5 class="panel-title">Activity History</h5>
            </div>
            
            <div class="table-responsive">
                <table class="table custom-table">
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
                                <?php
                                    $action = strtolower($r['action']);
                                    $badgeClass = 'bg-action-blue';
                                    if(strpos($action, 'login')!==false || strpos($action, 'success')!==false) {
                                        $badgeClass = 'bg-action-green';
                                    } elseif(strpos($action, 'update')!==false || strpos($action, 'edit')!==false) {
                                        $badgeClass = 'bg-action-orange';
                                    } elseif(strpos($action, 'delete')!==false || strpos($action, 'error')!==false) {
                                        $badgeClass = 'bg-action-red';
                                    }
                                ?>
                                <tr>
                                    <td><span class="badge-log <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($r['action']); ?></span></td>
                                    <td><?php echo htmlspecialchars($r['details']); ?></td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($r['created_at']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-3" style="opacity:0.4;"></i>
                                        <p class="mb-0">No activity logs found.</p>
                                        <small>Your recent actions will appear here.</small>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php include('footer.php'); ?>