<?php

namespace NetteAddons;

use Nette,
	Nette\Utils\Strings,
	FSHL\Highlighter,
	FSHL\Output\Html,
	FSHL\Lexer,
	NetteAddons\Model\Addon;



/**
 * @author David Grudl
 * @author Jan Marek
 * @author Patrik Votoček
 * @author Jan Tvrdík
 */
class TextPreprocessor extends Nette\Object
{
	const FORMAT_TEXY = 'texy';
	const FORMAT_MARKDOWN = 'markdown';

	/** @var ITextProcessor[]|array */
	private $processors = array();

	/** @var Model\Utils\Licenses */
	private $licenses;


	public function __construct(Model\Utils\Licenses $licenses)
	{
		$this->licenses = $licenses;
	}



	/**
	 * @return TextPreprocessor
	 */
	public function addProcessor(ITextProcessor $processor, $format = self::FORMAT_TEXY)
	{
		$this->processors[$format] = $processor;
		return $this;
	}



	/**
	 * @param Model\Addon
	 * @return array
	 * @throws NotImplementedException
	 */
	public function processDescription(Addon $addon)
	{
		if ($addon->descriptionFormat === self::FORMAT_MARKDOWN) {
			$markdown = $this->createMarkdown();
			return array(
				'content' => $markdown->invoke($addon->description),
				'toc' => array()
			);

		} elseif (isset($this->processors[$addon->descriptionFormat])) {
			return $this->processors[$addon->descriptionFormat]->process($addon->description);
			return $this->processTexyContent($addon->description);
		} else {
			throw new \NetteAddons\NotImplementedException('Format "' . $addon->descriptionFormat . '" is not supported');
		}
	}



	/**
	 * @param  string|array
	 * @return \Nette\Utils\Html
	 */
	public function processLicenses($licenses)
	{
		if (is_string($licenses)) {
			$licenses = array_map('trim', explode(',', $licenses));
		}

		$container = \Nette\Utils\Html::el();
		foreach ($licenses as $license) {
			if (count($container->getChildren()) > 0) {
				$container->add(', ');
			}

			if ($this->licenses->isValid($license)) {
				$container->create('a', array(
					'href' => $this->licenses->getUrl($license),
					'title' => $this->licenses->getFullName($license),
				))->setText($license);
			} else {
				$container->add($license);
			}
		}
		return $container;
	}



	/**
	 * @return Nette\Callback
	 */
	public function createMarkdown()
	{
		$markdown = new \Michelf\MarkdownExtra;

		return new Nette\Callback(function ($description) use ($markdown) {
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
			return $markdown->transform($description);
		});
	}
}
