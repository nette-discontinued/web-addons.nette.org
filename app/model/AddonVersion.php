<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Utils\Strings;


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AddonVersion extends Nette\Object
{

	/**
	 * @var string
	 */
	public $version;

	/**
	 * @var string
	 */
	public $license;

	/**
	 * @var string Name of the ZIP file.
	 */
	public $filename;

	/**
	 * @var array|string[]
	 */
	public $require = array();

	/**
	 * @var array|string[]
	 */
	public $suggest = array();

	/**
	 * @var array|string[]
	 */
	public $provide = array();

	/**
	 * @var array|string[]
	 */
	public $replace = array();

	/**
	 * @var array|string[]
	 */
	public $conflict = array();

	/**
	 * @var array|string[]
	 */
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
