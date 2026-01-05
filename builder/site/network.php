<?php
DEFINE('NETWORKSDEFINEDAT', AMADEUSSITEROOT . 'data/networks/');
DEFINE('DAWN_SECTION', '~AmadeusWeb\'s ');
DEFINE('DAWN_NAME', 'The Dynamic AmadeusWeb Network');

if (defined('SHOWSITESAT')) {
	setupNetwork(null);
	return;
}

$networkName = variable('network');
$noNetwork = in_array($networkName, BOOLFALSE);
setupNetwork($noNetwork);

if (!$noNetwork) {
	function network_menu() {
		if (variable('network') != 'dawn-only')
			__flatMenu(variable('networkSites'), variable('network'));
		dawn_menu();
	}
}

function dawn_menu() {
	$items = variable('dawnSites');

	$items[] = DAWN_SECTION . 'Domains';
	$urlKey = _getUrlKeySansPreview();
	$href = variable('local') ? 'http://localhost/%s/' : 'https://%s.amadeusweb.world/';
	foreach (['people', 'organizations', 'businesses'] as $item)
		$items[] = [$urlKey => sprintf($href, $item), 'name' => humanize($item)];

	__flatMenu($items, 'DAWN');
}

function __flatMenu($items, $name) {
	setMenuSettings(); //undo page-menu stuff
	extract(variable('menu-settings'));

	if ($wrapTextInADiv) $name = '<div>' . $name . '++' . $topLevelAngle . '</div>';

	echo '<li class="' . $itemClass . ' ' . $subMenuClass . '"><a class="' . $anchorClass . '">' . $name . '</a>' . NEWLINES2;
	echo '	<ul class="' . $ulClass . '">' . NEWLINE;

	$urlKey = _getUrlKeySansPreview();
	
	foreach ($items as $item) {
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
}

function getDawnSites() {
	$op = [
		'~<abbr title="'.DAWN_NAME.'">DAWN</abbr>',
		'world' => 'dawn/world',
		'ava' => 'dawn/ava',
		'planeteers' => 'dawn/planeteers',
		DAWN_SECTION . 'Technology',
		'smithy' => 'dawn/smithy',
		'spring' => 'dawn/spring',
		'admin' => 'dawn/admin',
		DAWN_SECTION . 'Authors',
		'imran' => 'people/imran',
	];

	if (!variable('local'))
		unset($op['admin']);

	return $op;
}

function setupNetwork($noNetwork) {
	$dawnSites = [];
	$networkSites = [];
	$networkUrls = [];

	$networkName = urldecode(getQueryParameter('network', variable('network')));

	//TEST: $networkName = 'Learning'; variable('network', $networkName);
	$urlKey = _getUrlKeySansPreview();

	$items = [];
	$folPrefix = '';
	$usePath = false;

	if (defined('SHOWSITESAT')) {
		$usePath = contains($tmp = substr(SHOWSITESAT, strlen(ALLSITESROOT)), DIRECTORY_SEPARATOR)
			? explode(DIRECTORY_SEPARATOR, $tmp)[0] . '/' : false;

		$folPrefix = pathinfo(SHOWSITESAT, PATHINFO_FILENAME);
		DEFINE('SITESATNAME', humanize($folPrefix));
		$items[] = '~' . SITESATNAME;

		if (disk_file_exists($txt = AMADEUSROOT . '/data/domains/' . $folPrefix . '.txt'))
			$files = textToList(disk_file_get_contents($txt));
		else
			$files = _skipNodeFiles(scandir(SHOWSITESAT), 'php');

		$folPrefix .= '/';
		foreach ($files as $file) {
			if (startsWith($file, '==') || !disk_file_exists(SHOWSITESAT . '/' . $file . '/data/site.tsv')) continue;
			$items[$file] = ($usePath ? $usePath : '') . $folPrefix . $file;
		}

		$items[] = '~' . DAWN_NAME;
		$items = array_merge($items, getDawnSites());
	} else if (variable('network') == 'dawn-only') {
		$items == array_merge(['~' . DAWN_NAME], getDawnSites());
	} else if (!$noNetwork) {
		$sheet = getSheet(NETWORKSDEFINEDAT . $networkName . '.tsv', false);
		$items = $sheet->rows;
	}

	$subsiteItems = DEFINED('SUBSITES') ? [] : false;
	if (DEFINED('SUBSITES')) foreach (SUBSITES as $key => $path) {
		$item = _getOrWarn($path);
		if ($item === false) continue;
		$subsiteItems[] = $networkSites[] = $item;
		if (!isset($subsiteHome)) $subsiteHome = $item;
		$networkUrls[OTHERSITEPREFIX . $key] = $item[$urlKey];
	}
	if ($subsiteItems) variables(['subsiteItems' => $subsiteItems, 'subsiteHome' => $subsiteHome]);

	foreach ($items as $key => $row) {
		$plain = is_string($row);
		$key = $plain ? $row : $sheet->getValue($row, 'key');
		if (startsWith($key, '~')) {
			$networkSites[] = $key;
			continue;
		}

		$item = _getOrWarn($plain ? $row : $sheet->getValue($row, 'path'));
		if ($item === false) continue;
		$networkSites[] = $item;
		$networkUrls[OTHERSITEPREFIX . $key] = $item[$urlKey];
	}

	//these always exist and have a urlOf short name ($key)
	$dawnPaths = getDawnSites();

	foreach ($dawnPaths as $key => $path) {
		if (is_int($key) && startsWith($path, '~')) {
			$dawnSites[] = $path;
			continue;
		}

		$item = _getOrWarn($path);
		if ($item === false) continue;
		$dawnSites[] = $item;
		$networkUrls[OTHERSITEPREFIX . $key] = $item[$urlKey];
	}

	variable('dawnSites', $dawnSites);
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
