<?php
DEFINE('NETWORKSDEFINEDAT', AMADEUSSITEROOT . 'data/networks/');
DEFINE('DAWN_SECTION', '~AmadeusWeb\'s ');
DEFINE('DAWN_NAME', 'The Dynamic AmadeusWeb Network');
DEFINE('DOMAINS', ['organizations', 'spaces', 'people', 'businesses']);

function is_dawn($fol) {
	return in_array($fol, ['dawn', 'public_html']);
}

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
	$href = variable('local') ? 'http://localhost/%s/' : 'https://amadeusweb.world/%s/';
	foreach (DOMAINS as $item)
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
		'oases' => 'oases', //TODO: HIGH: change urlOf-world
		DAWN_SECTION . 'Technology',
		'smithy' => 'smithy',
		'spring' => 'spring',
		DAWN_SECTION . 'Authors',
		'imran' => 'imran',
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

	//TEST: $networkName; variable('network', $networkName = 'Learning');
	$urlKey = _getUrlKeySansPreview();

	$items = [];

	if (defined('SHOWSITESAT')) {
		$folPrefix = pathinfo(SHOWSITESAT, PATHINFO_FILENAME);
		$isDawn = is_dawn($folPrefix);
		DEFINE('SITESATNAME', $isDawn ? 'DAWN' : humanize($folPrefix));
		if (!$isDawn) $items[] = '~' . SITESATNAME;

		if (disk_file_exists($txt = AMADEUSROOT . '/data/domains/' . $folPrefix . '.txt'))
			$files = textToList(disk_file_get_contents($txt));
		else
			$files = getSitesToShow($folPrefix);

		$folPrefix .= '/';
		foreach ($files as $file) {
			if (startsWith($file, '~')) {
				$items[] = $file;
				continue;
			}

			if (startsWith($file, '==') || !disk_file_exists($tsv = ALLSITESROOT . $file . '/data/site.tsv')) {
				if (variable('local')) echo '<!-- missing tsv: ' . $tsv . '-->' . NEWLINE;
				continue;
			}
			$items[$file] = $file;
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
	if (DEFINED('SUBSITES')) foreach (getSitesToShow(SUBSITES) as $path) {
		$key = $path;
		//if (contains($key, '/')) { $bits = explode('/', $path); $key = array_shift($bits); } //TODO: HI: check network like JE
		$item = _getOrWarn($path);
		if ($item === false) continue;
		$subsiteItems[] = $networkSites[] = $item;
		if (!isset($subsiteHome)) $subsiteHome = $item;
		$networkUrls[OTHERSITEPREFIX . $key] = $item[$urlKey];
	}
	if ($subsiteItems) showDebugging('131', variables(['subsiteItems' => $subsiteItems, 'subsiteHome' => $subsiteHome]), false, false, true);

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

	variables([
		'dawnSites' => $dawnSites,
		'networkSites' => $networkSites,
		'networkUrls' => $networkUrls
	]);
}

function getSitesToShow($at) {
	$isDawn = is_dawn($at);
	$byDomain = [];
	$fols = _skipNodeFiles(scandir(ALLSITESROOT), ONLYFOLDERS);

	$op = [];
	foreach ($fols as $relativePath) {
		$file = ALLSITESROOT . $relativePath . '/data/site.tsv';
		if (!sheetExists($file)) {
			if (variable('local')) debug(__FILE__, 'getSitesToShow', ['skipping' => $relativePath, 'TSV missing' => $file, 'hint' => 'IS NETWORK / Site Grouping?'], DEBUGVERBOSE);
			continue;
		}

		$site = getSheet($file, 'key');
		$showInConfig = $site->firstOfGroup('showIn', false, false);

		if (!$showInConfig) {
			if (variable('local')) debug(__FILE__, 'getSitesToShow', ['skipping' => $relativePath, 'TSV "showIn" missing' => $file, 'hint' => 'STILL IN v9.2?'], DEBUGSPECIAL);
			continue;
		}

		$showIn = $site->getValue($showInConfig, 'value');
		if ($isDawn) {
			if (!isset($byDomain[$showIn]))
				$byDomain[$showIn] = [];
			$byDomain[$showIn][] = $relativePath;
			continue;
		}

		if ($showIn != $at) continue;
		$op[] = $relativePath;
	}

	if (!$isDawn) return $op;

	foreach (DOMAINS as $domain) {
		if (!isset($byDomain[$showIn])) continue;
		$op[] = '~' . humanize($domain);
		$op = array_merge($op, $byDomain[$domain]);
	}

	//showDebugging('211', $op, true);
	return $op;
}

function _getOrWarn($relativePath) {
	$file = ALLSITESROOT . $relativePath . '/data/site.tsv';
	if (!sheetExists($file)) {
		if (variable('local')) debug(__FILE__, '_getOrWarn', ['missing for' => $relativePath, 'TSV missing' => $file], DEBUGSPECIAL);
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
