<?php
define('page','service');
include('header.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
  :root {
    --primary-grad: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    --bg-surface: #f8fafc;
    --card-shadow: 0 10px 30px -5px rgba(0,0,0,0.1);
    --text-dark: #1e293b;
  }

  body {
    background-color: var(--bg-surface);
    font-family: 'Poppins', sans-serif;
    color: var(--text-dark);
  }

  /* --- Hero Section --- */
  .service-hero {
    background: white;
    padding: 60px 20px;
    text-align: center;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
  }
  
  .service-hero::before {
    content: '';
    position: absolute;
    top: -50%; left: -50%; width: 200%; height: 200%;
    background: radial-gradient(circle, rgba(99,102,241,0.05) 0%, rgba(255,255,255,0) 70%);
    z-index: 0;
  }

  .hero-badge {
    background: #e0e7ff;
    color: #4338ca;
    padding: 6px 16px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-block;
    margin-bottom: 15px;
    position: relative;
    z-index: 1;
  }

  .hero-title {
    font-weight: 800;
    font-size: 2.5rem;
    margin-bottom: 10px;
    position: relative;
    z-index: 1;
    background: -webkit-linear-gradient(45deg, #1e293b, #4f46e5);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .hero-subtitle {
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
    font-size: 1.1rem;
    position: relative;
    z-index: 1;
  }

  /* --- Service Cards Grid --- */
  .service-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
  }

  .service-card {
    background: white;
    border-radius: 20px;
    border: 1px solid #f1f5f9;
    box-shadow: var(--card-shadow);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
  }

  .service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px -10px rgba(99, 102, 241, 0.2);
    border-color: #c7d2fe;
  }

  /* Card Header Colors */
  .card-header-bg {
    height: 100px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .bg-repair { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
  .bg-install { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
  .bg-custom { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }

  .service-icon {
    width: 70px;
    height: 70px;
    background: white;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    position: absolute;
    bottom: -35px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
  .icon-repair { color: #d97706; }
  .icon-install { color: #059669; }
  .icon-custom { color: #4f46e5; }

  .card-content {
    padding: 50px 30px 30px;
    text-align: center;
    flex-grow: 1;
  }

  .service-title {
    font-weight: 700;
    font-size: 1.25rem;
    margin-bottom: 10px;
    color: #1e293b;
  }

  .service-desc {
    color: #64748b;
    font-size: 0.95rem;
    margin-bottom: 25px;
    line-height: 1.6;
  }

  .btn-request {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    transition: all 0.2s;
  }
  .btn-repair { background: #fffbeb; color: #b45309; }
  .btn-repair:hover { background: #fcd34d; color: #78350f; }
  
  .btn-install { background: #ecfdf5; color: #047857; }
  .btn-install:hover { background: #6ee7b7; color: #064e3b; }
  
  .btn-custom { background: #eef2ff; color: #4338ca; }
  .btn-custom:hover { background: #a5b4fc; color: #312e81; }

  /* Modal Styling */
  .modal-content {
    border-radius: 24px;
    border: none;
    overflow: hidden;
  }
  .modal-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 20px 30px;
  }
  .modal-title { font-weight: 700; color: #1e293b; }
  .modal-body { padding: 30px; }
  
  .form-control, .form-select {
    border-radius: 12px;
    padding: 12px 15px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
  }
  .form-control:focus, .form-select:focus {
    background: white;
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
  }
  .form-label { font-weight: 600; font-size: 0.9rem; color: #475569; margin-bottom: 8px; }

  /* Submit Btn inside modal */
  .btn-submit-modal {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    background: var(--primary-grad);
    color: white;
    font-weight: 600;
    border: none;
    box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
    transition: 0.3s;
  }
  .btn-submit-modal:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 25px -5px rgba(99, 102, 241, 0.5);
  }

</style>

<div class="service-hero">
  <div class="container">
    <span class="hero-badge"><i class="bi bi-clock-history me-1"></i> Avg Response: 24-48 Hours</span>
    <h1 class="hero-title">How can we help you?</h1>
    <p class="hero-subtitle">Select a service category below to get started. Our expert team is ready to assist with repairs, installations, and custom projects.</p>
  </div>
</div>

<div class="container">
  
  <?php if(isset($_GET['ok']) && $_GET['ok']==1): ?>
    <div class="alert alert-success shadow-sm border-0 rounded-4 mb-5 d-flex align-items-center" role="alert">
      <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
      <div>
        <h5 class="alert-heading fw-bold mb-1">Request Submitted!</h5>
        <p class="mb-0 small">Our support team has received your details and will contact you shortly.</p>
      </div>
    </div>
  <?php endif; ?>

  <div class="service-grid">
    
    <div class="service-card">
      <div class="card-header-bg bg-repair">
        <div class="service-icon icon-repair"><i class="bi bi-tools"></i></div>
      </div>
      <div class="card-content">
        <h3 class="service-title">Repair Service</h3>
        <p class="service-desc">Hardware issues? Broken components? Let us diagnose and fix your equipment professionally.</p>
        <button class="btn-request btn-repair" data-bs-toggle="modal" data-bs-target="#repairModal">
          Request Repair <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </div>
    </div>

    <div class="service-card">
      <div class="card-header-bg bg-install">
        <div class="service-icon icon-install"><i class="bi bi-hdd-network"></i></div>
      </div>
      <div class="card-content">
        <h3 class="service-title">Installation</h3>
        <p class="service-desc">Need help setting up new gear? Schedule an expert installation for seamless performance.</p>
        <button class="btn-request btn-install" data-bs-toggle="modal" data-bs-target="#installModal">
          Book Installation <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </div>
    </div>

    <div class="service-card">
      <div class="card-header-bg bg-custom">
        <div class="service-icon icon-custom"><i class="bi bi-stars"></i></div>
      </div>
      <div class="card-content">
        <h3 class="service-title">Custom Request</h3>
        <p class="service-desc">Have a unique project or specific requirement? Tell us what you need, and we'll make it happen.</p>
        <button class="btn-request btn-custom" data-bs-toggle="modal" data-bs-target="#customModal">
          Start Custom Project <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </div>
    </div>

  </div>
</div>

<div class="modal fade" id="repairModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-tools text-warning me-2"></i>Repair Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="service_submit.php" method="post">
          <input type="hidden" name="service_type" value="repair">
          
          <div class="mb-3">
            <label class="form-label">Product / Item Name</label>
            <input name="item" class="form-control" placeholder="e.g., Solar Inverter X200" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Describe the Issue</label>
            <textarea name="details" class="form-control" rows="3" placeholder="What seems to be the problem?" required></textarea>
          </div>
          
          <div class="row g-3 mb-4">
            <div class="col-6">
              <label class="form-label">Phone (Optional)</label>
              <input type="tel" name="phone" class="form-control" placeholder="Mobile No.">
            </div>
            <div class="col-6">
              <label class="form-label">Best Time</label>
              <select name="contact_time" class="form-select">
                <option value="">Anytime</option>
                <option value="Morning">Morning</option>
                <option value="Afternoon">Afternoon</option>
                <option value="Evening">Evening</option>
              </select>
            </div>
          </div>
          
          <button class="btn-submit-modal" type="submit">Submit Request</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="installModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-hdd-network text-success me-2"></i>Installation Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="service_submit.php" method="post">
          <input type="hidden" name="service_type" value="installation">
          
          <div class="mb-3">
            <label class="form-label">Product to Install</label>
            <input name="item" class="form-control" placeholder="e.g., 300W Solar Panel Kit" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Preferred Date / Site Notes</label>
            <textarea name="details" class="form-control" rows="3" placeholder="Any specific date or access instructions?"></textarea>
          </div>
          
          <div class="row g-3 mb-4">
            <div class="col-6">
              <label class="form-label">Phone (Optional)</label>
              <input type="tel" name="phone" class="form-control" placeholder="Mobile No.">
            </div>
            <div class="col-6">
              <label class="form-label">Best Time</label>
              <select name="contact_time" class="form-select">
                <option value="">Anytime</option>
                <option value="Morning">Morning</option>
                <option value="Afternoon">Afternoon</option>
                <option value="Evening">Evening</option>
              </select>
            </div>
          </div>
          
          <button class="btn-submit-modal" type="submit">Book Installation</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="customModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-stars text-primary me-2"></i>Custom Project</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="service_submit.php" method="post">
          <input type="hidden" name="service_type" value="custom">
          
          <div class="mb-3">
            <label class="form-label">Project Title</label>
            <input name="item" class="form-control" placeholder="e.g., Full Office Setup" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Project Details</label>
            <textarea name="details" class="form-control" rows="3" placeholder="Describe your requirements..." required></textarea>
          </div>
          
          <div class="row g-3 mb-4">
            <div class="col-6">
              <label class="form-label">Phone (Optional)</label>
              <input type="tel" name="phone" class="form-control" placeholder="Mobile No.">
            </div>
            <div class="col-6">
              <label class="form-label">Best Time</label>
              <select name="contact_time" class="form-select">
                <option value="">Anytime</option>
                <option value="Morning">Morning</option>
                <option value="Afternoon">Afternoon</option>
                <option value="Evening">Evening</option>
              </select>
            </div>
          </div>
          
          <button class="btn-submit-modal" type="submit">Send Proposal</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include('footer.php'); ?>