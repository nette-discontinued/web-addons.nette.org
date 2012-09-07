<?php

namespace NetteAddons\Model\Importers;

use NetteAddons\Model\IAddonImporter;
use Nette;
use Nette\Http\Url;
use Nette\Utils\Strings;



/**
 * Factory for repository importers.
 *
 * @author Jan Tvrdík
 * @author Patrik Votoček
 */
class RepositoryImporterFactory extends Nette\Object
{
	/** @var array (name => callback) */
	private $factories;



	/**
	 * @param array (name => callback)
	 */
	public function __construct(array $factories)
	{
		$this->factories = $factories;
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
			$path = substr($url->getPath(), 1); // removed leading slash
			list($vendor, $name) = explode('/', $path);
			return callback($this->factories['github'])->invoke($vendor, $name);

		} else {
			throw new \NetteAddons\NotSupportedException("Currently only GitHub is supported.");
		}
	}
}
