<?php
include('header.php');
include('conn.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id <= 0){ header('Location: service_requests.php'); exit; }

$res = @mysqli_query($con, "SELECT * FROM service_requests WHERE id=$id LIMIT 1");
if(!$res || mysqli_num_rows($res) === 0){ header('Location: service_requests.php'); exit; }
$r = mysqli_fetch_assoc($res);

// Determine dynamic badge colors and icons based on status
$status_raw = strtolower(trim($r['status']));
$badge_class = 'badge-default';
$status_icon = 'bi-info-circle-fill';

if (strpos($status_raw, 'pending') !== false) {
    $badge_class = 'badge-warning';
    $status_icon = 'bi-hourglass-split';
} elseif (strpos($status_raw, 'progress') !== false) {
    $badge_class = 'badge-primary';
    $status_icon = 'bi-gear-wide-connected bi-spin';
} elseif (strpos($status_raw, 'complete') !== false || strpos($status_raw, 'delivered') !== false) {
    $badge_class = 'badge-success';
    $status_icon = 'bi-check-circle-fill';
} elseif (strpos($status_raw, 'cancel') !== false) {
    $badge_class = 'badge-danger';
    $status_icon = 'bi-x-octagon-fill';
}

$display_status = ucwords(str_replace('_', ' ', $r['status']));
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .ticket-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 40px;
    }

    .ticket-header {
        background: #f8fafc;
        padding: 24px 30px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .ticket-title {
        margin: 0;
        font-weight: 800;
        color: #0f172a;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ticket-body {
        padding: 30px;
    }

    /* Grid for Metadata */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .info-block {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f1f5f9;
        color: #6366f1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .info-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
    }

    /* Details Box */
    .details-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 25px;
    }

    .details-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .details-content {
        color: #334155;
        font-size: 1rem;
        line-height: 1.6;
        margin: 0;
        white-space: pre-wrap; /* Preserves line breaks automatically */
    }

    /* Status Badges */
    .ticket-badge {
        padding: 8px 16px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .badge-warning { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .badge-primary { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .badge-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .badge-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .badge-default { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

    /* Action Footer */
    .ticket-footer {
        padding: 20px 30px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }
    
    .btn-back {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .btn-back:hover {
        background: #f1f5f9;
        color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9 col-xl-8">
            
            <div class="ticket-card">
                <div class="ticket-header">
                    <h4 class="ticket-title">
                        <i class="bi bi-ticket-detailed text-primary"></i>
                        Support Ticket #<?php echo str_pad($r['id'], 5, "0", STR_PAD_LEFT); ?>
                    </h4>
                    <div class="ticket-badge <?php echo $badge_class; ?>">
                        <i class="bi <?php echo $status_icon; ?>"></i>
                        <?php echo htmlspecialchars($display_status); ?>
                    </div>
                </div>

                <div class="ticket-body">
                    <div class="info-grid">
                        <div class="info-block">
                            <div class="info-icon"><i class="bi bi-person-fill"></i></div>
                            <div>
                                <div class="info-label">Customer Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($r['user']); ?></div>
                            </div>
                        </div>

                        <div class="info-block">
                            <div class="info-icon" style="color: #0ea5e9; background: #e0f2fe;"><i class="bi bi-calendar-event"></i></div>
                            <div>
                                <div class="info-label">Submitted On</div>
                                <div class="info-value"><?php echo date('M d, Y h:i A', strtotime($r['created_at'])); ?></div>
                            </div>
                        </div>

                        <div class="info-block">
                            <div class="info-icon" style="color: #8b5cf6; background: #ede9fe;"><i class="bi bi-pc-display"></i></div>
                            <div>
                                <div class="info-label">Equipment / Item</div>
                                <div class="info-value"><?php echo htmlspecialchars($r['item']); ?></div>
                            </div>
                        </div>

                        <div class="info-block">
                            <div class="info-icon" style="color: #f59e0b; background: #fef3c7;"><i class="bi bi-tools"></i></div>
                            <div>
                                <div class="info-label">Service Type</div>
                                <div class="info-value"><?php echo htmlspecialchars($r['service_type']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="details-box">
                        <div class="details-title">
                            <i class="bi bi-card-text text-primary"></i> Issue Description & Details
                        </div>
                        <p class="details-content"><?php echo htmlspecialchars($r['details']); ?></p>
                    </div>
                </div>

                <div class="ticket-footer text-end">
                    <a href="service_requests.php" class="btn btn-back">
                        <i class="bi bi-arrow-left me-2"></i>Back to Requests
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include('footer.php'); ?>