<?php
include('header.php');
include('conn.php');
include('../delivery/helpers.php');

// --- DATABASE CHECKS & LOGIC (UNCHANGED) ---
ensure_delivery_tables($con);
ensure_service_requests_table($con);
ensure_service_requests_history_table($con);

$view = isset($_GET['view']) ? trim($_GET['view']) : 'active';

// check table exists
$db = '';
$rdb = @mysqli_query($con, "SELECT DATABASE() AS dbname");
$tbl_missing = false;
$hist_missing = false;
$res = false;
$hist_res = false;

if($rdb && mysqli_num_rows($rdb)>0){ $db = mysqli_fetch_assoc($rdb)['dbname']; }

if($db){
  $qc = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".mysqli_real_escape_string($con,$db)."' AND TABLE_NAME='service_requests' LIMIT 1");
  if($qc && mysqli_num_rows($qc)>0){
    if($view === 'history'){
      $res = false;
    } else {
      $res = @mysqli_query($con, "SELECT * FROM service_requests WHERE LOWER(IFNULL(status,'')) IN ('pending','in_progress') ORDER BY created_at DESC");
    }
  } else {
    $tbl_missing = true;
  }

  $qh = @mysqli_query($con, "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".mysqli_real_escape_string($con,$db)."' AND TABLE_NAME='service_requests_history' LIMIT 1");
  if($qh && mysqli_num_rows($qh)>0){
    $hist_res = @mysqli_query($con, "SELECT * FROM service_requests_history WHERE LOWER(IFNULL(status,'')) IN ('cancelled','completed') ORDER BY archived_at DESC");
  } else {
    $hist_missing = true;
  }
} else {
  $tbl_missing = true;
  $hist_missing = true;
}

// counts for tabs
$active_count = 0;
$history_count = 0;
$c1 = @mysqli_query($con, "SELECT COUNT(*) AS total FROM service_requests WHERE LOWER(IFNULL(status,'')) IN ('pending','in_progress')");
if($c1 && mysqli_num_rows($c1)>0){ $active_count = (int)(mysqli_fetch_assoc($c1)['total'] ?? 0); }
$c2 = @mysqli_query($con, "SELECT COUNT(*) AS total FROM service_requests_history WHERE LOWER(IFNULL(status,'')) IN ('cancelled','completed')");
if($c2 && mysqli_num_rows($c2)>0){ $history_count = (int)(mysqli_fetch_assoc($c2)['total'] ?? 0); }

// active delivery agents list
$agents = [];
$ares = mysqli_query($con, "SELECT username FROM del_login WHERE is_active=1 ORDER BY username");
if($ares){
  while($ar = mysqli_fetch_assoc($ares)){
    $agents[] = $ar['username'];
  }
}
?>

