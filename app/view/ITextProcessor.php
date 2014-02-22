<?php

namespace NetteAddons;


interface ITextProcessor
{
	/**
	 * @param string
	 * @return string[]|array
	 */
	public function process($text);
}
