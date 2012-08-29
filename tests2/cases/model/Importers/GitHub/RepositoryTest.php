<?php

namespace NetteAddons\Test;

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

	/** @var Mockery\MockInterface */
	private $curl;



	protected function setUp()
	{
		parent::setUp();
		$this->curl = Mockery::mock('NetteAddons\Curl');
		$this->repo = new Repository($this->curl, 'smith', 'browser');
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



	public function testGetMetadata()
	{
		$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/browser')
			->andReturn(json_encode(array(
				'name' => 'Smith\'s Browser',
			)));

		$metadata = $this->repo->getMetadata();
		$this->assertInstanceOf('stdClass', $metadata);
		$this->assertSame('Smith\'s Browser', $metadata->name);
	}



	public function testCurlError()
	{
		$ex = new NetteAddons\CurlException(NULL, CURLE_COULDNT_RESOLVE_HOST);
		$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/browser')
			->andThrow($ex);

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
		$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/browser')
			->andThrow($ex);

		try {
			$this->repo->getMetadata();
			$this->fail('expected NetteAddons\IOException');

		} catch (NetteAddons\IOException $e) {
			$this->assertSame($ex, $e);
		}
	}



	public function testInvalidJson()
	{
		$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/browser')
			->andReturn('{]'); // invalid JSON

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



	public function testGetFileContent()
	{
		// base64
		$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/browser/contents/file.txt?ref=cb3a02f')
			->andReturn(json_encode(array(
				'encoding' => 'base64',
				'content' => base64_encode('foobar'),
			)));

		$s = $this->repo->getFileContent('cb3a02f', 'file.txt');
		$this->assertSame('foobar', $s);

		// UTF-8
		$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/browser/contents/file.txt?ref=cb3a02f')
			->andReturn(json_encode(array(
				'encoding' => 'utf-8',
				'content' => 'foobar',
			)));

		$s = $this->repo->getFileContent('cb3a02f', 'file.txt');
		$this->assertSame('foobar', $s);

		// unknown
		$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/browser/contents/file.txt?ref=cb3a02f')
			->andReturn(json_encode(array(
				'encoding' => 'unknown',
				'content' => 'foobar',
			)));

		$this->setExpectedException('NetteAddons\IOException');
		$this->repo->getFileContent('cb3a02f', 'file.txt');
	}



	public function testGetReadme()
	{
		$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/browser/readme?ref=cb3a02f')
			->andReturn(json_encode(array(
				'encoding' => 'base64',
				'content' => base64_encode('foobar'),
			)));

		$s = $this->repo->getReadme('cb3a02f');
		$this->assertSame('foobar', $s);
	}



	public function testGetReadmeNotExist()
	{
		$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/browser/readme?ref=cb3a02f')
			->andThrow('NetteAddons\HttpException', NULL, 404);

		$s = $this->repo->getReadme('cb3a02f');
		$this->assertNull($s);
	}



	public function testGetTags()
	{
		$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/browser/tags')
			->andReturn(json_encode(array(
				array(
					'name' => 'tagA',
					'commit' => array('sha' => 'cb3a02f'),
				),
				array(
					'name' => 'tagB',
					'commit' => array('sha' => 'a630f70'),
				),
			)));

		$this->assertSame(array(
			'tagA' => 'cb3a02f',
			'tagB' => 'a630f70',
		), $this->repo->getTags());
	}



	public function testGetBranches()
	{
		$this->curl->shouldReceive('get')->once()
			->with('https://api.github.com/repos/smith/browser/branches')
			->andReturn(json_encode(array(
				array(
					'name' => 'branchA',
					'commit' => array('sha' => 'cb3a02f'),
				),
				array(
					'name' => 'branchB',
					'commit' => array('sha' => 'a630f70'),
				),
			)));

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
