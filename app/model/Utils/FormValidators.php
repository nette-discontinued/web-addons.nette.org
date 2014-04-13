<?php

namespace NetteAddons\Model\Utils;

use Nette\Forms\IControl;


class FormValidators extends \Nette\Object
{
	/** composerName regular expression */
	const COMPOSER_NAME_RE = Validators::COMPOSER_NAME_RE;

	/** @var Validators */
	private $validators;


	public function __construct(Validators $validators)
	{
		$this->validators = $validators;
	}


	/**
	 * @param \Nette\Forms\IControl
	 * @return bool
	 */
	public function isComposerVendorNameProtectionFree(IControl $control)
	{
		return $this->validators->isComposerVendorNameProtectionFree($control->getValue());
	}


	/**
	 * @param \Nette\Forms\IControl
	 * @return bool
	 */
	public function isComposerFullNameValid(IControl $control)
	{
		return $this->validators->isComposerFullNameValid($control->getValue());
	}


	/**
	 * @param \Nette\Forms\IControl
	 * @return bool
	 */
	public function isComposerFullNameUnique(IControl $control)
	{
		return $this->validators->isComposerFullNameUnique($control->getValue());
	}


	/**
	 * @param \Nette\Forms\IControl
	 * @return bool
	 */
	public function isVersionValid(IControl $control)
	{
		return $this->validators->isVersionValid($control->getValue());
	}


	/**
	 * @param \Nette\Forms\IControl
	 * @return bool
	 */
	public function isLicenseValid(IControl $control)
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
