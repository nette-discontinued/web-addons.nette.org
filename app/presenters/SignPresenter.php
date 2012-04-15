<?php

namespace NetteAddons;

use Nette\Security as NS;

class SignPresenter extends BasePresenter
{

	public function renderIn($backlink)
	{

	}

	protected function createComponentSignInForm()
	{
		$form = new SignInForm();
		$form->onSuccess[] = callback($this, 'signInFormSubmitted');

		return $form;
	}



	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			if ($values->remember) {
				$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('+ 20 minutes', TRUE);
			}
			$this->getUser()->login($values->username, $values->password);
			if (($backlink = $this->getParameter('backlink')) === NULL) {
				$this->redirect('Homepage:');
			} else {
				$this->restoreRequest($backlink);
			}

		} catch (NS\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}



	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('in');
	}

}
