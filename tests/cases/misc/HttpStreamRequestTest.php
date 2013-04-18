<?php

namespace NetteAddons\Test\Utils;

use NetteAddons\Utils\HttpStreamRequestFactory;



/**
 * Note: http://httpstat.us/ can be replaced by http://httpbin.org/.
 * @author Jan Tvrdík
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
		$this->assertInternalType('string', $s);
	}



	public function testCurlError()
	{
		$this->setExpectedException('NetteAddons\Utils\StreamException');
		$s = $this->requestFactory->create('foobar://example.com')->execute();
	}



	/**
	 * @dataProvider errorCodesProvider
	 */
	public function testHttpError($code)
	{
		$this->setExpectedException('NetteAddons\Utils\HttpException', NULL, $code);
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
