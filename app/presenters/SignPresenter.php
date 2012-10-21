<?php

namespace NetteAddons;

use Nette\Security\AuthenticationException,
	Nette\Security\IAuthenticator;

/**
 * @author Patrik Votoček
 */
class SignPresenter extends BasePresenter
{

	public function renderIn($backlink)
	{

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



	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('in');
	}

}
