<?php
/****
 * SHARER (AmadeusWeb.world feature) - version 3.0 - Jan 2026
 * In v3.1, we will do the following:
		as=uma
		by=imran
		from=zvmworld
		via=referrer-email
		to=relative-path
 ****/
$boxClass = 'container my-2 after-content img-max-500 text-center';

if (getQueryParameter('share') == '1') {
	$for = $url = getQueryParameter('url', getPageParameters());
	$url .= '?utm_source=%source%';
	$url .= isset($_GET['campaign']) && $_GET['campaign'] ? '&utm_campaign=' . $_GET['campaign'] : '';
	$url .= isset($_GET['by']) && $_GET['by'] ? '&utm_content=referred-by-' . strtolower($_GET['by']) : '';

	contentBox('share', $boxClass);

	$logo = getLogoOrIcon('logo');
	$home = concatSlugs(['<a href="', pageUrl(), '"><img src="', $logo, '" class="img-fluid img-max-',
		variableOr('footer-logo-max-width', '500'), '" alt="', variable('name'), '"></a><br>'], '');
	echo $home . BRNL;

	h2(title(FORHEADING), 'bg-light');
	h2('Hotlinks for Analytics Tracking', 'h5');
	echo HRTAG . NEWLINE;
	echo 'Click any label / textbox to copy it\'s link and<br /><b>share on that platform</b><br />(email / whatsapp / linkedin etc)';
	echo HRTAG . textBoxWithCopyOnClick('tracker without source', $for, 'Link ' . TAGBOLD . 'no tracker' . TAGBOLDEND, true);

	$sources = [VARWhatsapp, 'instagram', 'facebook', VAREmail, 'linkedin'];
	foreach ($sources as $source) echo HRTAG . textBoxWithCopyOnClick($source, str_replace('%source%', $source, $url), 'For ' . TAGBOLD . $source . TAGBOLDEND, true);

	echo cbCloseAndOpen($boxClass);
}
?>
<section id="amadeus-share" class="container" style="text-align: center; padding-top: 30px;">
	<?php h2('Submit With Different Parameters', 'bg-light');?>
	<form action="<?php echo getPageParameters(features::shareQS) ?>" target="_blank">
		<input type="hidden" name="share" value="1" />
		<input type="hidden" name="url" value="<?php echo $_SERVER['SERVER_NAME'] . explode('?', $_SERVER['REQUEST_URI'], 2)[0]; ?>" />
		<input style="width: 100%; margin-bottom: 10px;" type="text" name="campaign" placeholder="campaign (if known)" value="<?php echo isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : ''; ?>" /><br>
		<input style="width: 100%; margin-bottom: 10px;" type="text" name="by" placeholder="your name" value="<?php echo isset($_GET['utm_content']) ? str_replace('referred-by-', '', $_GET['utm_content']) : ''; ?>" /><br>
		<input style="width: 100%;" type="submit" value="Share This Page" />
	</form>
</section>

<?php
contentBox('end');

sectionId('credits','container text-center my-5');
_credits();
sectionEnd();
