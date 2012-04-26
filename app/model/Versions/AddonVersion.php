<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Utils\Strings;


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AddonVersion extends Nette\Object
{

	/** @var string */
	public $version;

	/** @var string */
	public $license;

	/** @var string Name of the ZIP file. */
	public $filename;

	/** @var string[] */
	public $require = array();

	/** @var string[] */
	public $suggest = array();

	/** @var string[] */
	public $provide = array();

	/** @var string[] */
	public $replace = array();

	/** @var string[] */
	public $conflict = array();

	/** @var string[] */
	public $recommend = array();

	/** @var array */
	public $composerJson = array();



	/**
	 * Builds the filename.
	 * @param \NetteAddons\Model\Addon $addon
	 * @return string
	 */
	public function getFilename(Addon $addon)
	{
		return Strings::webalize($addon->composerName) . '-' . $this->version . '.zip';
	}

}
