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
class RepositoryImporterFactory extends Nette\Object
{
	/** @var callable[]|array (name => callback) */
	private $factories = array();
	/** @var string[]|array (name => class) */
	private $classes = array();



	/**
	 * @param string
	 * @param callable
	 * @param string
	 */
	public function addImporter($name, $factory, $class)
	{
		if (isset($this->factories[$name])) {
			throw new \NetteAddons\InvalidStateException("Importer '$name' already registered");
		}
		if (!is_callable($factory)) {
			throw new \NetteAddons\InvalidArgumentException('Factory is not callable');
		}
		if (!is_subclass_of($class, 'NetteAddons\Model\IAddonImporter')) {
			throw new \NetteAddons\InvalidArgumentException("Class '$class' does not implement IAddonImporter");
		}
		$this->factories[$name] = $factory;
		$this->classes[$name] = $class;
	}



	/**
	 * Creates repository importer from url.
	 *
	 * @param  Url
	 * @return IAddonImporter
	 * @throws \NetteAddons\NotSupportedException
	 */
	public function createFromUrl(Url $url)
	{
		if ($url->getHost() === 'github.com') {
			return callback($this->factories['github'])->invoke((string) $url);
		} else {
			throw new \NetteAddons\NotSupportedException("Currently only GitHub is supported.");
		}
	}
}
