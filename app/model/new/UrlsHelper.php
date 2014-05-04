<?php

namespace NetteAddons\Model;

use Nette\Utils\Strings;
use Nette\Utils\Validators;

class UrlsHelper extends \Nette\Object
{
	const PACKAGIST_REGEXP = 'packagist\.org/packages/([a-z0-9]+(-[a-z0-9]+)*)/([a-z0-9]+(-[a-z0-9]+)*)';
	const GITHUB_REGEXP = 'github\.com(/|:)([a-zA-Z0-9]+((-|.)[a-zA-Z0-9]+)*)/([a-zA-Z0-9]+((-|.)[a-zA-Z0-9]+)*)';

	public function __construct()
	{
		throw new \NetteAddons\StaticClassException;
	}

	/**
	 * @param string
	 * @return bool
	 */
	public static function isPackagistPackageUrl($url)
	{
		return (bool) Strings::match($url, '~' . self::PACKAGIST_REGEXP . '~');
	}

	/**
	 * @param string
	 */
	public static function assertPackagistPackageUrl($url)
	{
		Validators::assert($url, 'string', 'url');
		if (!static::isPackagistPackageUrl($url)) {
			throw new \Nette\Utils\AssertionException('The url is not valid Packagist package URL.');
		}
	}

	/**
	 * @param string
	 * @return string
	 */
	public static function normalizePackagistPackageUrl($url)
	{
		self::assertPackagistPackageUrl($url);
		$match = Strings::match($url, '~' . self::PACKAGIST_REGEXP  . '~');
		return 'https://' . Strings::lower($match[0]);
	}

	/**
	 * @param string
	 * @return bool
	 */
	public static function isGithubRepositoryUrl($url)
	{
		return (bool) Strings::match($url, '~' . self::GITHUB_REGEXP  . '~');
	}

	/**
	 * @param string
	 */
	public static function assertGithubRepositoryUrl($url)
	{
		Validators::assert($url, 'string', 'url');
		if (!static::isGithubRepositoryUrl($url)) {
			throw new \Nette\Utils\AssertionException('The url is not valid GitHub repository URL.');
		}
	}

	/**
	 * @param string
	 * @return string
	 */
	public static function normalizeGithubRepositoryUrl($url)
	{
		self::assertGithubRepositoryUrl($url);
		$url = str_replace('github.com:', 'github.com/', $url);
		if (Strings::endsWith($url, '.git')) {
			$url = Strings::substring($url, 0, -4);
		}
		$match = Strings::match($url, '~' . self::GITHUB_REGEXP  . '~');
		return 'https://' . Strings::lower($match[0]);
	}
}
