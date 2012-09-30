<?php

namespace NetteAddons;

use NetteAddons\Model\Addon;
use NetteAddons\Model\Tags;
use NetteAddons\Model\Utils\FormValidators;
use Nette;
use Nette\Utils\Html;



/**
 * Form for new addon registration. When importing from GitHub, most of the field should be prefilled.
 * The license input won't be visible when composer.json is available.
 */
class AddAddonForm extends BaseForm
{
	/** @var FormValidators */
	private $validators;

	/** @var Tags */
	private $tags;

	/** @var Model\Utils\Licenses */
	private $licenses;



	public function __construct(FormValidators $validators, Tags $tags, Model\Utils\Licenses $licenses)
	{
		$this->validators = $validators;
		$this->tags = $tags;
		$this->licenses = $licenses;
		parent::__construct();
	}



	protected function buildForm()
	{
		$this->addText('name', 'Name', 100)
			->setAttribute('class', 'large-input')
			->addRule(self::MAX_LENGTH, NULL, 100)
			->setRequired();
		$this->addText('composerName', 'Composer name', 100)
			->setRequired()
			->addRule(self::MAX_LENGTH, NULL, 100)
			->addRule(self::PATTERN, 'Invalid composer name', FormValidators::COMPOSER_NAME_RE)
			->addRule($this->validators->isComposerNameUnique, 'This composer name has been already taken.')
			->setOption('description', '<vendor>/<project-name>, only lowercase letters and dash separation is allowed');
		$this->addTextArea('shortDescription', 'Short description', 80, 2)
			->addRule(self::MAX_LENGTH, NULL, 250)
			->setRequired();
		$this->addTextArea('description', 'Description', 80, 20)
			->setRequired();
		$this->addSelect('descriptionFormat', 'Description format', array('texy' => 'Texy!', 'markdown' => 'Markdown'))
			->setDefaultValue('texy')
			->setRequired();
		$this->addMultiSelect('defaultLicense', 'Default license', $this->licenses->getLicenses())
			->setAttribute('class', 'chzn-select')
			->setAttribute('style', 'width: 500px;')
			->setRequired()
			->addRule($this->validators->isLicenseValid, 'Invalid license identifier.');
		$this->addMultiSelect('tags', 'Categories', $this->getCategories())
				->setAttribute('class', 'chzn-select')
				->setAttribute('style', 'width: 500px;');
		$this->addText('repository', 'Repository URL', 60, 500)
			->setType('url')
			->addCondition(self::FILLED)
				->addRule(self::URL);
		$this->addText('demo', 'Demo URL', 60, 500)
			->setType('url')
			->addCondition(self::FILLED)
				->addRule(self::URL);
		$this->addSubmit('create', 'Next');
	}



	/**
	 * Sets default values. Used when importing from GitHub.
	 *
	 * @param Addon
	 */
	public function setAddonDefaults(Addon $addon)
	{
		$license = $addon->defaultLicense;
		if (is_string($license)) {
			$license = array_map('trim', explode(',', $license));
		}
		$this->setDefaults(array(
			'name' => $addon->name,
			'shortDescription' => $addon->shortDescription,
			'description' => $addon->description,
			'descriptionFormat' => $addon->descriptionFormat,
			'defaultLicense' => $license,
			'repository' => $addon->repository,
			'demo' => $addon->demo,
			'tags' => $addon->getTagsIds(),
		));
	}



	private function getCategories()
	{
		$categories = array();
		foreach ($this->tags->findMainTags() as $tag) {
			$categories[$tag->id] = $tag->name;
		}
		return $categories;
	}
}
