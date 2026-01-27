<?php
class socialBuilder {
	const HR = '----';
	const shareBtn = 'bi bi-send-plus bg-success';
	const dawnBtn = 'bi bi-heart-pulse bg-danger';
	const springBtn = 'bi bi-tools bg-warning';

	private $items = [];
	function getItems() { return $this->items; }

	static function create($items = []) {
		$r = new socialBuilder();
		if (!empty($items)) $r->items = $items;
		return $r;
	}

	function addHR() : socialBuilder {
		$this->items[] = self::HR;
		return $this;
	}

	private function add($type, $url, $name) : socialBuilder {
		$this->items[] = [ 'type' => $type, 'url' => $url, 'name' => $name ];
		return $this;
	}

	function addInternal($relUrl, $name, $type) {
		if (!contains($relUrl, 'http') AND !isSpecialLink($relUrl))
			$relUrl = pageUrl($relUrl);
		return $this->add($type, $relUrl, $name);
	}

	const linkedin = 'linkedin';
	function addLinkedIn($relUrl, $name, $type = self::linkedin, $skip = false) {
		if ($skip) return $this;
		return $this->add($type, 'https://www.linkedin.com/' . $relUrl, $name);
	}

	const youtube = 'youtube';
	function addYoutube($relUrl, $name, $type = self::youtube, $skip = false) {
		if ($skip) return $this;
		return $this->add($type, 'https://www.youtube.com/' . $relUrl, $name);
	}

	function addGithub($relUrl, $name, $skip = false, $type = 'github') {
		if ($skip) return $this;
		return $this->add($type, 'https://www.github.com/' . $relUrl, $name);
	}

	function addShare() {
		return $this
			->addInternal(getPageParameters(VARSlash . features::shareQS), 'Share Via&hellip;', self::shareBtn);
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
			->addGithub('amadeus-web-world/', 'AW Spring')
			->addGithub(variable('github-repo'), 'This Site', !variable('github-repo'))
			;
	}

	function addUtilityGroup() {
		return $this
			->addHR()
			->addShare()
			->addInternal(replaceNetworkUrls(getSiteKey(SITEROOT)), 'DAWN', self::dawnBtn)
			->addInternal(replaceNetworkUrls(getSiteKey(SITESPRING)), 'AW Spring', self::springBtn)
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
