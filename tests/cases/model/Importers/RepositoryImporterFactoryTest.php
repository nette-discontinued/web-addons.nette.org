<?php

namespace NetteAddons\Test;

use NetteAddons;
use NetteAddons\Model\Importers\RepositoryImporterFactory;
use Nette\Http\Url;
use Mockery;



/**
 * @author Jan Tvrdík
 */
class RepositoryImporterFactoryTest extends TestCase
{
	public function testCreateFromUrl()
	{
		$test = $this;
		$importer = Mockery::mock('NetteAddons\Model\Importers\GitHubImporter');

		$factory = new RepositoryImporterFactory(array(
			'github' => function ($vendor, $name) use ($test, $importer) {
				$test->assertSame('smith', $vendor);
				$test->assertSame('browser', $name);
				return $importer;
			},
		));

		$url = new Url('https://github.com/smith/browser');
		$this->assertSame($importer, $factory->createFromUrl($url));
	}



	/**
	 * @dataProvider dataUnsupportedUrls
	 */
	public function testCreateFromUnsupportedUrl($url)
	{
		$this->setExpectedException('NetteAddons\NotSupportedException');
		$factory = new RepositoryImporterFactory(array());
		$factory->createFromUrl(new Url($url));
	}



	public static function dataUnsupportedUrls()
	{
		return array(
			array('https://bitbucket.org/jiriknesl/mockista'),
		);
	}
}
