<?php

namespace NetteAddons;

use Texy;
use TexyHtml;
use FSHL\Highlighter;
use FSHL\Output\Html;
use FSHL\Lexer;



/**
 * @author Jan Marek
 */
class TexyHelper
{
	/** @var Texy */
	private $texy = NULL;



	public function __invoke($text)
	{
		if ($this->texy === NULL) {
			$this->texy = $this->createTexy();
		}

		return $this->texy->process($text);
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
		$texy->headingModule->top = 1;
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
	public function blockHandler($invocation, $blocktype, $content, $lang, $modifier)
	{
		if ($blocktype === 'block/php' || $blocktype === 'block/neon' || $blocktype === 'block/javascript' || $blocktype === 'block/js' || $blocktype === 'block/css' || $blocktype === 'block/html' || $blocktype === 'block/htmlcb' || $blocktype === 'block/latte') {
			list(, $lang) = explode('/', $blocktype);

		} elseif ($blocktype !== 'block/code') {
			return $invocation->proceed($blocktype, $content, $lang, $modifier);
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
