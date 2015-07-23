<?php

namespace NetteAddons\Test\Model;

use NetteAddons\Test\TestCase;


class AbstractTestCase extends TestCase
{

	final public function dataInvalidComposerFullName()
	{
		return array(
			array('-nette/nette'),
			array('net--te/nette'),
			array('nette-/nette'),

			array('nette/-nette'),
			array('nette/nette-'),
			array('nette/net--te'),

			array('_nette/nette'),
			array('net__te/nette'),
			array('nette_/nette'),

			array('nette/_nette'),
			array('nette/nette_'),
			array('nette/net__te'),

			array('nette foundation/nette'),
			array('nette/nette framework'),

			array('nette.foundation/nette'),
			array('nette/nette.framework'),
		);
	}


	final public function dataInvalidString()
	{
		return array(
			array(null),
			array(42),
			array(false),
			array(array()),
			array(new \stdClass),
		);
	}

}
