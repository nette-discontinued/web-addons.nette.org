<?php

namespace NetteAddons\Manage\Forms;

use Nette\Utils\Strings,
	Nette\Forms\IControl,
	Nette\Security\IIdentity,
	NetteAddons\Forms\Form,
	NetteAddons\Model\Facade\AddonManageFacade,
	NetteAddons\Model\Importers\RepositoryImporterManager,
	NetteAddons\Model\Utils\Validators;

/**
 * @author Patrik Votoček
 */
class ImportAddonFormFactory extends \Nette\Object
{
	/** @var \NetteAddons\Model\Facade\AddonManageFacade */
	private $manager;

	/** @var \NetteAddons\Model\Importers\RepositoryImporterManager */
	private $importerManager;

	/** @var \NetteAddons\Model\Utils\Validators */
	private $validators;



	/**
	 * @param \NetteAddons\Model\Facade\AddonManageFacade
	 * @param \NetteAddons\Model\Importers\RepositoryImporterManager
	 * @param \NetteAddons\Model\Utils\Validators
	 */
	public function __construct(AddonManageFacade $manager, RepositoryImporterManager $importers, Validators $validators)
	{
		$this->manager = $manager;
		$this->importerManager = $importers;
		$this->validators = $validators;
	}




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
