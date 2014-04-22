<?php

/**
 * Test: NetteAddons\Model\Importers\GitHub\Repository
 *
 * @testcase
 */

namespace NetteAddons\Test;

use Tester\Assert;
use Mockery;
use Nette\Http\Url;
use NetteAddons\Model\Importers\GitHub\Repository;

require_once __DIR__ . '/../../../../bootstrap.php';

class RepositoryTest extends TestCase
{
	/** @var Repository */
	private $repo;

	/** @var \Mockery\MockInterface */
	private $requestFactory;



	protected function setUp()
	{
		parent::setUp();
		$this->requestFactory = Mockery::mock('NetteAddons\Utils\HttpStreamRequestFactory');
		$this->repo = new Repository('beta', $this->requestFactory, 'http://github.com/smith/browser');
	}



	public function testSimpleGetters()
	{
		Assert::same('browser', $this->repo->getName());
		Assert::same('smith', $this->repo->getVendor());
	}



	public function testGetUrl()
	{
		Assert::same(
			'https://github.com/smith/browser',
			$this->repo->getUrl()
		);
	}



	/**
	 * @param string $url
	 * @return \Mockery\MockInterface|\NetteAddons\Utils\HttpStreamRequest
	 */
	private function mockRequest($url = NULL)
	{
		$request = Mockery::mock('NetteAddons\Utils\HttpStreamRequest');
		$request->shouldReceive('setOption')->withAnyArgs();
		$request->shouldReceive('addHeader')->withAnyArgs();

		$this->requestFactory->shouldReceive('create')->once()
			->withAnyArgs()
			->andReturn($request);

		return $request;
	}



	public function testGetMetadata()
	{
		$request = $this->mockRequest();
		$request->shouldReceive('execute')->withNoArgs()->once()
			->andReturn(json_encode(array(
				'name' => 'Smith\'s Browser',
			)));

		$metadata = $this->repo->getMetadata();
		Assert::type('stdClass', $metadata);
		Assert::same('Smith\'s Browser', $metadata->name);
	}



	public function testRequestError()
	{
		$ex = new \NetteAddons\Utils\StreamException;
		$this->mockRequest()
			->shouldReceive('execute')->withNoArgs()->once()
			->andThrow($ex);

		try {
			$this->repo->getMetadata();
			Assert::fail('expected NetteAddons\IOException');

		} catch (\NetteAddons\IOException $e) {
			Assert::same($ex, $e->getPrevious());
		}
	}



	public function testHttpError()
	{
		$ex = new \NetteAddons\Utils\HttpException(NULL, 404);
		$this->mockRequest()->shouldReceive('execute')->withNoArgs()->once()
			->andThrow($ex);

		try {
			$this->repo->getMetadata();
			Assert::fail('expected NetteAddons\IOException');

		} catch (\NetteAddons\IOException $e) {
			Assert::same($ex, $e);
		}
	}



	public function testInvalidJson()
	{
		$this->mockRequest()->shouldReceive('execute')->withNoArgs()->once()
			->andReturn('{]');

		try {
			$this->repo->getMetadata();
			Assert::fail('expected NetteAddons\IOException');

		} catch (\NetteAddons\IOException $e) {
			Assert::type('Nette\Utils\JsonException', $e->getPrevious());
		}
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
		$request->shouldReceive('execute')->withNoArgs()->once()
			->andReturn(json_encode(array(
				'encoding' => $encoding,
				'content' => $content,
			)));

		$s = $this->repo->getFileContent('cb3a02f', 'file.txt');
		Assert::same($expectedValue, $s);
	}



	/**
	 * @throws \NetteAddons\IOException
	 */
	public function testGetFileContent_UnknownEncodingException()
	{
		$request = $this->mockRequest('https://api.github.com/repos/smith/browser/contents/file.txt?ref=cb3a02f');
		$request->shouldReceive('execute')->withNoArgs()->once()
			->andReturn(json_encode(array(
				'encoding' => 'unknown',
				'content' => 'foobar',
			)));

		$this->repo->getFileContent('cb3a02f', 'file.txt');
	}



	public function testGetReadme()
	{
		$request = $this->mockRequest('https://api.github.com/repos/smith/browser/readme?ref=cb3a02f');
		$request->shouldReceive('execute')->withNoArgs()->once()
			->andReturn(json_encode(array(
				'encoding' => 'base64',
				'content' => base64_encode('foobar'),
			)));

		$s = $this->repo->getReadme('cb3a02f')->content;
		Assert::same('foobar', $s);
	}



	public function testGetReadmeNotExist()
	{
		$request = $this->mockRequest('https://api.github.com/repos/smith/browser/readme?ref=cb3a02f');
		$request->shouldReceive('execute')->withNoArgs()->once()
			->andThrow(new \NetteAddons\Utils\HttpException(NULL, 404));

		$s = $this->repo->getReadme('cb3a02f');
		Assert::null($s);
	}



	public function testGetTags()
	{
		$request = $this->mockRequest('https://api.github.com/repos/smith/browser/tags');
		$request->shouldReceive('execute')->withNoArgs()->once()
			->andReturn(json_encode(array(
				array('name' => 'tagA', 'commit' => array('sha' => 'cb3a02f')),
				array('name' => 'tagB', 'commit' => array('sha' => 'a630f70')),
			)));

		Assert::same(array(
			'tagA' => 'cb3a02f',
			'tagB' => 'a630f70',
		), $this->repo->getTags());
	}



	public function testGetBranches()
	{
		$request = $this->mockRequest('https://api.github.com/repos/smith/browser/branches');
		$request->shouldReceive('execute')->withNoArgs()->once()
			->andReturn(json_encode(array(
				array('name' => 'branchA', 'commit' => array('sha' => 'cb3a02f')),
				array('name' => 'branchB', 'commit' => array('sha' => 'a630f70')),
			)));

		Assert::same(array(
			'branchA' => 'cb3a02f',
			'branchB' => 'a630f70',
		), $this->repo->getBranches());
	}



	public function testGetArchiveLink()
	{
		Assert::same(
			'https://github.com/smith/browser/zipball/cb3a02f',
			$this->repo->getArchiveLink('zip', 'cb3a02f')
		);

		$repo = $this->repo;
		Assert::exception(function() use($repo) {
			$repo->getArchiveLink('rar', 'cb3a02f');
		}, 'NetteAddons\NotSupportedException');
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
		Assert::type('array', $data);
		Assert::same($vendor, $data[0]);
		Assert::same($name, $data[1]);
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
		Assert::null(Repository::getVendorAndName($url));
	}

}

id(new RepositoryTest)->run();
