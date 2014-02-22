<?php

namespace NetteAddons\Model\Importers;

use Nette\Utils\Strings;
use Nette\Utils\Callback;
use Nette\Reflection\ClassType;
use NetteAddons\Model\IAddonImporter;


class RepositoryImporterManager extends \Nette\Object
{
	/** @var callable[]|array (id => callback) */
	private $factories = array();

	/** @var string[]|array (id => class) */
	private $classes = array();


	/**
	 * @param string
	 * @param callable
	 * @param string
	 */
	public function addImporter($id, $factory, $class)
	{
		if (isset($this->factories[$id])) {
			throw new \NetteAddons\InvalidStateException("Importer '$id' is already registered");
		}

		if (!is_callable($factory)) {
			throw new \NetteAddons\InvalidArgumentException('Factory is not callable.');
		}

		$classRef = new ClassType($class);
		if (!$classRef->implementsInterface('NetteAddons\Model\IAddonImporter')) {
			throw new \NetteAddons\InvalidArgumentException("Class '$class' does not implement IAddonImporter.");
		}

		$this->factories[$id] = $factory;
		$this->classes[$id] = $class;
	}



	/**
	 * @param string
	 * @return string|NULL
	 */
	public function getIdByUrl($url)
	{
		foreach ($this->classes as $name => $class) {
			if (Callback::invoke(array($class, 'isSupported'), $url)) {
				return $name;
			}
		}

		return NULL;
	}


	/**
	 * @param string
	 * @return bool
	 */
	public function isSupported($url)
	{
		return !is_null($this->getIdByUrl($url));
	}


	/**
	 * @param string
	 * @return bool
	 */
	public function isValid($url)
	{
		$name = $this->getIdByUrl($url);

		if (is_null($name)) {
			return FALSE;
		}

		return Callback::invoke(array($this->classes[$name], 'isValid'), $url);
	}


	/**
	 * @param string
	 * @return string
	 */
	public function normalizeUrl($url)
	{
		$name = $this->getIdByUrl($url);
		if ($name !== NULL) {
			$data = Callback::invoke(array($this->classes[$name], 'normalizeUrl'), $url);
			if ($data !== NULL) {
				return $data;
			}
		}

		return Strings::match($url, '~^[a-z+]+://~i') ? $url : 'http://' . $url;
	}


	/**
	 * @param bool
	 * @return array|string
	 */
	public function getNames($asArray = FALSE)
	{
		$names = array();
		foreach ($this->classes as $class) {
			$names[] = Callback::invoke(array($class, 'getName'));
		}

		return $asArray ? $names : implode(', ', $names);
	}


	/**
	 * Creates repository importer from url.
	 *
	 * @param  string|\Nette\Http\Url
	 * @return IAddonImporter
	 * @throws \NetteAddons\NotSupportedException
	 */
	public function createFromUrl($url)
	{
		$url = (string) $url;
		if (($name = static::getIdByUrl($url)) != NULL) {
			return Callback::invoke($this->factories[$name], $url);
		} else {
			throw new \NetteAddons\NotSupportedException('We support only ' . $this->getNames() . '.');
		}
	}
}
