<?php

namespace NetteAddons\Test;

/**
 * @author Jan Marek
 */
abstract class SeleniumTestCase extends \PHPUnit_Extensions_Selenium2TestCase
{

	/** @var \SystemContainer */
	protected $context;



	protected function setUp()
	{
		parent::setUp();

		$this->context = $GLOBALS['container'];

		if (empty($this->context->parameters['selenium'])) {
			$this->markTestSkipped('Create parameters selenium.browser and selenium.baseUrl in config.local.neon');
		}

		$this->setBrowser($this->context->parameters['selenium']['browser']);
		$this->setBrowserUrl($this->context->parameters['selenium']['root']);
		$this->setPort(4444);
	}



	protected function reinstallDb()
	{
		$this->context->reinstall->recreateDatabase();
	}

}
