<?php
contentBox('', 'container text-center under-construction mt-3');
echo returnLine(pipeToBR('This website:|"**%siteName%**"|is under construction.||Check back in a while :)'));
contentBox('end');
?>
<style>
.under-construction { background-color: var(--amadeus-after-content-bgd, #eee)!important; font-size: 260%; padding: 40px; }
</style>
