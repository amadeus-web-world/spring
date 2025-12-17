<?php
$tpl = getThemeSnippet('countdown');
$message = returnLine(hasVariable('below-countdown') ? variable('below-countdown')
	: 'A proprietary system, AW Dawn is not for the faint of heart!<br />Launching [Dec 22nd](%urlOf-imran%writing/for/msa/BTNSITE)');
return replaceItems($tpl, [
	'left-hand-side'   => markdown('**COMING**'),
	'right-hand-side'  => markdown('**SOOOON**'),
	'countdown-params' => variableOr('countdown-params', 'data-year="2025" data-month="12" data-day="22" data-hour="12" data-minute="37" data-format="dHMS"'),
	'below-countdown'  => $message,
], '%');
