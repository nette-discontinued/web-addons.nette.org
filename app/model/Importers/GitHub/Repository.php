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
	 * Calls remote API.
	 *
	 * @param  string
	 * @return mixed json-decoded result
	 * @throws \NetteAddons\IOException
	 */
	protected function exec($path)
	{
		try {
			$url = new \Nette\Http\Url($this->baseUrl);
			$url->setPath($path);
			$json = $this->curl->get($url);
			return \Nette\Utils\Json::decode($json);

		} catch (\NetteAddons\CurlException $e) {
			throw new \NetteAddons\IOException('cURL execution failed.', NULL, $e);

		} catch (\NetteAddons\InvalidStateException $e) {
			throw new \NetteAddons\IOException();

		} catch (\Nette\Utils\JsonException $e) {
			throw new \NetteAddons\IOException('GitHub API returned invalid JSON.', NULL, $e);
		}
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
	 * Returns repository metadata.
	 *
	 * @link http://developer.github.com/v3/repos/#get GitHub API documentation
	 * @return \stdClass
	 * @throws \NetteAddons\IOException
	 */
	public function getMetadata()
	{
		if (isset($this->cache['repository'])) {
			return $this->cache['repository'];
		}

		return $this->cache['repository'] = $this->exec("/repos/{$this->vendor}/{$this->name}");
	}

	/**
	 * Gets default repository branch.
	 *
	 * @return string
	 * @throws \NetteAddons\IOException
	 */
	public function getMasterBranch()
	{
		$repo = $this->getMetadata();
		return isset($repo->master_branch) ? $repo->master_branch : 'master';
	}

	/**
	 * Returns Git "tree" specified by hash.
	 *
	 * @link http://developer.github.com/v3/git/trees/#get-a-tree
	 * @param  string sha-1 hash
	 * @return \stdClass
	 * @throws \NetteAddons\IOException
	 */
	public function getTree($hash)
	{
		return $this->exec("/repos/{$this->vendor}/{$this->name}/git/trees/$hash");
	}

	/**
	 * Returns readme content or NULL if readme does not exist.
	 *
	 * @return string|NULL
	 * @throws \NetteAddons\IOException
	 */
	public function getReadme()
	{
		if (array_key_exists('readme', $this->cache)) { // $this->cache['readme'] may contain NULL
			return $this->cache['readme'];
		}

		$loader = $this->createBlobLoader();
		$branch = $this->getMasterBranch();
		$data = $this->getTree($branch);

		if (isset($data->tree)) {
			foreach ($data->tree as $item) {
				if (Strings::startsWith(Strings::lower($item->path), 'readme')) {
					return $this->cache['readme'] = $loader->get($branch, $item->path);
				}
			}
		}

		$this->cache['readme'] = NULL; // means readme not found
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
	 * Returns list of repository tags.
	 *
	 * @return array (tagName => commitHash)
	 * @throws \NetteAddons\IOException
	 */
	public function getTags()
	{
		$data = $this->exec("/repos/{$this->vendor}/{$this->name}/tags");
		if (!is_array($data)) {
			throw new \NetteAddons\IOException('GitHub API returned unexpected value.');
		}

		$tags = array();
		foreach ($data as $tag) {
			$tags[$tag->name] = $tag->commit->sha;
		}
		return $tags;
	}

	/**
	 * Returns list of repository branches.
	 *
	 * @return array (branchName => commitHash)
	 * @throws \NetteAddons\IOException
	 */
	public function getBranches()
	{
		$data = $this->exec("/repos/{$this->vendor}/{$this->name}/branches");
		if (!is_array($data)) {
			throw new \NetteAddons\IOException('GitHub API returned unexpected value.');
		}

		$branches = array();
		foreach ($data as $branch) {
			$branches[$branch->name] = $branch->commit->sha;
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
			if ($version && $version->isValid()) {
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
