<?php

namespace NetteAddons\Test;

use NetteAddons\Model\Version;

class VersionTest extends \PHPUnit_Framework_TestCase
{



	public static function validVersions()
	{
		return array(
			array('1.1.1.1'),
			array('1.1.1'),
			array('1.*.1'),
			array('1.2.*'),
			array('0.1.0'),
			array('>=1.9.0'),
			array('<=1.10.0'),
			array('=1.11.0'),
			array('1.0.0-alpha'), // [0-9A-Za-z-]
			array('1.0.0-alpha.1'),
			array('1.0.0-0.3.7'),
			array('1.0.0-x.7.z.92'),
			array('1.0.0+build.1'), // [0-9A-Za-z-]
			array('1.3.7+build.11.e0f985a'),
			array('1.3.7+build.11.e0f985a'),
			array('<1.0.0-alpha.1'),
			array('1.0.0-rc.1+build.1'),
		);
	}



	public static function invalidVersions()
	{
		return array(
			array('1.2'),
			array('1')
		);
	}



	public static function matchPairs()
	{
		return array(
			array('=1.11.0', '1.11.0'),
			array('<1.0.0-alpha.1', '1.0.0-alpha'),
			array('>1.0.0-beta.2', '1.0.0-alpha.1'),
			array('>1.0.0-beta.11', '1.0.0-beta.2'),
			array('>1.0.0-rc.1', '1.0.0-beta.11'),
			array('>1.0.0-rc.1+build.1', '1.0.0-rc.1'),
			array('<1.0.0', '1.0.0-rc.1+build.1'),
			array('>1.0.0+0.3.7', '1.0.0'),
			array('>1.3.7+build', '1.0.0+0.3.7'),
			array('>1.3.7+build.2.b8f12d7', '1.3.7+build'),
			array('>1.3.7+build.11.e0f985a', '1.3.7+build.2.b8f12d7'),
		);
	}



	/**
	 * @dataProvider validVersions
	 */
	public function testValid($v)
	{
		$this->assertInstanceOf('NetteAddons\Model\Version', Version::create($v));
	}



	/**
	 * @dataProvider invalidVersions
	 */
	public function testInvalid($v)
	{
		$this->assertNull(Version::create($v));
	}



	/**
	 * @dataProvider matchPairs
	 */
	public function testMatch($v1, $v2)
	{
		$this->assertTrue(Version::create($v1)->match($v2));
	}

}
