<?php

namespace NetteAddons\Model\Importers;

use NetteAddons\Model\Utils\Validators;


class GitHubImporterFactory extends \Nette\Object
{
	/** @var GitHub\RepositoryFactory */
	private $repositoryFactory;

	/** @var \NetteAddons\Model\Utils\Validators */
	private $validators;


	public function __construct(GitHub\RepositoryFactory $repositoryFactory, Validators $validators)
	{
		$this->repositoryFactory = $repositoryFactory;
		$this->validators = $validators;
	}


	/**
	 * @param string
	 * @return GitHubImporter
	 */
	public function create($url)
	{
		$repository = $this->repositoryFactory->create($url);
		return new GitHubImporter($repository, $this->validators);
	}


	/**
	 * @deprecated
	 * @param string
	 * @return GitHubImporter
	 */
	public function __invoke($url)
	{
		return $this->create($url);
	}
}
