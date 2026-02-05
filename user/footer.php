		<script>
		document.addEventListener('DOMContentLoaded', function(){
			// delegate image preview clicks (user)
			document.body.addEventListener('click', function(e){
				var el = e.target.closest('.img-preview');
				if(!el) return;
				e.preventDefault();
				var src = el.getAttribute('data-full') || el.getAttribute('src');
				var img = document.getElementById('modalImage');
				if(img) img.src = src;
				var m = new bootstrap.Modal(document.getElementById('imageModal'));
				m.show();
			});
		});
		</script>
</div>
</div>
</body>
</html>