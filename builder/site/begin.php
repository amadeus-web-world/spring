<?php
getSiteUrlKey(); //get only needed for testing which should come soon

function __testSiteVars($array) {
	return; //comment to test
	print_r($array);
}

$sheet = getSheet('site', false);
$cols = $sheet->columns;

$siteVars = [];
foreach ($sheet->rows as $row) {
	$key = $row[$cols['key']];
	if (!$key || $key[0] == '|') continue;
	$siteVars[$key] = $row[$cols['value']];
}

variable('site-vars', $siteVars);

if (contains($url = $siteVars[variable(SITEURLKEY)], 'localhost')) {
	$url = replaceItems($url, ['localhost' => 'localhost' . variable('port')]);
	__testSiteVars(['url-for-localhost' => $url]);
}

if (hasPageParameter('health')) die('<span style="background-color: #cbfecb; padding: 10px;">Works!: ' . $url . '</span>');

variable(assetKey(SITEASSETS, ASSETFOLDER), SITEPATH . '/assets/');
variable(assetKey(SITEASSETS), $url . 'assets/');

function parseSectionsAndGroups($siteVars, $return = false, $forNetwork = false) {
	if (variable('sections') && !$forNetwork) return;
	$sections = isset($siteVars['sections']) ? $siteVars['sections'] : false;
	if (isset($siteVars['sections_local'])) $sections = $siteVars['sections_local'];

	if (!$sections) {
		$sections = [];
		if (!$forNetwork) variable('sections', $sections);
		__testSiteVars(['sections' => $sections]);
		return $sections;
	}

	$vars = [];
	//Eg.: research, causes, solutions, us: programs+members+blog
	if (contains($sections, ':')) {
		$swgs = explode(', ', $sections); //sections wtih groups
		$items = []; $groups = [];

		foreach ($swgs as $item) {
			if (contains($item, ':')) {
				$bits = explode(': ', $item, 2);
				$subItems = explode('+', $bits[1]);
				$groups[$bits[0]] = $subItems;
				$items = array_merge($items, $subItems);
			} else {
				$items[] = $item;
				$groups[] = $item;
			}
		}

		$vars['sections'] = $items;
		$vars['section-groups'] = $groups;
	} else {
		$vars['sections'] = explode(', ', $sections);
	}

	if ($return) return $vars;

	__testSiteVars($vars);
	variables($vars);
}

parseSectionsAndGroups($siteVars);

//valueIfSetAndNotEmpty
function _visane($siteVars) {
	//defaults are given, hence guaranteed and site is the only way
	$guarantees = [
		['footer-name', null], //needs null as uses !== in variableOr
		['link-to-site-home', true, 'bool'],
		['link-to-section-home', false, 'bool'],
		['ChatraID', '--use-amadeusweb'],
		['google-analytics', '--use-amadeusweb'],

		['email', 'imran@amadeusweb.world'],
		['phone', '+91-9841223313'],
		['whatsapp', '919841223313'],
		['phone2', '+91-9566166880'],
		['whatsapp2', '#no-alt-wa-number'],
		['address', 'Chennai, India'],
		['timings', 'Mon - Sat 11am to 7pm'],
		//['address-url', '#address'], //not here as needed for social too
		['owned-by', false], //in _copyright

		['mediakit', '?palette=polished'],
		['fonts', ''], //used in mediakit.php
		['description', false],
		['no-search', true], //TODO: GPSE - high
		['network', 'dawn'], //TODO: MENU - high
	];

	if (!hasVariable('theme')) {
		$guarantees[] = ['theme', 'canvas'];
		$guarantees[] = ['sub-theme', variableOr('sub-theme', 'business')];
	}

	$op = [];
	foreach ($guarantees as $cfg) {
		if (hasVariable($cfg[0])) continue;
		$op[$cfg[0]] = valueIfSetAndNotEmpty($siteVars, $cfg[0], $cfg[1], isset($cfg[2]) ? $cfg[2] : 'no-change');
	}

	if ($op['fonts'])
		$op['mediakit'] .= '&' . $op['fonts'];

	__testSiteVars($op);
	variables($op);

	variable(assetKey(THEMEASSETS), getThemeBaseUrl());
}

function _always($siteVars) {
	$op = [];
	$always = [
		'name',
		'byline',
		'safeName',
		'iconName',
		'footer-message',
		'siteMenuName',
	];
	foreach ($always as $item)
		$op[$item] = $siteVars[$item];

	$op['start_year'] = $siteVars['year'];

	__testSiteVars($op);
	variables($op);
}

_visane($siteVars);
_always($siteVars);

$safeName = $siteVars['safeName'];

variables($op = [
	'folder' => 'content/',
	'siteHumanizeReplaces' => siteHumanize(),
	'scaffold' => isset($siteVars['scaffold']) ? explode(', ', $siteVars['scaffold']) : [],

	'path' => SITEPATH,
	'assets-url' => $url,
	'page-url' => scriptSafeUrl($url),
]);

__testSiteVars($op);

runFrameworkFile('site/network');
runFrameworkFile('site/cms');

if (disk_file_exists($cms = SITEPATH . '/cms.php'))
	disk_include_once($cms);

if (hasPageParameter('embed')) variable('embed', true);

if (function_exists('after_framework_config')) after_framework_config();
render();
