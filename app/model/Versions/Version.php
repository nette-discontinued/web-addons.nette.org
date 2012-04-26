<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Utils\Strings;



/**
 * @link http://semver.org/ for version specification
 */
class Version extends Nette\Object
{

	const REGEXP = '^(=?>=?|=?<=?|==?)?v?((?:(?:\\*|\d{1,3})\\.?){3,4})(?:[-.]?(?P<pre>[-.a-z0-9]+))?(?:[+.]?(?P<build>[-.a-z0-9]+))?$';

	/** @var string */
	private $version;

	/** @var string */
	private $comparator;

	/** @var string */
	private $preRelease;

	/** @var string */
	private $build;



	/**
	 * @param string $version
	 */
	public function __construct($version)
	{
		if ($version instanceof Version) {
			$this->version = $version->version;

		} else {
			$this->version = $version;
		}
	}



	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version .
			($this->preRelease ? '-' . $this->preRelease : NULL) .
			($this->build ? '+' . $this->build : NULL);
	}



	/**
	 * @return array
	 */
	public function getParts()
	{
		return array(
			'version' => $this->version,
			'preRelease' => $this->preRelease,
			'build' => $this->build,
		);
	}



	/**
	 * @return bool
	 */
	public function isValid()
	{
		return (bool)static::create($this->getVersion());
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getVersion();
	}



	/**
	 * @param string $version
	 * @return bool
	 */
	public function match($version)
	{
		if ($other = static::create($version)) {
			$constraint = new VersionConstraint($this, $this->comparator);
			return $constraint->match($other);
		}

		return FALSE;
	}



	/**
	 * @param string $version
	 * @return Version
	 */
	public static function create($version)
	{
		if ($version instanceof Version) {
			return $version;
		}

		if ($m = Strings::match($version, '~' . static::REGEXP . '~i')) {
			$created =  new Version(!empty($m[2]) ? $m[2] : NULL);
			$created->comparator = !empty($m[1]) ? strtr($m[1], array(
				'=>' => '>=',
				'=<' => '<=',
			)) : '==';

			if (!empty($m['pre'])) {
				$created->preRelease = $m[3];
			}

			if (!empty($m['build'])) {
				$created->build = $m[4];
			}

			return $created;
		}
	}
}
