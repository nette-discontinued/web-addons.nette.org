<?php

namespace NetteAddons;

use Nette;



/**
 * Custom presenter factory with base namespace support.
 *
 * @author   Jan Tvrdík
 */
class PresenterFactory extends Nette\Application\PresenterFactory
{
	/** Base namespace for whole application */
	const BASE_NAMESPACE = __NAMESPACE__;

	/**
	 * Formats presenter class name from its name.
	 *
	 * @author   Jan Tvrdík
	 * @param    string
	 * @return   string
	 */
	public function formatPresenterClass($presenter)
	{
		if (substr_compare($presenter, 'Nette:', 0, 6) === 0)
		{
			return parent::formatPresenterClass($presenter);
		}

		return self::BASE_NAMESPACE . '\\' . str_replace(':', '\\', $presenter) . 'Presenter';
	}

	/**
	 * Formats presenter name from class name.
	 *
	 * @author   Jan Tvrdík
	 * @param    string
	 * @return   string
	 */
	public function unformatPresenterClass($class)
	{
		if (substr_compare($class, self::BASE_NAMESPACE . '\\', 0, strlen(self::BASE_NAMESPACE) + 1) !== 0)
		{
			return parent::unformatPresenterClass($class);
		}

		return str_replace('\\', ':', substr($class, strlen(self::BASE_NAMESPACE) + 1, -9));
	}

}
