<?php
define('page','service');
include('header.php');
?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card shadow-sm reveal">
        <div class="card-body">
          <h4 class="mb-3">Service Requests</h4>
          <?php if(isset($_GET['ok']) && $_GET['ok']==1): ?>
            <div class="alert alert-success">Support request submitted successfully.</div>
          <?php endif; ?>

          <p>Select a service type and fill the form:</p>
            <h4 class="mb-3">Support Requests</h4>
            <p>Select a support type and complete the form:</p>
          <ul class="nav nav-tabs mb-3" id="serviceTabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" id="repair-tab" data-bs-toggle="tab" data-bs-target="#repair" type="button" role="tab">Repair</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" id="install-tab" data-bs-toggle="tab" data-bs-target="#install" type="button" role="tab">Installation</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" id="custom-tab" data-bs-toggle="tab" data-bs-target="#custom" type="button" role="tab">Custom Request</button></li>
          </ul>

          <div class="tab-content">
            <div class="tab-pane fade show active" id="repair" role="tabpanel">
              <form action="service_submit.php" method="post">
                <input type="hidden" name="service_type" value="repair">
                <div class="mb-3"><label class="form-label">Product / Item</label><input name="item" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Problem Description</label><textarea name="details" class="form-control" rows="4" required></textarea></div>
                <button class="btn btn-primary" type="submit">Submit Repair Request</button>
              </form>
            </div>
            <div class="tab-pane fade" id="install" role="tabpanel">
              <form action="service_submit.php" method="post">
                <input type="hidden" name="service_type" value="installation">
                <div class="mb-3"><label class="form-label">Product / Item</label><input name="item" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Preferred Date / Notes</label><textarea name="details" class="form-control" rows="3"></textarea></div>
                <button class="btn btn-primary" type="submit">Submit Installation Request</button>
              </form>
            </div>
            <div class="tab-pane fade" id="custom" role="tabpanel">
              <form action="service_submit.php" method="post">
                <input type="hidden" name="service_type" value="custom">
                <div class="mb-3"><label class="form-label">Request Title</label><input name="item" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Details</label><textarea name="details" class="form-control" rows="4" required></textarea></div>
                <button class="btn btn-primary" type="submit">Submit Request</button>
              </form>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<?php include('footer.php'); ?>
