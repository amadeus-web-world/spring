<?php
$snippet = SITEPATH . '/data/snippets/cta-' . nodeValue() . '.';

if (!disk_one_of_files_exist($snippet, 'md, tsv')) return '';

runFeature('engage');

return sectionId('cta', 'container', false) .
	renderEngage('cta-' . nodeValue(), '%engage-note-above%' . NEWLINES2 . getSnippet('cta-' . nodeValue()) . NEWLINES2 . '%engage-note%', false)
	. sectionEnd(false);
