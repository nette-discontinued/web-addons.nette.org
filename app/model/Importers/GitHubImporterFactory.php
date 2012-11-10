<?php

namespace NetteAddons\Model\Importers;

use NetteAddons\Model\Utils\Validators;



/**
 * @author Patrik Votoček
 */
class GitHubImporterFactory extends \Nette\Object
{
	/** @var GitHub\RepositoryFactory */
	private $repositoryFactory;

	/** @var \NetteAddons\Model\Utils\Validators */
	private $validators;



	/**
	 * @param GitHub\RepositoryFactory
	 * @param \NetteAddons\Model\Utils\Validators
	 */
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
