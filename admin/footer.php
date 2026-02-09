		</main>
	</div>
</div>

		<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="globalToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="me-auto">Shree Swami Samarth</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body"></div>
  </div>
</div>
		<script src="../js/bootstrap.bundle.min.js"></script>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
				tooltipTriggerList.map(function (tooltipTriggerEl) {
					return new bootstrap.Tooltip(tooltipTriggerEl)
				})

				function showToast(message){
					var toastEl = document.getElementById('globalToast');
					if(!toastEl) return;
					toastEl.querySelector('.toast-body').textContent = message;
					var t = new bootstrap.Toast(toastEl);
					t.show();
				}

				// reveal animations
				var revealEls = document.querySelectorAll('.reveal');
				if('IntersectionObserver' in window){
					var io = new IntersectionObserver(function(entries){
						entries.forEach(function(entry){
							if(entry.isIntersecting){
								entry.target.classList.add('is-visible');
								io.unobserve(entry.target);
							}
						});
					}, { threshold: 0.12 });
					revealEls.forEach(function(el){ io.observe(el); });
				} else {
					revealEls.forEach(function(el){ el.classList.add('is-visible'); });
				}

				var params = new URLSearchParams(window.location.search);
				var msg = params.get('toast');
				if(msg){ showToast(msg); }

				// image preview click handler (admin)
				document.body.addEventListener('click', function(e){
					var el = e.target.closest('.img-preview');
					if(!el) return;
					e.preventDefault();
					var src = el.getAttribute('data-full') || el.getAttribute('src');
					var img = document.getElementById('modalImage');
					if(img){ img.src = src; }
					var m = new bootstrap.Modal(document.getElementById('imageModal'));
					m.show();
				});
			});
		</script>
</body>
</html>