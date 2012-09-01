<?php

namespace HttpPHPUnit;

use PHPUnit_TextUI_Command;

/**
 * @author Petr Prochazka
 */
class Command extends PHPUnit_TextUI_Command
{
	public function run(array $argv, $printer = NULL)
	{
		$this->arguments['printer'] = $printer;
		parent::run($argv, false);
	}
}
