<?php

namespace NetteAddons\Model;


class AddonResources extends Table
{
	const RESOURCE_GITHUB = 'github';
	const RESOURCE_PACKAGIST = 'packagist';
	const RESOURCE_DEMO = 'demo';

	/** @var string */
	protected $tableName = 'addons_resources';
}
