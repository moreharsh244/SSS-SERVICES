<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
    session_start();
}
if (!isset($_SESSION['is_login'])) {
    header('location:login.php');
    exit;
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location:login.php');
    exit;
}
include('conn.php');
include('../delivery/helpers.php');
ensure_builds_history_table($con);

// Ensure assigned_agent column exists in builds table
$col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='builds' AND COLUMN_NAME='assigned_agent' LIMIT 1";
$col_res = mysqli_query($con, $col_check);
if(!$col_res || mysqli_num_rows($col_res)===0){
    @mysqli_query($con, "ALTER TABLE builds ADD COLUMN assigned_agent VARCHAR(100) DEFAULT NULL");
}

$view = isset($_GET['view']) ? trim($_GET['view']) : 'active';

// --- LOGIC BLOCK ---
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
  $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
  $action = trim($_POST['action']);
  
  // Handle assign agent
  if($action === 'assign_agent' && $id > 0){
    $agent = trim($_POST['assigned_agent'] ?? '');
    $agent_esc = mysqli_real_escape_string($con, $agent);
    if($agent_esc !== ''){
      mysqli_query($con, "UPDATE builds SET assigned_agent='$agent_esc' WHERE id='$id' LIMIT 1");
      log_delivery_action($con, $agent, 'assign_build', 'Assigned build #'.$id.' by admin '.$_SESSION['username']);
    } else {
      mysqli_query($con, "UPDATE builds SET assigned_agent=NULL WHERE id='$id' LIMIT 1");
    }
    header('Location: builds.php'); exit;
  }
  
  if($id>0){
    // ensure builds table has status column
    $col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='builds' AND COLUMN_NAME='status' LIMIT 1";
    $cres = mysqli_query($con, $col_check);
    if(!$cres || mysqli_num_rows($cres)===0){ @mysqli_query($con, "ALTER TABLE builds ADD COLUMN status VARCHAR(32) NOT NULL DEFAULT 'pending'"); }

    // fetch build
    $bq = mysqli_query($con, "SELECT * FROM builds WHERE id='$id' LIMIT 1");
    if($bq && mysqli_num_rows($bq)>0){
      $build = mysqli_fetch_assoc($bq);
      $build_status = $build['status'] ?? 'pending';
      
      if($action === 'accept' && $build_status === 'pending'){
        // fetch items
        $items_q = "SELECT bi.*, p.pname AS product_name FROM build_items bi LEFT JOIN products p ON p.pid = bi.product_id WHERE bi.build_id='$id'";
        $items_r = mysqli_query($con, $items_q);
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

        if($items_r && mysqli_num_rows($items_r)>0){
          // determine correct identifier for purchase.user
          $userIdentifier = '';
          $maybe = trim($build['user_name'] ?? '');
          if(!empty($maybe) && filter_var($maybe, FILTER_VALIDATE_EMAIL)){
            $userIdentifier = $maybe;
          }
          if($userIdentifier === '' && !empty($build['user_id'])){
            $uid = intval($build['user_id']);
            $ru = mysqli_query($con, "SELECT c_email, c_name FROM cust_reg WHERE cid=$uid LIMIT 1");
            if($ru && mysqli_num_rows($ru)>0){ $ur = mysqli_fetch_assoc($ru); $userIdentifier = $ur['c_email'] ?? $ur['c_name'] ?? ''; }
          }
          if($userIdentifier === ''){ $userIdentifier = 'user_'.$build['user_id']; }
          $user = mysqli_real_escape_string($con, $userIdentifier);
          while($it = mysqli_fetch_assoc($items_r)){
            $pid = intval($it['product_id']);
                        if($pid <= 0 || empty($it['product_name'])){
                            continue;
                        }
                        $pname = mysqli_real_escape_string($con, $it['product_name']);
                        $price = floatval($it['price']);
            $qty = max(1, intval($it['qty'] ?? 1));
                        $ins = "INSERT INTO purchase (pname, user, pprice, qty, prod_id, status, delivery_status) VALUES ('{$pname}', '{$user}', '{$price}', {$qty}, {$pid}, 'pending', 'pending')";
            mysqli_query($con, $ins);
          }
        }

        mysqli_query($con, "UPDATE builds SET status='accepted' WHERE id='$id' LIMIT 1");
        header('Location: orders_list.php'); exit;
      } elseif($action === 'complete' && $build_status !== 'pending'){
        $user_id = intval($build['user_id'] ?? 0);
        $user_name = mysqli_real_escape_string($con, $build['user_name'] ?? '');
        $name = mysqli_real_escape_string($con, $build['name'] ?? '');
        $description = mysqli_real_escape_string($con, $build['description'] ?? '');
        $total = floatval($build['total'] ?? 0);
        $status = mysqli_real_escape_string($con, $build['status'] ?? 'pending');
        $created_at = mysqli_real_escape_string($con, $build['created_at'] ?? '');

        $ins = "INSERT INTO builds_history (id, user_id, user_name, name, description, total, status, created_at)
                VALUES ($id, $user_id, '$user_name', '$name', '$description', $total, '$status', ".
                (!empty($created_at) ? "'{$created_at}'" : "NULL").
                ")
                ON DUPLICATE KEY UPDATE status=VALUES(status), user_name=VALUES(user_name), description=VALUES(description)";
        @mysqli_query($con, $ins);

        @mysqli_query($con, "DELETE FROM builds WHERE id='$id' LIMIT 1");
        header('Location: builds.php?view=history&msg=completed'); exit;
      }
    }
  }
  header('Location: builds.php'); exit;
}

