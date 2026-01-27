<?php
class socialBuilder {
	const variableName = 'social';
	const HR = '----';
	const shareBtn = 'bi bi-send-plus bg-success';
	const dawnBtn = 'bi bi-heart-pulse bg-danger';
	const imranBtn = 'bi bi-heart-pulse bg-warning';
	const springBtn = 'bi bi-tools bg-warning';

	private $items = [];
	function getItems() { return $this->items; }

	static function create($items = []) {
		$r = new socialBuilder();
		if (!empty($items)) {
			foreach ($items as &$item) {
				if ($item === self::HR) continue;
				$url = $item['url'];
				if (
					startsWith($url, self::url_instagram) OR
					startsWith($url, self::url_linkedin) OR
					startsWith($url, self::url_youtube) OR
					startsWith($url, self::url_github)
				) $item['url'] = $item['url'] . NOFOLLOWSUFFIX;
			}
			//showDebugging(25, $items);
			$r->items = $items;
		}
		return $r;
	}

	function addHR() {
		$this->items[] = self::HR;
		return $this;
	}

	private function add($type, $url, $name) {
		$this->items[] = [ 'type' => $type, 'url' => $url, 'name' => $name ];
		return $this;
	}

	function addExternal($type, $relUrl, $name, $nofollow = true) {
		$relUrl .= NOFOLLOWSUFFIX;
		return $this->add($type, $relUrl, $name);
	}

	function addInternal($relUrl, $name, $type, $skip = false) {
		if ($skip) return $this;
		if (!contains($relUrl, 'http') AND !isSpecialLink($relUrl))
			$relUrl = pageUrl($relUrl);
		return $this->add($type, $relUrl, $name);
	}

	const instagram = 'instagram';
	private const url_instagram = 'https://www.instagram.com/';
	function addInstagram($relUrl, $name, $type = self::instagram, $skip = false) {
		if ($skip) return $this;
		return $this->addExternal($type, self::url_instagram . $relUrl, $name);
	}

	const linkedin = 'linkedin';
	private const url_linkedin = 'https://www.linkedin.com/';
	function addLinkedIn($relUrl, $name, $type = self::linkedin, $skip = false) {
		if ($skip) return $this;
		return $this->addExternal($type, self::url_linkedin . $relUrl, $name);
	}

	const youtube = 'youtube';
	private const url_youtube = 'https://www.youtube.com/';
	function addYoutube($relUrl, $name, $type = self::youtube, $skip = false) {
		if ($skip) return $this;
		return $this->addExternal($type, self::url_youtube . $relUrl, $name);
	}

	const github = 'github';
	private const url_github = 'https://www.github.com/';
	function addGithub($relUrl, $name, $skip = false, $type = self::github) {
		if ($skip) return $this;
		return $this->addExternal($type, self::url_github . $relUrl, $name);
	}

	function addShare() {
		return $this
			->addInternal(getPageParameters(VARSlash . features::shareQS), 'Share Via&hellip;', self::shareBtn);
	}

	function addImranPersonal($who = true, $technologist = true, $builder = true) {
		$base = getSiteUrl(SITEIMRAN);
		return $this
			->addInternal($base . 'whoami/on-linkedin/', 'Who Is Imran', 'fa-brands fa-redhat bg-danger', !$who)
			->addInternal($base . 'whoami/the-technologist/', 'The IT Guy', 'fa-brands fa-linkedin bg-linkedin text-light', !$technologist)
			->addInternal($base . '#dare-i-build', 'Darfe I Build', self::imranBtn, !$builder)
			;
	}

	function addDawn($linkedIn = true, $youtube = true) {
		return $this
			->addLinkedIn('company/amadeusweb/', 'Amadeus Web', self::linkedin, !$linkedIn)
			->addYoutube('@imran-thrives', 'Imran from DAWN', self::youtube, !$youtube);
	}

	function addThisSitesGithub() {
		return $this
				->addGithub(variable('github-repo'), 'This Site', !variable('github-repo'));
	}

	function addGithubGroup() {
		return $this
			->addHR()
			->addGithub('amadeus-web-world/', 'AW World')
			->addGithub('amadeus-web-world/spring', 'AW Spring')
			->addGithub(variable('github-repo'), 'This Site', !variable('github-repo'))
			;
	}

	function addUtilityGroup() {
		return $this
			->addHR()
			->addShare()
			->addInternal(getSiteUrl(SITEROOT), 'DAWN', self::dawnBtn)
			->addInternal(getSiteUrl(SITESPRING), 'AW Spring', self::springBtn)
			;
	}
}


class main {
	static function defaultSocial($prepend = []) {
		return socialBuilder::create($prepend)
			->addLinkedIn('in/imran-ali-namazi/', 'Imran Ali Namazi')
			->addDawn(true, true)
			->addGithubGroup()
			->addUtilityGroup()
			->getItems();
	}

	static function defaultSearches() {
		return [
			'amadeusweb' => ['code' => 'c0a96edc60a44407a', 'name' => 'AmadeusWeb Network&nbsp;&nbsp;', 'description' => 'All AmadeusWeb sites from 2025'],
			'imranali' =>   ['code' => '63a208ccffd5b4492', 'name' => 'Imran\'s Writing / Poems&nbsp;&nbsp;', 'description' => 'All of Imran\'s writing since 2017'],
			'yieldmore' =>  ['code' => '29e47bd630f4c73c0', 'name' => 'YieldMore Network&nbsp;', 'description' => 'All YieldMore sites from 2013 to 2024'],
			'sriaurobindo'=>['code' => '84d24b3918cbd5f1a', 'name' => 'Mother Sri Aurobindo Sites', 'description' => 'From the Aurobindonian World'],
		];
	}

	static function runAndReturn() {
		doToBuffering(1);
		main::analytics();
		if (!getQueryParameter('content'))
			main::chat();
		$result = doToBuffering(2);
		doToBuffering(3);
		return $result;
	}

	static function chat() {
		$val = variable(VARChatraID);
		$val = $val && $val != 'none' ? ($val != VARUseAmadeusWeb ? $val : 'wqzHJQrofB47q5oFj') : false;
		if (!$val) return;
		variable(VARChatraID, $val);
		runModule('chatra');
	}

	static function analytics() {
		$val = variable(VARGoogleAnalytics);
		$val = $val && $val != 'none' ? ($val != VARUseAmadeusWeb ? $val : 'G-LN2JB9GLDC') : false;
		if (!$val) return;
		variable(VARGoogleAnalytics, $val);
		runModule(VARGoogleAnalytics);
	}
}
