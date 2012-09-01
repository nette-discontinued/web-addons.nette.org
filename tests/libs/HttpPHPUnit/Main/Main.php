<?php

namespace HttpPHPUnit;

use Nette\Object;
use Nette\DirectoryNotFoundException;
use Nette\Utils\Finder;
use Exception;
use PHP_CodeCoverage;

/**
 * <pre>
 * 	require_once __DIR__ . '/libs/Nette/loader.php';
 * 	require_once __DIR__ . '/libs/HttpPHPUnit/init.php';
 *
 * 	$http = new HttpPHPUnit;
 * 	$http->structure();
 * 	$http->coverage(__DIR__ . '/../app', __DIR__ . '/report');
 * 	$http->run(__DIR__ . '/tests');
 *
 * </pre>
 * @author Petr Prochazka
 */
class Main extends Object
{

	/** @var bool|NULL null mean autodetect */
	public $debug = NULL;

	/** @var array phpunit params */
	private $arg = array();

	/** @var string */
	private $testDir;

	/** @var string|NULL */
	private $method = NULL;

	/** @var bool */
	private $run;

	/** @var array of callback before run test */
	private $onBefore = array();

	/** @var array of callback after run test */
	private $onAfter = array();

	/**
	 * @param string path to PHPUnit
	 * @throws DirectoryNotFoundException
	 */
	public function __construct($phpUnitDir = NULL)
	{
		if (!$phpUnitDir) $phpUnitDir = __DIR__ . '/../../PHPUnit';
		if (!is_dir($phpUnitDir)) throw new DirectoryNotFoundException($phpUnitDir);
		set_include_path($phpUnitDir);
		require_once 'PHPUnit/Autoload.php';
		require_once __DIR__ . '/Command.php';
		require_once __DIR__ . '/TemplateFactory.php';
		require_once __DIR__ . '/../ResultPrinter/ResultPrinter.php';
		require_once __DIR__ . '/../ResultPrinter/OpenInEditor.php';
		require_once __DIR__ . '/../ResultPrinter/ResultPrinterTestCaseHelper.php';
		require_once __DIR__ . '/../ResultPrinter/NetteDebug.php';
		require_once __DIR__ . '/../StructureRenderer/StructureRenderer.php';

		$this->testDir = isset($_GET['test']) ? $_GET['test'] : NULL;
		if ($this->testDir AND $pos = strrpos($this->testDir, '::'))
		{
			$this->method = substr($this->testDir, $pos+2);
			$this->arg('--filter ' . escapeshellarg('#(^|::)' . str_replace('"', '\x22', preg_quote($this->method, '#')) . '($| )#'));
			$this->testDir = substr($this->testDir, 0, $pos);
			if ($this->debug === NULL) $this->debug = true;
		}
		if ($this->debug === NULL) $this->debug = false;
		$this->run = (isset($_GET['run']) OR $this->testDir);
		$this->structure();
	}

	/**
	 * RUN FOREST!!!
	 * @param string dir to tests
	 * @param string params {@see self::arg()}
	 * @throws DirectoryNotFoundException
	 */
	public function run($dir, $arg = '--no-globals-backup --strict')
	{
		$template = TemplateFactory::create(__DIR__ . '/layout.latte');

		$template->testDir = $this->testDir;
		$template->method = $this->method;

		$this->arg($arg);
		$arg = $this->prepareArgs($dir);
		$onBefore = $this->onBefore; $_this = $this;
		$template->onBefore = function () use ($onBefore, $_this, $dir) {
			foreach ($onBefore as $cb) $cb($_this, $dir);
		};

		if ($this->run)
		{
			$command = new Command;
			$printer = new ResultPrinter;
			$printer->debug = (bool) $this->debug;
			$printer->dir = $dir . DIRECTORY_SEPARATOR;

			$template->run = function () use ($command, $printer, $arg) {
				while (@ob_end_flush()); flush();
				$command->run($arg, $printer);
				$printer->render();
			};
		}
		else
		{
			$template->run = false;
			$uri = rtrim($_SERVER['REQUEST_URI'], '?&');
			$uri .= strpos($uri, '?') === false ? '?' : '&';
			$uri .= 'run';
			$template->startUri = $uri;
		}

		$onAfter = $this->onAfter;
		$template->onAfter = function () use ($onAfter) {
			foreach ($onAfter as $cb) $cb();
		};

		$template->render();
	}

