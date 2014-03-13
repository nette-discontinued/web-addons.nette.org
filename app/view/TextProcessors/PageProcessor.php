<?php

namespace NetteAddons\TextProcessors;


class PageProcessor extends \Nette\Object
{
	/**
	 * @param string
	 * @param string|NULL
	 * @return string[]|array (title => string, content => string, toc => string[]|array)
	 */
	public function process($input, $currentSlug = NULL)
	{
		$converter = new Texy\Converter('addons', NULL, $currentSlug);
		$converter->paths['domain'] = 'nette.org';
		$converter->parse($input);

		return array(
			'title' => $converter->title,
			'content' => $converter->html,
			'toc' => $this->normalizeToc($converter->toc),
		);
	}


	/**
	 * @param stdClass[]
	 * @return array
	 */
	private function normalizeToc(array $input)
	{
		$output = array();

		foreach ($input as $item) {
			$output[] = array(
				'level' => $item->level,
				'title' => $item->title,
				'el' => (object) array('id' => $item->id),
			);
		}

		return $output;
	}
}
