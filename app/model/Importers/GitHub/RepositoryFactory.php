<?php

namespace NetteAddons\Model\Importers\GitHub;

use NetteAddons\Utils\CurlRequestFactory;

/**
 * GitHub repository factory
 *
 * @author Patrik Votoček
 */
class RepositoryFactory extends \Nette\Object
{
	/** @var \NetteAddons\Utils\CurlRequestFactory */
	private $curlFactory;

	/** @var string */
	private $apiVersion;

	/** @var string|NULL */
	private $clientId;

	/** @var string|NULL */
	private $clientSecret;



	/**
	 * @param string
	 * @param \NetteAddons\Utils\CurlRequestFactory
	 * @param string|NULL
	 * @param string|NULL
	 */
	public function __construct($apiVersion, CurlRequestFactory $curlFactory, $clientId = NULL, $clientSecret = NULL)
	{
		$this->apiVersion = $apiVersion;
		$this->curlFactory = $curlFactory;
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
	}



	/**
	 * @param string
	 * @return Repository
	 */
	public function create($url)
	{
		return new Repository($this->apiVersion, $this->curlFactory, $url, $this->clientId, $this->clientSecret);
	}
}
