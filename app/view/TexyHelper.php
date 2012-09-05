<?php

namespace NetteAddons;

use Texy;
use TexyHtml;



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

		return $texy;
	}
}
