<?php
DEFINE('NETWORKSDEFINEDAT', AMADEUSSITEROOT . 'data/networks/');
DEFINE('DAWN_NAME', 'Dynamic AmadeusWeb Network');

if (defined('SHOWSITESAT')) {
	setupNetwork(null);
	return;
}

$networkName = variable('network');
$noNetwork = in_array($networkName, BOOLFALSE);
setupNetwork($noNetwork);

if (!$noNetwork) {
	function network_menu() {
		setMenuSettings(); //undo page-menu stuff
		extract(variable('menu-settings'));

		$name = variable('network');
		if ($wrapTextInADiv) $name = '<div>' . $name . $topLevelAngle . '</div>';

		echo '<li class="' . $itemClass . ' ' . $subMenuClass . '"><a class="' . $anchorClass . '">' . $name . '</a>' . NEWLINES2;
		echo '	<ul class="' . $ulClass . '">' . NEWLINE;

		$all = variable('networkSites');
		$urlKey = _getUrlKeySansPreview();
		
		foreach ($all as $item) {
			if (is_string($item)) {
				$name = substr($item, 1);
				if ($wrapTextInADiv) $name = '<div class="' . $anchorClass . '">' . $name . $topLevelAngle . '</div>';
				echo '		<li class="' . $itemClass . ' ' . $subMenuClass . ' menu-section">' . $name . '</li>' . NEWLINE;
				continue;
			}

			$name = $item['name'];
			if ($wrapTextInADiv) $name = '<div>' . $name . $topLevelAngle . '</div>';
			echo '			<li class="' . $itemClass . ' ' . $subMenuClass . '">' . getLink($name, $item[$urlKey], $anchorClass, true) . '</li>' . NEWLINE;
		}

		echo '	</ul>' . NEWLINES2;
		echo '</li>' . NEWLINE;

		if (getDawnSites(variable('safeName'))) domains_menu();
	}
}

function domains_menu() {
	echo NEWLINES2 . '<li class="menu-item sub-menu"><a class="menu-link"><div>DOMAINS<i class="icon-angle-down"></i></div></a>' . NEWLINE;
	echo '	<ul class="sub-menu-container">' . NEWLINE;

	$href = variable('local') ? 'http://localhost/%folder%/' : 'https://%folder%.amadeusweb.world/';

	foreach (['people', 'organizations'] as $item)
		echo replaceItems('<li class="menu-item sub-menu"><a href="' . $href . '" class="menu-link" target="_blank"><div>%text%</div></a></li>' . NEWLINE,
			['folder' => $item, 'text' => humanize($item)], '%');

	echo '	</ul>' . NEWLINE;
	echo '</li>' . NEWLINES2;
}

function getDawnSites($key = false) {
	$op = [
		/*/TODO:
		'planeteers' => 'dawn/planeteers',
		'smithy' => 'dawn/smithy',
		*/
		'world' => 'dawn/world',
		'imran' => 'people/imran',
		'spring' => 'dawn/spring',
	];

	if (variable('local'))
		$op['admin'] = 'dawn/admin';

	if ($key) {
		$names = [
			'amadeuswebworld' => 'world',
			'amadeusweb9' => 'spring',
			'amadeuswebadmin' => 'admin',
			'imran-ali-namazi' => 'imran',
			//'' => '',
		];
		if (!isset($names[$key])) return false;
		$key = $names[$key];
		return in_array($key, array_keys($op));
	}

	return $op;
}

function setupNetwork($noNetwork) {
	$networkSites = [];
	$networkUrls = [];

	$networkName = urldecode(getQueryParameter('network', variable('network')));

	//TEST: $networkName = 'Learning'; variable('network', $networkName);
	$urlKey = _getUrlKeySansPreview();

	$items = [];
	$folPrefix = '';

	if (defined('SHOWSITESAT')) {
		$folPrefix = pathinfo(SHOWSITESAT, PATHINFO_FILENAME);
		DEFINE('SITESATNAME', humanize($folPrefix));
		$items[] = '~' . SITESATNAME;
		$folPrefix .= '/';

		if (disk_file_exists($txt = SHOWSITESAT . '/sites.txt'))
			$files = textToList(disk_file_get_contents($txt));
		else
			$files = _skipNodeFiles(scandir(SHOWSITESAT), 'txt, php');

		foreach ($files as $file) {
			if (startsWith($file, '==') || !disk_file_exists(SHOWSITESAT . '/' . $file . '/data/site.tsv')) continue;
			$items[] = $file;
		}

		$items[] = '~of the "' . DAWN_NAME . '"';
		$items = array_merge($items, getDawnSites());
	} else if (!$noNetwork) {
		$sheet = getSheet(NETWORKSDEFINEDAT . $networkName . '.tsv', false);
		$items = $sheet->rows;
	}

	foreach ($items as $row) {
		$plain = is_string($row);
		$key = $plain ? $row : $sheet->getValue($row, 'key');
		if (startsWith($key, '~')) {
			$networkSites[] = $key;
			continue;
		}

		if ($plain && $folPrefix && contains($row, '/')) $folPrefix = ''; //clear for dawn stuff!

		$item = _getOrWarn($plain ? $folPrefix . $row : $sheet->getValue($row, 'path'));
		if ($item === false) continue;
		$networkSites[] = $item;
		$networkUrls[OTHERSITEPREFIX . $key] = $item[$urlKey];
	}

	//these always exist and have a urlOf short name ($key)
	$noDawn = $networkName != 'DAWN';
	if (!$noDawn) $networkSites[] = '~DAWN';
	$sitePaths = getDawnSites();

	foreach ($sitePaths as $key => $path) {
		$item = _getOrWarn($path);
		if ($item === false) continue;
		if (!$noDawn) $networkSites[] = $item;
		$networkUrls[OTHERSITEPREFIX . $key] = $item[$urlKey];
	}

	variable('networkSites', $networkSites);
	variable('networkUrls', $networkUrls);
}

function _getOrWarn($relativePath) {
	$file = ALLSITESROOT . $relativePath . '/data/site.tsv';
	if (!sheetExists($file)) {
		if (variable('local')) echo '<!-- missing: ' . $relativePath . ' ~~ NOT FOUND: ' . $file . '-->' . NEWLINE;
		return false;
	}

	$site = getSheet($file, 'key');

	return [
		'key' => $site->getValue($site->firstOfGroup('safeName'), 'value'),
		'name' => $site->getValue($site->firstOfGroup('iconName'), 'value'),

		'siteName' => $site->getValue($site->firstOfGroup('name'), 'value'),
		'byline' => $site->getValue($site->firstOfGroup('byline'), 'value'),

		'local-url' => $site->getValue($site->firstOfGroup('local-url'), 'value'),
		'live-url' => $site->getValue($site->firstOfGroup('live-url'), 'value'),

		'path' => $relativePath,
	];
}
