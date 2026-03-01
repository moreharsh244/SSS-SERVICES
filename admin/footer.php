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

// low stock alert modal - auto-show on login or every 2 hours
			var lowStockModal = document.getElementById('lowStockModal');
			<?php if(!empty($low_stock) && $show_low_stock_modal): ?>
			if(lowStockModal && window.bootstrap && bootstrap.Modal){
				var ls = new bootstrap.Modal(lowStockModal);
				ls.show();
			}
			<?php endif; ?>

				// Initialize Bootstrap Dropdowns
				if(window.bootstrap && bootstrap.Dropdown){
					var dropdownButtons = document.querySelectorAll('[data-bs-toggle="dropdown"]');
					dropdownButtons.forEach(function(btn){
						new bootstrap.Dropdown(btn);
					});
				}

				// Ensure notification dropdown renders above sticky footer by
				// temporarily moving it to document.body and positioning fixed
				(function(){
					function measureMenu(menu){
						if(!menu) return {w: menu ? menu.offsetWidth : 0, h: menu ? menu.offsetHeight : 0};
						var wasHidden = window.getComputedStyle(menu).display === 'none';
						var origVis = menu.style.visibility;
						var origPos = menu.style.position;
						var origDisplay = menu.style.display;
						if(wasHidden){
							menu.style.visibility = 'hidden';
							menu.style.display = 'block';
							menu.style.position = 'fixed';
						}
						var w = menu.offsetWidth || 0;
						var h = menu.offsetHeight || 0;
						if(wasHidden){
							menu.style.visibility = origVis;
							menu.style.display = origDisplay;
							menu.style.position = origPos;
						}
						return {w: w, h: h};
					}

					function moveDropdownToBody(btn, menu){
						if(!menu || menu.__moved) return;
						menu.__origParent = menu.parentNode;
						menu.__origNext = menu.nextElementSibling;
						var rect = btn.getBoundingClientRect();
						var dims = measureMenu(menu);
						var menuW = dims.w || 200;
						var left = rect.right - menuW;
						left = Math.max(8, left);
						menu.style.position = 'fixed';
						menu.style.left = left + 'px';
						menu.style.top = (rect.bottom + 8) + 'px';
						menu.style.zIndex = 20000;
						document.body.appendChild(menu);
						menu.__moved = true;
					}

					function restoreDropdown(menu){
						if(!menu || !menu.__moved) return;
						menu.style.position = '';
						menu.style.left = '';
						menu.style.top = '';
						menu.style.zIndex = '';
						if(menu.__origParent){
							if(menu.__origNext) menu.__origParent.insertBefore(menu, menu.__origNext);
							else menu.__origParent.appendChild(menu);
						}
						delete menu.__moved; delete menu.__origParent; delete menu.__origNext;
					}

					// Move notification dropdown to document.body when shown to avoid clipping
					document.querySelectorAll('.notification-bell').forEach(function(btn){
						var menu = btn.nextElementSibling;
						if(!menu) return;
						// listen for bootstrap dropdown events
						btn.addEventListener('show.bs.dropdown', function(){ moveDropdownToBody(btn, menu); });
						btn.addEventListener('hide.bs.dropdown', function(){ restoreDropdown(menu); });
					});
				})();

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

<style>
.portal-toast-wrap{position:fixed;top:18px;right:18px;z-index:30000;display:flex;flex-direction:column;gap:10px;max-width:360px}
.portal-toast{padding:12px 14px;border-radius:12px;color:#fff;font-weight:600;box-shadow:0 12px 26px rgba(15,23,42,.24);animation:portalToastIn .25s ease-out}
.portal-toast.info{background:linear-gradient(135deg,#6366f1,#7c3aed)}
.portal-toast.success{background:linear-gradient(135deg,#10b981,#059669)}
.portal-toast.error{background:linear-gradient(135deg,#ef4444,#dc2626)}
@keyframes portalToastIn{from{transform:translateY(-8px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>
<div id="portalToastWrap" class="portal-toast-wrap" aria-live="polite" aria-atomic="true"></div>
<script>
(function(){
	function showPortalToast(message, type){
		if(!message) return;
		var wrap = document.getElementById('portalToastWrap');
		if(!wrap){
			wrap = document.createElement('div');
			wrap.id = 'portalToastWrap';
			wrap.className = 'portal-toast-wrap';
			document.body.appendChild(wrap);
		}
		var t = document.createElement('div');
		t.className = 'portal-toast ' + (type || 'info');
		t.textContent = String(message);
		wrap.appendChild(t);
		setTimeout(function(){ if(t && t.parentNode) t.parentNode.removeChild(t); }, 3200);
	}
	window.showPortalToast = showPortalToast;
	window.alert = function(msg){ showPortalToast(msg, 'info'); };
	var p = new URLSearchParams(window.location.search);
	var toast = p.get('toast');
	if(toast){
		var type = p.get('toast_type') || 'info';
		showPortalToast(toast, type);
		p.delete('toast'); p.delete('toast_type');
		var q = p.toString();
		history.replaceState({}, document.title, window.location.pathname + (q ? '?' + q : ''));
	}
})();
</script>

<!-- Bottom footer: copyright / bottom bar (sticky) -->
<footer id="siteFooter" class="site-footer bg-light py-3 mt-4">
	<div class="container text-center text-muted">&copy; 2026 Shree Swami Samarth</div>
</footer>

<style>
.site-footer{border-top:1px solid #eef2ff;}
</style>
<script>
 (function(){
	 var f = document.getElementById('siteFooter');
	 if(!f) return;
	 f.style.position = 'fixed';
	 f.style.left = '0';
	 f.style.right = '0';
	 f.style.bottom = '0';
	f.style.zIndex = '900';
	 function adjust(){ document.body.style.paddingBottom = (f.offsetHeight || 56) + 'px'; }
	 adjust(); window.addEventListener('resize', adjust);
 })();
</script>

</body>
</html>