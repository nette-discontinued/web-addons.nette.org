<?php
/**
 * Access
 * @link http://github.com/PetrP/Access
 * @author Petr Procházka (petr@petrp.cz)
 * @license "New" BSD License
 */

if (!defined('PHP_VERSION_ID'))
{
	// php < 5.2.7
	$tmp = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($tmp[0] * 10000 + $tmp[1] * 100 + $tmp[2]));
}

require_once dirname(__FILE__) . '/Base.php';
require_once dirname(__FILE__) . '/Class.php';
require_once dirname(__FILE__) . '/Method.php';
require_once dirname(__FILE__) . '/Property.php';
if (PHP_VERSION_ID >= 50300)
{
	require_once dirname(__FILE__) . '/Accessor.php';
}
if (PHP_VERSION_ID <= 50302)
{
	require_once dirname(__FILE__) . '/AccessorPhp52.php';
}

/**
 * Access to method, property or whole class.
 *
 * Method:
 * <code>
 * $a = Access(new Object, 'methodName');
 * $a->call();
 * $a->call(1, 2, 3);
 * </code>
 *
 * Property (prefixed with dollar sign):
 * <code>
 * $a = Access(new Object, '$propertyName');
 * $a->set(123);
 * $a->get(123);
 * </code>
 *
 * Whole class
 * <code>
 * $a = Access(new Object);
 *
 * $a->method();
 * $a->method(1, 2, 3);
 *
 * $a->property = 123;
 * $a->property;
 * </code>
 *
 * @param object|string object or class name
 * @param string|NULL $property or method
 * @return AccessMethod|AccessProperty|AccessClass
 */
function Access($object, $what = NULL)
{
	if ($what === NULL)
	{
		return new AccessClass($object);
	}
	else if ($what{0} === '$')
	{
		return new AccessProperty($object, substr($what, 1));
	}
	else
	{
		return new AccessMethod($object, $what);
	}
}
