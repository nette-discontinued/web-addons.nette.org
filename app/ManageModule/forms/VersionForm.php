<?php

namespace NetteAddons\ManageModule\Forms;

use Nette\Utils\Strings,
	Nette\Forms\Controls\UploadControl,
	NetteAddons\Model\Addon,
	NetteAddons\Model\AddonVersions,
	NetteAddons\Model\Utils\VersionParser,
	NetteAddons\Model\Utils\Licenses,
	NetteAddons\Model\Utils\FormValidators,
	NetteAddons\Model\Facade\AddonManageFacade;



/**
 * @author Patrik Votoček
 *
 * @property-write \NetteAddons\Model\Addon $addon
 */
abstract class VersionForm extends \NetteAddons\Forms\BaseForm
{
	/** @var \NetteAddons\Model\Facade\AddonManageFacade */
	protected $manager;

	/** @var \NetteAddons\Model\Utils\VersionParser */
	protected $versionParser;

	/** @var \NetteAddons\Model\Utils\FormValidators */
	private $validators;

	/** @var \NetteAddons\Model\Utils\Licenses */
	private $licenses;

	/** @var \NetteAddons\Model\AddonVersions */
	protected $model;

	/** @var \NetteAddons\Model\Addon */
	protected $addon;



	/**
	 * @param \NetteAddons\Model\Facade\AddonManageFacade
	 * @param \NetteAddons\Model\Utils\VersionParser
	 * @param \NetteAddons\Model\Utils\FormValidators
	 * @param \NetteAddons\Model\Utils\Licenses
	 * @param \NetteAddons\Model\AddonVersions
	 */
	public function __construct(AddonManageFacade $manager, VersionParser $versionParser, FormValidators $validators, Licenses $licenses, AddonVersions $model)
	{
		$this->manager = $manager;
		$this->versionParser = $versionParser;
		$this->validators = $validators;
		$this->licenses = $licenses;
		$this->model = $model;
		parent::__construct();
	}



	/**
	 * @param \NetteAddons\Model\Addon
	 * @return VersionForm
	 */
	public function setAddon(Addon $addon)
	{
		$this->addon = $addon;

		$license = $this->addon->defaultLicense;
		if (is_string($license)) {
			$license = array_map('trim', explode(',', $license));
		}
		$this->setDefaults(array(
			'license' => $license,
		));

		return $this;
	}



	protected function buildForm()
	{
		$this->addText('version', 'Version', NULL, 100)
			->setRequired()
			->addRule($this->validators->isVersionValid, 'Invalid version.');

		$this->addMultiSelect('license', 'License', $this->licenses->getLicenses(TRUE))
			->setAttribute('class', 'chzn-select')
			->setRequired()
			->addRule($this->validators->isLicenseValid, 'Invalid license identifier.');

		$providers = array(
			'link' => 'Provide link to distribution archive.',
			'upload' => 'Upload archive to this site.',
		);
		$this->addSelect('how', 'How would you like to provide source codes?', $providers)
			->setRequired()
			->addCondition(self::EQUAL, 'link')->toggle('xlink')
			->addCondition(self::EQUAL, 'upload')->toggle('xupload');

		$this->addText('archiveLink', 'Link to ZIP archive')
			->setOption('id', 'xlink')
			->addConditionOn($this['how'], self::EQUAL, 'link')
			->addRule(self::FILLED)
			->addRule(self::URL);

		$this->addUpload('archive', 'Archive')
			->setOption('id', 'xupload')
			->addConditionOn($this['how'], self::EQUAL, 'upload')
			->addRule(self::FILLED)
			->addRule($this->isArchiveValid, 'Only ZIP files are allowed.');
	}



	/**
	 * @param \Nette\Forms\Controls\UploadControl
	 * @return bool
	 */
	public function isArchiveValid(UploadControl $control)
	{
		return Strings::endsWith($control->value->name, '.zip');
	}

}
