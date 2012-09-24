<?php

namespace NetteAddons\Model\Utils;

use Nette;
use Composer\Package\Version\VersionParser as ComposerVersionParser;



/**
 * @author Jan Tvrdík
 */
class VersionParser extends Nette\Object
{
	/** @var ComposerVersionParser */
	private $parser;



	/**
	 * Tries to parse and normalize version string. Returns FALSE in case of failure.
	 *
	 * @param  string tag name or any other textual version representation
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
		$parsedTag = preg_replace('#\.0(\-[a-z0-9]+)?$#i', '$1', $parsedTag);
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
	 * @return ComposerVersionParser
	 */
	private function getParser()
	{
		if ($this->parser === NULL) {
			$this->parser = new ComposerVersionParser();
		}
		return $this->parser;
	}
}
