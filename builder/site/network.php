<?php
DEFINE('NETWORKSDEFINEDAT', AMADEUSSITEROOT . 'data/networks/');
DEFINE('DAWN_SECTION', '~AmadeusWeb\'s ');
DEFINE('DAWN_ABBR', 'DAWN');
DEFINE('DAWN_NAME', 'The Dynamic AmadeusWeb Network');
DEFINE('DOMAINS', ['authors', 'creativity', 'networks', 'technology', 'organizations', 'people', 'work-folk']);
DEFINE('DOMAINNames', ['creativity' => 'Creativity Corner']);

function is_dawn($fol) { return in_array($fol, ['dawn', 'public_html']); }
function domain_name($domain) { return array_key_exists($domain, DOMAINNames) ? DOMAINNames[$domain] : humanize($domain); }

global $networkUrls;
$networkUrls = [];

function addNetworkUrl($site, $url) {
	global $networkUrls;
	$networkUrls[URLOFPREFIX . $site] = $url;
}

function replaceNetworkUrls($html) {
	global $networkUrls;
	if (!contains($html, URLOFPREFIX) || empty($networkUrls)) return $html;
	//if (endsWith($html, '%')) showDebugging(23, [$html, $networkUrls], PleaseDie);
	return replaceItems($html, $networkUrls, '%');
}

function getSiteKey($site) { return '%' . URLOFPREFIX . $site . '%'; }

if (defined('SHOWSITESAT')) {
	setupNetwork(null);
	return;
}

//setup continues
$networkName = variable(VARNetwork);
$noNetwork = in_array($networkName, BOOLLISTFALSE);
setupNetwork($noNetwork);

if (!$noNetwork) {
	function network_menu() {
		if (variable(VARNetwork) != 'dawn-only')
			__flatMenu(variable('networkSites'), variable(VARNetwork));
		dawn_menu();
	}
}

function dawn_menu() {
	$urlKey = _getUrlKeySansPreview();
	$showIn = variable(DOMAINKEY);
	$back = getDomainLink('Back to ' . ($showIn ? humanize($showIn) : DAWN_ABBR), $showIn ? $showIn : '', $urlKey);
	$items = array_merge([$back], variable('dawnSites'));

	$items[] = DAWN_SECTION . 'Domains';

	foreach (DOMAINS as $item)
		$items[] = getDomainLink(domain_name($item), $item, $urlKey);

	__flatMenu($items, DAWN_ABBR);
}

function getDomainLink($name, $site, $urlKey, $hrefOnly = false) {
	$href = variable(VARLocal) ? replaceVariables('http://localhost%port%/', 'port') : 'https://amadeusweb.world/';
	if ($site) $href .= $site . '/';
	if ($hrefOnly) return $href;
	return [$urlKey => $href, 'name' => $name];
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

function setupNetwork($noNetwork) {
	$dawnSites = [];
	$networkSites = [];
	addNetworkUrl(SITEROOT, getDomainLink('', '', '', true));
	addNetworkUrl(SITEWORLD, getDomainLink('', 'oases', '', true));
	addNetworkUrl(SITESPRING, getDomainLink('', 'spring', '', true));

	$networkName = urldecode(getQueryParameter(VARNetwork, variable(VARNetwork)));

	//TEST: $networkName; variable(VARNetwork, $networkName = 'Learning');
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
				if (variable(VARLocal)) echo '<!-- missing tsv: ' . $tsv . '-->' . NEWLINE;
				continue;
			}
			$items[$file] = $file;
		}
	} else if (!$noNetwork && $networkName != 'dawn-only') {
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
		addNetworkUrl($key, $item[$urlKey]);
	}
	if ($subsiteItems) showDebugging('151', variables(['subsiteItems' => $subsiteItems, 'subsiteHome' => $subsiteHome]), false, false, true);

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
		addNetworkUrl($key, $item[$urlKey]);
	}

	variables([
		'dawnSites' => $dawnSites,
		'networkSites' => $networkSites,
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
			if (variable(VARLocal)) debug(__FILE__, 'getSitesToShow', ['skipping' => $relativePath, 'TSV missing' => $file, 'hint' => 'IS NETWORK / Site Grouping?'], DEBUGVERBOSE);
			continue;
		}

		$site = getSheet($file, 'key');
		$showInConfig = $site->firstOfGroup(DOMAINKEY, false, false);

		if (!$showInConfig) {
			if (variable(VARLocal)) debug(__FILE__, 'getSitesToShow', ['skipping' => $relativePath, 'TSV "' . DOMAINKEY . '" missing' => $file, 'hint' => 'STILL IN v9.2?'], DEBUGSPECIAL);
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
		if (!isset($byDomain[$domain])) continue;
		$op[] = '~' . domain_name($domain);
		$op = array_merge($op, $byDomain[$domain]);
	}

	//showDebugging('211', $op, true);
	return $op;
}

function _getOrWarn($relativePath) {
	$file = ALLSITESROOT . $relativePath . '/data/site.tsv';
	if (!sheetExists($file)) {
		if (variable(VARLocal)) debug(__FILE__, '_getOrWarn', ['missing for' => $relativePath, 'TSV missing' => $file], DEBUGSPECIAL);
		return false;
	}

	$site = getSheet($file, 'key');

	return [
		'key' => $site->getValue($site->firstOfGroup(VARSafeName), 'value'),
		'name' => $site->getValue($site->firstOfGroup(VARIconName), 'value'),

		'siteName' => $site->getValue($site->firstOfGroup('name'), 'value'),
		VARByline => $site->getValue($site->firstOfGroup(VARByline), 'value'),

		'local-url' => $site->getValue($site->firstOfGroup('local-url'), 'value'),
		'live-url' => $site->getValue($site->firstOfGroup('live-url'), 'value'),

		'path' => $relativePath,
	];
}
