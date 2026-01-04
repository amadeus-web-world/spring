<?php
$tpl = _excludeFromGoogleSearch(getThemeSnippet('countdown'));
$heading = returnLine(hasVariable('heading-of-countdown') ? variable('heading-of-countdown') :
	'A proprietary system, The <span class="ls-2">[**Dynamic AmadeusWeb Network**](%urlOf-world%BTNINFO)</span> is not for the faint of heart!');
$message = returnLine(hasVariable('below-countdown') ? variable('below-countdown') :
	'by [Imran Ali Namazi, Architect](%urlOf-imran%BTNPRIMARY)');
return replaceItems($tpl, [
	'heading'   => $heading,
	'left-hand-side'   => markdown('Next Release In'),
	'right-hand-side'  => markdown('i.e AW Spring [v9.3 of Feb 21st](%urlOf-spring%BTNSUCCESS)'),
	'countdown-params' => variableOr('countdown-params', 'data-year="2026" data-month="2" data-day="21" data-hour="12" data-minute="37" data-format="dHMS"'),
	'message'  => $message,
], '%');
