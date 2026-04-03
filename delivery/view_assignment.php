<?php
include('header.php');
include('../admin/conn.php');
include('helpers.php');

ensure_purchase_table($con);
ensure_service_requests_table($con);
ensure_service_requests_history_table($con);
ensure_builds_history_table($con);

$agent = mysqli_real_escape_string($con, $_SESSION['username'] ?? '');
$type = strtolower(trim($_GET['type'] ?? 'order'));
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(!in_array($type, ['order', 'build', 'service'], true) || $id <= 0){
    echo "<div class='container py-4'><div class='alert alert-warning'>Invalid request.</div></div>";
    include(__DIR__ . '/../footer.php');
    exit;
}

function resolve_customer_address($con, $userRaw = '', $userId = 0){
    $userRaw = trim((string)$userRaw);
    $userId = (int)$userId;

    $email = '';
    if($userRaw !== '' && filter_var($userRaw, FILTER_VALIDATE_EMAIL)){
        $email = $userRaw;
    }

    if($email === '' && stripos($userRaw, 'user_') === 0){
        $uid = intval(substr($userRaw, 5));
        if($uid > 0){
            $userId = $uid;
        }
    }

    $where = [];
    if($email !== '') $where[] = "c_email='".mysqli_real_escape_string($con, $email)."'";
    if($userId > 0) $where[] = "cid='".$userId."'";

    if(empty($where)){
        return ['name'=>'', 'email'=>'', 'contact'=>'', 'address'=>'Not available'];
    }

    $q = "SELECT c_name, c_email, c_contact, c_address, c_city, c_state, c_pincode FROM cust_reg WHERE (".implode(' OR ', $where).") LIMIT 1";
    $r = mysqli_query($con, $q);
    if($r && mysqli_num_rows($r) > 0){
        $u = mysqli_fetch_assoc($r);
        $addr = trim((string)($u['c_address'] ?? ''));
        $city = trim((string)($u['c_city'] ?? ''));
        $state = trim((string)($u['c_state'] ?? ''));
        $pin = trim((string)($u['c_pincode'] ?? ''));
        $full = trim($addr . (($city !== '' || $state !== '' || $pin !== '') ? ', ' : '') . trim($city . ($state !== '' ? ', '.$state : '') . ($pin !== '' ? ' - '.$pin : '')));
        if($full === '') $full = 'Not available';
        return [
            'name' => (string)($u['c_name'] ?? ''),
            'email' => (string)($u['c_email'] ?? ''),
            'contact' => (string)($u['c_contact'] ?? ''),
            'address' => $full
        ];
    }

    return ['name'=>'', 'email'=>'', 'contact'=>'', 'address'=>'Not available'];
}

$title = '';
$rows = [];
$addressText = 'Not available';
$meta = [];

if($type === 'order'){
    $res = mysqli_query($con, "SELECT * FROM purchase WHERE pid='$id' AND assigned_agent='$agent' LIMIT 1");
    $source = 'Active Order';
    if(!$res || mysqli_num_rows($res) === 0){
        $res = mysqli_query($con, "SELECT * FROM purchase_history WHERE pid='$id' AND assigned_agent='$agent' LIMIT 1");
        $source = 'Order History';
    }
    if($res && mysqli_num_rows($res) > 0){
        $row = mysqli_fetch_assoc($res);
        $rows[] = $row;

        $cust = resolve_customer_address($con, $row['user'] ?? '', 0);
        $addr = trim((string)($row['delivery_address'] ?? ''));
        $city = trim((string)($row['delivery_city'] ?? ''));
        $state = trim((string)($row['delivery_state'] ?? ''));
        $pin = trim((string)($row['delivery_pincode'] ?? ''));
        $orderAddress = trim($addr . (($city !== '' || $state !== '' || $pin !== '') ? ', ' : '') . trim($city . ($state !== '' ? ', '.$state : '') . ($pin !== '' ? ' - '.$pin : '')));
        $addressText = $orderAddress !== '' ? $orderAddress : $cust['address'];

        $meta = [
            'Type' => $source,
            'Order ID' => '#'.str_pad((string)$row['pid'], 5, '0', STR_PAD_LEFT),
            'Customer' => (string)($row['user'] ?? '-'),
            'Status' => ucwords(str_replace('_', ' ', (string)($row['delivery_status'] ?? $row['status'] ?? 'order_confirmed'))),
            'Payment' => strtoupper((string)($row['payment_method'] ?? 'cod')),
            'Date' => (string)($row['pdate'] ?? '-')
        ];
        $title = 'Product Order Details';
    }
}

