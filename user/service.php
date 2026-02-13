<?php
define('page','service');
include('header.php');
?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card shadow-sm reveal service-card">
        <div class="card-body">
          <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
            <div>
              <h4 class="mb-1">Service Requests</h4>
              <p class="text-muted mb-0">Choose a request type and provide the key details. Our support team will respond soon.</p>
            </div>
            <span class="badge bg-light text-dark border mt-2 mt-md-0 service-meta-badge">Average response: 24-48 hrs</span>
          </div>

          <?php if(isset($_GET['ok']) && $_GET['ok']==1): ?>
            <div class="alert alert-success">Your service request has been submitted successfully. Our team will contact you soon.</div>
          <?php endif; ?>

          <div class="accordion service-accordion" id="serviceAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingRepair">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRepair" aria-expanded="true" aria-controls="collapseRepair">
                  Repair Request
                </button>
              </h2>
              <div id="collapseRepair" class="accordion-collapse collapse show" aria-labelledby="headingRepair" data-bs-parent="#serviceAccordion">
                <div class="accordion-body">
                  <form action="service_submit.php" method="post">
                    <input type="hidden" name="service_type" value="repair">
                    <div class="mb-3">
                      <label class="form-label">Product / Item</label>
                      <input name="item" class="form-control" placeholder="e.g., Solar inverter model X200" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Problem Description</label>
                      <textarea name="details" class="form-control" rows="4" placeholder="Describe the issue, any error lights, or recent changes" required></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                      <div class="col-md-6">
                        <label class="form-label">Phone Number (optional)</label>
                        <input type="tel" name="phone" class="form-control" placeholder="e.g., 9876543210">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Preferred Contact Time (optional)</label>
                        <select name="contact_time" class="form-select">
                          <option value="">Select a time window</option>
                          <option value="Morning">Morning (9am - 12pm)</option>
                          <option value="Afternoon">Afternoon (12pm - 4pm)</option>
                          <option value="Evening">Evening (4pm - 7pm)</option>
                        </select>
                      </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Submit Repair Request</button>
                  </form>
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header" id="headingInstall">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInstall" aria-expanded="false" aria-controls="collapseInstall">
                  Installation Request
                </button>
              </h2>
              <div id="collapseInstall" class="accordion-collapse collapse" aria-labelledby="headingInstall" data-bs-parent="#serviceAccordion">
                <div class="accordion-body">
                  <form action="service_submit.php" method="post">
                    <input type="hidden" name="service_type" value="installation">
                    <div class="mb-3">
                      <label class="form-label">Product / Item</label>
                      <input name="item" class="form-control" placeholder="e.g., 300W panel kit" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Preferred Date / Notes</label>
                      <textarea name="details" class="form-control" rows="3" placeholder="Share availability, site details, or access notes"></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                      <div class="col-md-6">
                        <label class="form-label">Phone Number (optional)</label>
                        <input type="tel" name="phone" class="form-control" placeholder="e.g., 9876543210">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Preferred Contact Time (optional)</label>
                        <select name="contact_time" class="form-select">
                          <option value="">Select a time window</option>
                          <option value="Morning">Morning (9am - 12pm)</option>
                          <option value="Afternoon">Afternoon (12pm - 4pm)</option>
                          <option value="Evening">Evening (4pm - 7pm)</option>
                        </select>
                      </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Submit Installation Request</button>
                  </form>
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header" id="headingCustom">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCustom" aria-expanded="false" aria-controls="collapseCustom">
                  Custom Service Request
                </button>
              </h2>
              <div id="collapseCustom" class="accordion-collapse collapse" aria-labelledby="headingCustom" data-bs-parent="#serviceAccordion">
                <div class="accordion-body">
                  <form action="service_submit.php" method="post">
                    <input type="hidden" name="service_type" value="custom">
                    <div class="mb-3">
                      <label class="form-label">Request Title</label>
                      <input name="item" class="form-control" placeholder="e.g., Site audit and recommendation" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Details</label>
                      <textarea name="details" class="form-control" rows="4" placeholder="Explain what you need and the goal" required></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                      <div class="col-md-6">
                        <label class="form-label">Phone Number (optional)</label>
                        <input type="tel" name="phone" class="form-control" placeholder="e.g., 9876543210">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Preferred Contact Time (optional)</label>
                        <select name="contact_time" class="form-select">
                          <option value="">Select a time window</option>
                          <option value="Morning">Morning (9am - 12pm)</option>
                          <option value="Afternoon">Afternoon (12pm - 4pm)</option>
                          <option value="Evening">Evening (4pm - 7pm)</option>
                        </select>
                      </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Submit Request</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          
        </div>
      </div>
    </div>
  </div>
</div>
<?php include('footer.php'); ?>
