<?php

namespace NetteAddons;

use Nette\Http\Request,
	Nette\Http\UrlScript,
	Nette\Utils\Strings,
	Nette\Application\Routers\RouteList,
	Nette\Security\AuthenticationException,
	Nette\Security\IAuthenticator;

/**
 * @author Patrik Votoček
 */
class SignPresenter extends BasePresenter
{
	/** @var \Nette\Application\Routers\RouteList */
	private $router;



	/**
	 * @param \Nette\Application\Routers\RouteList
	 */
	public function injectRouter(RouteList $router)
	{
		$this->router = $router;
	}



	public function renderIn($backlink)
	{
		$referer = $this->getHttpRequest()->referer;
		if (!$backlink && $referer && $referer->host == $this->getHttpRequest()->url->host) {
			$url = new UrlScript($referer);
			$tmp = new Request($url);
			$req = $this->router->match($tmp);
			if (!$req) {
				return;
			}
			if (isset($req->parameters[static::SIGNAL_KEY])) {
				$params = $req->parameters;
				unset($params[static::SIGNAL_KEY]);
				$req->setParameters($params);
			}
			if ($req->getPresenterName() != $this->getName()) {
				$session = $this->getSession('Nette.Application/requests');
				do {
					$key = Strings::random(5);
				} while (isset($session[$key]));

				$session[$key] = array($this->getUser()->getId(), $req);
				$session->setExpiration('+ 10 minutes', $key);

				$this->params['backlink'] = $key;
			}
		}
	}


	/**
	 * @return Forms\SignInForm
	 */
	protected function createComponentSignInForm()
	{
		$form = new Forms\SignInForm();
		$form->onSuccess[] = $this->signInFormSubmitted;

		return $form;
	}



	/**
	 * @param Forms\SignInForm
	 */
	public function signInFormSubmitted(Forms\SignInForm $form)
	{
		try {
			$values = $form->values;
			$user = $this->getUser();

			if ($values->remember) {
				$user->setExpiration('+ 14 days', FALSE);
			} else {
				$user->setExpiration('+ 20 minutes', TRUE);
			}
			$user->login($values->username, $values->password);
			if (($backlink = $this->getParameter('backlink')) === NULL) {
				$this->redirect(':Homepage:');
			} else {
				$this->restoreRequest($backlink);
			}

		} catch (AuthenticationException $e) {
			if ($e->getCode() == IAuthenticator::IDENTITY_NOT_FOUND) {
				$form['username']->addError("User '$values->username' not found.");
			} elseif ($e->getCode() == IAuthenticator::INVALID_CREDENTIAL) {
				$form['password']->addError('Invalid password.');
			} else {
				$form->addError('Invalid credentials.');
			}
		}
	}



	/**
	 * Restores current request to session.
	 *
	 * @todo remove non canonic redirect
	 *
	 * @param  string key
	 * @return void
	 */
	public function restoreRequest($key)
	{
		$session = $this->getSession('Nette.Application/requests');
		if (!isset($session[$key]) || ($session[$key][0] !== NULL && $session[$key][0] !== $this->getUser()->getId())) {
			$this->redirect(':Homepage:');
		}
		$request = clone $session[$key][1];
		unset($session[$key]);
		$request->setFlag(\Nette\Application\Request::RESTORED, TRUE);
		$params = $request->getParameters();
		$params[self::FLASH_KEY] = $this->getParameter(self::FLASH_KEY);
		$action = $params[self::ACTION_KEY];
		unset($params[self::ACTION_KEY]);
		$this->redirect(':' . $request->presenterName . ':' . $action, $request->parameters);
	}



	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('in');
	}

}
