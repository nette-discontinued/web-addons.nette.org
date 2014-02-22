<?php

namespace NetteAddons;

use emberlabs\GravatarLib\Gravatar;


/**
 * Nette addons template helper loader
 */
class HelperLoader extends \Nette\Object
{
	/** @var array */
	private $helpers = array();


	/**
	 * @param TextPreprocessor
	 * @param \emberlabs\GravatarLib\Gravatar
	 */
	public function __construct(TextPreprocessor $preprocessor, Gravatar $gravatar)
	{
		$this->helpers['description'] = array($preprocessor, 'processDescription');
		$this->helpers['licenses'] = array($preprocessor, 'processLicenses');
		$this->helpers['gravatar'] = function($email, $size = 40) use($gravatar) {
			$gravatar->setAvatarSize($size);
			return $gravatar->buildGravatarURL($email);
		};
		$this->helpers['profile'] = function($id) {
			return 'http://forum.nette.org/cs/profile.php?id=' . $id;
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
