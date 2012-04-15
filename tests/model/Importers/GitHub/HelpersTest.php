<?php

namespace NetteAddons\Test;

/**
 * @author	Patrik Votoček
 */
class GitHubHelpersTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @return array
	 */
	public static function dataNormalizeGitHubUrl()
	{
		return array(
			array('https://github.com/nette/nette', 'https://github.com/nette/nette'),
			array('http://github.com/nette/nette', 'https://github.com/nette/nette'),
			array('github.com/nette/nette', 'https://github.com/nette/nette'),
			array('https://github.com/nette/nette/commits/master', 'https://github.com/nette/nette'),
			array('https://github.com/nette/nette.git', 'https://github.com/nette/nette'),
			array('git://github.com/nette/nette.git', 'https://github.com/nette/nette'),
		);
	}

	/**
	 * @dataProvider dataNormalizeGitHubUrl
	 * @param string
	 * @param string
	 */
	public function testNormalizeGitHubUrl($input, $expected)
	{
		$this->assertEquals(
			$expected, \NetteAddons\Model\Importers\GitHub\Helpers::normalizeRepositoryUrl($input),
			"normalize: $input"
		);
	}

}
