<?php

namespace NetteAddons;

use NetteAddons\TemplateFactory;
use NetteAddons\Model;
use NetteAddons\Model\Authorizator;



abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
	/** @var Authorizator */
	protected $auth;

	/** @var TemplateFactory */
	protected $tplFactory;

	/** @var Model\Tags */
	protected $tags;



	public function injectAuthorizator(Authorizator $auth)
	{
		$this->auth = $auth;
	}



	public function injectTemplateFactory(TemplateFactory $factory)
	{
		$this->tplFactory = $factory;
	}



	public function injectTags(Model\Tags $tags)
	{
		$this->tags = $tags;
	}



	public function createTemplate($class = NULL)
	{
		return $this->tplFactory->createTemplate(NULL, $this);
	}



	protected function beforeRender()
	{
		$this->template->auth = $this->auth;
		$this->template->categories = $this->tags->findMainTags();
		$this->template->tags = $this->tags;
	}
}
