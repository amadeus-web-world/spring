<?php
/*
//.php
DEFINE('VAR ', );
*/
DEFINE('BOOLYes', true);
	DEFINE('PleaseDie', BOOLYes);
DEFINE('BOOLNo', false);
	DEFINE('BlockExecution', BOOLNo); ///dead code

function bool_r(bool $value) {
	return ($value ? 'true (yes)' : 'false (no)') . ' type: bool';
}

DEFINE('VARLocal', 'local');

//4-array
DEFINE('TYPENOCHANGE', 'no-change');
DEFINE('TYPEBOOLEAN', 'bool');
DEFINE('TYPEARRAY', 'array');

DEFINE('BOOLLISTFALSE', [false, 'false', 'no', '0']);
DEFINE('BOOLLISTTRUE', [true, 'true', 'yes', '1']);
	//TODO: deprecated. remove once testing process is in places
	DEFINE('BOOLFALSE', BOOLLISTFALSE);
	DEFINE('BOOLTRUE', BOOLLISTTRUE);

//7-html.php
DEFINE('VARNoContentBoxes', 'no-content-boxes');
DEFINE('VARCustom', 'custom');

//9-render.php
DEFINE('VAREcho', 'echo');
	DEFINE('BOOLDontEcho', false);
DEFINE('VARStripParagraphTag', 'strip-paragraph-tag');
DEFINE('VARExcerpt', 'excerpt');
DEFINE('VARMarkdown', 'markdown');
//DEFINE('VAR ', );

DEFINE('VARFirstSectionOnly', 'FirstSectionOnly');
DEFINE('VARFullAccessNotice', 'FullAccessNotice');
	//TODO: deprecated. remove once testing process is in places
	DEFINE('FIRSTSECTIONONLY', VARFirstSectionOnly);
	DEFINE('FULLACCESSNOTICE', VARFullAccessNotice);

DEFINE('VARDontPrepareLinks', 'dont-prepare-links');
DEFINE('VARWrapInSection', 'wrap-in-section');
DEFINE('VARUseContentBox', 'use-content-box');

DEFINE('ENGAGE', '<!--engage-->');
DEFINE('ENGAGESTART', '<!--start-engage-->');
DEFINE('ENGAGESANSCB', '<!--engage-without-cb-->');

function is_engage($raw) { return contains($raw, ' //engage-->') || contains($raw, ENGAGE) || contains($raw, ENGAGESTART); }
function wants_engage_until_eof($raw) { return contains($raw, ENGAGESTART); }
function wants_md_in_parser($raw) { return contains($raw, '<!--markdown-when-processing-->'); }

//12-macros.php
DEFINE('VARCTAONLY', '?cta=1&content=1');

//14-main
DEFINE('VARSystemEmail', 'imran@amadeusweb.world');

//15-routing - todo cleanup
DEFINE('VARNodeSiteName', 'nodeSiteName');
DEFINE('VARDontOverwriteLogo', 'dont-overwrite-logo');
DEFINE('VARPrefixSafeName', 'prefix-safeName');
DEFINE('VARNodeSafeName', 'nodeSafeName');
	//TODO: deprecated. remove once testing process is in places
	DEFINE('DontOverwriteLogo', VARDontOverwriteLogo);
	DEFINE('PrefixSafeName', VARPrefixSafeName);
	DEFINE('NodeSafeName', VARNodeSafeName); 

//16-theme.php
DEFINE('VARSubmenuAtNode', 'submenu-at-node');

//features/engage.php
DEFINE('VARWantsNoEngageBox', '<!--no-engage-box-->');

//site/header-menu.php
DEFINE('VARLinkToNodeHome', 'link-to-node-home');
DEFINE('VARLinkToSectionHome', 'link-to-section-home');
DEFINE('VARSectionsHaveFiles', 'sections-have-files');

//site/network.php
DEFINE('URLOFPREFIX', 'urlOf-');
	DEFINE('OTHERSITEPREFIX', URLOFPREFIX); //TODO: cleanup
DEFINE('SITEROOT', 'root');
DEFINE('SITESPRING', 'spring');
DEFINE('SITEWORLD', 'world');
DEFINE('SITEWORK', 'work');
DEFINE('SITEZVM', 'zvmworld');

//site/node-menu.php
DEFINE('VARNodesHaveFiles', 'nodes-have-files');
