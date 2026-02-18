		</main>
	</div>
</div>

		<script src="../js/bootstrap.bundle.min.js"></script>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
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

				// low stock alert modal
				var lowStockModal = document.getElementById('lowStockModal');
				if(lowStockModal && window.bootstrap && bootstrap.Modal){
					var ls = new bootstrap.Modal(lowStockModal);
					ls.show();
				}

				// Initialize Bootstrap Dropdowns
				if(window.bootstrap && bootstrap.Dropdown){
					var dropdownButtons = document.querySelectorAll('[data-bs-toggle="dropdown"]');
					dropdownButtons.forEach(function(btn){
						new bootstrap.Dropdown(btn);
					});
				}

				// dropdown fallback if Bootstrap JS is not active
				if(!(window.bootstrap && bootstrap.Dropdown)){
					// Fallback for user menu button
					var userMenuBtn = document.getElementById('userMenuBtn');
					if(userMenuBtn && userMenuBtn.nextElementSibling){
						var userMenu = userMenuBtn.nextElementSibling;
						userMenuBtn.addEventListener('click', function(e){
							e.preventDefault();
							userMenu.classList.toggle('show');
							userMenuBtn.setAttribute('aria-expanded', userMenu.classList.contains('show') ? 'true' : 'false');
						});
						document.addEventListener('click', function(e){
							if(!userMenu || userMenuBtn.contains(e.target) || userMenu.contains(e.target)) return;
							userMenu.classList.remove('show');
							userMenuBtn.setAttribute('aria-expanded', 'false');
						});
					}
					
					// Fallback for notification button
					var notifBtn = document.getElementById('notifBtn');
					if(notifBtn && notifBtn.nextElementSibling){
						var notifMenu = notifBtn.nextElementSibling;
						notifBtn.addEventListener('click', function(e){
							e.preventDefault();
							notifMenu.classList.toggle('show');
							notifBtn.setAttribute('aria-expanded', notifMenu.classList.contains('show') ? 'true' : 'false');
						});
						document.addEventListener('click', function(e){
							if(!notifMenu || notifBtn.contains(e.target) || notifMenu.contains(e.target)) return;
							notifMenu.classList.remove('show');
							notifBtn.setAttribute('aria-expanded', 'false');
						});
					}
				}

			});
		</script>
</body>
</html>