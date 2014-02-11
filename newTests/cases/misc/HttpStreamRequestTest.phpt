<?php

/**
 * Test: NetteAddons\Utils\HttpStreamRequestFactory
 *
 * @testcase
 */

namespace NetteAddons\Test\Utils;

use Tester\Assert;
use NetteAddons\Utils\HttpStreamRequestFactory;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Note: http://httpstat.us/ can be replaced by http://httpbin.org/.
 */
class HttpStreamRequestTest extends \NetteAddons\Test\TestCase
{
	/** @var \NetteAddons\Utils\HttpStreamRequestFactory */
	private $requestFactory;



	protected function setUp()
	{
		parent::setUp();
		$this->requestFactory = new HttpStreamRequestFactory(10000);
	}



	public function testOK()
	{
		$s = $this->requestFactory->create('http://httpstat.us/200')->execute();
		Assert::type('string', $s);
	}


	/**
	 * @throws \NetteAddons\Utils\StreamException
	 */
	public function testCurlError()
	{
		$s = $this->requestFactory->create('foobar://example.com')->execute();
	}



	/**
	 * @throws \NetteAddons\Utils\HttpException
	 * @dataProvider errorCodesProvider
	 */
	public function testHttpError($code)
	{
		$this->requestFactory->create('http://httpstat.us/' . $code)->execute();
	}



	public function errorCodesProvider()
	{
		return array(
			array(400),
			array(403),
			array(404),
			array(500),
		);
	}
}

id(new HttpStreamRequestTest)->run();