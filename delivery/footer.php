<script>
document.addEventListener('DOMContentLoaded', function(){
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

});
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

</body>
</html>