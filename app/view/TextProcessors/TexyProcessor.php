<?php

namespace NetteAddons\TextProcessors;

class TexyProcessor extends \Nette\Object implements \NetteAddons\ITextProcessor
{
	/** @var \NetteAddons\TextProcessors\Texy\AddonsConverter */
	private $converter;

	public function __construct()
	{
		$this->converter = new Texy\AddonsConverter;
	}

	public function process($input)
	{
		$this->converter->parse($input);

		return array(
			'content' => $this->converter->html,
			'toc' => $this->converter->toc,
		);
	}
}