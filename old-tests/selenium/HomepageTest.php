<?php

namespace NetteAddons\Test;

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
