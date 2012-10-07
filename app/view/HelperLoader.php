<?php

namespace NetteAddons;

use Nette;
use emberlabs\GravatarLib\Gravatar;



/**
 * Nette addons template helper loader
 *
 * @author Patrik Votoček
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
		$this->helpers['description'] = $preprocessor->processDescription;
		$this->helpers['licenses'] = $preprocessor->processLicenses;
		$this->helpers['gravatar'] = function($email, $size = 40) use($gravatar) {
			$gravatar->setAvatarSize($size);
			return $gravatar->buildGravatarURL($email);
		};
		$this->helpers['profile'] = function($id) {
			return 'http://forum.nette.org/cs/profile.php?id=' . $id;
		};
	}



	/**
	 * @param string    helper name
	 * @return callable|NULL
	 */
	public function __invoke($helper)
	{
		if (array_key_exists($helper, $this->helpers)) {
			return $this->helpers[$helper];
		}
	}
}
