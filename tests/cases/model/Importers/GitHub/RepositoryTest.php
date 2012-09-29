<?php

namespace NetteAddons\Test;

use Nette;
use NetteAddons;
use NetteAddons\Model\Importers\GitHub\Repository;
use Mockery;



/**
 * @author Jan Tvrdík
 */
class RepositoryTest extends TestCase
{
	/** @var Repository */
	private $repo;

	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $curl;



	protected function setUp()
	{
		parent::setUp();
		$this->curl = $this->getMockBuilder('NetteAddons\CurlRequestFactory')->disableOriginalConstructor()->getMock();
		$this->repo = new Repository('beta', $this->curl, 'smith', 'browser');
	}



	public function testSimpleGetters()
	{
		$this->assertSame('smith', $this->repo->getVendor());
		$this->assertSame('browser', $this->repo->getName());
	}



	public function testGetUrl()
	{
		$this->assertSame(
			'https://github.com/smith/browser',
			$this->repo->getUrl()
		);
	}



	/**
	 * @param string $url
	 * @return \PHPUnit_Framework_MockObject_MockObject|\NetteAddons\CurlRequest
	 */
	private function mockRequest($url = NULL)
	{
		$request = $this->getMockBuilder('NetteAddons\CurlRequest')
			->disableOriginalConstructor()->getMock();
		$request->expects($this->any())->method('setOption');

		$urlConstraint = $url !== NULL
			? $this->equalTo(new Nette\Http\Url($url))
			: $this->isInstanceOf('Nette\Http\Url'); // https://api.github.com/repos/smith/browser

		$this->curl->expects($this->once())->method('create')
			->with($urlConstraint)
			->will($this->returnValue($request));

		return $request;
	}



	public function testGetMetadata()
	{
		$request = $this->mockRequest();
		$request->expects($this->once())->method('execute')
			->will($this->returnValue(json_encode(array(
				'name' => 'Smith\'s Browser',
			))));

		$metadata = $this->repo->getMetadata();
		$this->assertInstanceOf('stdClass', $metadata);
		$this->assertSame('Smith\'s Browser', $metadata->name);
	}



	public function testCurlError()
	{
		$ex = new NetteAddons\CurlException(NULL, CURLE_COULDNT_RESOLVE_HOST);
		$this->mockRequest()
			->expects($this->once())
			->method('execute')
			->will($this->throwException($ex));

		try {
			$this->repo->getMetadata();
			$this->fail('expected NetteAddons\IOException');

		} catch (NetteAddons\IOException $e) {
			$this->assertSame($ex, $e->getPrevious());
		}
	}



	public function testHttpError()
	{
		$ex = new NetteAddons\HttpException(NULL, 404);
		$this->mockRequest()
			->expects($this->once())
			->method('execute')
			->will($this->throwException($ex));

		try {
			$this->repo->getMetadata();
			$this->fail('expected NetteAddons\IOException');

		} catch (NetteAddons\IOException $e) {
			$this->assertSame($ex, $e);
		}
	}



	public function testInvalidJson()
	{
		$this->mockRequest()
			->expects($this->once())
			->method('execute')
			->will($this->returnValue('{]'));

		try {
			$this->repo->getMetadata();
			$this->fail('expected NetteAddons\IOException');

		} catch (NetteAddons\IOException $e) {
			$this->assertInstanceOf('Nette\Utils\JsonException', $e->getPrevious());
		}
	}



	public function testGetTree()
	{
		$this->markTestIncomplete('Not sure whether getTree() method will be used at all.');
		/*$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/git/trees/cb3a02f')
			->andReturn(json_encode(array(

			)));*/
	}



	/**
	 * @return array
	 */
	public function dataGetFileContent()
	{
		return array(
			array('base64', base64_encode('foobar'), 'foobar'),
			array('utf-8', 'barfoo', 'barfoo'),
		);
	}



	/**
	 * @dataProvider dataGetFileContent
	 */
	public function testGetFileContent($encoding, $content, $expectedValue)
	{
		$request = $this->mockRequest('https://api.github.com/repos/smith/browser/contents/file.txt?ref=cb3a02f');
		$request->expects($this->once())
			->method('execute')
			->will($this->returnValue(json_encode(array(
				'encoding' => $encoding,
				'content' => $content,
			))));

		$s = $this->repo->getFileContent('cb3a02f', 'file.txt');
		$this->assertSame($expectedValue, $s);
	}



	/**
	 * @expectedException NetteAddons\IOException
	 */
	public function testGetFileContent_UnknownEncodingException()
	{
		$request = $this->mockRequest('https://api.github.com/repos/smith/browser/contents/file.txt?ref=cb3a02f');
		$request->expects($this->once())
			->method('execute')
			->will($this->returnValue(json_encode(array(
				'encoding' => 'unknown',
				'content' => 'foobar',
			))));

		$this->repo->getFileContent('cb3a02f', 'file.txt');
	}



	public function testGetReadme()
	{
		$request = $this->mockRequest('https://api.github.com/repos/smith/browser/readme?ref=cb3a02f');
		$request->expects($this->once())
			->method('execute')
			->will($this->returnValue(json_encode(array(
				'encoding' => 'base64',
				'content' => base64_encode('foobar'),
			))));

		$s = $this->repo->getReadme('cb3a02f')->content;
		$this->assertSame('foobar', $s);
	}



	public function testGetReadmeNotExist()
	{
		$request = $this->mockRequest('https://api.github.com/repos/smith/browser/readme?ref=cb3a02f');
		$request->expects($this->once())
			->method('execute')
			->will($this->throwException(new NetteAddons\HttpException(NULL, 404)));

		$s = $this->repo->getReadme('cb3a02f');
		$this->assertNull($s);
	}



	public function testGetTags()
	{
		$request = $this->mockRequest('https://api.github.com/repos/smith/browser/tags');
		$request->expects($this->once())
			->method('execute')
			->will($this->returnValue(json_encode(array(
				array('name' => 'tagA', 'commit' => array('sha' => 'cb3a02f')),
				array('name' => 'tagB', 'commit' => array('sha' => 'a630f70')),
			))));

		$this->assertSame(array(
			'tagA' => 'cb3a02f',
			'tagB' => 'a630f70',
		), $this->repo->getTags());
	}



	public function testGetBranches()
	{
		$request = $this->mockRequest('https://api.github.com/repos/smith/browser/branches');
		$request->expects($this->once())
			->method('execute')
			->will($this->returnValue(json_encode(array(
				array('name' => 'branchA', 'commit' => array('sha' => 'cb3a02f')),
				array('name' => 'branchB', 'commit' => array('sha' => 'a630f70')),
			))));

		$this->assertSame(array(
			'branchA' => 'cb3a02f',
			'branchB' => 'a630f70',
		), $this->repo->getBranches());
	}



	public function testGetArchiveLink()
	{
		$this->assertSame(
			'https://github.com/smith/browser/zipball/cb3a02f',
			$this->repo->getArchiveLink('zip', 'cb3a02f')
		);

		$this->setExpectedException('NetteAddons\NotSupportedException');
		$this->repo->getArchiveLink('rar', 'cb3a02f');
	}

}
