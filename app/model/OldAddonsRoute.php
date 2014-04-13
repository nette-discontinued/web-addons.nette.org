<?php

namespace NetteAddons;

use Nette\Utils\Strings;

class OldAddonsRoute extends \Nette\Application\Routers\Route
{
	public function __construct($metadata)
	{
		$map = array(
			"cs/jquery-ajax" => 66,
			"cs/invoice-control" => 53,
			"cs/treeview" => 78,
			"cs/cnb" => 34,
			"cs/file-downloader" => 80,
			"cs/scriptloader" => 81,
			"cs/live-form-validation" => 55,
			"cs/multiplefileupload" => 11,
			"cs/webloader" => 39,
			"cs/confirmationdialog" => 65,
			"cs/prototype-ajax" => 83,
			"cs/headercontrol" => 9,
			"cs/curl-wrapper" => 69,
			"cs/pdfresponse" => 7,
			"cs/datetimepicker" => 84,
			"cs/gui-for-acl" => 85,
			"cs/mootools-ajax" => 86,
			"cs/pswdinput" => 4,
			"cs/multiplefileupload/jak-na-vlastni-interface" => 11,
			"cs/dependentselectbox" => 3,
			"cs/suggestinput" => 1,
			"cs/tabella" => 87,
			"cs/userpanel" => 88,
			"cs/navigationpanel" => 5,
			"cs/presenterlinkpanel" => 64,
			"cs/phpedpanel" => 63,
			"cs/template-translator" => 62,
			"cs/componenttreepanel" => 61,
			"cs/imageselectbox" => 89,
			"cs/bigfiletools" => 60,
			"cs/dateinput" => 59,
			"cs/gmapformcontrol" => 17,
			"cs/json-rpc" => 56,
			"cs/mailpanel" => 57,
			"cs/textcaptcha" => 79,
			"cs/twitter-control" => 2,
			"cs/json-rpc2" => 56,
			"cs/live-form-validation-for-nette-2-0" => 55,
			"en/live-form-validation-for-nette-2-0" => 55,
			"cs/form-container-replicator" => 68,
			"cs/xdebugtracepanel" => 20,
			"en/form-container-replicator" => 68,
			"cs/email-protection" => 90,
			"cs/twitter" => 14,
			"cs/eciovni" => 53,
			"en/eciovni" => 53,
			"cs/gotopanel" => 10,
			"en/gotopanel" => 10,
			"cs/nette-ajax-js" => 26,
			"en/nette-ajax-js" => 26,
			"cs/sessionpanel" => 38,
			"cs/markette-gopay" => 24,
			"cs/niftygrid" => 52,
			"cs/redis-storage" => 72,
			"cs/dropbox-api" => 51,
			"cs/extensions-list" => 50,
			"cs/composer-extension" => 13,
			"cs/google-oauth2" => 49,
			"cs/eventcalendar" => 48,
			"cs/gps-picker" => 35,
			"en/gps-picker" => 35,
			"cs/gitbranch-debug-panel" => 47,
			"cs/facebook-connect-for-nette" => 46,
			"cs/menu" => 22,
			"cs/multi-authenticator" => 25,
			"cs/header" => 9,
			"cs/grido" => 45,
			"cs/mail-library" => 44,
			"en/mail-library" => 44,
			"cs/pdfresponse2" => 43,
			"cs/thumbnail-helper" => 42,
			"cs/cachepanel" => 92,
		);

		$metadata['id'] = array(
			self::PATTERN => '(cs|en)/[a-z0-9-]+',
			self::FILTER_IN => function($slug) use($map) {
				if (!array_key_exists(Strings::lower($slug), $map)) {
					return NULL;
				}

				return $map[Strings::lower($slug)];
			},
		);

		parent::__construct('<id>', $metadata, self::ONE_WAY);
	}
}