// Fetch delivery agents
$agents = [];
$agent_query = "SELECT DISTINCT username FROM del_login WHERE role='delivery' AND is_active=1 ORDER BY username ASC";
$agent_result = mysqli_query($con, $agent_query);
if($agent_result && mysqli_num_rows($agent_result) > 0){
    while($ag = mysqli_fetch_assoc($agent_result)){
        $agents[] = $ag['username'];
    }
}

// count queries
$active_count = 0;
$history_count = 0;
$c1 = @mysqli_query($con, "SELECT COUNT(*) AS total FROM builds WHERE LOWER(IFNULL(status,'pending')) <> 'completed'");
if($c1 && mysqli_num_rows($c1)>0){ $active_count = (int)(mysqli_fetch_assoc($c1)['total'] ?? 0); }
$c2 = @mysqli_query($con, "SELECT COUNT(*) AS total FROM builds_history");
if($c2 && mysqli_num_rows($c2)>0){ $history_count = (int)(mysqli_fetch_assoc($c2)['total'] ?? 0); }

// fetch data
$res = false;
$hist_res = false;
if($view === 'history'){
  $hist_res = @mysqli_query($con, "SELECT * FROM builds_history ORDER BY completed_at DESC");
} else {
  $res = @mysqli_query($con, "SELECT * FROM builds WHERE LOWER(IFNULL(status,'pending')) <> 'completed' ORDER BY created_at DESC");
}

include('header.php');
?>

<style>
    :root {
        --primary-soft: #eef2ff;
        --primary-bold: #4338ca;
        --success-soft: #ecfdf5;
        --success-text: #065f46;
        --warning-soft: #fffbeb;
        --warning-text: #92400e;
        --text-main: #1f2937;
        --text-muted: #6b7280;
    }
    
    body { background-color: #f3f4f6; }

    .card-modern {
        background: white;
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
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
    echo "<td><span class='status-badge {$badgeClass}'>" . htmlspecialchars(ucfirst($status)) . "</span></td>";    
    // Agent Column
    echo "<td>";
    if($isHistory || empty($agents)){
        echo $assigned_agent ? "<small class='text-muted'><i class='bi bi-truck'></i> ".htmlspecialchars($assigned_agent)."</small>" : "<small class='text-muted'>—</small>";
    } else {
        echo "<form action='builds.php' method='post' class='d-inline'>";
        echo "<input type='hidden' name='id' value='{$id}'>";
        echo "<input type='hidden' name='action' value='assign_agent'>";
        echo "<select name='assigned_agent' class='form-select form-select-sm' onchange='this.form.submit()' style='font-size:0.75rem;'>";
        echo "<option value=''".($assigned_agent===''?' selected':'')."'>Unassigned</option>";
        foreach($agents as $ag){
            $sel = ($assigned_agent === $ag) ? ' selected' : '';
            echo "<option value='".htmlspecialchars($ag)."'{$sel}>".htmlspecialchars($ag)."</option>";
        }
        echo "</select>";
        echo "</form>";
    }
    echo "</td>";
        echo "<td class='col-price'>₹{$total}</td>";
    echo "<td class='small text-muted'>{$dateFormatted}</td>";
    echo "<td>";
    echo "<div class='btn-action-group'>";
    
    // View Button
    echo "<a href='view_build.php?id={$id}' class='btn btn-sm btn-view' title='View Details'><i class='bi bi-eye'></i></a>";

    if (!$isHistory) {
        if ($status === 'pending') {
            // Accept Button Form
            echo "<form action='builds.php' method='post' class='d-inline'>";
            echo "<input type='hidden' name='id' value='{$id}'>";
            echo "<input type='hidden' name='action' value='accept'>";
            echo "<button class='btn btn-sm btn-accept' type='submit' title='Accept & Create Order'><i class='bi bi-check-lg'></i></button>";
            echo "</form>";
        } else {
            // Complete Button Form
            echo "<form action='builds.php' method='post' class='d-inline' onsubmit='return confirm(\"Mark this build as complete and archive it to history?\");'>";
            echo "<input type='hidden' name='id' value='{$id}'>";
            echo "<input type='hidden' name='action' value='complete'>";
            echo "<button class='btn btn-sm btn-complete' type='submit' title='Mark Complete'><i class='bi bi-archive'></i></button>";
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

<?php include('footer.php'); ?>