<?php
DEFINE('CONTENTFILES', 'php, md, tsv, txt, html');
DEFINE('CONTENTFILEEXTENSIONS', explode(', ', CONTENTFILES));
DEFINE('ENGAGEFILES', 'md, tsv');
DEFINE('FILESWITHMETA', 'md, tsv');

variables([
	'exclude-folders' => ['home', 'assets', 'data', 'engage', 'home', 'images', 'thumbnails'],
]);

function isContentFile($fileOrRaw) {
	foreach (CONTENTFILEEXTENSIONS as $extn)
		if (endsWith($fileOrRaw, '.' . $extn)) return true;
	return false;
}

function builtinOrRender($file, $type = false, $useHeading = true) {
	if (endsWith($file, '.php')) {
		renderAny($file);
		return;
	}

	if (variable('skip-heading-for-page'))
		$useHeading = false;

	//TODO: engage, blurbs, deck, tsv
	$raw = disk_file_exists($file) ? disk_file_get_contents($file) : '[RAW]';
	$embed = hasPageParameter('embed');
	$pageName = title(FORHEADING);

	//cannot use startsWith as edit in vs-code wouldnt work
	$detectedEngage = contains($raw, '|is-engage') || contains($raw, '<!--is-engage-->');
	if ($type != 'engage' && $detectedEngage) $type = 'engage';

	if ($type == 'engage') {
		$md = !endsWith($file, '.tsv');

		runFeature('engage');

		if ($detectedEngage)
			sectionId('special-form' . ($ix = variableOr('special-form', 1)), 'container');

		if ($md)
			renderEngage($pageName, $raw);
		else
			runEngageFromSheet(getPageName(), $file);

		if ($detectedEngage) {
			variableOr('special-form', ++$ix);
			sectionEnd();
		}

		pageMenu($file);
		return;
	}

	if (endsWith($file, '.md')) {
		sectionId('special-md', 'container');
		if (startsWith($raw, '<!--is-blurbs-->')) {
			_renderedBlurbs($file);
		} else if (startsWith($raw, '<!--is-deck-->')) {
			_renderedDeck($file, $pageName);
		} else {
			$settings = ['use-content-box' => (!variable('skip-content-box-for-this-page'))];
			if ($useHeading) $settings['heading'] = $pageName;
			if (variable(FIRSTSECTIONONLY)) $settings[FIRSTSECTIONONLY] = true;
			renderAny($file, $settings);
		}

		sectionEnd();
		pageMenu($file);
		return;
	}

	if (endsWith($file, '.tsv')) {
		runFeature('tables');

		$meta = getSheet($file, false);
		$istwt = contains($raw, '|is-table-with-template');
		if ($meta && isset($meta->values['use-template']))
			$meta->values = array_merge($meta->values, getSheet(getTableTemplate($meta), false)->values);

		$noCB = $meta ? valueIfSet($meta->values, 'no-content-box') : false;

		if (valueIfSet($meta->values, 'no-title')) $title = false;
		else if (($mh = variable('menu-humanize')) && isset($mh[nodeValue()])) $title = $mh[nodeValue()];
		else $title = title(FORHEADING);
		if ($title) h2($title, 'amadeus-icon');

		$isDeck = contains($raw, '|is-deck');
		$notRendering = !hasPageParameter('embed') && !hasPageParameter('expanded');

		if ($noCB) sectionId('special-table', 'container'); else
		if (!$embed) sectionId('special-table', _getCBClassIfWanted('container' . ($isDeck && !$notRendering ? ' deck deck-from-sheet' : '')));

		if ($isDeck)
			renderSheetAsDeck($file, variableOr('all_page_parameters', nodeValue()) . '/');
		else if (startsWith($raw, '|is-rich-page'))
			renderRichPage($file);
		else if (contains($raw, '|is-table'))
			add_table(pathinfo($file, PATHINFO_FILENAME), $file, valueIfSet($meta->values, 'head-columns', 'auto'), valueIfSet($meta->values, 'row-template', 'auto'), $meta->values);
		else if ($istwt)
			add_table(pathinfo($file, PATHINFO_FILENAME), $file, $meta->values['head-columns'], $meta->values['row-template'], $meta->values);
		else
			showDebugging('unsupported tsv file - see line 1 for type definition', $file, true);

		if (!$embed) sectionEnd();
		pageMenu($file);
		return;
	}

	$siteTheme = variable('site-has-theme') || variable('skip-container-for-this-page');
	if (!$siteTheme) sectionId('file', _getCBClassIfWanted('container'));
	renderAny($file);
	if (!$siteTheme) sectionEnd();
}

function hasBuiltin() {
	$scaffold = variableOr('scaffold', []);
	//NOTE: sitemap always needed
	$always = false; //variable('local') && nodeIs('sitemap');
	if (!$always && !nodeIsOneOf($scaffold))
		return false;

	if (hasPageParameter('embed')) variable('embed', true);
	variable('scaffoldCode', nodeValue());
	return true;
}

function renderedBuiltin() {
	$code = variable('scaffoldCode');
	if (!$code) return false;

	runFrameworkFile('pages/' . $code);
	return true;
}

/* ai stuff - no parser.php anymore */
DEFINE('FROM_GEMINI_AI', '<!--exported-from-gemini-ai-->');
DEFINE('GEMINI_AI_MSG', 'This is a Chat with "Gemini AI"');
DEFINE('GEMINI_CLASSES', 'with-ai has-gemini-ai has-prompts');

function peekAtMainFile($file, $notMain = false) {
	$raw = disk_file_get_contents($file);
	$ai = contains($raw, FROM_GEMINI_AI);
	if ($notMain) return $ai ? ' ' . GEMINI_CLASSES : '';

	if ($ai) add_body_class(GEMINI_CLASSES);
}

function processAI($raw, $aiName) {
	$replaces = [
		FROM_GEMINI_AI => FROM_GEMINI_AI . SPACERSTART . GEMINI_AI_MSG . SPACEREND,
		'## Prompt:' => '[prompt]',
		'## Response:' => '[/prompt]' . NEWLINES2,
	];

	if ($sr = variable('siteAIReplaces'))
		$raw = replaceItems($raw, $sr);

	return replaceItems($raw, $replaces);
}

function adjustOutputOfAI($raw, $aiName) {
	if (!contains($raw, '<p>| ')) return $raw;

	runFeature('tables');
	_includeDatatables(false);
	_includeTableAssets();

	return replaceItems($raw, [
		'<p>| ' => '<table class="datatables table-sans-th table table-striped table-bordered"><thead></thead><tbody><tr><td>',
		'|</p> ' => '</tr></tbody></table>',
		' | ' => '</td><td>',
		'| ' => '<tr><td>',
		' |' => '</td></tr>',
		'</td></tr></p>' => '</tr></tbody></table>',
		'--- |' . NEWLINE => '-->' . NEWLINE,
		'| --- |' => '<!--',
		'<tr><td>---' => '<tr class="d-none"><td>',
	]);
}
