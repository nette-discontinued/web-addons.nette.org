<?php

namespace NetteAddons;

final class RedirectPresenter extends \Nette\Application\UI\Presenter
{
	const NEW_PORTAL_URL_MASK = 'https://componette.com/%s/';

	/** @var string[] */
	private $map;


	public function __construct()
	{
		parent::__construct();

		$this->map = [
			'kdyby/events' => 'kdyby/events'
		];
	}


	/**
	 * @param string
	 */
	public function actionDefault($slug)
	{
		$slug = strtolower($slug);

		if (isset($this->map[$slug])) {
			$this->redirectUrl(sprintf(self::NEW_PORTAL_URL_MASK, $this->map[$slug]));
		}

		$this->error('Addon not found', 404);
	}
}
