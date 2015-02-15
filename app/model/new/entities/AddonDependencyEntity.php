<?php

namespace NetteAddons\Model;

use Nette\Utils\Validators;

/**
 * @property-read string $composerFullName
 * @property-read string $version
 * @property-read string $type
 * @property-read string $dependencyName
 * @property-read string $dependencyVersion
 */
class AddonDependencyEntity extends \Nette\Object
{
	const TYPE_REQUIRE = 'require';
	const TYPE_REQUIRE_DEV = 'require-dev';
	const TYPE_CONFLICT = 'conflict';
	const TYPE_REPLACE = 'replace';
	const TYPE_PROVIDE = 'provide';

	/** @var string */
	private $composerFullName;
	/** @var string */
	private $version;
	/** @var string */
	private $type;
	/** @var string */
	private $dependencyName;
	/** @var string */
	private $dependencyVersion;

	/**
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 */
	public function __construct($composerFullName, $version, $type, $dependencyName, $dependencyVersion)
	{
		Validators::assert($composerFullName, 'string', 'composerFullName');
		Validators::assert($composerFullName, 'pattern:(([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]+))', 'composerFullName');
		Validators::assert($version, 'string', 'version');
		Validators::assert($type, 'string', 'type');
		Validators::assert($dependencyName, 'string', 'dependencyName');
		$patterns = array(
			'pattern:(([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]+))',
			'pattern:(php)',
			'pattern:(hhvm)',
			'pattern:(ext-(?:\w+))',
			'pattern:(lib-(?:\w+))',
		);
		Validators::assert($dependencyName, implode('|', $patterns), 'dependencyName');
		Validators::assert($dependencyVersion, 'string', 'dependencyVersion');
		if (!in_array($type, $this->getTypes())) {
			throw new \Nette\Utils\AssertionException('Type "' . $type . '" not supported.');
		}
		$this->composerFullName = $composerFullName;
		$this->version = $version;
		$this->type = $type;
		$this->dependencyName = $dependencyName;
		$this->dependencyVersion = $dependencyVersion;
	}

	/**
	 * @return string
	 */
	public function getComposerFullName()
	{
		return $this->composerFullName;
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getDependencyName()
	{
		return $this->dependencyName;
	}

	/**
	 * @return string
	 */
	public function getDependencyVersion()
	{
		return $this->dependencyVersion;
	}

	/**
	 * @return string[]|array
	 */
	private function getTypes()
	{
		return array(
			self::TYPE_REQUIRE,
			self::TYPE_REQUIRE_DEV,
			self::TYPE_CONFLICT,
			self::TYPE_REPLACE,
			self::TYPE_PROVIDE,
		);
	}
}
