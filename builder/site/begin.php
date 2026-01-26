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
	if (!isset($row[$cols['value']])) showDebugging(16, 'site.tsv - value column missing for row: ' . $key, true);
	$siteVars[$key] = $row[$cols['value']];
}

variables([
	'site-vars' => $siteVars,
	'local-url' => $siteVars['local-url'],
	'live-url' => $siteVars['live-url'],
	'showIn' => isset($siteVars['showIn']) ? $siteVars['showIn'] : false,
]);

if (contains($url = $siteVars[variable(SITEURLKEY)], 'localhost'))
	$url = replaceItems($url, ['localhost' => 'localhost' . variable('port')]);

if (hasPageParameter('health')) die('<span style="background-color: #cbfecb; padding: 10px;">Works!: ' . $url . '</span>');

variable(assetKey(SITEASSETS, ASSETFOLDER), SITEPATH . '/assets/');
variable(assetKey(SITEASSETS), $url . 'assets/');

function parseSectionsAndGroups($siteVars, $return = false, $forNetwork = false) {
	if (variable('sections') && !$forNetwork) return;
	$sections = isset($siteVars['sections']) ? $siteVars['sections'] : false;
	if (variable(VARLocal) && isset($siteVars['sections_local'])) $sections = $siteVars['sections_local'];

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
		[VARFooterName, null], //needs null as uses !== in variableOr
		[VARLinkToSiteHome, true, TYPEBOOLEAN],
		[VARLinkToSectionHome, false, TYPEBOOLEAN],
		[VARChatraID, VARUseAmadeusWeb],
		[VARGoogleAnalytics, VARUseAmadeusWeb],

		[VAREmail, VARSystemEmail],
		[VAREmail2, plus_email(VARSystemEmail, 'owner')],
		[VAREmail3, plus_email(VARSystemEmail, 'hr')],
		[VARPhone, $ph1 = '+91-9841223313'],
		[VARWhatsapp, _whatsAppME($ph1, '', true)],
		[VARPhone2, $ph2 = '+91-9500001909'],
		[VARWhatsapp2, _whatsAppME($ph2, '', true)],
		[VARAddress, 'Chennai, India'],
		[VARAddressUrl, '#no-maps-set'],
		[VARFullAddress, 'Devakalam, Mahalingapuram, Chennai, India'],
		[VARTimings, 'Mon - Sat 11am to 7pm'],
		[VAROwnedBy, false], //in _copyright

		[VARMediakit, '?palette=polished'],
		[VARFonts, ''], //used in mediakit.php
		[VARDescription, false],
		[VARWelcomeMessage, 'Welcomes you!'],
		[VARNoSearch, true], //TODO: GPSE - high
		[VARNetwork, 'Webring'],
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

	if ($op[VARFonts])
		$op[VARMediakit] .= '&' . $op[VARFonts];

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

if (defined('NETWORKPATH') && disk_file_exists($ntk = NETWORKPATH . '/network.php'))
	disk_include_once($ntk);

if (hasPageParameter('embed')) variable('embed', true);

if (function_exists('after_framework_config')) after_framework_config();
render();
