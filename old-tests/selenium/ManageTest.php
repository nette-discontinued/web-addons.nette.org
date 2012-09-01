<?php

namespace NetteAddons\Test;


/**
 *
 */
class ManageTest extends SeleniumTestCase
{
	public function logIn()
	{
		$this->url('/sign/in');
		$user = $this->byId('frmsignInForm-username');
		$user->value('panda');
		$password = $this->byId('frmsignInForm-password');
		$password->value('heslo');
		$password->submit();
	}

	/**
	 * Guest user should not be able to create a new addon.
	 */
	public function testNotSignedIn()
	{
		$this->url('/manage/add');
		$this->assertContains('/sign/in', $this->url());
	}


	/**
	 * Signed in user should be able to access the addon creation.
	 */
	public function testSignedIn()
	{
		$this->logIn();
		$this->url('/manage/add');
		$this->assertContains('/manage/add', $this->url());
	}
}
