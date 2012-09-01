<?php

namespace NetteAddons;

use NetteAddons\Model\Addon;
use Nette;
use Nette\Utils\Html;



/**
 * Form for new addon registration. When importing from GitHub, most of the field should be prefilled.
 * The license input won't be visible when composer.json is available.
 */
class AddAddonForm extends BaseForm
{
	protected function buildForm()
	{
		$this->addText('name', 'Name', 40, 100)
			->setRequired();
		$this->addText('composerName', 'Composer name')
			->setRequired()
			->addRule(self::PATTERN, 'Invalid composer name', '^[a-z]+(-[a-z]+)*/[a-z]+(-[a-z]+)*$')
			->setOption('description', '<vendor>/<project-name>, only lowercase letters and dash separation is allowed');
		$this->addText('shortDescription', 'Short description', 60, 4)
			->setAttribute('class', 'span4')
			->setRequired();
		$this->addTextArea('description', 'Description', 80, 20)
			->setAttribute('class', 'span6');
		$this->addText('defaultLicense', 'License')
			->setRequired()
			->addRule($this->validateLicense, 'Invalid license identifier.')
			->setOption(
				'description',
				Html::el()->setHtml(
					'See <a href="http://www.spdx.org/licenses/">SPDX Open Source License Registry</a> for list of possible identifiers.'
				)
			);
		$this->addText('demo', 'Demo URL:', 60, 500)
			->setAttribute('class', 'span6');
		// $this->addText('tags');
		$this->addSubmit('create', 'Next');
	}



	/**
	 * Sets default values. Used when importing from GitHub.
	 *
	 * @param Addon
	 */
	public function setAddonDefaults(Addon $addon)
	{
		$this->setDefaults(array(
			'name' => $addon->name,
			'shortDescription' => $addon->shortDescription,
			'description' => $addon->description,
			'demo' => $addon->demo
		));
	}



	/**
	 * @param  Nette\Forms\IControl
	 * @return bool
	 */
	public function validateLicense(Nette\Forms\IControl $control)
	{
		$validator = new \Composer\Util\SpdxLicenseIdentifier();
		return $validator->validate($control->getValue());
	}
}
