<?php

namespace NetteAddons\Manage\Forms;

use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;
use Nette\Security\IIdentity;
use NetteAddons\Forms\Form;
use NetteAddons\Model\Addon;
use NetteAddons\Model\AddonResources;
use NetteAddons\Model\Facade\AddonManageFacade;
use NetteAddons\Model\Importers\AddonVersionImporters\PackagistImporter;
use NetteAddons\Model\Utils\Validators;


class ImportAddonFormFactory extends \Nette\Object
{
	/** @var \NetteAddons\Model\Facade\AddonManageFacade */
	private $manager;

	/** @var \NetteAddons\Model\Importers\AddonVersionImporters\PackagistImporter */
	private $packagistImporter;

	/** @var \NetteAddons\Model\Utils\Validators */
	private $validators;


	public function __construct(
		AddonManageFacade $manager,
		PackagistImporter $packagistImporter,
		Validators $validators
	) {
		$this->manager = $manager;
		$this->packagistImporter = $packagistImporter;
		$this->validators = $validators;
	}


	/**
	 * @param \Nette\Security\IIdentity
	 * @return \NetteAddons\Forms\Form
	 */
	public function create(IIdentity $user)
	{
		$form = new Form;

		$form->addHidden('token', Strings::random());
		$form->addText('url', 'Repository URL', NULL, 256)
			->setAttribute('autofocus', TRUE)
			->setRequired();

		$form['url']->addRule(
			Form::PATTERN,
			'Sorry, we currently support only packagist.',
			'(https?\://)?packagist\.org/packages/([a-z0-9]+(?:-[a-z0-9]+)*)/([a-z0-9]+(?:-[a-z0-9]+)*)'
		);

		$form->addSubmit('sub', 'Load');

		$manager = $this->manager;
		$validators = $this->validators;
		$form->onSuccess[] = function(Form $form) use($manager, $validators, $user) {
			$values = $form->getValues();

			try {
				$addonEntity = $this->packagistImporter->getAddon($values['url']);

				if (!$validators->isComposerFullNameUnique($addonEntity->getComposerFullName())) {
					$form->addError("Addon with composer name '{$addonEntity->getComposerFullName()}' already exists.");
					return;
				}

				$addon = new Addon;

				// Back compatability
				$addon->composerFullName = $addonEntity->getComposerFullName();
				$addon->userId = $user->getId();
				$addon->shortDescription = $addonEntity->getPerex();
				$addon->type = Addon::TYPE_COMPOSER;
				$addon->defaultLicense = array();
				$addon->resources[AddonResources::RESOURCE_GITHUB] = $addonEntity->getGithub();
				$addon->resources[AddonResources::RESOURCE_PACKAGIST] = $addonEntity->getPackagist();

				$manager->storeAddon($values->token, $addon);
			} catch (\NetteAddons\Model\Importers\AddonVersionImporters\AddonNotFoundException $e) {
				$form['url']->addError("Package with URL '{$values->url}' does not exist.");
			} catch (\Exception $e) {
				$form['url']->addError('Importing failed. Try again later.');
				Debugger::log($e, Debugger::WARNING);
			}
		};

		return $form;
	}
}
