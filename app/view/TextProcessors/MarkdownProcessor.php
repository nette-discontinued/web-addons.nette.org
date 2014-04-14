<?php

namespace NetteAddons\TextProcessors;

use Nette\Utils\Strings;
use Nette\Utils\Callback;
use FSHL\Highlighter;
use FSHL\Output\Html;
use FSHL\Lexer;

class MarkdownProcessor extends \Nette\Object implements \NetteAddons\ITextProcessor
{
	/** @var callable */
	private $converter;


	public function __construct($htmlPurifierCachePath)
	{
		$markdown = new \Michelf\MarkdownExtra;

		$htmlPurifierConfig = \HTMLPurifier_Config::createDefault();
		$htmlPurifierConfig->set('Cache.SerializerPath', $htmlPurifierCachePath);
		$htmlPurifier = new \HTMLPurifier($htmlPurifierConfig);

		$this->converter = function ($description) use ($markdown, $htmlPurifier) {
			$description = Strings::replace(
				$description,
				'/([^#]*)(#{1,6})(.*)/',
				function ($matches) {
					return $matches[1] . (strlen($matches[2]) < 6 ? '#' : '') . $matches[2] . $matches[3];
				}
			);
			$description = Strings::replace(
				$description,
				'/```(php|neon|javascript|js|css|html|htmlcb|latte|sql)?\h*\v(.+?)\v```/s',
				function ($matches) {
					$fshl = new Highlighter(new Html, Highlighter::OPTION_TAB_INDENT);

					switch(strtolower($matches[1])) {
						case 'php':
							$fshl->setLexer(new Lexer\Php);
							break;
						case 'neon':
							$fshl->setLexer(new Lexer\Neon);
							break;
						case 'javascript':
						case 'js':
							$fshl->setLexer(new Lexer\Javascript);
							break;
						case 'css':
							$fshl->setLexer(new Lexer\Css);
							break;
						case 'html':
						case 'htmlcb':
						case 'latte':
							$fshl->setLexer(new Lexer\Html);
							break;
						case 'sql':
							$fshl->setLexer(new Lexer\Sql);
							break;
						default:
							$fshl->setLexer(new Lexer\Minimal);
							break;
					}

					return '<pre' . ($matches[1] ? ' class="src-' . strtolower($matches[1]) . '"' : '') . '><code>' . $fshl->highlight(ltrim($matches[2])) . '</code></pre>';
				}
			);
			return $htmlPurifier->purify($markdown->transform($description));
		};
	}

	/**
	 * @param string
	 * @return string[]|array (content => string, toc => string[]|array)
	 */
	public function process($input)
	{
		return array(
			'content' => Callback::invoke($this->converter, $input),
			'toc' => array(),
		);
	}
}
