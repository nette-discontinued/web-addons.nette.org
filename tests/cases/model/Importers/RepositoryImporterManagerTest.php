<?php

namespace NetteAddons\Test;

use Mockery,
	Nette\Http\Url,
	NetteAddons,
	NetteAddons\Model\Importers\RepositoryImporterManager;



/**
 * @author Jan Tvrdík
 * @author Patrik Votoček
 */
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
			$test->assertSame($expectedUrl, $url);
			return $importer;
		};

		$this->factory->addImporter('github', $callback, $class);

		return $importer;
	}



	/**
	 * @expectedException NetteAddons\InvalidStateException
	 */
	public function testAddAlreadyRegisteredImporter()
	{
		$this->setupGithubImporter('foo');

		$this->factory->addImporter('github', 'invalid', 'invalid');
	}



	/**
	 * @expectedException NetteAddons\InvalidArgumentException
	 */
	public function testAddInvalidCallback()
	{
		$this->factory->addImporter('github', 'invalid', 'invalid');
	}



	/**
	 * @expectedException NetteAddons\InvalidArgumentException
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

		$this->assertSame($importer, $this->factory->createFromUrl($url));
	}



	public static function dataUnsupportedUrls()
	{
		return array(
			array('https://bitbucket.org/jiriknesl/mockista'),
		);
	}



	/**
	 * @expectedException NetteAddons\NotSupportedException
	 * @dataProvider dataUnsupportedUrls
	 */
	public function testCreateFromUnsupportedUrl($url)
	{
		$factory = new RepositoryImporterManager;
		$factory->createFromUrl($url);
	}



	/**
	 * @author Patrik Votoček
	 * @author Jan Tvrdík
	 */
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
		$this->assertEquals($normalizedUrl, $parsedUrl);
	}
}
