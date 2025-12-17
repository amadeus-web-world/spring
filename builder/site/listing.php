<?php
$sites = variable('networkSites');

sectionId('network-sites', 'container');

$op = ['ALLARTICLES'];
foreach ($sites as $item) {
	if (is_string($item) && startsWith($item, '~')) {
		if (count($op) > 1) $op[] = '<hr class="mt-5" />';
		$op[] = h2(substr($item, 1), 'btn-outline-info', true);
		continue;
	}

	if (!isset($item[variable(SITEURLKEY)]))
		showDebugging('7 url-key-missing', [variable(SITEURLKEY), $item], true);

	$item['url'] = $item[variable(SITEURLKEY)];
	$item['safeName'] = $item['key'];

	$op[] = 'ARTICLE-BOX';
	$op[] = replaceItems('<a href="%url%"><h3 class="h3 m-0 mb-1">%name%</h3><img src="%url%%safeName%-logo.png" class="img-fluid mb-2" />%byline%</a>', $item, '%');
	$op[] = 'ARTICLE-CLOSE';
	$op[] = ''; $op[] = '';
}
$op[] = 'ALLARTICLES-CLOSE';

echo returnLinesNoParas(implode(NEWLINE, $op));

sectionEnd();
