<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
    session_start();
}

// DO NOT REDIRECT OR EXIT FOR ANY REASON

include('conn.php');
include('../delivery/helpers.php');
ensure_builds_history_table($con);

function archive_terminal_builds($con) {
    $q = @mysqli_query($con, "SELECT * FROM builds WHERE LOWER(IFNULL(status,'')) IN ('delivered','completed','cancelled')");
    if(!$q || mysqli_num_rows($q) === 0) return;

    while($build = mysqli_fetch_assoc($q)){
        $id = intval($build['id'] ?? 0);
        if($id <= 0) continue;

        $user_id = intval($build['user_id'] ?? 0);
        $user_name = mysqli_real_escape_string($con, $build['user_name'] ?? '');
        $name = mysqli_real_escape_string($con, $build['name'] ?? '');
        $description = mysqli_real_escape_string($con, $build['description'] ?? '');
        $total = floatval($build['total'] ?? 0);
        $status = mysqli_real_escape_string($con, strtolower($build['status'] ?? 'completed'));
        $created_at = mysqli_real_escape_string($con, $build['created_at'] ?? '');
        $assigned_agent = mysqli_real_escape_string($con, $build['assigned_agent'] ?? '');

        $ins = "INSERT INTO builds_history (id, user_id, user_name, name, description, total, status, created_at, completed_at, assigned_agent) VALUES ($id, $user_id, '$user_name', '$name', '$description', $total, '$status', " . (!empty($created_at) ? "'{$created_at}'" : "NULL") . ", NOW(), '$assigned_agent') ON DUPLICATE KEY UPDATE status=VALUES(status), user_name=VALUES(user_name), description=VALUES(description), completed_at=VALUES(completed_at), assigned_agent=VALUES(assigned_agent)";
        @mysqli_query($con, $ins);

        @mysqli_query($con, "DELETE FROM build_items WHERE build_id='$id'");
        @mysqli_query($con, "DELETE FROM builds WHERE id='$id' LIMIT 1");
    }
}

archive_terminal_builds($con);

// Ensure assigned_agent column exists in builds table
$col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='builds' AND COLUMN_NAME='assigned_agent' LIMIT 1";
$col_res = mysqli_query($con, $col_check);
echo '<div style="display:flex;flex-direction:column;min-height:100vh;">';
if(!$col_res || mysqli_num_rows($col_res)===0){
    @mysqli_query($con, "ALTER TABLE builds ADD COLUMN assigned_agent VARCHAR(100) DEFAULT NULL");
}

