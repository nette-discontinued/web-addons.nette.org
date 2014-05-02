<?php

/**
 * Test: NetteAddons\Model\Importers\GitHubImporter
 *
 * @testcase
 */

namespace NetteAddons\Test;

use Tester\Assert;
use Mockery;
use NetteAddons\Model\Importers\GitHubImporter;

require_once __DIR__ . '/../../../bootstrap.php';

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
				'default_branch' => 'work_br',
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

		Assert::type('NetteAddons\Model\Addon', $addon);
		Assert::same(NULL, $addon->id);
		Assert::same('gh-name', $addon->name);
		Assert::same('c/name', $addon->composerFullName);
		Assert::same(NULL, $addon->userId);
		Assert::same('c-desc', $addon->shortDescription);
		Assert::same('readme', $addon->description);
		Assert::same('MIT,GPL-2.0+', $addon->defaultLicense);
		Assert::same('https://github.com/smith/browser', $addon->repository);
		Assert::same(NULL, $addon->demo);
		Assert::same(NULL, $addon->updatedAt); // ?
		Assert::same(array(), $addon->versions);
		Assert::same(array('web', 'internet', 'browser'), $addon->tags);
	}



	public function testImportWithoutComposer()
	{
		$this->repo->shouldReceive('getUrl')
			->withNoArgs()->once()
			->andReturn('https://github.com/smith/browser');

		$this->repo->shouldReceive('getMetadata')
			->withNoArgs()->once()
			->andReturn((object) array(
				'default_branch' => 'work_br',
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

		Assert::type('NetteAddons\Model\Addon', $addon);
		Assert::same(NULL, $addon->id);
		Assert::same('gh-name', $addon->name);
		Assert::same(NULL, $addon->composerName);
		Assert::same(NULL, $addon->userId);
		Assert::same('gh-desc', $addon->shortDescription);
		Assert::same(NULL, $addon->description);
		Assert::same(NULL, $addon->defaultLicense);
		Assert::same('https://github.com/smith/browser', $addon->repository);
		Assert::same(NULL, $addon->demo);
		Assert::same(NULL, $addon->updatedAt); // ?
		Assert::same(array(), $addon->versions);
		Assert::same(array(), $addon->tags);
	}



	public function testImportWithMinimalData()
	{
		$this->repo->shouldReceive('getUrl')
			->withNoArgs()->once()
			->andReturn('https://github.com/smith/browser');

		$this->repo->shouldReceive('getMetadata')
			->withNoArgs()->once()
			->andReturn((object) array(
				'default_branch' => 'work_br',
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

		Assert::type('NetteAddons\Model\Addon', $addon);
		Assert::same(NULL, $addon->id);
		Assert::same('gh-name', $addon->name);
		Assert::same(NULL, $addon->composerName);
		Assert::same(NULL, $addon->userId);
		Assert::same(NULL, $addon->shortDescription);
		Assert::same(NULL, $addon->description);
		Assert::same(NULL, $addon->defaultLicense);
		Assert::same('https://github.com/smith/browser', $addon->repository);
		Assert::same(NULL, $addon->demo);
		Assert::same(NULL, $addon->updatedAt); // ?
		Assert::same(array(), $addon->versions);
		Assert::same(array(), $addon->tags);
	}
}

id(new GitHubImporterTest)->run();