<?php

namespace NetteAddons\Test;

use NetteAddons;
use NetteAddons\Model\Importers\RepositoryImporterFactory;
use Mockery;



/**
 * @author Jan Tvrdík
 */
class RepositoryImporterFactoryTest extends TestCase
{
	/**
	 * @dataProvider dataNormalizeGitHubUrl
	 */
	public function testCreateFromUrl($input, $expected)
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

		$this->assertSame($importer, $factory->createFromUrl($input));
	}



	/**
	 * @dataProvider dataUnsupportedUrls
	 */
	public function testCreateFromInvalidUrl($url)
	{
		$this->setExpectedException('NetteAddons\NotSupportedException');
		$factory = new RepositoryImporterFactory(array());
		$factory->createFromUrl($url);
	}



	/**
	 * @dataProvider dataNormalizeGitHubUrl
	 */
	public function testNormalizeUrl($input, $expected)
	{
		$factory = new RepositoryImporterFactory(array());
		$method = Access($factory, 'normalizeUrl');

		$this->assertSame($expected, (string) $method->call($input));
	}



	public static function dataUnsupportedUrls()
	{
		return array(
			array('https://bitbucket.org/jiriknesl/mockista'),
			array('invalid'),
		);
	}



	/**
	 * @author Patrik Votoček
	 */
	public static function dataNormalizeGitHubUrl()
	{
		return array(
			array('https://github.com/smith/browser', 'https://github.com/smith/browser'),
			array('http://github.com/smith/browser', 'https://github.com/smith/browser'),
			array('github.com/smith/browser', 'https://github.com/smith/browser'),
			array('https://github.com/smith/browser/commits/master', 'https://github.com/smith/browser'),
			array('https://github.com/smith/browser.git', 'https://github.com/smith/browser'),
			array('git://github.com/smith/browser.git', 'https://github.com/smith/browser'),
		);
	}
}
