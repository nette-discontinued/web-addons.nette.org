<?php

namespace NetteAddons\Forms;


class SignInForm extends BaseForm
{
	protected function buildForm()
	{
		$this->addText('username', 'Username:')
			->setRequired('Please provide a username.');

		$this->addPassword('password', 'Password:')
			->setRequired('Please provide a password.');

		$this->addCheckbox('remember', 'Remember me?');

		$this->addSubmit('login', 'Login');
	}
}