<style>
    :root {
        --primary-color: #7c3aed;
        --primary-soft: #ede9fe;
        --success-soft: #dcfce7;
        --success-text: #166534;
        --warning-soft: #fef9c3;
        --warning-text: #854d0e;
        --danger-soft: #fee2e2;
        --danger-text: #991b1b;
        --info-soft: #e0f2fe;
        --info-text: #075985;
    }

    body {
        background:
            radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
            radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
            radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 30%),
            linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
    }

    .dashboard-card {
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        box-shadow: 0 10px 22px rgba(30, 64, 175, 0.12);
        background: linear-gradient(155deg, rgba(245, 243, 255, 0.9) 0%, rgba(238, 246, 255, 0.9) 55%, rgba(240, 253, 244, 0.9) 100%);
        overflow: hidden;
    }

    .dashboard-header {
        padding: 1.5rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .nav-tabs-custom {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 8px;
        display: inline-flex;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        color: #64748b;
        font-weight: 500;
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .nav-tabs-custom .nav-link:hover {
        color: #334155;
    }

    .nav-tabs-custom .nav-link.active {
        background: #fff;
        color: var(--primary-strong);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .table-modern {
        margin-bottom: 0;
        width: 100%;
    }

    .table-modern thead th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding: 1rem;
        white-space: nowrap;
    }

    .table-modern tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.95rem;
    }

    .table-modern tbody tr:last-child td {
        border-bottom: none;
    }

    .table-modern tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Column Specifics */
    .col-user { font-weight: 600; color: #1e293b; }
    .col-item { color: var(--primary-strong); font-weight: 500; }
    .col-details { max-width: 200px; font-size: 0.85rem; color: #64748b; line-height: 1.4; }
    .col-date { font-size: 0.85rem; color: #94a3b8; font-family: monospace; }
    
    /* Badges */
    .status-badge {
        padding: 0.35em 0.8em;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
        text-transform: capitalize;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-pending { background: var(--warning-soft); color: var(--warning-text); }
    .badge-in_progress { background: var(--info-soft); color: var(--info-text); }
    .badge-completed { background: var(--success-soft); color: var(--success-text); }
    .badge-cancelled { background: var(--danger-soft); color: var(--danger-text); }

    /* Action Form */
    .agent-select-wrapper {
        display: flex;
        gap: 5px;
        align-items: center;
    }
    .form-select-compact {
        font-size: 0.85rem;
        padding: 0.3rem 0.5rem;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        width: 130px;
    }
    .btn-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .btn-icon:hover { background-color: #f1f5f9; border-color: #cbd5e1; }
    .btn-view { color: var(--primary-strong); background: var(--primary-soft); }
    .btn-view:hover { background: #e0e7ff; }
    
    .empty-state {
        text-align: center;
        padding: 4rem 1rem;
        color: #94a3b8;
    }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                
                <div class="dashboard-header">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">
                            <i class="bi bi-tools text-primary me-2"></i>Service Requests
                        </h4>
                        <div class="text-muted small">Manage support tickets and agent assignments</div>
                    </div>
                    
                    <div class="nav-tabs-custom">
                        <a href="service_requests.php" class="nav-link <?php echo $view==='active'?'active':''; ?>">
                            Active <span class="badge bg-white text-dark ms-1 rounded-pill border"><?php echo $active_count; ?></span>
                        </a>
                        <a href="service_requests.php?view=history" class="nav-link <?php echo $view==='history'?'active':''; ?>">
                            History <span class="badge bg-white text-dark ms-1 rounded-pill border"><?php echo $history_count; ?></span>
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">User</th>
                                <th width="15%">Item</th>
                                <th width="10%">Type</th>
                                <th width="20%">Details</th>
                                <th width="10%">Status</th>
                                <th width="10%">Agent</th>
                                <th width="10%">Date</th>
                                <?php if($view !== 'history'): ?>
                                    <th width="15%" class="text-end pe-4">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
<?php
// --- LOGIC TO RENDER ROWS ---
if($view === 'history'){
    if($hist_missing || ($hist_res && mysqli_num_rows($hist_res) == 0)){
        // Empty State History
        echo "<tr><td colspan='8'>
                <div class='empty-state'>
                    <i class='bi bi-clock-history'></i>
                    <p class='mb-0'>No history records found.</p>
                </div>
              </td></tr>";
    } elseif($hist_res && mysqli_num_rows($hist_res)>0){
        $i=1;
        while($r = mysqli_fetch_assoc($hist_res)){
            $id = (int)$r['id'];
            $user = htmlspecialchars($r['user']);
            $item = htmlspecialchars($r['item']);
            $type = htmlspecialchars($r['service_type']);
            $details = htmlspecialchars(mb_strimwidth($r['details'],0,80,'...'));
            $status = strtolower($r['status'] ?? 'cancelled');
            $date = !empty($r['archived_at']) ? date('M d, Y H:i', strtotime($r['archived_at'])) : '-';
            $assigned_agent = htmlspecialchars($r['assigned_agent'] ?? '');

            // Determine badge class
            $badge_cls = 'badge-secondary';
            if($status == 'completed') $badge_cls = 'badge-completed';
            if($status == 'cancelled') $badge_cls = 'badge-cancelled';

            // Determine Icon
            $status_icon = ($status == 'completed') ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-x-circle-fill"></i>';

            echo "<tr>";
            echo "<td class='text-muted'>{$i}</td>";
            echo "<td class='col-user'><div class='d-flex align-items-center gap-2'><i class='bi bi-person-circle text-secondary'></i> {$user}</div></td>";
            echo "<td class='col-item'>{$item}</td>";
            echo "<td><span class='badge bg-light text-dark border'>{$type}</span></td>";
            echo "<td class='col-details' title='".htmlspecialchars($r['details'])."'>{$details}</td>";
            echo "<td><span class='status-badge {$badge_cls}'>{$status_icon} {$status}</span></td>";
            echo "<td>".($assigned_agent ? "<span class='text-dark fw-medium'>@{$assigned_agent}</span>" : "<span class='text-muted'>-</span>")."</td>";
            echo "<td class='col-date'>{$date}</td>";
            echo "</tr>";
            $i++;
        }
    }
} else {
    if($tbl_missing || ($res && mysqli_num_rows($res) == 0)){
        // Empty State Active
        echo "<tr><td colspan='9'>
                <div class='empty-state'>
                    <i class='bi bi-inbox'></i>
                    <p class='mb-0'>No active service requests.</p>
                    <small>Good job! All caught up.</small>
                </div>
              </td></tr>";
    } elseif($res && mysqli_num_rows($res)>0){
        $i=1;
        while($r = mysqli_fetch_assoc($res)){
            $id = (int)$r['id'];
            $user = htmlspecialchars($r['user']);
            $item = htmlspecialchars($r['item']);
            $type = htmlspecialchars($r['service_type']);
            $details = htmlspecialchars(mb_strimwidth($r['details'],0,80,'...'));
            $status = strtolower($r['status'] ?? 'pending');
            $date = !empty($r['created_at']) ? date('M d, H:i', strtotime($r['created_at'])) : '';
            $assigned_agent = htmlspecialchars($r['assigned_agent'] ?? '');

            // Determine badge class
            $badge_cls = 'badge-secondary';
            if($status == 'pending') $badge_cls = 'badge-pending';
            if($status == 'in_progress') $badge_cls = 'badge-in_progress';

            // Determine Icon
            $status_icon = ($status == 'pending') ? '<i class="bi bi-hourglass-split"></i>' : '<i class="bi bi-gear-wide-connected"></i>';

            echo "<tr>";
            echo "<td class='text-muted'>{$i}</td>";
            echo "<td class='col-user'><div class='d-flex align-items-center gap-2'><i class='bi bi-person-circle text-secondary'></i> {$user}</div></td>";
            echo "<td class='col-item'>{$item}</td>";
            echo "<td><span class='badge bg-light text-dark border'>{$type}</span></td>";
            echo "<td class='col-details' title='".htmlspecialchars($r['details'])."'>{$details}</td>";
            echo "<td><span class='status-badge {$badge_cls}'>{$status_icon} ".str_replace('_',' ',$status)."</span></td>";
            echo "<td>".($assigned_agent ? "<span class='text-dark fw-medium'>@{$assigned_agent}</span>" : "<span class='text-danger small'>Unassigned</span>")."</td>";
            echo "<td class='col-date'>{$date}</td>";
            
            // ACTIONS COLUMN
            echo "<td class='text-end pe-4'>";
            echo "<div class='d-flex justify-content-end align-items-center gap-2'>";
            
            // View Button
            echo "<a href='view_service.php?id={$id}' class='btn-icon btn-view' title='View Full Details'><i class='bi bi-eye-fill'></i></a>";
            
            // Assign Form
            echo "<form method='post' action='assign_service.php' class='agent-select-wrapper'>";
            echo "<input type='hidden' name='id' value='{$id}'>";
            echo "<select name='assigned_agent' class='form-select form-select-compact' title='Assign Agent'>";
            echo "<option value='' class='text-muted'>Select Agent</option>";
            foreach($agents as $ag){
                $ag_esc = htmlspecialchars($ag);
                $sel = ($ag === ($r['assigned_agent'] ?? '')) ? 'selected' : '';
                echo "<option value='{$ag_esc}' {$sel}>{$ag_esc}</option>";
            }
            echo "</select>";
            echo "<button class='btn btn-sm btn-dark' type='submit' title='Save Assignment'><i class='bi bi-check-lg'></i></button>";
            echo "</form>";
            
            echo "</div>";
            echo "</td>";
            echo "</tr>";
            $i++;
        }
    }
}
?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include(__DIR__ . '/../footer.php'); ?>