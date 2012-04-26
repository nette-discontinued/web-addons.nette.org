<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Http;



/**
 * Addons table repository
 */
class Addons extends Table
{
	/** @var string Prefix where the uploaded files are stored. */
	private $uploadUri;

	/** @var string */
	protected $tableName = 'addons';



	/**
	 * Filter addons selection by tag.
	 *
	 * @param  \Nette\Database\Table\Selection
	 * @param  int tag id
	 * @return \Nette\Database\Table\Selection for fluent interface
	 */
	public function filterByTag(Selection $addons, $tagId)
	{
		$addonIds = $this->connection->table('addons_tags')
			->where('tagId = ?', $tagId)->select('addonId');

		return $addons->where('id', $addonIds);
	}



	/**
	 * Filter addon selection by some text.
	 *
	 * @param  \Nette\Database\Table\Selection
	 * @param  string
	 * @return \Nette\Database\Table\Selection for fluent interface
	 */
	public function filterByString(Selection $addons, $string)
	{
		$string = "%$string%";
		return $addons->where('name LIKE ? OR shortDescription LIKE ?', $string, $string);
	}



	public function setUploadUri($uploadUri, Http\IRequest $request)
	{
		$this->uploadUri = rtrim($request->getUrl()->getBaseUrl(), '/') . $uploadUri;
	}



	/**
	 * @param Addon|ActiveRow $addon
	 * @param AddonVersion|ActiveRow $version
	 * @return string
	 */
	public function getZipUrl($addon, $version)
	{
		if ($addon->repository) {
			return $addon->repository . '/zipball/' . $version->version;
		} else {
			return $this->uploadUri . '/' . $version->filename;
		}
	}

}
