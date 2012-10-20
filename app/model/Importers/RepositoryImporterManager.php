<?php

namespace NetteAddons\Model\Importers;

use Nette,
	Nette\Http\Url,
	Nette\Utils\Strings,
	NetteAddons\Model\IAddonImporter;



/**
 * Factory for repository importers.
 *
 * @author Jan Tvrdík
 * @author Patrik Votoček
 */
class RepositoryImporterManager extends Nette\Object
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
			throw new \NetteAddons\InvalidStateException("Importer '$id' already registered");
		}
		if (!is_callable($factory)) {
			throw new \NetteAddons\InvalidArgumentException('Factory is not callable');
		}
		if (!is_subclass_of($class, 'NetteAddons\Model\IAddonImporter')) {
			throw new \NetteAddons\InvalidArgumentException("Class '$class' does not implement IAddonImporter");
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
			if (callback($class, 'isSupported')->invoke($url)) {
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
		return callback($this->classes[$name], 'isValid')->invoke($url);
	}



	/**
	 * @param string
	 * @return string
	 */
	public function normalizeUrl($url)
	{
		$name = $this->getIdByUrl($url);
		if (is_null($name)) {
			return $url;
		}
		$data = callback($this->classes[$name], 'normalizeUrl')->invoke($url);
		if (is_null($data)) {
			return $url;
		}
		return $data;
	}



	/**
	 * @param bool
	 * @return array|string
	 */
	public function getNames($asArray = FALSE)
	{
		$names = array();
		foreach ($this->classes as $class) {
			$names[] = callback($class, 'getName')->invoke();
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
			return callback($this->factories[$name])->invoke($url);
		} else {
			throw new \NetteAddons\NotSupportedException('We support only ' . $this->getNames() . '.');
		}
	}
}
