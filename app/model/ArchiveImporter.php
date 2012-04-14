<?php

namespace NetteAddons\Model;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ArchiveImporter extends Nette\Object implements IAddonImporter
{

	public function __construct($archiveFile)
	{
		throw new Nette\NotImplementedException;
	}



	/**
	 * @return Addon
	 */
	public function import()
	{
		throw new Nette\NotImplementedException;
	}

	/**
	 * @return AddonVersion[]
	 * @throws \Nette\NotImplementedException
	 */
	public function importVersions()
	{
		throw new Nette\NotImplementedException;
	}

}
