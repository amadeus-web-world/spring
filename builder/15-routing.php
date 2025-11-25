<?php
DEFINE('OURNETWORK', '');

DEFINE('OTHERSITEPREFIX', 'urlOf-');
function sluggize($relPath) {
	if (!contains($relPath, '/')) return $relPath;
	$slugs = explode('/', $relPath);
	return end($slugs);
}

DEFINE('SITEURLKEY', 'site-url-key'); //typo proof

function _getUrlKeySansPreview() {
	return (variable('local') ? 'local' : 'live') . '-url';
}

function getSiteUrlKey() {
	$usePreview = variableOr('use-preview', false);
	$local = variable('local'); //this is now in before_bootstrap

	//tests preview urls locally
	//$local = false; $preview = true;

	if (!$usePreview) {
		$result = ($local ? 'local' : 'live') . '-url';
		variable(SITEURLKEY, $result);
		return $result;
	}

	$live = variable('live');
	$testSafeHost = variableOr('testingHost', $_SERVER['HTTP_HOST']);
	$preview = hasVariable('preview') ? variable('preview') :
		($local ? !$live : contains($testSafeHost, 'preview'));

	$result = ($local ? 'local-' : 'live-') . ($preview ? 'preview-' : '') . 'url';
	//showDebugging('ROUTING', ['key' => $result, 'live' => $live, 'local' => $local, 'preview' => $preview ]);

	variable('preview', $preview);
	variable(SITEURLKEY, $result);
	return $result;
}

DEFINE('MENUNAME', 'menu_name');
DEFINE('FILELOOKUP', 'file_lookup');
DEFINE('MENUITEMS', 'menu_items');
function getSectionKey($slug, $for) {
	return 'this_' . $slug . '_' . $for;
}

function getSectionFrom($dir) {
	return pathinfo($dir, PATHINFO_FILENAME);
}

DEFINE('LASTPARAM', 'last-page');
DEFINE('NODEVAR', 'node');
DEFINE('SITEHOME', 'index');
function nodeValue() { return variable(NODEVAR); }
function nodeIs($what) { return nodeValue() == $what; }
function nodeIsNot($what) { return nodeValue() != $what; }
function nodeIsOneOf($whatAll) { return in_array(nodeValue(), $whatAll); }
function lastParamIs($what) { return lastParam() == $what; }
function lastParam() { return getPageParameterAt(variable(LASTPARAM)); }

DEFINE('SECTIONVAR', 'section');
function sectionValue() { return variable(SECTIONVAR); }
function sectionIs($what) { return sectionValue() == $what; }
function nodeIsSection() { return nodeValue() == sectionValue(); }


DEFINE('SAFENODEVAR', 'safeNode');

DEFINE('USEDNODEVAR', 'usedNodeVars');
variable(USEDNODEVAR, []);
function nodeVarsInUse($append = false) {
	$vars = variable(USEDNODEVAR);
	if (!$append) return $vars;

	$vars[] = $append;
	sort($vars);
	variable(USEDNODEVAR, $vars);
}

DEFINE('DontOverwriteLogo', 'dont-overwrite-logo');
DEFINE('PrefixSafeName', 'prefix-safeName');
DEFINE('NodeSafeName', 'nodeSafeName');

function autoSetNode($level, $where, $overrides = []) {
	$section = variable('section');

	nodeVarsInUse($level);
	if (nodeIs(SITEHOME) OR nodeIsSection()) return;

	$relPath = $level == 0 ? nodeValue() : str_replace('\\', '/', 
		substr($where, strlen(SITEPATH . '/' . $section) + 1));
	$endSlug = nodeValue();
	if ($level > 1) { $bits = explode('/', $relPath); $endSlug = array_pop($bits); }

	$prefix = valueIfSet($overrides, 'prefix-safeName') ? variable('safeName') . '-' : '';
	if ($prefix && isset($overrides['nodeSafeName']))
		$overrides['nodeSafeName'] = $prefix . $overrides['nodeSafeName'];

	$vars = array_merge([
		'nodeSlug' => $relPath,
		assetKey(NODEASSETS) => fileUrl($section . '/' . $relPath . '/assets/'),
		'nodeSiteName' => humanize($endSlug),
		'nodeSafeName' => $prefix . $endSlug,
		'submenu-at-node' => true,
		'nodes-have-files' => true,
		'nodepath' => $where,
	], $overrides);

	//TODO: develop this
	if ($engage = valueIfSet($vars, 'engage-from')) {
		$source = valueIfSet($engage, 'source', 'opus');
		$folder = valueIfSet($engage, 'folder', );
		$files = valueIfSet($engage, 'files', $endSlug);
		if (!is_array($files)) $files = [$files];
	}

	variable('NodeVarsAt' . $level, $vars);
}

function lastNodeVarsIndex() {
	return count(nodeVarsInUse());
}

function ensureNodeVar() {
	if (count($indices = nodeVarsInUse())) {
		$vars = variable('NodeVarsAt' . end($indices));
		variables($vars);
		$slug = $vars['nodeSlug'];
		variable(assetKey(LEAFNODEASSETS), $vars[assetKey(NODEASSETS)]); //assume required as its always set above
		DEFINE('NODEPATH', $vars['nodepath']);
	} else {
		$slug = nodeValue();
	}
	variable(SAFENODEVAR, $slug);
}
