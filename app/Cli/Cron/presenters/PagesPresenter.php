<?php

namespace NetteAddons\Cli\Cron;

use Guzzle\Http\Client;
use Nette\Utils\Finder;
use Nette\Utils\FileSystem;

class PagesPresenter extends BasePresenter
{
	/** @var string */
	private $dataPath;
	/** @var string */
	private $sourceUrl;
	/** @var string */
	private $tempPath;
	/** @var string|NULL */
	private $zipRootDirectory;

	/**
	 * @param string
	 * @param string
	 * @param string
	 * @param string|NULL
	 */
	public function __construct($dataPath, $sourceUrl, $tempPath, $zipRootDirectory = NULL)
	{
		parent::__construct();

		$this->dataPath = $dataPath;
		$this->sourceUrl = $sourceUrl;
		$this->tempPath = $tempPath;
		$this->zipRootDirectory = $zipRootDirectory;
	}

	public function actionUpdate()
	{
		$tempZipPath = $this->tempPath . '/pages.zip';
		$tempDataPath = $this->tempPath . '/pages';

		$this->fetchZip($tempZipPath);
		$this->unpackZip($tempZipPath, $tempDataPath);
		$this->checkMetaMenuFile($tempDataPath);
		$this->moveData($tempDataPath);
	}

	/**
	 * @param string
	 */
	private function fetchZip($zipPath)
	{
		if ($this->verbose) {
			$this->writeln('Downloading pages zip file');
		}

		if (file_exists($zipPath) && !unlink($zipPath)) {
			throw new \NetteAddons\InvalidStateException('Pages temp zip file already exists');
		}

		try {
			$client = new Client;
			$client->get($this->sourceUrl)
				->setResponseBody($zipPath)
				->send();
		} catch (\Guzzle\Http\Exception $e) {
			throw new \NetteAddons\InvalidStateException('Download pages file failed', NULL, $e);
		}

		if (!file_exists($zipPath)) {
			throw new \NetteAddons\InvalidStateException('Download pages file failed');
		}
	}

	/**
	 * @param string
	 * @param string
	 */
	private function unpackZip($zipPath, $dataPath)
	{
		if ($this->verbose) {
			$this->writeln('Unpacking pages zip file');
		}

		if (file_exists($dataPath)) {
			try {
				FileSystem::delete($dataPath);
			} catch (\Nette\IOException $e) {
				throw new \NetteAddons\InvalidStateException('Pages temp data directory already exists');
			}
		}

		$zip = new \ZipArchive;
		if ($err = $zip->open($zipPath) !== TRUE) {
			throw new \NetteAddons\InvalidStateException('Pages zip file is corrupted', $err);
		}

		if (!mkdir($dataPath, 0777, TRUE)) {
			throw new \NetteAddons\InvalidStateException('Pages temp data directory could not be created');
		}

		if (!$zip->extractTo($dataPath)) {
			throw new \NetteAddons\InvalidStateException('Pages zip file is corrupted');
		}

		$zip->close();

		unlink($zipPath);
	}

	/**
	 * @param string
	 */
	private function checkMetaMenuFile($dataPath)
	{
		if ($this->verbose) {
			$this->writeln('Checking meta menu file');
		}

		$menuFile = $this->getBaseDataPath($dataPath) . '/meta/menu.texy';
		if (!file_exists($menuFile)) {
			throw new \NetteAddons\InvalidStateException('Missing meta menu file');
		}
	}

	/**
	 * @param string
	 */
	private function moveData($dataPath)
	{
		if ($this->verbose) {
			$this->writeln('Moving pages data');
		}

		try {
			FileSystem::delete($this->dataPath);
		} catch(\Nette\IOException $e) {
			throw new \NetteAddons\InvalidStateException('Moving pages data failed', NULL, $e);
		}

		$dataPath = $this->getBaseDataPath($dataPath);
		foreach (Finder::findFiles('*')->from($dataPath) as $file) {
			/** @var \SplFileInfo $file */
			$destinationPath = str_replace($dataPath, $this->dataPath, $file->getRealPath());
			FileSystem::rename($file->getRealPath(), $destinationPath);
		}
	}

	/**
	 * @param string
	 * @return string
	 */
	private function getBaseDataPath($dataPath)
	{
		if ($this->zipRootDirectory === NULL) {
			return realpath($dataPath);
		}

		return realpath($dataPath . '/' . $this->zipRootDirectory);
	}
}
