<?php

namespace NetteAddons\Manage\Forms;

use NetteAddons\Model\Facade\AddonManageFacade;
use NetteAddons\Model\Importers\RepositoryImporterManager;
use NetteAddons\Model\Tags;
use NetteAddons\Model\Utils\FormValidators;
use NetteAddons\Model\Utils\Licenses;
use NetteAddons\Model\Addons;
use NetteAddons\Model\Addon;


/**
 * Form for new addon registration. When importing from GitHub, most of the fields should be prefilled.
 * The license input won't be visible when composer.json is available.
 *
 * @property string $token
 */
class EditAddonFormFactory extends AddonFormFactory
{
	/** @var \NetteAddons\Model\Addons */
	private $model;


	public function __construct(
		AddonManageFacade $manager,
		RepositoryImporterManager $importerManager,
		Tags $tags,
		FormValidators $validators,
		Licenses $licenses,
		Addons $model
	) {
		parent::__construct($manager, $importerManager, $tags, $validators, $licenses);

		$this->model = $model;
	}


	/**
	 * @param \NetteAddons\Model\Addon
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
