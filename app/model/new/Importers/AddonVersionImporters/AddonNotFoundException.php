<?php

namespace NetteAddons\Model\Importers\AddonVersionImporters;

class AddonNotFoundException extends \Exception
{
	/** @var string */
	private $name;

	/**
	 * @param string
	 * @param string
	 * @param \Exception
	 */
	public function __construct($message, $name, \Exception $parent)
	{
		parent::__construct($message, 0, $parent);

		$this->name = $name;
	}

	public function getName()
	{
		return $this->name;
	}
}
