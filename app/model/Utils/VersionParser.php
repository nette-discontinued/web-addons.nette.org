<?php

namespace NetteAddons\Model\Utils;

use NetteAddons\Model\AddonVersion;
use Composer\Package\Version\VersionParser as ComposerVersionParser;
use Composer\Package\LinkConstraint\VersionConstraint;


class VersionParser extends \Nette\Object
{
	/** @var ComposerVersionParser */
	private $parser;


	/**
	 * Tries to parse and normalize version string. Returns FALSE in case of failure.
	 *
	 * @param string tag name or any other textual version representation
	 * @return string|FALSE
	 */
	public function parseTag($tag)
	{
		// Inspired by https://github.com/composer/composer/blob/8d7e5c/src/Composer/Repository/VcsRepository.php#L116
		$tag = str_replace('release-', '', $tag);

		try {
			$parsedTag = $this->getParser()->normalize($tag);
		} catch (\UnexpectedValueException $e) {
			return FALSE;
		}

		$parsedTag = preg_replace('#\\.0(\\-[a-z0-9]+)?$#i', '$1', $parsedTag);

		return $parsedTag;
	}


	/**
	 * @param  string branch name (e.g. '2.0.x')
	 * @return string version string (e.g. '2.0.x-dev')
	 */
	public function parseBranch($branch)
	{
		// Inspired by https://github.com/composer/composer/blob/8d7e5c/src/Composer/Repository/VcsRepository.php#L205
		$branch = str_replace('release-', '', $branch);
		$parsedBranch = $this->getParser()->normalizeBranch($branch);

		if ('dev-' === substr($parsedBranch, 0, 4) || '9999999-dev' === $parsedBranch) {
			return 'dev-' . $branch;
		} else {
			return preg_replace('{(\.9{7})+}', '.x', $parsedBranch);
		}
	}


	/**
	 * Parses version and returns its stability.
	 *
	 * @param  string
	 * @return string
	 */
	public function parseStability($version)
	{
		return ComposerVersionParser::parseStability($version);
	}


	/**
	 * @param AddonVersion[]
	 * @return AddonVersion[]
	 */
	public function filterStable($versions)
	{
		$that = $this;
		return array_filter($versions, function (AddonVersion $version) use ($that) {
			return $that->parseStability($version->version) === 'stable';
		});
	}


	/**
	 * Compares two versions and returns 0 if $a == $b, -1 if $a < $b and +1 if $b > $a.
	 *
	 * @param AddonVersion
	 * @param AddonVersion
	 * @return int
	 */
	public function compare(AddonVersion $a, AddonVersion $b)
	{
		$parser = $this->getParser();
		$a = $parser->normalize($a->version);
		$b = $parser->normalize($b->version);

		$constraint = new VersionConstraint(NULL, NULL);
		if ($constraint->versionCompare($a, $b, '==')) {
			return 0;
		} elseif ($constraint->versionCompare($a, $b, '<')) {
			return -1;
		} else {
			return 1;
		}
	}


	/**
	 * @param AddonVersion[]
	 * @param bool
	 * @return void
	 */
	public function sort(&$versions, $reverse)
	{
		$reverse = ($reverse ? -1 : 1);
		$that = $this;
		uksort($versions, function ($a, $b) use ($that, $versions, $reverse) {
			return $that->compare($versions[$a], $versions[$b]) * $reverse;
		});
	}


	/**
	 * @return ComposerVersionParser
	 */
	private function getParser()
	{
		if ($this->parser === NULL) {
			$this->parser = new ComposerVersionParser;
		}
		return $this->parser;
	}
}
