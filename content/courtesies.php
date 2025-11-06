<?php
echo '<hr class="mt-4" />';
printH1InDivider('README & SETUP', false);
builtinOrRender(SITEPATH . '/README.md', false, false);

echo '<hr class="mt-4" />';
printH1InDivider('LICENSE (Courtesies)', false);
builtinOrRender(SITEPATH . '/LICENSE.md', false, false);
