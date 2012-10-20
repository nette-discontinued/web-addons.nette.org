<?php

namespace NetteAddons\Forms;

use Nette\Forms\IControl,
	NetteAddons\Model\Importers\RepositoryImporterManager;

/**
 * @author Patrik Votoček
 */
class ImportAddonForm extends BaseForm
{
	/** @var \NetteAddons\Model\Importers\RepositoryImporterManager */
	private $importerManager;



	/**
	 * @param \NetteAddons\Model\Importers\RepositoryImporterManager
	 */
	public function __construct(RepositoryImporterManager $importerManager)
	{
		parent::__construct();
		$this->importerManager = $importerManager;
	}



	protected function buildForm()
	{
		$this->addText('url', 'Repository URL', NULL, 256)
			->setAttribute('autofocus', TRUE)
			->setRequired();

		$this['url']->addRule(
			$this->validateRepositoryUrlSupported,
			'Sorry we are supported only ' . $this->importerManager->getNames() . ' repositories');

		$this['url']->addRule($this->validateRepositoryUrl, 'It is not valid repository URL');

		$this->addSubmit('import', 'Import');
	}



	public function validateRepositoryUrlSupported(IControl $control)
	{
		return $this->importerManager->isSupported($control->getValue());
	}



	public function validateRepositoryUrl(IControl $control)
	{
		return $this->importerManager->isValid($control->getValue());
	}

}
