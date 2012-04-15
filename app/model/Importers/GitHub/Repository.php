<?php

namespace NetteAddons\Model\Importers\GitHub;

use Nette\Utils\Strings;

/**
 * Get repository metadata from GitHub
 *
 * @author	Patrik Votoček
 */
class Repository extends \Nette\Object
{
	const COMPOSER_JSON = 'composer.json';

	/** @var \NetteAddons\Curl */
	private $curl;
	/** @var string */
	private $vendor;
	/** @var string */
	private $name;
	/** @var string */
	public $baseUrl = 'https://api.github.com';
	/** @var array */
	private $cache = array();

	/**
	 * @param \NetteAddons\Curl
	 * @param string
	 * @param string
	 */
	public function __construct(\NetteAddons\Curl $curl, $vendor, $name)
	{
		$this->curl = $curl;
		$this->vendor = $vendor;
		$this->name = $name;
	}

	/**
	 * @return File
	 */
	private function createBlobLoader()
	{
		return new File($this->curl, $this->vendor, $this->name);
	}

	/**
	 * Call remote API
	 *
	 * @param string
	 * @return string
	 */
	protected function exec($path)
	{
		$url = new \Nette\Http\Url($this->baseUrl);
		$url->setPath($path);
		return $this->curl->get($url);
	}

	/**
	 * @return string
	 */
	public function getVendor()
	{
		return $this->vendor;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return stdClass
	 */
	public function getMetadata()
	{
		if (isset($this->cache['repository'])) {
			return $this->cache['repository'];
		}

		return $this->cache['repository'] = Helpers::decodeJSON($this->exec("/repos/{$this->vendor}/{$this->name}"));
	}

	/**
	 * Get default repository branch
	 *
	 * @return NULL|string
	 */
	public function getMasterBranch()
	{
		$repo = $this->getMetadata();
		if (!$repo) {
			return NULL;
		}
		return isset($repo->master_branch) ? $repo->master_branch : 'master';
	}

	/**
	 * @param string
	 * @return stdClass
	 */
	public function getTree($hash)
	{
		return Helpers::decodeJSON($this->exec("/repos/{$this->vendor}/{$this->name}/git/trees/$hash"));
	}

	/**
	 * @return string
	 */
	public function getReadme()
	{
		if (isset($this->cache['readme'])) {
			return $this->cache['readme'];
		}

		$loader = $this->createBlobLoader();
		$data = $this->getTree($this->getMasterBranch());

		if (isset($data->tree)) {
			foreach ($data->tree as $item) {
				if (Strings::startsWith(Strings::lower($item->path), 'readme')) {
					return $this->cache['readme'] = $loader->get($this->getMasterBranch(), $item->path);
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function getComposerJson()
	{
		if (isset($this->cache['composer'])) {
			return $this->cache['composer'];
		}

		$loader = $this->createBlobLoader();

		return $this->cache['composer'] = $loader->get($this->getMasterBranch(), static::COMPOSER_JSON);
	}

	/**
	 * @return array
	 */
	public function getTags()
	{
		$data = Helpers::decodeJSON($this->exec("/repos/{$this->vendor}/{$this->name}/tags")) ?: array();
		$tags = array();
		foreach ($data as $tag) {
			$tags[$tag->name] = $tag->commit->sha;
		}
		return $tags;
	}

	/**
	 * @return array
	 */
	public function getBranches()
	{
		$data = Helpers::decodeJSON($this->exec("/repos/{$this->vendor}/{$this->name}/branches")) ?: array();
		$branches = array();
		foreach ($data as $branche) {
			$branches[$branche->name] = $branche->commit->sha;
		}
		return $branches;
	}

	/**
	 * @return array
	 */
	public function getVersions()
	{
		$versions = array($this->getMasterBranch() => $this->getMasterBranch());
		foreach ($this->getTags() as $v => $hash) {
			$version = \NetteAddons\Model\Version::create($v);
			if ($version->isValid()) {
				$versions[$v] = $hash;
			}
		}

		return $versions;
	}

	/**
	 * @return array
	 */
	public function getVersionsComposersJson()
	{
		$loader = $this->createBlobLoader();

		$versions = array();
		foreach ($this->getVersions() as $v => $hash) {
			$composer = $loader->get($hash, static::COMPOSER_JSON);
			if ($composer) {
				$versions[$v] = $composer;
			}
		}

		return $versions;
	}
}