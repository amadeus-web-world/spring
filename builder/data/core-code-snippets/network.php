<?php
if (variable('safeName') != 'amadeuswebworld') return ''; //TODO: in_array

doToBuffering(1);

echo cbCloseAndOpen('after-content');
printSpacer('NETWORK');

runFrameworkFile('site/listing');

$result = doToBuffering(2);
doToBuffering(3);
return $result;
