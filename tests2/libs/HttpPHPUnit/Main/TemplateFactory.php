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
		$httpReqFaq = new \Nette\Http\RequestFactory();
		$httpReq = $httpReqFaq->setEncoding('utf-8')->createHttpRequest();
		return rtrim($httpReq->url->basePath, '/') . '/libs/HttpPHPUnit';
	}

}
