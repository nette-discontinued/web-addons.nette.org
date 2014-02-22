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


	/**
	 * @param string
	 * @return string[]|array (content => string, toc => string[]|array)
	 */
	public function process($input)
	{
		$this->converter->parse($input);

		return array(
			'content' => $this->converter->html,
			'toc' => $this->converter->toc,
		);
	}
}
