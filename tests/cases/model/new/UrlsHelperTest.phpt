<?php

/**
 * Test: NetteAddons\Model\UrlsHelper
 *
 * @testcase
 */

namespace NetteAddons\Test\Model;

use Tester\Assert;
use NetteAddons\Test\TestCase;
use NetteAddons\Model\UrlsHelper;

require_once __DIR__ . '/../../../bootstrap.php';

class UrlsHelperTest extends TestCase
{
	public function dataPackagistPackageUrl()
	{
		return array(
			array('packagist.org/packages/nette/nette'),
			array('http://packagist.org/packages/nette/nette'),
			array('https://packagist.org/packages/nette/nette'),
			array('www.packagist.org/packages/nette/nette'),
			array('http://www.packagist.org/packages/nette/nette'),
			array('https://www.packagist.org/packages/nette/nette'),
		);
	}

	public function dataInvalidPackagistPackageUrl()
	{
		return array(
			array('packagist.com/packages/nette/nette'),
			array('packagist.org/package/nette/nette'),
			array('packagist.org/nette/nette'),
			array('github.com/nette/nette'),
		);
	}

	public function dataNetteGithubRepositoryUrl()
	{
		return array(
			array('github.com/nette/nette'),
			array('github.com/Nette/nette'),
			array('github.com/Nette/Nette'),
			array('www.github.com/nette/nette'),
			array('http://github.com/nette/nette'),
			array('https://github.com/nette/nette'),
			array('http://www.github.com/nette/nette'),
			array('https://www.github.com/nette/nette'),
			array('git://github.com/nette/nette'),
			array('git@github.com:nette/nette'),
		);
	}

	public function dataGithubRepositoryUrl()
	{
		$data = $this->dataNetteGithubRepositoryUrl();
		$data[] = array('github.com/ne-on/ne-on');
		$data[] = array('github.com/Vrtak-CZ/nette');
		return $data;
	}

	/**
	 * @dataProvider dataPackagistPackageUrl
	 */
	public function testIsPackagistPackageUrl($url)
	{
		Assert::true(UrlsHelper::isPackagistPackageUrl($url));
	}

	/**
	 * @dataProvider dataInvalidPackagistPackageUrl
	 */
	public function testIsNotPackagistPackageUrl($url)
	{
		Assert::false(UrlsHelper::isPackagistPackageUrl($url));
	}

	/**
	 * @dataProvider dataPackagistPackageUrl
	 */
	public function testAssertPackagistPackageUrl($url)
	{
		UrlsHelper::assertPackagistPackageUrl($url);
		Assert::true(true);
	}

	/**
	 * @dataProvider dataInvalidPackagistPackageUrl
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidAssertPackagistPackageUrl($url)
	{
		UrlsHelper::assertPackagistPackageUrl($url);
	}

	/**
	 * @dataProvider dataPackagistPackageUrl
	 */
	public function testNormalizePackagistPackageUrl($url)
	{
		Assert::equal('https://packagist.org/packages/nette/nette', UrlsHelper::normalizePackagistPackageUrl($url));
	}

	/**
	 * @dataProvider dataInvalidPackagistPackageUrl
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidNormalizePackagistPackageUrl($url)
	{
		UrlsHelper::normalizePackagistPackageUrl($url);
	}

	/**
	 * @dataProvider dataGithubRepositoryUrl
	 */
	public function testIsGithubRepositoryUrl($url)
	{
		Assert::true(UrlsHelper::isGithubRepositoryUrl($url));
	}

	/**
	 * @dataProvider dataPackagistPackageUrl
	 */
	public function testIsNotGithubRepositoryUrl($url)
	{
		Assert::false(UrlsHelper::isGithubRepositoryUrl($url));
	}

	/**
	 * @dataProvider dataGithubRepositoryUrl
	 */
	public function testAssertGithubRepositoryUrl($url)
	{
		UrlsHelper::assertGithubRepositoryUrl($url);
		Assert::true(true);
	}

	/**
	 * @dataProvider dataPackagistPackageUrl
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidAssertGithubRepositoryUrl($url)
	{
		UrlsHelper::assertGithubRepositoryUrl($url);
	}

	/**
	 * @dataProvider dataNetteGithubRepositoryUrl
	 */
	public function testNormalizeGithubRepositoryUrl($url)
	{
		Assert::equal('https://github.com/nette/nette', UrlsHelper::normalizeGithubRepositoryUrl($url));
	}

	/**
	 * @dataProvider dataPackagistPackageUrl
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidNormalizeGithubRepositoryUrl($url)
	{
		UrlsHelper::normalizeGithubRepositoryUrl($url);
	}
}

id(new UrlsHelperTest)->run();
