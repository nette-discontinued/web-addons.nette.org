<?php

namespace NetteAddons\Model\Importers\GitHub;

use Nette\Http\Url;
use Nette\Utils\Strings;
use NetteAddons\Utils\HttpStreamRequestFactory;


/**
 * GitHub repository API implementation.
 *
 * This class is not aware how it will be used.
 *
 * @link http://developer.github.com/v3/ GitHub API documentation
 */
class Repository extends \Nette\Object
{
	const URL_PATTERN_PUBLIC = '~^(?:(?:https?|git)://)?github\.com/(?<vendor>[a-z0-9][a-z0-9_-]*)/(?<name>[a-z0-9_.-]+?)(?:\.git)?(/.*)?$~i';
	const URL_PATTERN_PRIVATE = '~^(?:ssh://)?git@github.com(?:/|:)(?<vendor>[a-z0-9][a-z0-9_-]*)/(?<name>[a-z0-9_.-]+?)(?:\.git)?$~i';

	/** @var \NetteAddons\Utils\HttpStreamRequestFactory */
	private $requestFactory;

	/** @var string */
	private $vendor;

	/** @var string */
	private $name;

	/** @var string */
	public $baseUrl = 'https://api.github.com';

	/** @var string */
	private $apiVersion;

	/** @var string|NULL */
	private $clientId;

	/** @var string|NULL */
	private $clientSecret;


	/**
	 * @param string
	 * @param \NetteAddons\Utils\HttpStreamRequestFactory
	 * @param string
	 * @param string|NULL
	 * @param string|NULL
	 */
	public function __construct(
		$apiVersion,
		HttpStreamRequestFactory $requestFactory,
		$url,
		$clientId = NULL,
		$clientSecret = NULL
	) {
		$this->apiVersion;
		$this->requestFactory = $requestFactory;

		$data = static::getVendorAndName($url);
		if (!is_array($data)) {
			throw new \NetteAddons\InvalidArgumentException("Url '$url' is not valid GitHub url");
		}
		list($this->vendor, $this->name) = $data;

		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
	}


	/**
	 * @param string|\Nette\Http\Url
	 * @return array|NULL (vendor, name)
	 */
	public static function getVendorAndName($url)
	{
		if (($matches = Strings::match((string) $url, self::URL_PATTERN_PUBLIC)) !== NULL) {
			return array($matches['vendor'], $matches['name']);
		} elseif (($matches = Strings::match((string) $url, self::URL_PATTERN_PRIVATE)) !== NULL) {
			return array($matches['vendor'], $matches['name']);
		}

		return NULL;
	}


	/**
	 * Calls remote API.
	 *
	 * @param string
	 * @return mixed json-decoded result
	 * @throws \NetteAddons\IOException
	 */
	protected function exec($path)
	{
		try {
			$url = new Url($this->baseUrl . '/' . ltrim($path, '/'));
			if ($this->clientId && $this->clientSecret) {
				$url->appendQuery(array('client_id' => $this->clientId, 'client_secret' => $this->clientSecret));
			}

			$request = $this->requestFactory->create($url);
			$request->addHeader('Accept', "application/vnd.github.{$this->apiVersion}+json");

			return \Nette\Utils\Json::decode($request->execute());

		} catch (\NetteAddons\Utils\StreamException $e) {
			throw new \NetteAddons\IOException('Request execution failed.', NULL, $e);

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
	 * Returns URL to this repository.
	 *
	 * @todo   Use GitHub API?
	 * @return string
	 */
	public function getUrl()
	{
		return "https://github.com/{$this->vendor}/{$this->name}";
	}


	/**
	 * Returns repository metadata.
	 *
	 * @link http://developer.github.com/v3/repos/#get
	 * @return \stdClass
	 * @throws \NetteAddons\IOException
	 */
	public function getMetadata()
	{
		return $this->exec("/repos/{$this->vendor}/{$this->name}");
	}


	/**
	 * Returns Git "tree" specified by hash.
	 *
	 * @link http://developer.github.com/v3/git/trees/#get-a-tree
	 * @param  string commit or tree hash, branch or tag
	 * @return \stdClass
	 * @throws \NetteAddons\IOException
	 */
	public function getTree($hash)
	{
		return $this->exec("/repos/{$this->vendor}/{$this->name}/git/trees/$hash");
	}


	/**
	 * Returns Git "commit" specified by hash.
	 *
	 * @link http://developer.github.com/v3/repos/commits/#get-a-single-commit
	 * @param  string commit or tree hash, branch or tag
	 * @return \stdClass
	 * @throws \NetteAddons\IOException
	 */
	public function getCommit($hash)
	{
		return $this->exec("/repos/{$this->vendor}/{$this->name}/commits/$hash");
	}


	/**
	 * Gets file content.
	 *
	 * @link http://developer.github.com/v3/repos/contents/#get-contents
	 * @param  string commit hash, branch or tag
	 * @param  string
	 * @return string
	 * @throws \NetteAddons\IOException
	 */
	public function getFileContent($hash, $path)
	{
		$data = $this->exec("/repos/{$this->vendor}/{$this->name}/contents/$path?ref=$hash");
		return $this->processContentResponse($data)->content;
	}


	/**
	 * Returns readme content or NULL if readme does not exist.
	 *
	 * @link http://developer.github.com/v3/repos/contents/#get-the-readme
	 * @param  string commit hash, branch or tag
	 * @return string|NULL
	 * @throws \NetteAddons\IOException
	 */
	public function getReadme($hash)
	{
		try {
			$data = $this->exec("/repos/{$this->vendor}/{$this->name}/readme?ref=$hash");
		} catch (\NetteAddons\Utils\HttpException $e) {
			if ($e->getCode() === 404) {
				return NULL;
			}

			throw $e;
		}

		return $this->processContentResponse($data);
	}


	/**
	 * @param string
	 * @return \stdClass
	 * @throws \NetteAddons\IOException
	 */
	protected function processContentResponse($data)
	{
		if (!$data instanceof \stdClass || !isset($data->encoding, $data->content)) {
			throw new \NetteAddons\IOException('GitHub API returned unexpected response.');
		}

		if ($data->encoding === 'base64') {
			$data->content = base64_decode($data->content);

		} elseif ($data->encoding !== 'utf-8') {
			throw new \NetteAddons\IOException('GitHub API returned file content in unknown encoding.');
		}

		return $data;
	}


	/**
	 * Returns list of repository tags.
	 *
	 * @link http://developer.github.com/v3/repos/#list-tags
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
	 * @link http://developer.github.com/v3/repos/#list-branches
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
			if (strpos($branch->name, '/') === FALSE) {
				$branches[$branch->name] = $branch->commit->sha;
			}
		}

		return $branches;
	}


	/**
	 * Returns download link.
	 *
	 * @todo Implement it using GitHub API?
	 * @param string
	 * @param string
	 * @return string
	 * @throws \NetteAddons\NotSupportedException if $type is other than 'zip'
	 */
	public function getArchiveLink($type, $hash)
	{
		if ($type === 'zip') {
			return "https://github.com/{$this->vendor}/{$this->name}/zipball/$hash";
		} else {
			throw new \NetteAddons\NotSupportedException();
		}
	}
}
