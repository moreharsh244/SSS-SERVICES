		</main>
	</div>
</div>

		<script src="../js/bootstrap.bundle.min.js"></script>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
				tooltipTriggerList.map(function (tooltipTriggerEl) {
					return new bootstrap.Tooltip(tooltipTriggerEl)
				})
			});
		</script>
</body>
</html>