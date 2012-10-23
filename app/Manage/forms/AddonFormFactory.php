<?php

namespace NetteAddons\Manage\Forms;

use NetteAddons\Model\Facade\AddonManageFacade,
	NetteAddons\Model\Importers\RepositoryImporterManager,
	NetteAddons\Model\Tags,
	NetteAddons\Model\Utils\FormValidators,
	NetteAddons\Model\Utils\Licenses;


/**
 * @author Patrik Votoček
 *
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



	/**
	 * @param \NetteAddons\Model\Facade\AddonManageFacade
	 * @param \NetteAddons\Model\Importers\RepositoryImporterManager
	 * @param \NetteAddons\Model\Tags
	 * @param \NetteAddons\Model\Utils\FormValidators
	 * @param \NetteAddons\Model\Utils\Licenses
	 */
	public function __construct(AddonManageFacade $manager, RepositoryImporterManager $importerManager, Tags $tags, FormValidators $validators, Licenses $licenses)
	{
		$this->manager = $manager;
		$this->importerManager = $importerManager;
		$this->tags = $tags;
		$this->validators = $validators;
		$this->licenses = $licenses;
	}



	/**
	 * @param string
	 * @param string
	 * @return AddonForm
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
