<?php
/**
 * This php framework is Proprietary, Source-available software!
 * It is licensed for distribution at the sole discretion of it's owner Imran.
 * Copyright Oct 2019 -> 2026, AmadeusWeb.world, All Rights Reserved!
 *     
 * Author:    Imran Ali Namazi <imran@amadeusweb.world>
 * Architect: https://amadeusweb.world/imran/
 * Website:   https://amadeusweb.world/spring/
 * Source:    https://github.com/amadeus-web-world/spring
 * License:   https://github.com/amadeus-web-world/spring#License-1-ov-file
 * Note: AmadeusWeb Spring v9.3 is based on 25 years of Imran's programming experience:
 * You MUST agree to the "proprietary" nature and Imran's PULL PLUG RIGHTS
 * Rights:    https://amadeusweb.world/oases/proprietariness/with-ai/2025-12--16th-chat/
 */

DEFINE('AMADEUSROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
DEFINE('AMADEUSSITEROOT', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR);
DEFINE('ALLSITESROOT', dirname(AMADEUSROOT) . DIRECTORY_SEPARATOR);
DEFINE('AMADEUSFRAMEWORK', __DIR__ . DIRECTORY_SEPARATOR);
DEFINE('AMADEUSCORE', __DIR__ . DIRECTORY_SEPARATOR);
DEFINE('AMADEUSFEATURES', AMADEUSCORE . 'features/');
DEFINE('AMADEUSMODULES', AMADEUSFRAMEWORK . 'modules/');
DEFINE('AMADEUSTHEMESFOLDER', AMADEUSROOT . 'themes/');
DEFINE('AMADEUSEXTENSIONS', AMADEUSCORE . 'extensions/');

include_once AMADEUSFRAMEWORK . '2-stats.php'; //start time, needed to log disk load in files.php
include_once AMADEUSFRAMEWORK . '3-files.php'; //disk_calls, needed first to measure include times

function runFrameworkFile($name) {
	disk_include_once(AMADEUSFRAMEWORK . $name . '.php');
}

function runModule($name) {
	disk_include_once(AMADEUSMODULES . $name . '.php');
}

function runFeature($name, $variables = []) {
	disk_include_once(AMADEUSFEATURES . $name . '.php', $variables);
}

function runFeatureMultiple($name, $variables = []) {
	disk_include(AMADEUSFEATURES . $name . '.php', $variables);
}

runFrameworkFile('0-varnames');
runFrameworkFile('0-builder-base'); //2nd as uses varnames

runFrameworkFile('4-array');
runFrameworkFile('5-vars');
runFrameworkFile('6-text'); //needs vars
runFrameworkFile('7-html');
runFrameworkFile('8-menu');

//New from 4.1 to 8.5
runFrameworkFile('9-render');
runFrameworkFile('10-seo');
runFrameworkFile('11-assets');
runFrameworkFile('12-macros');
runFrameworkFile('13-builtin'); //was special
runFrameworkFile('14-main');
runFrameworkFile('15-routing');
runFrameworkFile('16-theme');
runFrameworkFile('18-related');

function before_bootstrap() {
	$port = $_SERVER['SERVER_PORT'];

	$testMobile = 80; //80 for normal, 8000 to simulate mobile/no-url-rewrite
	$isMobile = $testMobile != 80 || startsWith(__DIR__, '/storage/');
	
	variable('port', $port != $testMobile ? ':' . $port : '');

	variable(VARLocal, $local = startsWith($_SERVER['HTTP_HOST'], 'localhost'));
	variable(VARLive, defined('SHOWSITESAT') ? false : contains(SITEPATH, VARLive));

	variable('app', $url = ($local && !$isMobile ? replaceVariables('http://localhost%port%/spring/', 'port') : '//amadeusweb.world/spring/'));

	if (DEFINED('AMADEUSURL')) variable('app', AMADEUSURL);

	variable('app-themes', $url . 'themes/');

	variable(assetKey(COREASSETS, ASSETFOLDER), AMADEUSROOT . 'assets/');
	variable(assetKey(COREASSETS), variable('app') . 'assets/');

	$php = contains($_SERVER['DOCUMENT_ROOT'], 'magique') || contains($_SERVER['DOCUMENT_ROOT'], 'Magique');
	variable('is-mobile', $isMobile || $php);

	variable('no_url_rewrite', $isMobile || $php);
	if ($isMobile || $php) variable('scriptNameForUrl', 'index.php/'); //do here so we can simulate usage in site.php

	runModule('markdown');
	runModule('wordpress');
}

if (!DEFINED('AMADEUSPRODUCT'))
	before_bootstrap();

if (defined('SHOWSITESAT')) return;

//Now this only sets up the node and page parameters - rest moved to before_bootstrap()
function bootstrap($config) {
	variables($config);

	$noRewrite = variable('no_url_rewrite');
	if ($noRewrite) $node = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	else $node = getQueryParameter(VARNode, '');

	$node = removeSlash($node, 'both');

	if ($node == '') $node = SITEHOME;
	variable('all_page_parameters', $node); //let it always be available

	if (strpos($node, '/') !== false) {
		$slugs = explode('/', $node);
		$node = array_shift($slugs);
		variable('page_parameters', $slugs);
		foreach ($slugs as $ix => $slug) variable('page_parameter' . ($ix + 1), $slug);
		variable(LASTPARAM, $ix + 1);
	}

	variable(NODEVAR, variableOr('node-alias', $node));
}

function getPageParameterAt($index = 1, $or = false) {
	return variableOr('page_parameter' . $index, $or);
}

function removeSlash($node, $where) {
	if (in_array($where, ['both', 'end']) AND endsWith($node, '/')) $node = substr($node, 0, strlen($node) - 1);
	if (in_array($where, ['both', 'start']) AND startsWith($node, '/')) $node = substr($node, 1);
	return $node;
}

function getPageParameters($trail = '/', $baseUrl = true) {
	$base = $baseUrl ? getHtmlVariable('url') : '';
	$all = variable('all_page_parameters');
	if ($all == SITEHOME) {
		$all = '';
		$trail = removeSlash($trail, 'start');
	}
	return $base . ($all ? $all : '') . $trail;
}

function hasPageParameter($param) {
	return in_array($param, variableOr('page_parameters', [])) || isset($_GET[$param]);
}

function getQueryParameter($param, $or = false) {
	return isset($_GET[$param]) ? $_GET[$param] : $or;
}

function render() {
	if (function_exists('before_render')) before_render();
	ob_start();

	$theme = variable('theme') ? variable('theme') : 'default';
	$embed = variable('embed');

	$folder = SITEPATH . '/' . (variable('folder') ? variable('folder') : '');
	$contentExt = disk_one_of_files_exist($contentFWE = $folder . nodeValue() . '.', CONTENTFILES);
	if ($contentExt) {
		read_seo($contentFWE . $contentExt, true);
	}

	if (!$embed) {
		renderThemeFile('header', $theme);
		if (function_exists('network_before_file')) network_before_file();
		if (function_exists('before_file')) before_file();
	}

	if (variable(features::underConstruction)) {
		runFeature(features::underConstruction);
		$rendered = true;
	} else if (isset($_GET[features::share])) {
		features::runPage(features::share);
		$rendered = true;
	} else if (isset($_GET['cta'])) {
		H2(title(FORHEADING), 'container text-center my-3');
		echo getCodeSnippet('cta-or-engage', CORESNIPPET);
		$rendered = true;
	} else if (hasPageParameter('slider')) {
		$rendered = true; //dont want to render content. and needed here as it shouldnt support "content" menu pages
	} else if (variable('skip-content-render')) {
		$rendered = false;
	} else {
		$rendered = false;
		if ($contentExt) {
			$rendered = true;
			builtinOrRender($file = $contentFWE . $contentExt, false, !variable('skip-container-for-this-page'));
			pageMenu($file);
		}
	}

	if (isset($_GET['debug']) || isset($_GET['stats'])) {
		variable('stats', true);
	}

	if (!$rendered) {
		if (function_exists('did_render_page') && did_render_page()) {
			//noop
		} else if ($missing = getSnippet('missing-page')) {
			h2(title(FORHEADING), 'container text-center mt-4');
			contentBox('missing-page', 'container');
			renderMarkdown($missing);
			contentBox('end');
		} else {
			//NOTE: Uses output buffering magic methods to delay sending of output until 404 header is sent 
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
			ob_flush();

			if (isset($_GET['debug'])) {
				echo 'NOTE: Turning on stats so you can additionally see what files are included! This appears below the footer' . variable('brnl') . variable('brnl');

				$verbose = $_GET['debug'] == 'verbose';
				if ($verbose) {
					global $cscore;
					showDebugging('ALL AMADEUS VARS - global $cscore;', $cscore);
				}
			}

			$breadcrumbs = variable('breadcrumbs');
			$file = nodeValue(); $message = 'at level 1 (content / section / node)';
			if ($breadcrumbs) {
				$file = BRNL . variableOr('all_page_parameters', 'node/section missing?');
				$message = 'found only these valid params:' . BRNL . BRNL . '<strong>' . implode(' &mdash; ', $breadcrumbs) . '</strong>';
			}
			error('<h1 class="alert alert-danger rounded-pill mt-3 mb-0">Couldn\'t find page:</h1>'
				. '<h2 class="mt-3 mb-3">' . $file . '</h2>' . BRNL . NEWLINE . '<p class="rounded-pill alert alert-secondary">' . $message . '</p>');
		}
	}

	ob_end_flush();

	if (!$embed) {
		if (function_exists('pollenAt')) pollenAt('embed');
		if (function_exists('after_file')) after_file();
		renderThemeFile('footer', $theme); //theme.php is now responsible for calling stats before styles+scipts as the table feature requires its usage and it will be before </body>
	}

	if (function_exists('after_render')) after_render();
}

function copyright_and_credits($separator = '<br>', $return = false) {
	$copy = _copyright(true);
	$cred = _credits('', true);
	$result = $copy . $separator . $cred;
	if ($return) return $result;
	echo $result;
}

function _copyright($return = false) {
	if (variable('dont_show_copyright')) return '';

	$year = date('Y');
	$start = variable('start_year');
	$from = ($start && ($start != $year)) ? $start . ' - ' : '';

	$before = variable(VAROwnedBy) ? '<strong>' . variable('name') . '</strong>, ' : '';
	$after = variable(VAROwnedBy) ? variable(VAROwnedBy) : variable('name');

	$result = '&copy; ' . $before . 'Copyright <strong><span>' . $after . '</span></strong>. ' . $from . $year . ' All Rights Reserved.';
	if ($return) return $result; else echo $result;
}

function _credits($pre = '', $return = false) {
	$root = getSiteUrl(SITEROOT);
	$work = getSiteUrl(SITEWORK);
	$utm = '?utm_content=site-credits&utm_referrer=' . variable(VARSafeName);

	$url = $work . $utm;
	$img = '<img src="' . $root . 'amadeusweb-work-logo.png" height="40" alt="' . DAWN_NAME . '" class="m-2 align-middle rounded-2">';

	$result = $pre . 'Powered by' . getLink($img, $url, 'd-inline-block', true) . NEWLINE
		. returnLine('[Do Request a Service](%work-signup%' . $utm . 'BTNPRIMARY)');

	if ($return) return $result; else echo $result;
}
