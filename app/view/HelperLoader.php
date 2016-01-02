<?php

namespace NetteAddons;

use forxer\Gravatar\Gravatar;
use Latte\Engine;


/**
 * Nette addons template helper loader
 */
class HelperLoader extends \Nette\Object
{
	/** @var array */
	private $helpers = array();


	/**
	 * @param TextPreprocessor
	 * @param string
	 * @param string
	 */
	public function __construct(TextPreprocessor $preprocessor, $gravatarMaxRating, $wwwDir)
	{
		$this->helpers['description'] = array($preprocessor, 'processDescription');
		$this->helpers['licenses'] = array($preprocessor, 'processLicenses');
		$this->helpers['gravatar'] = function($email, $size = 40) use($gravatarMaxRating) {
			return html_entity_decode(Gravatar::image($email, $size, null, $gravatarMaxRating));
		};
		$this->helpers['profile'] = function($id) {
			return 'https://forum.nette.org/en/profile.php?id=' . $id;
		};
		$this->helpers['mtime'] = function($path) use ($wwwDir) {
			return filemtime($wwwDir . DIRECTORY_SEPARATOR . $path);
		};
	}


	public function load(Engine $latte)
	{
		foreach ($this->helpers as $name => $callback) {
			$latte->addFilter($name, $callback);
		}
	}


	/**
	 * @param string
	 * @return callable|NULL
	 */
	public function __invoke($helper)
	{
		if (array_key_exists($helper, $this->helpers)) {
			return $this->helpers[$helper];
		}
	}
}
