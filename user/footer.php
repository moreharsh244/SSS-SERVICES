		<!-- Generic Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden;">
      <div class="modal-body text-center p-4" style="background: white;">
        <div class="mb-3">
          <div id="messageIcon" style="width: 60px; height: 60px; margin: 0 auto; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </div>
        </div>
        
        <p id="messageText" class="mb-4" style="color: #1f2937; font-size: 15px; font-weight: 500;"></p>
        
        <button type="button" class="btn btn-primary px-4" id="messageOkBtn" style="border-radius: 8px;">
          OK
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Purchase Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden;">
      <div class="modal-body text-center p-5" style="background: white;">
        <div class="mb-4">
          <div style="width: 80px; height: 80px; margin: 0 auto; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </div>
        </div>
        
        <h4 class="mb-3" style="color: #1f2937; font-weight: 600;">Order Confirmed!</h4>
        <p class="mb-4" style="color: #6b7280; font-size: 15px;">Your order has been placed successfully. You'll receive updates on our status page.</p>
        
				<div class="d-flex gap-2 justify-content-center">
					<a class="btn btn-primary px-4" href="myorder.php" role="button" style="border-radius: 8px;">
						View Orders
					</a>
					<a class="btn btn-outline-secondary px-4" href="view_products.php" role="button" style="border-radius: 8px;">
						Continue Shopping
					</a>
				</div>
      </div>
    </div>
  </div>
</div>

<style>
.modal-backdrop {
	opacity: 0 !important;
	z-index: 6990;
	pointer-events: none;
}

.modal {
	z-index: 7000;
}

#successModal,
#successModal .modal-dialog,
#successModal .modal-content,
#successModal .modal-body {
	pointer-events: auto;
}

#successModal .modal-content,
#messageModal .modal-content {
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
</style>

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

			var messageModalInstance = null;

			function showMessage(message, type){
				var messageEl = document.getElementById('messageModal');
				if(!messageEl) return;
				
				var messageText = document.getElementById('messageText');
				var messageIcon = document.getElementById('messageIcon');
				
				if(messageText) messageText.textContent = message;
				
				// Set icon color based on message type
				if(messageIcon){
					if(message.toLowerCase().includes('cancel') || message.toLowerCase().includes('failed') || message.toLowerCase().includes('invalid') || message.toLowerCase().includes('cannot')){
						messageIcon.style.background = '#ef4444'; // red
					} else if(message.toLowerCase().includes('success')){
						messageIcon.style.background = '#10b981'; // green
					} else {
						messageIcon.style.background = '#3b82f6'; // blue
					}
				}
				
				if(!messageModalInstance){
					messageModalInstance = new bootstrap.Modal(messageEl);
				}
				messageModalInstance.show();
			}

			// OK button click handler
			var okBtn = document.getElementById('messageOkBtn');
			if(okBtn){
				okBtn.addEventListener('click', function(){
					if(messageModalInstance){
						messageModalInstance.hide();
					}
				});
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
			if(msg){ 
				// Show modal for purchase success with special layout
				if(msg.toLowerCase().includes('purchase successful')){
					var successModal = new bootstrap.Modal(document.getElementById('successModal'));
					successModal.show();
				} else {
					// Show generic centered modal for all other messages
					showMessage(msg);
				}
				// Clean URL without reload
				var cleanUrl = window.location.pathname + window.location.search.replace(/[?&]toast=[^&]*/, '').replace(/^&/, '?');
				window.history.replaceState({}, document.title, cleanUrl);
			}

			// quick cart storage
		});
		</script>
</div>
</div>
</body>
</html>