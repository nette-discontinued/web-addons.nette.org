<?php

namespace NetteAddons\Test;

use Mockery,
	NetteAddons,
	NetteAddons\Model\Importers\GitHubImporter;



/**
 * @author Jan Tvrdík
 */
class GitHubImporterTest extends TestCase
{
	/** @var GitHubImporter */
	private $imp;

	/** @var Mockery\MockInterface */
	private $repo;



	protected function setUp()
	{
		parent::setUp();

		$this->repo = Mockery::mock('NetteAddons\Model\Importers\GitHub\Repository');;
		$this->validators = Mockery::mock('NetteAddons\Model\Utils\Validators');
		$this->imp = new GitHubImporter($this->repo, $this->validators);
	}



	public function testImportWithComposer()
	{
		$this->repo->shouldReceive('getUrl')
			->withNoArgs()->once()
			->andReturn('https://github.com/smith/browser');

		$this->repo->shouldReceive('getMetadata')
			->withNoArgs()->once()
			->andReturn((object) array(
				'master_branch' => 'work_br',
				'name' => 'gh-name',
				'description' => 'gh-desc',
			));

		$this->repo->shouldReceive('getFileContent')
			->with('work_br', 'composer.json')->once()
			->andReturn(json_encode(array(
				'name' => 'c/name',
				'description' => 'c-desc',
				'version' => '1.3.7-beta1',
				'license' => array('MIT', 'GPL-2.0+'),
				'keywords' => array('web', 'internet', 'browser')
			)));

		$this->repo->shouldReceive('getReadme')
			->with('work_br')->once()
			->andReturn((object)array('content' => 'readme', 'path' => 'readme.md'));

		$this->validators->shouldReceive('isComposerFullNameValid')
			->with('c/name')->once()
			->andReturn(TRUE);

		$addon = $this->imp->import();

		$this->assertInstanceOf('NetteAddons\Model\Addon', $addon);
		$this->assertSame(NULL, $addon->id);
		$this->assertSame('gh-name', $addon->name);
		$this->assertSame('c/name', $addon->composerFullName);
		$this->assertSame(NULL, $addon->userId);
		$this->assertSame('c-desc', $addon->shortDescription);
		$this->assertSame('readme', $addon->description);
		$this->assertSame('MIT,GPL-2.0+', $addon->defaultLicense);
		$this->assertSame('https://github.com/smith/browser', $addon->repository);
		$this->assertSame(NULL, $addon->demo);
		$this->assertSame(NULL, $addon->updatedAt); // ?
		$this->assertSame(array(), $addon->versions);
		$this->assertSame(array('web', 'internet', 'browser'), $addon->tags);
	}



	public function testImportWithoutComposer()
	{
		$this->repo->shouldReceive('getUrl')
			->withNoArgs()->once()
			->andReturn('https://github.com/smith/browser');

		$this->repo->shouldReceive('getMetadata')
			->withNoArgs()->once()
			->andReturn((object) array(
				'master_branch' => 'work_br',
				'name' => 'gh-name',
				'description' => 'gh-desc',
			));

		$this->repo->shouldReceive('getFileContent')
			->with('work_br', 'composer.json')->once()
			->andThrow('NetteAddons\Utils\HttpException', NULL, 404);

		$this->repo->shouldReceive('getReadme')
			->with('work_br')->once()
			->andReturn(NULL);

		$addon = $this->imp->import();

		$this->assertInstanceOf('NetteAddons\Model\Addon', $addon);
		$this->assertSame(NULL, $addon->id);
		$this->assertSame('gh-name', $addon->name);
		$this->assertSame(NULL, $addon->composerName);
		$this->assertSame(NULL, $addon->userId);
		$this->assertSame('gh-desc', $addon->shortDescription);
		$this->assertSame(NULL, $addon->description);
		$this->assertSame(NULL, $addon->defaultLicense);
		$this->assertSame('https://github.com/smith/browser', $addon->repository);
		$this->assertSame(NULL, $addon->demo);
		$this->assertSame(NULL, $addon->updatedAt); // ?
		$this->assertSame(array(), $addon->versions);
		$this->assertSame(array(), $addon->tags);
	}



	public function testImportWithMinimalData()
	{
		$this->repo->shouldReceive('getUrl')
			->withNoArgs()->once()
			->andReturn('https://github.com/smith/browser');

		$this->repo->shouldReceive('getMetadata')
			->withNoArgs()->once()
			->andReturn((object) array(
				'master_branch' => 'work_br',
				'name' => 'gh-name',
				'description' => '',
			));

		$this->repo->shouldReceive('getFileContent')
			->with('work_br', 'composer.json')->once()
			->andThrow('NetteAddons\Utils\HttpException', NULL, 404);

		$this->repo->shouldReceive('getReadme')
			->with('work_br')->once()
			->andReturn(NULL);

		$addon = $this->imp->import();

		$this->assertInstanceOf('NetteAddons\Model\Addon', $addon);
		$this->assertSame(NULL, $addon->id);
		$this->assertSame('gh-name', $addon->name);
		$this->assertSame(NULL, $addon->composerName);
		$this->assertSame(NULL, $addon->userId);
		$this->assertSame(NULL, $addon->shortDescription);
		$this->assertSame(NULL, $addon->description);
		$this->assertSame(NULL, $addon->defaultLicense);
		$this->assertSame('https://github.com/smith/browser', $addon->repository);
		$this->assertSame(NULL, $addon->demo);
		$this->assertSame(NULL, $addon->updatedAt); // ?
		$this->assertSame(array(), $addon->versions);
		$this->assertSame(array(), $addon->tags);
	}
}

