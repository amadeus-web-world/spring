<?php
if (variable('network'))
	setupNetwork();

function setupNetwork() {
	//if (!sites & !teams) -> return
	$urlKey = (variable('local') ? 'local' : 'live') . '-url';
	$sites = [
		'spring' => ['local-url' => 'http://localhost/dawn/spring/', 'live-url' => 'https://spring.amadeusweb.world/'],
		'world' => ['local-url' => 'http://localhost/dawn/world/', 'live-url' => 'https://amadeusweb.world/'],
		'imran' => ['local-url' => 'http://localhost/people/imran/', 'live-url' => 'https://people.amadeusweb.world/imran/'],
	];

	$networkUrls = [];

	foreach ($sites as $siteAt => $item) {
		$site = sluggize($siteAt);
		$networkUrls[OTHERSITEPREFIX . $site] = $item[$urlKey];
	}

	variable('networkUrls', $networkUrls);
}
