<?php

namespace NetteAddons\Manage\Forms;

use Nette\Utils\Strings,
	Nette\Forms\IControl,
	Nette\Security\IIdentity,
	NetteAddons\Model\Facade\AddonManageFacade,
	NetteAddons\Model\Importers\RepositoryImporterManager,
	NetteAddons\Model\Utils\Validators;

/**
 * @author Patrik Votoček
 *
 * @property-write \Nette\Security\Identity $user
 * @property-read \NetteAddons\Model\Addon $addon
 * @property-read string $token
 */
class ImportAddonForm extends \NetteAddons\Forms\BaseForm
{
	/** @var \NetteAddons\Model\Facade\AddonManageFacade */
	private $manager;

	/** @var \NetteAddons\Model\Importers\RepositoryImporterManager */
	private $importerManager;

	/** @var \NetteAddons\Model\Utils\Validators */
	private $validators;

	/** @var \Nette\Security\IIdentity|NULL */
	private $user;

	/** @var \NetteAddons\Model\Addon|NULL */
	private $addon;

	/** @var string */
	private $token;



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
		parent::__construct();
	}



	/**
	 * @return \NetteAddons\Model\Addon|NULL
	 */
	public function getAddon()
	{
		return $this->addon;
	}



	/**
	 * @param \Nette\Security\IIdentity
	 * @return ImportAddonForm
	 */
	public function setUser(IIdentity $user)
	{
		$this->user = $user;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getToken()
	{
		if (is_null($this->token)) {
			$this->token = Strings::random();
		}
		return $this->token;
	}



	public function buildForm()
	{
		$this->addText('url', 'Repository URL', NULL, 256)
			->setAttribute('autofocus', TRUE)
			->setRequired();

		$this['url']->addRule(
			$this->validateRepositoryUrlSupported,
			'Sorry, we currently support only repositories from ' . $this->importerManager->getNames() . '.');

		$this['url']->addRule($this->validateRepositoryUrl, 'Repository URL is not valid.');

		$this->addSubmit('sub', 'Load');

		$this->onSuccess[] = $this->process;
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



	public function process()
	{
		$values = $this->getValues();

		try {
			$importer = $this->importerManager->createFromUrl($values->url);

		} catch (\NetteAddons\NotSupportedException $e) {
			$this['url']->addError(
				'Sorry, we currently support only repositories from ' . $this->importerManager->getNames() . '.'
			);
			return;
		}

		try {
			$this->addon = $this->manager->import($importer, $this->user);

			if ($this->addon->composerFullName && !$this->validators->isComposerFullNameUnique($this->addon->composerFullName)) {
				$this->addError("Addon with composer name '{$this->addon->composerFullName}' already exists.");
				return;
			}

			$this->manager->storeAddon($this->getToken(), $this->addon);

		} catch (\NetteAddons\Utils\HttpException $e) {
			if ($e->getCode() === 404) {
				$this['url']->addError("Repository with URL '{$values->url}' does not exist.");
			} else {
				$importerName = $importer::getName();
				$this['url']->addError("Importing failed because '$importerName' returned error #" . $e->getCode() . '.');
			}

		} catch (\NetteAddons\IOException $e) {
			if ($e->getCode() === 404) {
				$this['url']->addError("Repository with URL '{$values->url}' does not exist.");
			} else {
				$this['url']->addError('Importing failed. Try again later.');
			}
		}
	}

}
