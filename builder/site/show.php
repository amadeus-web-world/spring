<?php
getSiteUrlKey();
runFrameworkFile('site/network');

variables([
	'theme' => 'canvas',
	'sub-theme' => 'go',
	'custom-footer' => true,
	VARMediakit => '?palette=1',

	VARNode => SITEHOME,
	'name' => SITESATNAME,
	VARByline => DAWN_NAME,
]);

add_body_class('showing-sites');
addStyle('v9-spring', COREASSETS);
addStyle('v9-features', COREASSETS);

DEFINE('SITEPATH', SHOWSITESAT);
runThemePart('header');
runFrameworkFile('site/listing');
runThemePart('footer');
