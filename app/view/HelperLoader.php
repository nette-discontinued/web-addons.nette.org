<?php

namespace NetteAddons;

use forxer\Gravatar\Gravatar;


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
	 */
	public function __construct(TextPreprocessor $preprocessor, $gravatarMaxRating)
	{
		$this->helpers['description'] = array($preprocessor, 'processDescription');
		$this->helpers['licenses'] = array($preprocessor, 'processLicenses');
		$this->helpers['gravatar'] = function($email, $size = 40) use($gravatarMaxRating) {
			return html_entity_decode(Gravatar::image($email, $size, null, $gravatarMaxRating));
		};
		$this->helpers['profile'] = function($id) {
			return 'http://forum.nette.org/en/profile.php?id=' . $id;
		};
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
