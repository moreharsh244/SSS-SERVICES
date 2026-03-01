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
   // ensure body has enough bottom padding to prevent overlap
   function adjust(){ document.body.style.paddingBottom = (f.offsetHeight || 56) + 'px'; }
   adjust(); window.addEventListener('resize', adjust);
 })();
</script>