$view = isset($_GET['view']) ? trim($_GET['view']) : 'active';
// --- LOGIC BLOCK (POST actions) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = trim($_POST['action']);
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($action === 'assign_agent' && $id > 0) {
                $agent = trim($_POST['assigned_agent'] ?? '');
                if ($agent !== '') {
                        $agent_esc = mysqli_real_escape_string($con, $agent);
                        mysqli_query($con, "UPDATE builds SET assigned_agent='$agent_esc', status='out_for_delivery' WHERE id='$id' LIMIT 1");
                        log_delivery_action($con, $agent, 'assign_build', 'Assigned build #'.$id.' by admin '.($_SESSION['username'] ?? ''));

                        // Also update related purchases for this build's user to out_for_delivery
                        $build_q = mysqli_query($con, "SELECT user_name, user_id FROM builds WHERE id='$id' LIMIT 1");
                        $userIdentifier = '';
                        if($build_q && mysqli_num_rows($build_q)>0){
                            $brow = mysqli_fetch_assoc($build_q);
                            $maybe = trim($brow['user_name'] ?? '');
                            if (!empty($maybe) && filter_var($maybe, FILTER_VALIDATE_EMAIL)) { $userIdentifier = $maybe; }
                            if ($userIdentifier === '' && !empty($brow['user_id'])) { $userIdentifier = 'user_'.intval($brow['user_id']); }
                        }
                        if($userIdentifier !== ''){
                            // collect product ids for this build
                            $pids = [];
                            $bi_res = mysqli_query($con, "SELECT product_id FROM build_items WHERE build_id='$id'");
                            if($bi_res && mysqli_num_rows($bi_res)>0){
                                while($bi = mysqli_fetch_assoc($bi_res)){
                                    $pids[] = intval($bi['product_id']);
                                }
                            }
                            if(!empty($pids)){
                                $pid_list = implode(',', array_map('intval', array_unique($pids)));
                                $uq = "UPDATE purchase SET assigned_agent='".mysqli_real_escape_string($con,$agent)."', status='out_for_delivery', delivery_status='out_for_delivery' WHERE prod_id IN ($pid_list) AND user='".mysqli_real_escape_string($con,$userIdentifier)."' AND LOWER(IFNULL(delivery_status,'')) IN ('pending','order_confirmed')";
                                @mysqli_query($con, $uq);
                            }
                        }
                } else {
                        mysqli_query($con, "UPDATE builds SET assigned_agent=NULL, status='pending' WHERE id='$id' LIMIT 1");
                        // clear assignment on related purchases
                        $bi_res = mysqli_query($con, "SELECT product_id FROM build_items WHERE build_id='$id'");
                        $pids = [];
                        if($bi_res && mysqli_num_rows($bi_res)>0){ while($bi = mysqli_fetch_assoc($bi_res)){ $pids[] = intval($bi['product_id']); } }
                        if(!empty($pids)){
                            $pid_list = implode(',', array_map('intval', array_unique($pids)));
                            @mysqli_query($con, "UPDATE purchase SET assigned_agent=NULL, status='order_confirmed', delivery_status='order_confirmed' WHERE prod_id IN ($pid_list)");
                        }
                }
                header('Location: builds.php'); exit;
        }

        if ($action === 'accept' && $id > 0) {
                // ensure purchase table exists
                $create = "CREATE TABLE IF NOT EXISTS `purchase` (
                        `pid` INT AUTO_INCREMENT PRIMARY KEY,
                        `pname` VARCHAR(255) NOT NULL,
                        `user` VARCHAR(255) NOT NULL,
                        `pprice` DECIMAL(10,2) NOT NULL,
                        `qty` INT NOT NULL DEFAULT 1,
                        `prod_id` INT DEFAULT NULL,
                        `status` VARCHAR(50) DEFAULT 'pending',
                        `delivery_status` VARCHAR(50) DEFAULT 'pending',
                        `pdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                mysqli_query($con, $create);

                // fetch build and items
                $bq = mysqli_query($con, "SELECT * FROM builds WHERE id='$id' LIMIT 1");
                if ($bq && mysqli_num_rows($bq) > 0) {
                        $build = mysqli_fetch_assoc($bq);
                        $items_q = "SELECT bi.*, p.pname AS product_name FROM build_items bi LEFT JOIN products p ON p.pid = bi.product_id WHERE bi.build_id='$id'";
                        $items_r = mysqli_query($con, $items_q);

                        // determine user identifier
                        $userIdentifier = '';
                        $maybe = trim($build['user_name'] ?? '');
                        if (!empty($maybe) && filter_var($maybe, FILTER_VALIDATE_EMAIL)) { $userIdentifier = $maybe; }
                        if ($userIdentifier === '' && !empty($build['user_id'])) {
                                $uid = intval($build['user_id']);
                                $ru = mysqli_query($con, "SELECT c_email, c_name FROM cust_reg WHERE cid=$uid LIMIT 1");
                                if ($ru && mysqli_num_rows($ru) > 0) { $ur = mysqli_fetch_assoc($ru); $userIdentifier = $ur['c_email'] ?? $ur['c_name'] ?? ''; }
                        }
                        if ($userIdentifier === '') { $userIdentifier = 'user_'.$build['user_id']; }
                        $user = mysqli_real_escape_string($con, $userIdentifier);

                        if ($items_r && mysqli_num_rows($items_r) > 0) {
                                while ($it = mysqli_fetch_assoc($items_r)) {
                                        $pid = intval($it['product_id']);
                                        if ($pid <= 0 || empty($it['product_name'])) continue;
                                        $pname = mysqli_real_escape_string($con, $it['product_name']);
                                        $price = floatval($it['price']);
                                        $qty = max(1, intval($it['qty'] ?? 1));
                                        // insert purchase
                                        $ins = "INSERT INTO purchase (pname, user, pprice, qty, prod_id, status, delivery_status) VALUES ('{$pname}', '{$user}', '{$price}', {$qty}, {$pid}, 'pending', 'pending')";
                                        mysqli_query($con, $ins);
                                }
                        }
                }
                mysqli_query($con, "UPDATE builds SET status='accepted' WHERE id='$id' LIMIT 1");
                header('Location: orders_list.php'); exit;
        }

            if ($action === 'delete_build' && $id > 0) {
                // delete build and its items
                @mysqli_query($con, "DELETE FROM build_items WHERE build_id='$id'");
                @mysqli_query($con, "DELETE FROM builds WHERE id='$id' LIMIT 1");
                header('Location: builds.php?msg=deleted'); exit;
            }

        if ($action === 'complete' && $id > 0) {
                // archive build
                $bq = mysqli_query($con, "SELECT * FROM builds WHERE id='$id' LIMIT 1");
                if ($bq && mysqli_num_rows($bq) > 0) {
                        $build = mysqli_fetch_assoc($bq);
                        $user_id = intval($build['user_id'] ?? 0);
                        $user_name = mysqli_real_escape_string($con, $build['user_name'] ?? '');
                        $name = mysqli_real_escape_string($con, $build['name'] ?? '');
                        $description = mysqli_real_escape_string($con, $build['description'] ?? '');
                        $total = floatval($build['total'] ?? 0);
                        $status = mysqli_real_escape_string($con, $build['status'] ?? 'pending');
                        $created_at = mysqli_real_escape_string($con, $build['created_at'] ?? '');

                        $ins = "INSERT INTO builds_history (id, user_id, user_name, name, description, total, status, created_at) VALUES ($id, $user_id, '$user_name', '$name', '$description', $total, '$status', " . (!empty($created_at) ? "'{$created_at}'" : "NULL") . ") ON DUPLICATE KEY UPDATE status=VALUES(status), user_name=VALUES(user_name), description=VALUES(description)";
                        @mysqli_query($con, $ins);
                        @mysqli_query($con, "DELETE FROM builds WHERE id='$id' LIMIT 1");
                }
                header('Location: builds.php?view=history&msg=completed'); exit;
        }

        // Fetch delivery agents for assignment dropdown
        $agents = [];
        $agent_result = @mysqli_query($con, "SELECT username FROM del_login WHERE role='delivery' AND is_active=1 ORDER BY username ASC");
        if($agent_result && mysqli_num_rows($agent_result) > 0){
            while($ag = mysqli_fetch_assoc($agent_result)){
                $agents[] = $ag['username'];
            }
        }

}

// Ensure $agents is defined for rendering (populate for GET requests)
if(!isset($agents) || !is_array($agents)){
    $agents = [];
    $agent_res2 = @mysqli_query($con, "SELECT username FROM del_login WHERE role='delivery' AND is_active=1 ORDER BY username ASC");
    if($agent_res2 && mysqli_num_rows($agent_res2) > 0){
        while($ag = mysqli_fetch_assoc($agent_res2)){
            $agents[] = $ag['username'];
        }
    }
}

// count queries
$active_count = 0;
$history_count = 0;
$c1 = @mysqli_query($con, "SELECT COUNT(*) AS total FROM builds WHERE LOWER(IFNULL(status,'pending')) NOT IN ('completed','delivered','cancelled')");
if($c1 && mysqli_num_rows($c1)>0){ $active_count = (int)(mysqli_fetch_assoc($c1)['total'] ?? 0); }
$c2 = @mysqli_query($con, "SELECT COUNT(*) AS total FROM builds_history");
if($c2 && mysqli_num_rows($c2)>0){ $history_count = (int)(mysqli_fetch_assoc($c2)['total'] ?? 0); }

// fetch data
$res = false;
$hist_res = false;
if($view === 'history'){
  $hist_res = @mysqli_query($con, "SELECT * FROM builds_history ORDER BY completed_at DESC");
} else {
    $res = @mysqli_query($con, "SELECT * FROM builds WHERE LOWER(IFNULL(status,'pending')) NOT IN ('completed','delivered','cancelled') ORDER BY created_at DESC");
}

include('header.php');
?>

<style>
    :root {
        --primary-soft: #ede9fe;
        --primary-bold: #7c3aed;
        --success-soft: #ecfdf5;
        --success-text: #065f46;
        --warning-soft: #fffbeb;
        --warning-text: #92400e;
        --text-main: #1f2a44;
        --text-muted: #64748b;
    }
    
    body {
        background:
            radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
            radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
            radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 30%),
            linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
    }

    .card-modern {
        background: linear-gradient(155deg, rgba(245, 243, 255, 0.9) 0%, rgba(238, 246, 255, 0.9) 55%, rgba(240, 253, 244, 0.9) 100%);
        border: 1px solid #bfdbfe;
        border-radius: 16px;
        box-shadow: 0 10px 22px rgba(30, 64, 175, 0.12);
        overflow: hidden;
    }

    .card-header-modern {
        padding: 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    /* Custom Tab Switcher */
    .view-switcher {
        background: #f3f4f6;
        padding: 4px;
        border-radius: 8px;
        display: inline-flex;
    }
    .view-switcher a {
        padding: 6px 16px;
        border-radius: 6px;
        text-decoration: none;
        color: var(--text-muted);
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .view-switcher a.active {
        background: white;
        color: var(--primary-bold);
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .view-switcher .count {
        font-size: 0.75em;
        margin-left: 6px;
        opacity: 0.8;
    }

    /* Table Styling */
    .table-custom thead th {
        background: #f9fafb;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem;
    }
    .table-custom tbody td {
        padding: 1rem;
        vertical-align: middle;
        color: var(--text-main);
        border-bottom: 1px solid #f3f4f6;
    }
    .table-custom tbody tr:hover {
        background-color: #f9fafb;
    }

    /* Specific Columns */
    .col-build-name {
        font-weight: 600;
        color: var(--primary-bold);
    }
    .col-price {
        font-family: 'Courier New', monospace;
        font-weight: 700;
    }
    
    /* Badges */
    .status-badge {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }
    .badge-pending { background: var(--warning-soft); color: var(--warning-text); }
    .badge-accepted { background: #eff6ff; color: #1e40af; }
    .badge-processed, .badge-completed { background: var(--success-soft); color: var(--success-text); }

    /* Action Buttons */
    .btn-action-group {
        display: flex;
        gap: 8px;
        justify-content: center;
    }
    .btn-view {
        color: var(--text-muted);
        background: transparent;
        border: 1px solid #e5e7eb;
    }
    .btn-view:hover { background: #f3f4f6; color: var(--text-main); }
    
    .btn-accept {
        background: var(--success-soft);
        color: var(--success-text);
        border: 1px solid transparent;
    }
    .btn-accept:hover { background: #d1fae5; }
    
    .btn-complete {
        background: var(--warning-soft);
        color: var(--warning-text);
        border: 1px solid transparent;
    }
    .btn-complete:hover { background: #fef3c7; }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #9ca3af;
    }
    /* Action column styles */
    .action-wrap { display:flex; gap:8px; align-items:center; justify-content:flex-end; }
    .action-select { display:inline-flex; align-items:center; gap:6px; }
    .action-select select { padding:6px 8px; border-radius:8px; border:1px solid #e6edf3; background:#fff; font-size:0.85rem; }
    .action-wrap form { margin:0; display:flex; align-items:center; }
    .btn-icon { width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; border:none; cursor:pointer; vertical-align:middle; line-height:1; padding:0; }
    .btn-icon i { display:inline-block; font-size:1rem; line-height:1; }
    .btn-view-icon { background:transparent; color:#374151; border:1px solid #e6edf3; }
    .btn-accept-icon { background:#ecfdf5; color:#065f46; }
    .btn-assign-icon { background:#eef2ff; color:#4c1d95; }
    .btn-complete-icon { background:#fffbeb; color:#92400e; }
    .btn-delete-icon { background:#fee2e2; color:#9b111e; }
    .btn-icon:hover { transform:translateY(-1px); box-shadow:0 4px 10px rgba(15,23,42,0.06); }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            
            <?php if(isset($_GET['msg']) && $_GET['msg']==='completed'): ?>
                <div class="alert alert-success d-flex align-items-center mb-4 shadow-sm border-0 rounded-3">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>Build successfully archived to history.</div>
                </div>
            <?php endif; ?>

            <div class="card-modern">
                <div class="card-header-modern">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark"><i class="bi bi-pc-display text-primary me-2"></i>Build Requests</h4>
                        <div class="text-muted small">Manage custom PC configurations submitted by users</div>
                    </div>
                    
                    <div class="view-switcher">
                        <a href="builds.php" class="<?php echo $view==='active'?'active':''; ?>">
                            Active <span class="count bg-secondary text-white rounded-pill px-1"><?php echo $active_count; ?></span>
                        </a>
                        <a href="builds.php?view=history" class="<?php echo $view==='history'?'active':''; ?>">
                            History <span class="count bg-secondary text-white rounded-pill px-1"><?php echo $history_count; ?></span>
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 18%">Build Name</th>
                                <th style="width: 15%">User</th>
                                <th style="width: 12%">Status</th>
                                <th style="width: 12%">Agent</th>
                                <th style="width: 12%">Total</th>
                                <th style="width: 12%"><?php echo $view === 'history' ? 'Completed' : 'Created'; ?></th>
                                <th style="width: 14%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
// --- TABLE BODY RENDERING ---

// Helper function to render a row
function renderRow($r, $i, $isHistory, $agents) {
    $id = (int)$r['id'];
    $bname = htmlspecialchars($r['name']);
    $uname = htmlspecialchars($r['user_name'] ?: 'User#'.$r['user_id']);
    $status = strtolower($r['status'] ?? 'pending');
    $assigned_agent = $r['assigned_agent'] ?? '';
    $total = number_format((float)$r['total'], 2);
    $date = $isHistory ? $r['completed_at'] : $r['created_at'];
    $dateFormatted = date('M d, Y', strtotime($date));

    // Badge Logic
    $badgeClass = 'badge-secondary';
    if ($status === 'pending') $badgeClass = 'badge-pending';
    elseif ($status === 'accepted') $badgeClass = 'badge-accepted';
    elseif ($status === 'processed' || $status === 'completed') $badgeClass = 'badge-completed';

    echo "<tr>";
    echo "<td class='text-muted'>{$i}</td>";
    echo "<td class='col-build-name'>{$bname}</td>";
    echo "<td><div class='d-flex align-items-center gap-2'><i class='bi bi-person-circle text-secondary'></i> {$uname}</div></td>";
    echo "<td>";
    echo "<span class='status-badge {$badgeClass}'>" . htmlspecialchars(ucfirst($status)) . "</span>";
    echo $assigned_agent ? "<small class='text-muted ms-2'><i class='bi bi-truck'></i> ".htmlspecialchars($assigned_agent)."</small>" : "<small class='text-muted ms-2'>—</small>";
    echo "</td>";
    // Agent Column (show assigned agent or assignment form)
    echo "<td>";
    if ($isHistory) {
        echo $assigned_agent ? "<small class='text-muted'><i class='bi bi-truck'></i> ".htmlspecialchars($assigned_agent)."</small>" : "<small class='text-muted'>—</small>";
    } else {
        if (!empty($agents)) {
            // Assignment form sits in Agent column
            echo "<form action='builds.php' method='post' class='d-flex align-items-center' style='gap:6px;margin:0;'>";
            echo "<input type='hidden' name='id' value='{$id}'>";
            echo "<input type='hidden' name='action' value='assign_agent'>";
            echo "<select name='assigned_agent' class='form-select form-select-sm' style='min-width:120px;font-size:0.85rem;'>";
            echo "<option value=''".($assigned_agent===''?' selected':'').">Unassigned</option>";
            foreach($agents as $ag){
                $sel = ($assigned_agent === $ag) ? ' selected' : '';
                echo "<option value='".htmlspecialchars($ag)."'{$sel}>".htmlspecialchars($ag)."</option>";
            }
            echo "</select>";
            echo "<button type='submit' class='btn-icon btn-assign-icon' title='Assign' style='margin-left:6px;'><i class='bi bi-check-lg'></i></button>";
            echo "</form>";
        } else {
            echo $assigned_agent ? "<small class='text-muted'><i class='bi bi-truck'></i> ".htmlspecialchars($assigned_agent)."</small>" : "<small class='text-muted'>—</small>";
        }
    }
    echo "</td>";
        echo "<td class='col-price'>₹{$total}</td>";
    echo "<td class='small text-muted'>{$dateFormatted}</td>";
    echo "<td>";
    echo "<div class='action-wrap'>";
    // View Button
    echo "<a href='view_build.php?id={$id}' class='btn-icon btn-view-icon' title='View Details'><i class='bi bi-eye'></i></a>";

    if (!$isHistory) {
        // Delete Button (replace accept/create order)
        echo "<form action='builds.php' method='post' class='d-inline' style='display:flex;align-items:center;margin-left:6px;'>";
        echo "<input type='hidden' name='id' value='{$id}'>";
        echo "<input type='hidden' name='action' value='delete_build'>";
        echo "<button class='btn-icon btn-delete-icon' type='submit' title='Delete Build'><i class='bi bi-trash'></i></button>";
        echo "</form>";

        // (assign control moved to Agent column)

        if ($status !== 'pending') {
            // Complete Button Form
            echo "<form action='builds.php' method='post' class='d-inline' style='display:flex;align-items:center;margin-left:6px;'>";
            echo "<input type='hidden' name='id' value='{$id}'>";
            echo "<input type='hidden' name='action' value='complete'>";
            echo "<button class='btn-icon btn-complete-icon' type='submit' title='Mark Complete'><i class='bi bi-archive'></i></button>";
            echo "</form>";
        }
    }

    echo "</div>";
    echo "</td>";
    echo "</tr>";
}

if($view === 'history'){
    if($hist_res && mysqli_num_rows($hist_res) > 0){
        $i=1;
        while($r = mysqli_fetch_assoc($hist_res)){
            renderRow($r, $i, true, $agents);
            $i++;
        }
    } else {
        echo "<tr><td colspan='8'><div class='empty-state'><i class='bi bi-clock-history fs-1 mb-2'></i><p>No history found</p></div></td></tr>";
    }
} else {
    if($res && mysqli_num_rows($res) > 0){
        $i=1;
        while($r = mysqli_fetch_assoc($res)){
            renderRow($r, $i, false, $agents);
            $i++;
        }
    } else {
        echo "<tr><td colspan='8'><div class='empty-state'><i class='bi bi-inbox fs-1 mb-2'></i><p>No active builds</p></div></td></tr>";
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

<?php include(__DIR__ . '/footer.php'); ?>
</div>