if($type === 'build'){
    $res = mysqli_query($con, "SELECT * FROM builds WHERE id='$id' AND assigned_agent='$agent' LIMIT 1");
    $source = 'Active Build';
    if(!$res || mysqli_num_rows($res) === 0){
        $res = mysqli_query($con, "SELECT * FROM builds_history WHERE id='$id' AND assigned_agent='$agent' LIMIT 1");
        $source = 'Build History';
    }
    if($res && mysqli_num_rows($res) > 0){
        $build = mysqli_fetch_assoc($res);
        $cust = resolve_customer_address($con, $build['user_name'] ?? '', (int)($build['user_id'] ?? 0));
        $addressText = $cust['address'];

        $items_r = mysqli_query($con, "SELECT bi.*, p.pname, p.pcompany FROM build_items bi LEFT JOIN products p ON p.pid = bi.product_id WHERE bi.build_id='$id'");
        if($items_r && mysqli_num_rows($items_r) > 0){
            while($it = mysqli_fetch_assoc($items_r)) $rows[] = $it;
        }

        $meta = [
            'Type' => $source,
            'Build ID' => '#'.str_pad((string)$build['id'], 5, '0', STR_PAD_LEFT),
            'Build Name' => (string)($build['name'] ?? '-'),
            'Customer' => (string)($build['user_name'] ?? ('User#'.(int)($build['user_id'] ?? 0))),
            'Status' => ucwords(str_replace('_', ' ', (string)($build['status'] ?? 'pending'))),
            'Total' => 'Rs.'.number_format((float)($build['total'] ?? 0), 2),
            'Date' => (string)($build['created_at'] ?? '-')
        ];
        $title = 'Custom Build Details';
    }
}

if($type === 'service'){
    $res = mysqli_query($con, "SELECT * FROM service_requests WHERE id='$id' AND assigned_agent='$agent' LIMIT 1");
    $source = 'Active Service Request';
    if(!$res || mysqli_num_rows($res) === 0){
        $res = mysqli_query($con, "SELECT * FROM service_requests_history WHERE id='$id' AND assigned_agent='$agent' LIMIT 1");
        $source = 'Service History';
    }
    if($res && mysqli_num_rows($res) > 0){
        $req = mysqli_fetch_assoc($res);
        $cust = resolve_customer_address($con, $req['user'] ?? '', 0);
        $addressText = $cust['address'];

        $meta = [
            'Type' => $source,
            'Ticket ID' => '#'.str_pad((string)$req['id'], 5, '0', STR_PAD_LEFT),
            'Customer' => (string)($req['user'] ?? '-'),
            'Equipment' => (string)($req['item'] ?? '-'),
            'Service Type' => (string)($req['service_type'] ?? '-'),
            'Phone' => (string)($req['phone'] ?? '-'),
            'Preferred Time' => (string)($req['contact_time'] ?? '-'),
            'Status' => ucwords(str_replace('_', ' ', (string)($req['status'] ?? 'pending'))),
            'Date' => (string)($req['created_at'] ?? '-')
        ];
        $rows[] = $req;
        $title = 'Service Request Details';
    }
}

