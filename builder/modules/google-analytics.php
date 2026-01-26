<?php if (notSetOrNotLive(VARGoogleAnalytics)) return; ?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo variable(VARGoogleAnalytics); ?>"></script>
<script>
	window.dataLayer = window.dataLayer || [];
 	function gtag(){dataLayer.push(arguments);}
 	gtag('js', new Date());

 	gtag('config', '<?php echo variable(VARGoogleAnalytics); ?>');
</script>
