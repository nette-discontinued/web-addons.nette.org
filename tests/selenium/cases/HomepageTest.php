<?php

namespace NetteAddons\Model;



/**
 * @author Jan Marek
 */
class HomepageTest extends SeleniumTestCase
{
	public function testHomepage()
	{
		$this->reinstallDb();

		$this->url('/');

		$this->assertContains('Addons', $this->title());
	}
}
