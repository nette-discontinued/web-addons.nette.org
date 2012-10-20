<?php

namespace NetteAddons\Test;

use Mockery,
	Nette\Http\Url,
	NetteAddons,
	NetteAddons\Model\Importers\RepositoryImporterFactory;



/**
 * @author Jan Tvrdík
 * @author Patrik Votoček
 */
class RepositoryImporterFactoryTest extends TestCase
{
	/** @var \NetteAddons\Model\Importers\RepositoryImporterFactory */
	private $factory;



	protected function setUp()
	{
		parent::setUp();

		$this->factory = new RepositoryImporterFactory;
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
		$factory = new RepositoryImporterFactory;
		$factory->createFromUrl($url);
	}
}
