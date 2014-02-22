<?php

namespace NetteAddons\Model\Importers\GitHub;

use NetteAddons\Utils\HttpStreamRequestFactory;


/**
 * GitHub repository factory
 */
class RepositoryFactory extends \Nette\Object
{
	/** @var \NetteAddons\Utils\HttpStreamRequestFactory */
	private $requestFactory;

	/** @var string */
	private $apiVersion;

	/** @var string|NULL */
	private $clientId;

	/** @var string|NULL */
	private $clientSecret;


	/**
	 * @param string
	 * @param \NetteAddons\Utils\HttpStreamRequestFactory
	 * @param string|NULL
	 * @param string|NULL
	 */
	public function __construct(
		$apiVersion,
		HttpStreamRequestFactory $requestFactory,
		$clientId = NULL,
		$clientSecret = NULL
	) {
		$this->apiVersion = $apiVersion;
		$this->requestFactory = $requestFactory;
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
	}

	/**
	 * @param string
	 * @return Repository
	 */
	public function create($url)
	{
		return new Repository($this->apiVersion, $this->requestFactory, $url, $this->clientId, $this->clientSecret);
	}
}
