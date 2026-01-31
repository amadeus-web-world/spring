<?php
echo '<hr class="mt-4" />';
printH1InDivider('README & SETUP', false);
builtinOrRender(SITEPATH . '/README.md', false, false);

echo '<hr class="mt-4" />';
printH1InDivider('LICENSE (Courtesies)', false);

sectionId('license', 'container');
$op = renderAny(SITEPATH . '/LICENSE.md', ['echo' => false, 'use-content-box' => true]);

if (is_local()) {
	$op = str_replace('https://amadeusweb.world/', replaceHtml('%urlOf-world%'), $op);
	$op = str_replace('https://people.amadeusweb.world/imran/', replaceHtml('%urlOf-imran%'), $op);
}

echo $op;

sectionEnd();
