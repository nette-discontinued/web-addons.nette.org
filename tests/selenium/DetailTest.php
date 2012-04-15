<?php

namespace NetteAddons\Test;

/**
 * @author Jan Marek
 */
class DetailTest extends SeleniumTestCase
{

	protected function setUp()
	{
		parent::setUp();

		$this->reinstallDb();
	}

	public function testHeaders()
	{
		$this->url('/detail/1');
		$this->assertEquals('WebLoader', $this->byCssSelector('h1')->text());
		$this->assertEquals('JanMarek/WebLoader', $this->byCssSelector('h4.vendor')->text());
	}

	public function testTags()
	{
		$this->url('/detail/1');
		$this->assertCount(2, $this->elements(
			$this->using('css selector')->value('div.box-tags a.label.label-info')
		), 'Wrong count of categories');
		$this->assertCount(4, $this->elements(
			$this->using('css selector')->value('div.box-tags a.label')
		), 'Wrong count of tags');
	}

}
