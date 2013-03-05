<?php

namespace NetteAddons\Manage\Forms;

use NetteAddons\Model\Facade\AddonManageFacade,
	NetteAddons\Model\Importers\RepositoryImporterManager,
	NetteAddons\Model\Tags,
	NetteAddons\Model\Utils\FormValidators,
	NetteAddons\Model\Utils\Licenses,
	NetteAddons\Model\Addons,
	NetteAddons\Model\Addon;


/**
 * Form for new addon registration. When importing from GitHub, most of the fields should be prefilled.
 * The license input won't be visible when composer.json is available.
 *
 * @author Patrik Votoček
 *
 * @property string $token
 */
class EditAddonFormFactory extends AddonFormFactory
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



	/**
	 * @param Addon
	 * @return AddonForm
	 */
	public function create(Addon $addon)
	{
		$form = $this->createForm();

		$form->addSubmit('sub', 'Save');

		$manager = $this->manager;
		$model = $this->model;
		$form->onSuccess[] = function(AddonForm $form) use($manager, $model) {
			$addon = $form->getAddon();
			$values = $form->getValues(TRUE);

			$manager->fillAddonWithValues($addon, $values);
			$model->update($addon);
		};

		$form->setAddon($addon);

		return $form;
	}

}
