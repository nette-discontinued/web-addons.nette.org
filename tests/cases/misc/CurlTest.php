<?php

namespace NetteAddons\Test;

use NetteAddons\CurlRequestFactory;



/**
 * Note: http://httpstat.us/ can be replaced by http://httpbin.org/.
 * @author Jan Tvrdík
 */
class CurlTest extends TestCase
{
	/** @var CurlRequestFactory */
	private $curl;



	protected function setUp()
	{
		parent::setUp();
		$this->curl = new CurlRequestFactory(10000);
	}



	public function testOK()
	{
		$s = $this->curl->create('http://httpstat.us/200')->execute();
		$this->assertInternalType('string', $s);
	}



	public function testCurlError()
	{
		$this->setExpectedException('NetteAddons\CurlException', NULL, CURLE_UNSUPPORTED_PROTOCOL);
		$s = $this->curl->create('foobar://example.com')->execute();
	}



	/**
	 * @dataProvider errorCodesProvider
	 */
	public function testHttpError($code)
	{
		$this->setExpectedException('NetteAddons\HttpException', NULL, $code);
		$this->curl->create('http://httpstat.us/' . $code)->execute();
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
