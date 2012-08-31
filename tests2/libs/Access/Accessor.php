<?php
/**
 * Access
 * @link http://github.com/PetrP/Access
 * @author Petr Procházka (petr@petrp.cz)
 * @license "New" BSD License
 */

/**
 * Accessor for PHP 5.3.
 *
 * Uses reflection setAccessible.
 *
 * AccessMethod PHP > 5.3.2
 * AccessProperty PHP > 5.3.0
 *
 * @see AccessAccessorPhp52
 */
class AccessAccessor extends AccessBase
{

	public function __construct()
	{

	}

	/**
	 * @param ReflectionMethod
	 * @return callable(object|NULL $instance, array $args)
	 */
	public function accessMethod(ReflectionMethod $method)
	{
		if (PHP_VERSION_ID < 50302)
		{
			throw new Exception('AccessMethod needs PHP 5.3.2 or newer.');
		}
		$method->setAccessible(true);

		return function ($instance, array $args) use ($method) {
			return call_user_func_array(array($method, 'invoke'), array_merge(array($instance), $args));
		};
	}

	/**
	 * @param ReflectionProperty
	 * @return object get => callable(object|NULL $instance), set => callable(object|NULL $instance, mixed $value)
	 */
	public function accessProperty(ReflectionProperty $property)
	{
		if (PHP_VERSION_ID < 50300)
		{
			throw new Exception('AccessProperty needs PHP 5.3.0 or newer.');
		}
		$property->setAccessible(true);

		return (object) array(
			'get' => function ($instance) use ($property) {
				if ($instance)
				{
					return $property->getValue($instance);
				}
				return $property->getValue();
			},
			'set' => function ($instance, $value) use ($property) {
				if ($instance)
				{
					$property->setValue($instance, $value);
				}
				else
				{
					$property->setValue($value);
				}
			},
		);
	}
}
