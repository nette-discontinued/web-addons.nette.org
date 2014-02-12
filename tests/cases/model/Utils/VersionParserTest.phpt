<?php

/**
 * Test: NetteAddons\Model\Utils\VersionParser
 *
 * @testcase
 */

namespace NetteAddons\Test;

use Tester\Assert;
use Mockery;
use NetteAddons\Model\Utils\VersionParser;

require_once __DIR__ . '/../../../bootstrap.php';

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
		Assert::same($expected, $this->versions->parseTag($input));
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
		Assert::same($expected, $this->versions->parseBranch($input));
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

id(new VersionParsertest)->run();