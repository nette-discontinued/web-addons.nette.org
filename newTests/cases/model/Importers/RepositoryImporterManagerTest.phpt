<?php

/**
 * Test: NetteAddons\Model\Importers\RepositoryImporterManager
 *
 * @testcase
 */

namespace NetteAddons\Test;

use Tester\Assert;
use Mockery;
use NetteAddons\Model\Importers\RepositoryImporterManager;

require_once __DIR__ . '/../../../bootstrap.php';

class RepositoryImporterManagerTest extends TestCase
{
	/** @var \NetteAddons\Model\Importers\RepositoryImporterManager */
	private $factory;



	protected function setUp()
	{
		parent::setUp();

		$this->factory = new RepositoryImporterManager;
	}


	/**
	 * @param string
	 * @return \NetteAddons\Model\IAddonImporter
	 */
	protected function setupGithubImporter($expectedUrl)
	{
		$class = 'NetteAddons\Model\Importers\GitHubImporter';
		$importer = Mockery::mock($class);
		$test = $this;
		$callback = function ($url) use ($test, $importer, $expectedUrl) {
			Assert::same($expectedUrl, $url);
			return $importer;
		};

		$this->factory->addImporter('github', $callback, $class);

		return $importer;
	}



	/**
	 * @throws \NetteAddons\InvalidStateException
	 */
	public function testAddAlreadyRegisteredImporter()
	{
		$this->setupGithubImporter('foo');

		$this->factory->addImporter('github', 'invalid', 'invalid');
	}



	/**
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	public function testAddInvalidCallback()
	{
		$this->factory->addImporter('github', 'invalid', 'invalid');
	}



	/**
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	public function testAddInvalidClass()
	{
		$this->factory->addImporter('github', function() {}, get_called_class());
	}



	public function dataSupportedUrls()
	{
		return array(
			array('https://github.com/smith/browser'),
		);
	}



	/**
	 * @dataProvider dataSupportedUrls
	 */
	public function testCreateFromUrl($url)
	{
		$importer = $this->setupGithubImporter($url);

		Assert::same($importer, $this->factory->createFromUrl($url));
	}



	public static function dataUnsupportedUrls()
	{
		return array(
			array('https://bitbucket.org/jiriknesl/mockista'),
		);
	}



	/**
	 * @throws \NetteAddons\NotSupportedException
	 * @dataProvider dataUnsupportedUrls
	 */
	public function testCreateFromUnsupportedUrl($url)
	{
		$factory = new RepositoryImporterManager;
		$factory->createFromUrl($url);
	}



	public function dataNormalizeUrl()
	{
		return array(
			array('https://github.com/smith/browser', 'https://github.com/smith/browser'),
			array('http://github.com/smith/browser', 'https://github.com/smith/browser'),
			array('github.com/smith/browser', 'https://github.com/smith/browser'),
			array('https://github.com/smith/browser/commits/master', 'https://github.com/smith/browser'),
			array('https://github.com/smith/browser.git', 'https://github.com/smith/browser'),
			array('git://github.com/smith/browser.git', 'https://github.com/smith/browser'),
			array('https://bitbucket.org/jiriknesl/mockista', 'https://bitbucket.org/jiriknesl/mockista'),
			array('http://example.com/foo', 'http://example.com/foo'),
			array('example.com/foo', 'http://example.com/foo'),
			array('git+ssh://example.com/foo', 'git+ssh://example.com/foo'),
			array('https://github.com/nette/addons.nette.org/commits/master', 'https://github.com/nette/addons.nette.org'), // #104
		);
	}


	/**
	 * @dataProvider dataNormalizeUrl
	 * @param string
	 * @param string
	 */
	public function testNormalizeUrl($url, $normalizedUrl)
	{
		$this->factory->addImporter('github', function() {}, 'NetteAddons\Model\Importers\GitHubImporter');

		$parsedUrl = $this->factory->normalizeUrl($url);
		Assert::equal($normalizedUrl, $parsedUrl);
	}
}

id(new RepositoryImporterManagerTest)->run();
