<?php

namespace NetteAddons\Test;

use NetteAddons;
use NetteAddons\Model\Utils\VersionParser;



/**
 * @author Jan Tvrdík
 */
class VersionParserTest extends TestCase
{
	/** @var VersionsParser */
	private $versions;



	protected function setUp()
	{
		parent::setUp();
		$this->versions = new VersionParser;
	}



	/**
	 * @dataProvider tagsProvider
	 */
	public function testParseTag($input, $expected)
	{
		$this->assertSame($expected, $this->versions->parseTag($input));
	}



	public function tagsProvider()
	{
		return array(
			// valid
			array('1.0', '1.0.0'),
			array('1.0.0', '1.0.0'),
			array('1.0.0.0', '1.0.0'),
			array('1.0.0.1', '1.0.0.1'),
			array('v1.0.0', '1.0.0'),
			array('v4.5.6beta2', '4.5.6-beta2'),
			array('v2.0.4-p1', '2.0.4-patch1'),
			array('release-v2.4', '2.4.0'),

			// invalid
			array('foo', FALSE),
		);
	}



	/**
	 * @dataProvider branchesProvider
	 */
	public function testParseBranch($input, $expected)
	{
		$this->assertSame($expected, $this->versions->parseBranch($input));
	}



	public function branchesProvider()
	{
		return array(
			array('v2.1', '2.1.x-dev'),
			array('v2.1.x', '2.1.x-dev'),
			array('v2.1.*', '2.1.x-dev'),
			array('release-2.0.x', '2.0.x-dev'),
			array('master', 'dev-master'),
			array('forms', 'dev-forms'),
		);
	}
}
