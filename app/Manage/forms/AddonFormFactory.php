<?php

namespace NetteAddons\Manage\Forms;

use NetteAddons\Model\Facade\AddonManageFacade;
use NetteAddons\Model\Importers\RepositoryImporterManager;
use NetteAddons\Model\Tags;
use NetteAddons\Model\Utils\FormValidators;
use NetteAddons\Model\Utils\Licenses;


/**
 * @property string $token
 */
abstract class AddonFormFactory extends \Nette\Object
{
	/** @var \NetteAddons\Model\Facade\AddonManageFacade */
	protected $manager;

	/** @var \NetteAddons\Model\Importers\RepositoryImporterManager */
	private $importerManager;

	/** @var \NetteAddons\Model\Tags */
	private $tags;

	/** @var \NetteAddons\Model\Utils\FormValidators */
	private $validators;

	/** @var \NetteAddons\Model\Utils\Licenses */
	private $licenses;

	/** @var array */
	private $descriptionFormats = array();


	public function __construct(
		AddonManageFacade $manager,
		RepositoryImporterManager $importerManager,
		Tags $tags,
		FormValidators $validators,
		Licenses $licenses
	) {
		$this->manager = $manager;
		$this->importerManager = $importerManager;
		$this->tags = $tags;
		$this->validators = $validators;
		$this->licenses = $licenses;
	}


	/**
	 * @param string
	 * @param string
	 * @return AddonFormFactory
	 */
	public function addDescriptionFormat($id, $name)
	{
		$this->descriptionFormats[$id] = $name;
		return $this;
	}


	/**
	 * @return AddonForm
	 */
	protected function createForm()
	{
		return new AddonForm(
			$this->manager,
			$this->importerManager,
			$this->tags,
			$this->validators,
			$this->licenses,
			$this->descriptionFormats
		);
	}
}
