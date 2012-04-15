<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Utils\Strings;



/**
 * @link http://semver.org/ for version specification
 */
class Version extends Nette\Object
{

	const REGEXP = '^=?(>=?|<=?|==?)?v?((?:(?:\\*|\d{1,3})\\.?){3,4})(?:[-.]?(?P<pre>[-.a-z0-9]+))?(?:[+.]?(?P<build>[-.a-z0-9]+))?$';

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var string
	 */
	private $comparator;

	/**
	 * @var string
	 */
	private $preRelease;

	/**
	 * @var string
	 */
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
			$end = FALSE;
			$part = function ($comparator, $me, $other) use (&$end) {
				$otherSteps = Version::normalize($other);
				foreach (Version::normalize($me) as $i => $step) {
					if (!isset($otherSteps[$i])) {
						return TRUE;
					}

					if (Version::compare($comparator, $step, $otherSteps[$i]) === FALSE) {
						return FALSE;

					} elseif (trim($comparator, '=')) {
						return $end = TRUE;
					}
				}

				return TRUE;
			};

			if ($part($this->comparator, $this->version, $other->version) === FALSE) {
				return FALSE;

			} elseif (!$end && ($this->preRelease && !$other->preRelease) || ($other->preRelease && !$this->preRelease)) {
				return !$part($this->comparator, $this->preRelease ? -1 : 1, $other->preRelease ? -1 : 1);

			} elseif (!$end && $part($this->comparator, $this->preRelease, $other->preRelease) === FALSE) {
				return FALSE;

			} elseif (!$end && $part($this->comparator, $this->build, $other->build) === FALSE) {
				return FALSE;
			}

			return TRUE;
		}

		return FALSE;
	}



	/**
	 * @internal
	 * @param string $version
	 * @return array
	 */
	public static function normalize($version)
	{
		return explode('.', Strings::replace(str_replace('*', '255', $version), array('~[^a-z0-9]+~' => '.')));
	}



	/**
	 * @internal
	 * @param string $sign
	 * @param string $version
	 * @param string $other
	 * @return bool
	 */
	public static function compare($sign, $version, $other)
	{
		$version = strtr($version, '*', '255');
		$other = strtr($other, '*', '255');

		switch ($sign) {
			case '>=':
				return $version >= $other;
				break;

			case '<=':
				return $version <= $other;
				break;

			case '==':
				return $version == $other;
				break;
		}

		return FALSE;
	}



	/**
	 * @param string $version
	 * @return Version
	 */
	public static function create($version)
	{
		if ($m = Strings::match($version, '~' . static::REGEXP . '~i')) {
			$version =  new Version(!empty($m[2]) ? $m[2] : NULL);
			$version->comparator = str_pad(!empty($m[1]) ? $m[1] : '==', 2, '=');

			if (!empty($m['pre'])) {
				$version->preRelease = $m[3];
			}

			if (!empty($m['build'])) {
				$version->build = $m[4];
			}

			return $version;
		}
	}
}
