<?php

namespace NetteAddons\Test;

use NetteAddons\Model\Addon;

/**
 * @author Jan Marek
 */
class AddonTest extends \PHPUnit_Framework_TestCase
{

	/** @var Addon */
	private $object;

	protected function setUp()
	{
		$this->object = new Addon();
	}

	public function testBuildComposerName()
	{
		$this->object->name = 'Muj Plugínek';
		$author = (object) array(
			'name' => 'Honzík Marků',
		);
		$expected = 'HonzikMarku/MujPluginek';
		$this->object->buildComposerName($author);

		$this->assertEquals($expected, $this->object->composerName);
	}

}
