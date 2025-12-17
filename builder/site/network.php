<?php
DEFINE('NETWORKSDEFINEDAT', AMADEUSSITEROOT . 'data/networks/');

$networkName = variable('network');
$noNetwork = in_array($networkName, BOOLFALSE);
setupNetwork($noNetwork);

if (!$noNetwork) {
	function network_menu() {
		setMenuSettings(); //undo page-menu stuff
		extract(variable('menu-settings'));

		$name = variable('network');
		if ($wrapTextInADiv) $name = '<div>' . $name . $topLevelAngle . '</div>';

		echo '<li class="' . $itemClass . ' ' . $subMenuClass . '"><a class="' . $anchorClass . '">' . $name . '</a>' . NEWLINE;
		echo '	<ul class="' . $ulClass . '">' . NEWLINE;

		$all = variable('networkSites');
		$urlKey = _getUrlKeySansPreview();
		
		foreach ($all as $item) {
			if (is_string($item)) {
				$name = substr($item, 1);
				if ($wrapTextInADiv) $name = '<div class="' . $anchorClass . '">' . $name . $topLevelAngle . '</div>';
				echo '<li class="' . $itemClass . ' ' . $subMenuClass . ' menu-section">' . $name . '</li>';
				continue;
			}

			$name = $item['name'];
			if ($wrapTextInADiv) $name = '<div>' . $name . $topLevelAngle . '</div>';
			echo '<li class="' . $itemClass . ' ' . $subMenuClass . '">' . getLink($name, $item[$urlKey], $anchorClass, true) . '</li>';
		}

		echo '	</ul>' . variable('2nl');
		echo '</li>' . NEWLINE;
	}
}

function setupNetwork($noNetwork) {
	$networkSites = [];
	$networkUrls = [];

	$networkName = urldecode(getQueryParameter('network', variable('network')));

	//TEST: $networkName = 'Learning'; variable('network', $networkName);
	$urlKey = _getUrlKeySansPreview();

	$items = [];
	if (!$noNetwork) {
		$sheet = getSheet(NETWORKSDEFINEDAT . $networkName . '.tsv', false);
		$items = $sheet->rows;
	}

	foreach ($items as $row) {
		$key = $sheet->getValue($row, 'key');
		if (startsWith($key, '~')) {
			$networkSites[] = $key;
			continue;
		}

		$item = _getOrWarn($sheet->getValue($row, 'path'));
		if ($item === false) continue;
		$networkSites[] = $item;
		$networkUrls[OTHERSITEPREFIX . $key] = $item[$urlKey];
	}

	//these always exist and have a urlOf short name ($key)
	$noDawn = $networkName != 'DAWN';
	if (!$noDawn) $networkSites[] = '~DAWN';
	$sitePaths = [
		/*/TODO:
		'planeteers' => 'dawn/planeteers',
		'smithy' => 'dawn/smithy',
		*/
		'world' => 'dawn/world',
		'imran' => 'people/imran',
		'spring' => 'dawn/spring',
	];

	if (variable('local'))
		$sitePaths['admin'] = 'dawn/admin';

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
