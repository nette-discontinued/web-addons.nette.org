<?php

namespace NetteAddons;

use Nette;



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
	 */
	public function __construct(TextPreprocessor $preprocessor)
	{
		$this->helpers['description'] = $preprocessor->processDescription;
		$this->helpers['licenses'] = $preprocessor->processLicenses;
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
