<?php

namespace NetteAddons\Manage\Forms;

use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;
use Nette\Forms\IControl;
use Nette\Security\IIdentity;
use NetteAddons\Forms\Form;
use NetteAddons\Model\Addon;
use NetteAddons\Model\Facade\AddonManageFacade;
use NetteAddons\Model\Importers\RepositoryImporterManager;
use NetteAddons\Model\Utils\Validators;


class ImportAddonFormFactory extends \Nette\Object
{
	/** @var \NetteAddons\Model\Facade\AddonManageFacade */
	private $manager;

	/** @var \NetteAddons\Model\Importers\RepositoryImporterManager */
	private $importerManager;

	/** @var \NetteAddons\Model\Utils\Validators */
	private $validators;


	public function __construct(
		AddonManageFacade $manager,
		RepositoryImporterManager $importers,
		Validators $validators
	) {
		$this->manager = $manager;
		$this->importerManager = $importers;
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
			$this->validateRepositoryUrlSupported,
			'Sorry, we currently support only repositories from ' . $this->importerManager->getNames() . '.');

		$form['url']->addRule($this->validateRepositoryUrl, 'Repository URL is not valid.');

		$form->addSubmit('sub', 'Load');

		$manager = $this->manager;
		$importerManager = $this->importerManager;
		$validators = $this->validators;
		$form->onSuccess[] = function(Form $form) use($manager, $importerManager, $validators, $user) {
			$values = $form->getValues();

			try {
				$importer = $importerManager->createFromUrl($values->url);
			} catch (\NetteAddons\NotSupportedException $e) {
				$form['url']->addError(
					'Sorry, we currently support only repositories from ' . $importerManager->getNames() . '.'
				);
				return;
			}

			try {
				$addon = $manager->import($importer, $user);
				$addon->type = Addon::TYPE_COMPOSER;

				if ($addon->composerFullName && !$validators->isComposerFullNameUnique($addon->composerFullName)) {
					$form->addError("Addon with composer name '{$addon->composerFullName}' already exists.");
					return;
				}

				$manager->storeAddon($values->token, $addon);
			} catch (\NetteAddons\Utils\HttpException $e) {
				if ($e->getCode() === 404) {
					$form['url']->addError("Repository with URL '{$values->url}' does not exist.");
				} else {
					$importerName = $importer::getName();
					$form['url']->addError("Importing failed because '$importerName' returned error #" . $e->getCode() . '.');
				}
			} catch (\NetteAddons\IOException $e) {
				if ($e->getCode() === 404) {
					$form['url']->addError("Repository with URL '{$values->url}' does not exist.");
				} else {
					$form['url']->addError('Importing failed. Try again later.');
					Debugger::log($e, Debugger::WARNING);
				}
			}
		};

		return $form;
	}


	/**
	 * @param \Nette\Forms\IControl
	 * @return bool
	 */
	public function validateRepositoryUrlSupported(IControl $control)
	{
		return $this->importerManager->isSupported($control->getValue());
	}


	/**
	 * @param \Nette\Forms\IControl
	 * @return bool
	 */
	public function validateRepositoryUrl(IControl $control)
	{
		return $this->importerManager->isValid($control->getValue());
	}
}
