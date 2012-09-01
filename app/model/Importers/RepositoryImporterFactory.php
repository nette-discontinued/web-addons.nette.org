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
	 * @param  string
	 * @return IAddonImporter
	 * @throws \NetteAddons\NotSupportedException
	 */
	public function createFromUrl($url)
	{
		$url = $this->normalizeUrl($url);
		if ($url->getHost() === 'github.com') {
			$path = substr($url->getPath(), 1); // removed leading slash
			list($vendor, $name) = explode('/', $path);
			return callback($this->factories['github'])->invoke($vendor, $name);

		} else {
			throw new \NetteAddons\NotSupportedException("Currently only GitHub is supported.");
		}
	}



	/**
	 * @author Patrik Votoček
	 * @author Jan Tvrdík
	 * @param  string
	 * @return Url
	 * @throws \NetteAddons\NotSupportedException
	 */
	private function normalizeUrl($url)
	{
		if (!Strings::match($url, '#^[a-z]+://#i')) {
			$url = 'http://' . $url;
		}

		$url = new Url($url);
		if ($url->getHost() === 'github.com') {
			$path = substr($url->getPath(), 1); // without leading slash
			if (strpos($path, '/') === FALSE) {
				throw new \NetteAddons\NotSupportedException("Invalid GitHub URL.");
			}

			if (Strings::endsWith($path, '.git')) {
				$path = Strings::substring($path, 0, -4);
			}

			list($vendor, $name) = explode('/', $path);

			$normalized = new Url("https://github.com");
			$normalized->setPath("/$vendor/$name");

		} else {
			throw new \NetteAddons\NotSupportedException("Currently only GitHub is supported.");
		}

		return $normalized;
	}
}
