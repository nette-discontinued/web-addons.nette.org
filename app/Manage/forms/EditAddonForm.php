<?php

namespace NetteAddons\Manage\Forms;

use NetteAddons\Model\Facade\AddonManageFacade,
	NetteAddons\Model\Importers\RepositoryImporterManager,
	NetteAddons\Model\Tags,
	NetteAddons\Model\Utils\FormValidators,
	NetteAddons\Model\Utils\Licenses,
	NetteAddons\Model\Addons;


/**
 * Form for new addon registration. When importing from GitHub, most of the field should be prefilled.
 * The license input won't be visible when composer.json is available.
 *
 * @author Patrik Votoček
 *
 * @property string $token
 */
class EditAddonForm extends AddonForm
{
	/** @var \NetteAddons\Model\Addons */
	private $model;



	/**
	 * @param \NetteAddons\Model\Facade\AddonManageFacade
	 * @param \NetteAddons\Model\Importers\RepositoryImporterManager
	 * @param \NetteAddons\Model\Tags
	 * @param \NetteAddons\Model\Utils\FormValidators
	 * @param \NetteAddons\Model\Utils\Licenses
	 * @param \NetteAddons\Model\Addons
	 */
	public function __construct(AddonManageFacade $manager, RepositoryImporterManager $importerManager, Tags $tags, FormValidators $validators, Licenses $licenses, Addons $model)
	{
		parent::__construct($manager, $importerManager, $tags, $validators, $licenses);
		$this->model = $model;
	}



	protected function buildForm()
	{
		parent::buildForm();

		$this->addSubmit('sub', 'Save');

		$this->onSuccess[] = $this->process;
	}



	public function process()
	{
		$values = $this->preProcess($this->getValues(TRUE));

		$addon = $this->getAddon();

		$this->manager->fillAddonWithValues($addon, $values);
		$this->model->update($addon);
	}

}
