<?php

namespace NetteAddons;

use Nette,
	Nette\Utils\Strings,
	Texy,
	TexyHtml,
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
	const FORMAT_MARKDOWN = 'markdown';

	/** @var Model\Utils\Licenses */
	private $licenses;


	/**
	 * @param Model\Utils\Licenses
	 */
	public function __construct(Model\Utils\Licenses $licenses)
	{
		$this->licenses = $licenses;
	}



	/**
	 * @param Model\Addon
	 * @return array
	 * @throws NotImplementedException
	 */
	public function processDescription(Addon $addon)
	{
		if ($addon->descriptionFormat === self::FORMAT_MARKDOWN) {
			$markdown = new \MarkdownExtra_Parser();
			return array(
				'content' => $markdown->transform($addon->description),
				'toc' => array()
			);

		} else {
			$texy = $this->createTexy();
			return array(
				'content' => $texy->process($addon->description),
				'toc' => $texy->headingModule->TOC
			);
		}
	}



	/**
	 * @param string|array
	 * @return string
	 */
	public function processLicenses($licenses)
	{
		if (is_string($licenses)) {
			$licenses = array_map('trim', explode(',', $licenses));
		}

		foreach ($licenses as $license) {
			$el = $license;
			if ($this->licenses->isValid($license)) {
				$el = \Nette\Utils\Html::el('a');
				$el->href = $this->licenses->getUrl($license);
				$el->title = $this->licenses->getFullName($license);
				$el->add($license);
			}

			if (empty($s)) {
				$s = (string) $el;
			} else {
				$s .= ', ' . $el;
			}
		}
		return $s;
	}



	/**
	 * @return \Texy
	 */
	public function createTexy()
	{
		$texy = new Texy;
		$texy->setOutputMode(Texy::HTML5);
		$texy->linkModule->root = '';
		$texy->alignClasses['left'] = 'left';
		$texy->alignClasses['right'] = 'right';
		$texy->emoticonModule->class = 'smiley';
		$texy->headingModule->top = 2;
		$texy->headingModule->generateID = TRUE;
		$texy->tabWidth = 4;
		$texy->tableModule->evenClass = 'alt';
		$texy->dtd['body'][1]['style'] = TRUE;
		$texy->allowed['longwords'] = FALSE;
		$texy->allowed['block/html'] = FALSE;
		$texy->phraseModule->tags['phrase/strong'] = 'b';
		$texy->phraseModule->tags['phrase/em'] = 'i';
		$texy->phraseModule->tags['phrase/em-alt'] = 'i';

		$texy->addHandler('block', array($this, 'blockHandler'));

		return $texy;
	}



	/**
	 * User handler for code block.
	 *
	 * @param  TexyHandlerInvocation  handler invocation
	 * @param  string  block type
	 * @param  string  text to highlight
	 * @param  string  language
	 * @param  TexyModifier modifier
	 * @return TexyHtml
	 */
	public function blockHandler($invocation, $blockType, $content, $lang, $modifier)
	{
		if ($blockType === 'block/php' || $blockType === 'block/neon' || $blockType === 'block/javascript' || $blockType === 'block/js' || $blockType === 'block/css' || $blockType === 'block/html' || $blockType === 'block/htmlcb' || $blockType === 'block/latte') {
			list(, $lang) = explode('/', $blockType);

		} elseif ($blockType !== 'block/code') {
			return $invocation->proceed($blockType, $content, $lang, $modifier);
		}

		$fshl = new Highlighter(new Html, Highlighter::OPTION_TAB_INDENT);

		switch(strtolower($lang)) {
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
				return $invocation->proceed();
				break;
		}

		$texy = $invocation->getTexy();
		$content = Texy::outdent($content);
		$content = $fshl->highlight($content);
		$content = $texy->protect($content, Texy::CONTENT_BLOCK);

		$elPre = TexyHtml::el('pre');
		if ($modifier) $modifier->decorate($texy, $elPre);
		$elPre->attrs['class'] = 'src-' . strtolower($lang);

		$elCode = $elPre->create('code', $content);

		return $elPre;
	}

}