if($title === ''){
    echo "<div class='container py-4'><div class='alert alert-warning'>Record not found for your assignments.</div><a href='index.php' class='btn btn-outline-secondary'>Back</a></div>";
    include(__DIR__ . '/../footer.php');
    exit;
}
?>

<style>
.detail-wrap{max-width:1000px;margin:24px auto;}
.detail-card{background:#fff;border-radius:16px;box-shadow:0 12px 30px rgba(30,64,175,.12);border:1px solid #dbeafe;}
.detail-head{padding:18px 22px;border-bottom:1px solid #eef2ff;display:flex;justify-content:space-between;align-items:center;}
.detail-title{font-weight:800;color:#2b3674;margin:0;}
.meta-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;padding:18px 22px;}
.meta-box{background:#f8fbff;border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px;}
.meta-label{font-size:.75rem;color:#64748b;text-transform:uppercase;font-weight:700;}
.meta-val{font-weight:700;color:#1e293b;word-break:break-word;}
.addr-box{margin:0 22px 18px;background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:12px 14px;}
.addr-label{font-size:.75rem;color:#9a3412;text-transform:uppercase;font-weight:800;}
.addr-val{font-weight:700;color:#7c2d12;word-break:break-word;}
.table-wrap{padding:0 22px 22px;}
</style>

<div class="detail-wrap">
  <div class="detail-card">
    <div class="detail-head">
      <h4 class="detail-title"><?php echo htmlspecialchars($title); ?></h4>
      <a href="index.php" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="meta-grid">
      <?php foreach($meta as $k => $v): ?>
        <div class="meta-box">
          <div class="meta-label"><?php echo htmlspecialchars($k); ?></div>
          <div class="meta-val"><?php echo htmlspecialchars((string)$v); ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="addr-box">
      <div class="addr-label">Delivery Address</div>
      <div class="addr-val"><?php echo htmlspecialchars($addressText); ?></div>
    </div>

    <div class="table-wrap">
      <?php if($type === 'order' && !empty($rows)): $r = $rows[0]; ?>
        <table class="table table-bordered">
          <thead class="table-light"><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
          <tbody>
            <tr>
              <td><?php echo htmlspecialchars((string)($r['pname'] ?? '-')); ?></td>
              <td><?php echo (int)($r['qty'] ?? 0); ?></td>
              <td>Rs.<?php echo number_format((float)($r['pprice'] ?? 0), 2); ?></td>
              <td>Rs.<?php echo number_format(((float)($r['pprice'] ?? 0) * (int)($r['qty'] ?? 0)), 2); ?></td>
            </tr>
          </tbody>
        </table>
      <?php elseif($type === 'build'): ?>
        <table class="table table-bordered">
          <thead class="table-light"><tr><th>Component</th><th>Company</th><th>Qty</th><th>Price</th></tr></thead>
          <tbody>
            <?php if(!empty($rows)): foreach($rows as $it): ?>
              <tr>
                <td><?php echo htmlspecialchars((string)($it['pname'] ?? ('Product #'.(int)($it['product_id'] ?? 0)))); ?></td>
                <td><?php echo htmlspecialchars((string)($it['pcompany'] ?? '-')); ?></td>
                <td><?php echo (int)($it['qty'] ?? 1); ?></td>
                <td>Rs.<?php echo number_format((float)($it['price'] ?? 0), 2); ?></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="4" class="text-center text-muted">Build item details are not available in history.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      <?php elseif($type === 'service' && !empty($rows)): $r = $rows[0]; ?>
        <table class="table table-bordered">
          <thead class="table-light"><tr><th>Issue Details</th><th>Agent Note</th></tr></thead>
          <tbody>
            <tr>
              <td><?php echo nl2br(htmlspecialchars((string)($r['details'] ?? '-'))); ?></td>
              <td><?php echo nl2br(htmlspecialchars((string)($r['agent_note'] ?? '-'))); ?></td>
            </tr>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include(__DIR__ . '/../footer.php'); ?>
