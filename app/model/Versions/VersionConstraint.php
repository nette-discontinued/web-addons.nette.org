<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class VersionConstraint extends Nette\Object
{

	/** @var Version */
	private $version;

	/** @var string */
	private $comparator;



	/**
	 * @param string|Version $version
	 * @param string $comparator
	 */
	public function __construct($version, $comparator)
	{
		$this->version = Version::create($version);
		$this->comparator = $comparator;
	}



	/**
	 * @todo
	 * @param string $version
	 * @return bool
	 */
	public function match($version)
	{
		if (!$other = Version::create($version)) {
			return FALSE;
		}

		return version_compare($this->version, $other, $this->comparator);
	}



	/**
	 * Compares two values.
	 *
	 * @param string $l
	 * @param string $operator
	 * @param string $r
	 * @throws \NetteAddons\InvalidArgumentException
	 * @return bool
	 */
	public static function compare($l, $operator, $r)
	{
		switch ($operator) {
			case '>':
				return $l > $r;
			case '>=':
				return $l >= $r;
			case '<':
				return $l < $r;
			case '<=':
				return $l <= $r;
			case '=':
			case '==':
				return $l == $r;
			case '!':
			case '!=':
			case '<>':
				return $l != $r;
		}

		throw new \NetteAddons\InvalidArgumentException("Unknown operator $operator.");
	}



	/**
	 * @internal
	 * @param string $version
	 * @return array
	 */
	private static function normalize($version)
	{
		return explode('.', Strings::replace(str_replace('*', '255', $version), array('~[^a-z0-9]+~' => '.')));
	}

}
