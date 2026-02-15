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

				// dropdown fallback if Bootstrap JS is not active
				if(!(window.bootstrap && bootstrap.Dropdown)){
					var userMenu = document.getElementById('userMenu');
					if(userMenu){
						var menu = userMenu.nextElementSibling;
						userMenu.addEventListener('click', function(e){
							e.preventDefault();
							if(!menu) return;
							menu.classList.toggle('show');
							userMenu.setAttribute('aria-expanded', menu.classList.contains('show') ? 'true' : 'false');
						});
						document.addEventListener('click', function(e){
							if(!menu || userMenu.contains(e.target) || menu.contains(e.target)) return;
							menu.classList.remove('show');
							userMenu.setAttribute('aria-expanded', 'false');
						});
					}
				}

				// ensure dropdown toggles even if auto-init fails
				var userMenuEl = document.getElementById('userMenu');
				if(userMenuEl && window.bootstrap && bootstrap.Dropdown){
					userMenuEl.addEventListener('click', function(e){
						e.preventDefault();
						bootstrap.Dropdown.getOrCreateInstance(userMenuEl).toggle();
					});
				}

				// navigation fallbacks for header links
				var viewProducts = document.getElementById('adminViewProducts');
				if(viewProducts){
					viewProducts.addEventListener('click', function(e){
						e.preventDefault();
						window.location.href = viewProducts.getAttribute('href');
					});
				}
				var logoutLink = document.getElementById('adminLogout');
				if(logoutLink){
					logoutLink.addEventListener('click', function(e){
						e.preventDefault();
						window.location.href = logoutLink.getAttribute('href');
					});
				}
			});
		</script>
</body>
</html>