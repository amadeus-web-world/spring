<?php
if (variable('network')) {
	setupNetwork();

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

function setupNetwork() {
	$networkSites = [];
	$networkUrls = [];

	$networkName = variable('network');
	//TEST: $networkName = 'Learning'; variable('network', $networkName);
	$urlKey = _getUrlKeySansPreview();

	$sheet = getSheet(AMADEUSSITEROOT . 'data/networks/' . $networkName . '.tsv', false);
	foreach ($sheet->rows as $row) {
		$item = _getOrWarn($sheet->getValue($row, 'path'));
		if ($item === false) continue;
		$networkSites[] = $item;
		$networkUrls[OTHERSITEPREFIX . $item['key']] = $item[$urlKey];
	}

	//these always exist and have a urlOf short name ($key)
	$networkSites[] = '~DAWN Core';
	$sitePaths = [
		'spring' => 'dawn/spring',
		'world' => 'dawn/world',
		'imran' => 'people/imran'
	];

	foreach ($sitePaths as $key => $path) {
		$item = _getOrWarn($path);
		if ($item === false) continue;
		$networkSites[] = $item;
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
		'local-url' => $site->getValue($site->firstOfGroup('local-url'), 'value'),
		'live-url' => $site->getValue($site->firstOfGroup('live-url'), 'value'),
		'name' => $site->getValue($site->firstOfGroup('iconName'), 'value'),
		'path' => $relativePath,
	];
}
