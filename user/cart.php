<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['is_login'])){ header('location:login.php'); exit; }
include('header.php');
include('../admin/conn.php');

$categories = ['CPU','Motherboard','RAM','GPU','Storage','PSU','Case','Cooler','Monitor','Accessory'];
$products_by_cat = [];
foreach($categories as $cat){
    $q = mysqli_query($con, "SELECT * FROM `products` WHERE pcat='".mysqli_real_escape_string($con,$cat)."'");
    $products_by_cat[$cat] = mysqli_fetch_all($q, MYSQLI_ASSOC);
}

?>
<div class="container mt-3">
  <div class="row">
    <div class="col-12">
      <h4>Build PC</h4>
      <p class="text-muted">Select one item for each component category to assemble your PC.</p>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-8">
      <?php foreach($products_by_cat as $cat => $rows): ?>
      <div class="card mb-3 shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3"><?php echo htmlentities($cat); ?></h5>
          <div class="row">
            <?php if(!$rows): ?>
              <div class="col-12 text-muted">No products in this category yet.</div>
            <?php endif; ?>
            <?php foreach($rows as $row): ?>
            <div class="col-12 col-md-6 col-lg-4 mb-3">
              <div class="card h-100 component-card">
                <img src="../productimg/<?php echo $row['pimg']; ?>" class="card-img-top" style="height:140px;object-fit:cover;">
                <div class="card-body p-2 d-flex flex-column">
                  <h6 class="card-title mb-1"><?php echo htmlentities($row['pname']); ?></h6>
                  <div class="mb-2 text-muted small"><?php echo htmlentities($row['pcompany']); ?></div>
                  <div class="mt-auto d-flex justify-content-between align-items-center">
                    <div class="fw-bold">₹<?php echo number_format($row['pprice'],2); ?></div>
                    <div class="btn-group">
                      <button type="button" data-pid="<?php echo $row['pid']; ?>" data-cat="<?php echo htmlentities($cat); ?>" data-price="<?php echo $row['pprice']; ?>" class="btn btn-sm btn-outline-primary select-component select-btn">Select</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Stock: <?php echo intval($row['pqty']); ?>"><i class="bi bi-box-seam"></i></button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="col-lg-4">
      <div class="card shadow-sm sticky-top" style="top:20px;">
        <div class="card-body">
          <h5 class="card-title">Build Summary</h5>
          <div id="buildSummary" class="mb-3">
            <div class="text-muted">No components selected.</div>
          </div>
          <div class="d-grid gap-2">
            <button id="saveBuildBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#saveModal" disabled>Save Build</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Save modal -->
<div class="modal fade" id="saveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="saveBuildForm" method="post" action="save_build.php">
        <div class="modal-header">
          <h5 class="modal-title">Save Build</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Build Name</label>
            <input type="text" name="build_name" class="form-control" required>
          </div>
          <input type="hidden" name="items_json" id="items_json">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  const selected = {};
  const summary = document.getElementById('buildSummary');
  const saveBtn = document.getElementById('saveBuildBtn');
  const itemsInput = document.getElementById('items_json');

  function updateCardStates(){
    document.querySelectorAll('.component-card').forEach(card => card.classList.remove('selected'));
    document.querySelectorAll('.select-btn').forEach(b=>b.classList.remove('selected'));
    Object.keys(selected).forEach(cat=>{
      const it = selected[cat];
      const btn = document.querySelector(`.select-btn[data-cat="${cat}"][data-pid="${it.pid}"]`);
      if(btn){
        btn.classList.add('selected');
        const c = btn.closest('.component-card');
        if(c) c.classList.add('selected');
      }
    });
  }

  function renderSummary(){
    const keys = Object.keys(selected);
    if(!keys.length){ summary.innerHTML = '<div class="text-muted">No components selected.</div>'; saveBtn.disabled=true; itemsInput.value=''; updateCardStates(); return; }
    let total = 0;
    let html = '<ul class="list-group mb-2 build-summary">';
    keys.forEach(k=>{
      const it = selected[k];
      html += `<li class="list-group-item d-flex justify-content-between align-items-center small"><div class=\"d-flex align-items-center\"><img src=\"../productimg/${it.img}\" class=\"comp-thumb\"> <div><div class=\"fw-bold\">${k}</div><div class=\"small text-muted\">${it.name}</div></div></div><div><div class=\"text-muted\">₹${parseFloat(it.price).toFixed(2)}</div><button data-cat=\"${k}\" class=\"btn btn-sm btn-link text-danger remove-item\">Remove</button></div></li>`;
      total += parseFloat(it.price);
    });
    html += '</ul>';
    html += `<div class="fw-bold mb-2">Total: ₹${total.toFixed(2)}</div>`;
    html += '<div class="build-actions"><button id="resetSelection" class="btn btn-clear btn-sm">Clear</button><div class="ms-auto"><button id="saveBuildBtnLocal" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#saveModal">Save Build</button></div></div>';
    summary.innerHTML = html;
    saveBtn.disabled=false;
    itemsInput.value = JSON.stringify({items:selected, total: total});
    updateCardStates();

    // bind remove buttons
    document.querySelectorAll('.remove-item').forEach(b=>{
      b.addEventListener('click', function(){
        const cat = this.dataset.cat;
        delete selected[cat];
        renderSummary();
      });
    });

    const resetBtn = document.getElementById('resetSelection');
    if(resetBtn){ resetBtn.addEventListener('click', function(){ for(const k in selected) delete selected[k]; renderSummary(); }); }
  }

  document.querySelectorAll('.select-component').forEach(btn=>{
    btn.addEventListener('click', function(){
      const pid = this.dataset.pid;
      const cat = this.dataset.cat;
      const price = this.dataset.price;
      const card = this.closest('.component-card');
      const name = card.querySelector('.card-title').innerText;
      const img = card.querySelector('img').getAttribute('src');
      // toggle: if already selected same pid, deselect
      if(selected[cat] && selected[cat].pid == pid){
        delete selected[cat];
      } else {
        selected[cat] = {pid: pid, name: name, price: price, img: img.replace('../productimg/','') };
      }
      renderSummary();
    });
  });

})();
</script>

<?php include('footer.php'); ?>
