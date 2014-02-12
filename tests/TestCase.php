<?php

namespace NetteAddons\Test;

class TestCase extends \Tester\TestCase
{
	protected function tearDown()
	{
		parent::tearDown();
		\Mockery::close();
	}
}