<?php

namespace NetteAddons\Manage\Forms;

use Nette\Security\IIdentity;
use NetteAddons\Model\Addon;
use NetteAddons\Model\AddonVersions;
use NetteAddons\Model\Utils\VersionParser;
use NetteAddons\Model\Utils\Licenses;
use NetteAddons\Model\Utils\FormValidators;
use NetteAddons\Model\Facade\AddonManageFacade;


class AddVersionFormFactory extends \Nette\Object
{
	/** @var \NetteAddons\Model\Facade\AddonManageFacade */
	private $manager;

	/** @var \NetteAddons\Model\Utils\VersionParser */
	private $versionParser;

	/** @var \NetteAddons\Model\Utils\FormValidators */
	private $validators;

	/** @var \NetteAddons\Model\Utils\Licenses */
	private $licenses;

	/** @var \NetteAddons\Model\AddonVersions */
	private $model;

	/** @var \NetteAddons\Model\Addon */
	protected $addon;


	public function __construct(
		AddonManageFacade $manager,
		VersionParser $versionParser,
		FormValidators $validators,
		Licenses $licenses,
		AddonVersions $model
	) {
		$this->model = $model;
		$this->manager = $manager;
		$this->versionParser = $versionParser;
		$this->validators = $validators;
		$this->licenses = $licenses;
	}


	/**
	 * @param \NetteAddons\Model\Addon
	 * @param \Nette\Security\IIdentity
	 * @param string
	 * @return VersionForm
	 */
	public function create(Addon $addon, IIdentity $user, $token)
	{
		$form = new VersionForm($this->validators, $this->licenses, $addon);

		$form->addHidden('token', $token);
		$form->addSubmit('sub', 'Save');

		$model = $this->model;
		$manager = $this->manager;
		$versionParser = $this->versionParser;
		$form->onSuccess[] = function(VersionForm $form) use($model, $manager, $addon, $versionParser, $user) {
			$values = $form->getValues();

			try {
				$version = $manager->addVersionFromValues($addon, $values, $user, $versionParser);
			} catch (\NetteAddons\IOException $e) {
				$form['archive']->addError('Uploading file failed.');
				return;
			}

			if ($addon->id) {
				try {
					$model->add($version);
				} catch (\NetteAddons\DuplicateEntryException $e) {
					$form['version']->addError(sprintf("Version '%s' already exists.", $version->version));
				}
			} else {
				$manager->storeAddon($values->token, $addon);
			}
		};

		return $form;
	}
}