	/**
	 * Enable coverage
	 * @param string app dir
	 * @param string report dir
	 * @throws DirectoryNotFoundException
	 * @return PHP_CodeCoverage
	 */
	public function coverage($appDir, $coverageDir)
	{
		require_once 'PHP/CodeCoverage.php';
		$coverage = PHP_CodeCoverage::getInstance();
		if (!$this->run OR $this->testDir OR !extension_loaded('xdebug'))
		{
			if (!extension_loaded('xdebug'))
			{
				$this->onAfter['coverage'] = function () {
					echo 'Coverage: The Xdebug extension is not loaded.';
				};
			}
			return $coverage;
		}
		@mkdir ($coverageDir);
		if (!is_writable($coverageDir))
		{
			throw new DirectoryNotFoundException("Report directory is not exist or writable $coverageDir");
		}
		if (!is_dir($appDir))
		{
			throw new DirectoryNotFoundException($appDir);
		}
		$appDir = realpath($appDir);
		$coverage->filter()->addDirectoryToWhitelist($appDir);
		$lastModify = array();
		$this->onBefore['coverage'] = function () use ($coverageDir, & $lastModify) {
			foreach (Finder::findFiles('*.html')->from($coverageDir) as $file)
			{
				$file = (string) $file;
				$lastModify[$file] = filemtime($file);
			}
		};
		$this->onAfter['coverage'] = function () use ($coverageDir, & $lastModify) {
			$d = str_replace(DIRECTORY_SEPARATOR, '/', Main::dirDiff(dirname($_SERVER['SCRIPT_FILENAME']), $coverageDir));
			echo "<a href='$d'>coverage</a>";
			foreach (Finder::findFiles('*.html')->from($coverageDir) as $file)
			{
				$file = (string) $file;
				if (isset($lastModify[$file]) AND $lastModify[$file] === filemtime($file))
				{
					unlink($file);
				}
			}
		};
		$this->arg('--coverage-html ' . $coverageDir);
		return $coverage;
	}

	/**
	 * Enable display structure
	 * @see StructureRenderer
	 * @return HttpPHPUnit
	 */
	protected function structure()
	{
		$open = $this->testDir . '::' . $this->method;
		$this->onBefore['structure'] = function ($foo, $dir) use ($open) {
			$structure = new StructureRenderer($dir, $open);
			$structure->render();
		};
	}

	/**
	 * add phpunit param
	 * @param string
	 * @return HttpPHPUnit
	 */
	public function arg($arg)
	{
		if (!preg_match_all('#((?<=^| )(?:(")[^"]*"|(\')[^\']*\'|[^ ]+))(?:$| )#U', $arg, $tmp))
		{
			throw new Exception("Invalid argument: '$arg'");
		}
		foreach ($tmp[1] as $k => $v)
		{
			$s = strlen($tmp[2][$k])+strlen($tmp[3][$k]);
			$this->arg[] = substr($v, 0+$s, strlen($v)-$s-$s);
		}
		return $this;
	}

	/**
	 * @param string dir to tests
	 * @return array
	 */
	private function prepareArgs(& $dir)
	{
		$arg = $this->arg;
		if (!is_dir($dir))
		{
			throw new DirectoryNotFoundException($dir);
		}
		$dir = realpath($dir);
		$arg[] = $dir . ($this->testDir ? '/' . $this->testDir : '');
		return $arg;
	}

	/**
	 * Return relative path between two directory
	 * @param string /foo/bar/aaa/bbb
	 * @param string /foo/bar/ccc/ddd/eee
	 * @return string ../../ccc/ddd/eee
	 */
	public static function dirDiff($current, $wish)
	{
		$dir1 = explode(DIRECTORY_SEPARATOR, realpath($current));
		$dir2 = explode(DIRECTORY_SEPARATOR, realpath($wish));
		$result = array('.');
		$diferent = array();
		foreach (range(0, max(count($dir1), count($dir2))-1) as $i)
		{
			$part1 = next($dir1); $part2 = next($dir2);
			if (!$diferent AND $part1 === $part2) unset($dir1[$i], $dir2[$i]);
			else $diferent[] = array($part1, $part2);
		}
		foreach ($diferent as $d) if ($d[0]) $result[] = '..';
		foreach ($diferent as $d) if ($d[1]) $result[] = $d[1];
		return implode(DIRECTORY_SEPARATOR, $result);
	}

}
