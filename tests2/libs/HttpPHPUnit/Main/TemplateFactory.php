<?php

namespace HttpPHPUnit;

use Nette\Object;
use Nette\Templating\FileTemplate;
use Nette\Latte\Engine;
use Exception;

/**
 * @author Petr Prochazka
 */
class TemplateFactory extends Object
{
	public static function create($file)
	{
		$template = new FileTemplate;
		$template->onPrepareFilters[] = function ($template) {
			$template->registerFilter(new Engine);
		};
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');
		$template->setFile($file);
		$template->basePath = self::getBasePath();
		return $template;
	}

	public static function getBasePath()
	{
		$dir = realpath(__DIR__ . '/..');
		$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
		if (!$documentRoot) throw new Exception;
		$documentRoot = rtrim($documentRoot, DIRECTORY_SEPARATOR);
		$tmp = $documentRoot . DIRECTORY_SEPARATOR;
		if ($documentRoot != $dir AND strncmp($dir, $tmp, strlen($tmp)) !== 0) throw new Exception;
		return str_replace('\\', '/', substr($dir, strlen($documentRoot)));
	}

}
