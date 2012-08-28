<?php

namespace NetteAddons\Test;

use NetteAddons\Curl;



/**
 * Note: http://httpstat.us/ can be replaced by http://httpbin.org/.
 * @author Jan Tvrdík
 */
class CurlTest extends TestCase
{
	/** @var Curl */
	private $curl;



	protected function setUp()
	{
		parent::setUp();
		$this->curl = new Curl(1000);
	}



	public function testOK()
	{
		$s = $this->curl->get('http://httpstat.us/200');
		$this->assertType('string', $s);
	}



	public function testCurlError()
	{
		$this->setExpectedException('NetteAddons\CurlException', NULL, CURLE_UNSUPPORTED_PROTOCOL);
		$s = $this->curl->get('foobar://example.com');
	}



	/**
	 * @dataProvider errorCodesProvider
	 */
	public function testHttpError($code)
	{
		$this->setExpectedException('NetteAddons\HttpException', NULL, $code);
		$this->curl->get('http://httpstat.us/' . $code);
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
