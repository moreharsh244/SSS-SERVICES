		<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="globalToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="me-auto">Shree Swami Samarth</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body"></div>
  </div>
</div>
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

			// toast via URL params
			var params = new URLSearchParams(window.location.search);
			var msg = params.get('toast');
			if(msg){ showToast(msg); }

			// quick cart storage
			function getCart(){
				try{ return JSON.parse(localStorage.getItem('quick_cart') || '[]'); }catch(e){ return []; }
			}
			function setCart(items){ localStorage.setItem('quick_cart', JSON.stringify(items)); }
			function renderCart(){
				var items = getCart();
				var cartItems = document.getElementById('cartItems');
				var cartTotal = document.getElementById('cartTotal');
				var cartBadge = document.getElementById('cartBadge');
				if(!cartItems || !cartTotal || !cartBadge) return;
				cartItems.innerHTML = '';
				var total = 0;
				items.forEach(function(it){
					total += (it.price * it.qty);
					var row = document.createElement('div');
					row.className = 'cart-item';
					row.innerHTML = '<img src="'+it.img+'" alt="'+it.name+'"><div><div><strong>'+it.name+'</strong></div><div class="small text-muted">Qty '+it.qty+' • ₹'+it.price.toFixed(2)+'</div></div>';
					cartItems.appendChild(row);
				});
				cartTotal.textContent = '₹' + total.toFixed(2);
				cartBadge.textContent = items.length;
				cartBadge.style.display = items.length ? 'inline-block' : 'none';
			}

			renderCart();

			document.body.addEventListener('submit', function(e){
				var form = e.target.closest('form[data-cart-form]');
				if(!form) return;
				var item = {
					name: form.getAttribute('data-cart-name') || 'Item',
					price: Number(form.getAttribute('data-cart-price') || '0'),
					qty: Number(form.querySelector('input[name="qty"]')?.value || '1'),
					img: form.getAttribute('data-cart-img') || ''
				};
				var items = getCart();
				items.unshift(item);
				items = items.slice(0, 5);
				setCart(items);
				renderCart();
			});
		});
		</script>
</div>
</div>
</body>
</html>