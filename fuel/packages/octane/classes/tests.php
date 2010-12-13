<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Fuel Development Team
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

namespace Fuel\Octane;

use Fuel\Application\Fuel;
use Fuel\Application\Cli;
use Fuel\Application\Exception;

class Tests {
	
	public static $results = array(
		'passes'		=> 0,
		'failures'		=> 0,
		'assertions'	=> 0,
	);

	public static $classes = array();
	
	public static function run_all($args)
	{
		foreach (Fuel::get_paths() as $path)
		{
			if (is_dir($path.'tests/classes'))
			{
				static::load_tests($path.'tests/classes/');
			}
		}

		static::output_header('All Tests');
		static::_run_tests();
		static::output_results();
	}

	public static function __callStatic($name, $args)
	{
		if ($name == '_init')
		{
			return;
		}

		if (strncmp($name, 'run_', 4) !== 0)
		{
			throw new Exception('Invalid method call: '.$name);
			return;
		}

		$name = substr($name, 4);
		foreach (Fuel::get_paths() as $path)
		{
			if (is_dir($path.'tests/classes/'.$name.'/'))
			{
				static::load_tests($path.'tests/classes/'.$name.'/');
			}
		}

		static::output_header(ucfirst($name).' Tests');
		static::_run_tests();
		static::output_results();
	}


	protected static function _run_tests()
	{
		foreach (static::$classes as $class)
		{
			$class = '\\Fuel\\Octane\\Test\\'.ucfirst($class).'Test';

			$test = new $class;
			$methods = get_class_methods($test);

			foreach ($methods as $method)
			{
				if (strncmp($method, 'test_', 5) !== 0)
				{
					continue;
				}
				$test->$method();
				if ($test->results[$method])
				{
					static::$results['passes']++;
				}
				else
				{
					static::$results['failures']++;
				}
			}
		}
	}

	public static function output_header($description = '')
	{
		Cli::write('-------------------------------------------------');
		Cli::write(' Octane Unit Testing');
		Cli::write(' Running Test: '.$description);
		Cli::write('-------------------------------------------------');
		Cli::write();
	}

	public static function output_results()
	{
		$passes = Cli::color('Passes: '.static::$results['passes'], 'green');
		$failures = Cli::color('Failures: '.static::$results['failures'], 'red');
		$assertions = 'Assertions: '.static::$results['assertions'];
		Cli::write();
		Cli::write($passes.' | '.$failures.' | '.$assertions);
	}

	public static function load_tests($path)
	{
		$dir = opendir($path);
		while (false !== ($file = readdir($dir)))
		{
			if ($file !== '.' && $file !== '..')
			{
				if (strpos($file, '.php') === false)
				{
					static::load_tests($path.$file);
				}
				else
				{
					static::$classes[] = basename($file, '.php');
					require_once $path.$file;
				}
			}
	    }
	}
}