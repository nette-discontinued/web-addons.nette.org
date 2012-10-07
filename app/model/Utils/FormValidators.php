<?php

namespace NetteAddons\Model\Utils;

use Nette,
	Nette\Forms;



class FormValidators extends Nette\Object
{
	/** composerName regular expression */
	const COMPOSER_NAME_RE = Validators::COMPOSER_NAME_RE;

	/** @var Validators */
	private $validators;



	public function __construct(Validators $validators)
	{
		$this->validators = $validators;
	}



	public function isComposerNameValid(Forms\IControl $control)
	{
		return $this->validators->isComposerNameValid($control->getValue());
	}



	public function isComposerNameUnique(Forms\IControl $control)
	{
		return $this->validators->isComposerNameUnique($control->getValue());
	}



	public function isVersionValid(Forms\IControl $control)
	{
		return $this->validators->isVersionValid($control->getValue());
	}



	public function isLicenseValid(Forms\IControl $control)
	{
		$licenses = $control->getValue();
		if (is_string($licenses)) {
			$licenses = array_map('trim', explode(',', $licenses));
		}
		foreach ($licenses as $license) {
			if (!$this->validators->isLicenseValid($license)) {
				return FALSE;
			}
		}
		return TRUE;
	}
}
