<?php

namespace NetteAddons\Test;

use Mockery,
	Nette\Http\Url,
	NetteAddons,
	NetteAddons\Model\Importers\GitHub\Repository;



/**
 * @author Jan Tvrdík
 * @author Patrik Votoček
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
		$this->curl = $this->getMockBuilder('NetteAddons\Utils\CurlRequestFactory')->disableOriginalConstructor()->getMock();
		$this->repo = new Repository('beta', $this->curl, 'http://github.com/smith/browser');
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
	 * @return \PHPUnit_Framework_MockObject_MockObject|\NetteAddons\Utils\CurlRequest
	 */
	private function mockRequest($url = NULL)
	{
		$request = $this->getMockBuilder('NetteAddons\Utils\CurlRequest')
			->disableOriginalConstructor()->getMock();
		$request->expects($this->any())->method('setOption');

		$urlConstraint = $url !== NULL
			? $this->equalTo(new Url($url))
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
		$ex = new NetteAddons\Utils\CurlException(NULL, CURLE_COULDNT_RESOLVE_HOST);
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
		$ex = new NetteAddons\Utils\HttpException(NULL, 404);
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
			->will($this->throwException(new NetteAddons\Utils\HttpException(NULL, 404)));

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



	public function dataGetVendorAndName()
	{
		return array(
			array('http://github.com/foo/bar', 'foo', 'bar'),
			array('https://github.com/foo/bar', 'foo', 'bar'),
			array('git://github.com/foo/bar', 'foo', 'bar'),
			array('git@github.com/foo/bar', 'foo', 'bar'),
			array('ssh://git@github.com/foo/bar', 'foo', 'bar'),
			array('http://github.com/foo/bar.git', 'foo', 'bar'),
			array('https://github.com/foo/bar.git', 'foo', 'bar'),
			array('git://github.com/foo/bar.git', 'foo', 'bar'),
			array('git@github.com:foo/bar.git', 'foo', 'bar'),
			array('ssh://git@github.com:foo/bar.git', 'foo', 'bar'),
			array('http://github.com/foo/bar-baz.bax', 'foo', 'bar-baz.bax'),
			array('http://github.com/foo/bar-baz.bax.git', 'foo', 'bar-baz.bax'),
			array('https://github.com/foo/bar-baz.bax', 'foo', 'bar-baz.bax'),
			array('git://github.com/foo/bar-baz.bax', 'foo', 'bar-baz.bax'),
			array('git@github.com/foo/bar-baz.bax', 'foo', 'bar-baz.bax'),
			array('ssh://git@github.com/foo/bar-baz.bax', 'foo', 'bar-baz.bax'),
			array('ssh://git@github.com/foo/bar-baz.bax.git', 'foo', 'bar-baz.bax'),
			array('http://github.com/Foo/Bar', 'Foo', 'Bar'),
			array('http://github.com/Foo/Bar.json', 'Foo', 'Bar.json'),
			array('http://github.com/Foo/Bar/tree', 'Foo', 'Bar'),
			array('http://github.com/Foo/Bar/issues/42', 'Foo', 'Bar'),
		);
	}



	/**
	 * @dataProvider dataGetVendorAndName
	 * @param string
	 * @param string
	 * @param string
	 */
	public function testGetVendorAndName($url, $vendor, $name)
	{
		$data = Repository::getVendorAndName($url);
		$this->assertInternalType('array', $data);
		$this->assertSame($vendor, $data[0]);
		$this->assertSame($name, $data[1]);
	}



	public function dataGetInvalidVendorAndName()
	{
		return array(
			array('ftp://github.com/foo/bar'),
			array('http://www.github.com/foo/bar'),
			array('git@bitbucket.org/foo/bar'),
			array('http://github.com/foo'),
			array('http://github.com/'),
			array('http://github.com/foo.bar/baz'),
			array('http://github.com/foo.bar%baz'),
			array('http://github.com/foo.bar.git.git'),
			array('http://'),
			array(''),
		);
	}



	/**
	 * @dataProvider dataGetInvalidVendorAndName
	 * @param string
	 */
	public function testGetInvalidVendorAndName($url)
	{
		$this->assertNull(Repository::getVendorAndName($url));
	}

}
