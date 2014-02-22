<?php

namespace NetteAddons\Model\Utils;


/**
 * @see http://spdx.org/licenses/
 */
class Licenses extends \Nette\Object
{
	/** @var string */
	private $urlMask;

	/** @var array */
	private $list = array(
		'AFL-1.1' => 'Academic Free License v1.1',
		'AFL-1.2' => 'Academic Free License v1.2',
		'AFL-2.0' => 'Academic Free License v2.0',
		'AFL-2.1' => 'Academic Free License v2.1',
		'AFL-3.0' => 'Academic Free License v3.0',
		'APL-1.0' => 'Adaptive Public License 1.0',
		'Aladdin' => 'Aladdin Free Public License',
		'ANTLR-PD' => 'ANTLR Software Rights Notice',
		'Apache-1.0' => 'Apache License 1.0',
		'Apache-1.1' => 'Apache License 1.1',
		'Apache-2.0' => 'Apache License 2.0',
		'APSL-1.0' => 'Apple Public Source License 1.0',
		'APSL-1.1' => 'Apple Public Source License 1.1',
		'APSL-1.2' => 'Apple Public Source License 1.2',
		'APSL-2.0' => 'Apple Public Source License 2.0',
		'Artistic-1.0' => 'Artistic License 1.0',
		'Artistic-1.0-cl8' => 'Artistic License 1.0 w/clause 8',
		'Artistic-1.0-Perl' => 'Artistic License 1.0 (Perl)',
		'Artistic-2.0' => 'Artistic License 2.0',
		'AAL' => 'Attribution Assurance License',
		'BitTorrent-1.0' => 'BitTorrent Open Source License v1.0',
		'BitTorrent-1.1' => 'BitTorrent Open Source License v1.1',
		'BSL-1.0' => 'Boost Software License 1.0',
		'BSD-2-Clause' => 'BSD 2-clause "Simplified" License',
		'BSD-2-Clause-FreeBSD' => 'BSD 2-clause FreeBSD License',
		'BSD-2-Clause-NetBSD' => 'BSD 2-clause NetBSD License',
		'BSD-3-Clause' => 'BSD 3-clause "New" or "Revised" License',
		'BSD-3-Clause-Clear' => 'BSD 3-clause Clear License',
		'BSD-4-Clause' => 'BSD 4-clause "Original" or "Old" License',
		'BSD-4-Clause-UC' => 'BSD-4-Clause (University of California-Specific)',
		'CECILL-1.0' => 'CeCILL Free Software License Agreement v1.0',
		'CECILL-1.1' => 'CeCILL Free Software License Agreement v1.1',
		'CECILL-2.0' => 'CeCILL Free Software License Agreement v2.0',
		'CECILL-B' => 'CeCILL-B Free Software License Agreement',
		'CECILL-C' => 'CeCILL-C Free Software License Agreement',
		'ClArtistic' => 'Clarified Artistic License',
		'CNRI-Python' => 'CNRI Python License',
		'CNRI-Python-GPL-Compatible' => 'CNRI Python Open Source GPL Compatible License Agreement',
		'CPOL-1.02' => 'Code Project Open License 1.02',
		'CDDL-1.0' => 'Common Development and Distribution License 1.0',
		'CDDL-1.1' => 'Common Development and Distribution License 1.1',
		'CPAL-1.0' => 'Common Public Attribution License 1.0 ',
		'CPL-1.0' => 'Common Public License 1.0',
		'CATOSL-1.1' => 'Computer Associates Trusted Open Source License 1.1',
		'Condor-1.1' => 'Condor Public License v1.1',
		'CC-BY-1.0' => 'Creative Commons Attribution 1.0',
		'CC-BY-2.0' => 'Creative Commons Attribution 2.0',
		'CC-BY-2.5' => 'Creative Commons Attribution 2.5',
		'CC-BY-3.0' => 'Creative Commons Attribution 3.0',
		'CC-BY-ND-1.0' => 'Creative Commons Attribution No Derivatives 1.0',
		'CC-BY-ND-2.0' => 'Creative Commons Attribution No Derivatives 2.0',
		'CC-BY-ND-2.5' => 'Creative Commons Attribution No Derivatives 2.5',
		'CC-BY-ND-3.0' => 'Creative Commons Attribution No Derivatives 3.0',
		'CC-BY-NC-1.0' => 'Creative Commons Attribution Non Commercial 1.0',
		'CC-BY-NC-2.0' => 'Creative Commons Attribution Non Commercial 2.0',
		'CC-BY-NC-2.5' => 'Creative Commons Attribution Non Commercial 2.5',
		'CC-BY-NC-3.0' => 'Creative Commons Attribution Non Commercial 3.0',
		'CC-BY-NC-ND-1.0' => 'Creative Commons Attribution Non Commercial No Derivatives 1.0',
		'CC-BY-NC-ND-2.0' => 'Creative Commons Attribution Non Commercial No Derivatives 2.0',
		'CC-BY-NC-ND-2.5' => 'Creative Commons Attribution Non Commercial No Derivatives 2.5',
		'CC-BY-NC-ND-3.0' => 'Creative Commons Attribution Non Commercial No Derivatives 3.0',
		'CC-BY-NC-SA-1.0' => 'Creative Commons Attribution Non Commercial Share Alike 1.0',
		'CC-BY-NC-SA-2.0' => 'Creative Commons Attribution Non Commercial Share Alike 2.0',
		'CC-BY-NC-SA-2.5' => 'Creative Commons Attribution Non Commercial Share Alike 2.5',
		'CC-BY-NC-SA-3.0' => 'Creative Commons Attribution Non Commercial Share Alike 3.0',
		'CC-BY-SA-1.0' => 'Creative Commons Attribution Share Alike 1.0',
		'CC-BY-SA-2.0' => 'Creative Commons Attribution Share Alike 2.0',
		'CC-BY-SA-2.5' => 'Creative Commons Attribution Share Alike 2.5',
		'CC-BY-SA-3.0' => 'Creative Commons Attribution Share Alike 3.0',
		'CC0-1.0' => 'Creative Commons Zero v1.0 Universal',
		'CUA-OPL-1.0' => 'CUA Office Public License v1.0',
		'D-FSL-1.0' => 'Deutsche Freie Software Lizenz',
		'WTFPL' => 'Do What The F*ck You Want To Public License',
		'EPL-1.0' => 'Eclipse Public License 1.0',
		'eCos-2.0' => 'eCos license version 2.0',
		'ECL-1.0' => 'Educational Community License v1.0',
		'ECL-2.0' => 'Educational Community License v2.0',
		'EFL-1.0' => 'Eiffel Forum License v1.0',
		'EFL-2.0' => 'Eiffel Forum License v2.0',
		'Entessa' => 'Entessa Public License v1.0',
		'ErlPL-1.1' => 'Erlang Public License v1.1',
		'EUDatagrid' => 'EU DataGrid Software License',
		'EUPL-1.0' => 'European Union Public License 1.0',
		'EUPL-1.1' => 'European Union Public License 1.1',
		'Fair' => 'Fair License',
		'Frameworx-1.0' => 'Frameworx Open License 1.0',
		'FTL' => 'Freetype Project License',
		'AGPL-1.0' => 'GNU Affero General Public License v1.0',
		'AGPL-3.0' => 'GNU Affero General Public License v3.0',
		'GFDL-1.1' => 'GNU Free Documentation License v1.1',
		'GFDL-1.2' => 'GNU Free Documentation License v1.2',
		'GFDL-1.3' => 'GNU Free Documentation License v1.3',
		'GPL-1.0' => 'GNU General Public License v1.0 only',
		'GPL-1.0+' => 'GNU General Public License v1.0 or later',
		'GPL-2.0' => 'GNU General Public License v2.0 only',
		'GPL-2.0+' => 'GNU General Public License v2.0 or later',
		'GPL-2.0-with-autoconf-exception' => 'GNU General Public License v2.0 w/Autoconf exception',
		'GPL-2.0-with-bison-exception' => 'GNU General Public License v2.0 w/Bison exception',
		'GPL-2.0-with-classpath-exception' => 'GNU General Public License v2.0 w/Classpath exception',
		'GPL-2.0-with-font-exception' => 'GNU General Public License v2.0 w/Font exception',
		'GPL-2.0-with-GCC-exception' => 'GNU General Public License v2.0 w/GCC Runtime Library exception',
		'GPL-3.0' => 'GNU General Public License v3.0 only',
		'GPL-3.0+' => 'GNU General Public License v3.0 or later',
		'GPL-3.0-with-autoconf-exception' => 'GNU General Public License v3.0 w/Autoconf exception',
		'GPL-3.0-with-GCC-exception' => 'GNU General Public License v3.0 w/GCC Runtime Library exception',
		'LGPL-2.1' => 'GNU Lesser General Public License v2.1 only',
		'LGPL-2.1+' => 'GNU Lesser General Public License v2.1 or later',
		'LGPL-3.0' => 'GNU Lesser General Public License v3.0 only',
		'LGPL-3.0+' => 'GNU Lesser General Public License v3.0 or later',
		'LGPL-2.0' => 'GNU Library General Public License v2 only',
		'LGPL-2.0+' => 'GNU Library General Public License v2 or later',
		'gSOAP-1.3b' => 'gSOAP Public License v1.3b',
		'HPND' => 'Historic Permission Notice and Disclaimer',
		'IBM-pibs' => 'IBM PowerPC Initialization and Boot Software ',
		'IPL-1.0' => 'IBM Public License v1.0',
		'Imlib2' => 'Imlib2 License',
		'IJG' => 'Independent JPEG Group License',
		'Intel' => 'Intel Open Source License',
		'IPA' => 'IPA Font License',
		'ISC' => 'ISC License',
		'JSON' => 'JSON License',
		'LPPL-1.3a' => 'LaTeX Project Public License 1.3a ',
		'LPPL-1.0' => 'LaTeX Project Public License v1.0',
		'LPPL-1.1' => 'LaTeX Project Public License v1.1',
		'LPPL-1.2' => 'LaTeX Project Public License v1.2',
		'LPPL-1.3c' => 'LaTeX Project Public License v1.3c',
		'Libpng' => 'libpng License',
		'LPL-1.02' => 'Lucent Public License v1.02',
		'LPL-1.0' => 'Lucent Public License Version 1.0',
		'MS-PL' => 'Microsoft Public License',
		'MS-RL' => 'Microsoft Reciprocal License',
		'MirOS' => 'MirOS Licence',
		'MIT' => 'MIT License',
		'Motosoto' => 'Motosoto License',
		'MPL-1.0' => 'Mozilla Public License 1.0',
		'MPL-1.1' => 'Mozilla Public License 1.1 ',
		'MPL-2.0' => 'Mozilla Public License 2.0',
		'MPL-2.0-no-copyleft-exception' => 'Mozilla Public License 2.0 (no copyleft exception)',
		'Multics' => 'Multics License',
		'NASA-1.3' => 'NASA Open Source Agreement 1.3',
		'Naumen' => 'Naumen Public License',
		'NBPL-1.0' => 'Net Boolean Public License v1 ',
		'NGPL' => 'Nethack General Public License',
		'NOSL' => 'Netizen Open Source License',
		'NPL-1.0' => 'Netscape Public License v1.0',
		'NPL-1.1' => 'Netscape Public License v1.1',
		'Nokia' => 'Nokia Open Source License',
		'NPOSL-3.0' => 'Non-Profit Open Software License 3.0',
		'NTP' => 'NTP License',
		'OCLC-2.0' => 'OCLC Research Public License 2.0',
		'ODbL-1.0' => 'ODC Open Database License v1.0',
		'PDDL-1.0' => 'ODC Public Domain Dedication & License 1.0',
		'OGTSL' => 'Open Group Test Suite License',
		'OLDAP-2.2.2' => 'Open LDAP Public License 2.2.2',
		'OLDAP-1.1' => 'Open LDAP Public License v1.1',
		'OLDAP-1.2' => 'Open LDAP Public License v1.2 ',
		'OLDAP-1.3' => 'Open LDAP Public License v1.3',
		'OLDAP-1.4' => 'Open LDAP Public License v1.4',
		'OLDAP-2.0' => 'Open LDAP Public License v2.0 (or possibly 2.0A and 2.0B)',
		'OLDAP-2.0.1' => 'Open LDAP Public License v2.0.1',
		'OLDAP-2.1' => 'Open LDAP Public License v2.1',
		'OLDAP-2.2' => 'Open LDAP Public License v2.2',
		'OLDAP-2.2.1' => 'Open LDAP Public License v2.2.1',
		'OLDAP-2.3' => 'Open LDAP Public License v2.3',
		'OLDAP-2.4' => 'Open LDAP Public License v2.4',
		'OLDAP-2.5' => 'Open LDAP Public License v2.5',
		'OLDAP-2.6' => 'Open LDAP Public License v2.6',
		'OLDAP-2.7' => 'Open LDAP Public License v2.7',
		'OPL-1.0' => 'Open Public License v1.0',
		'OSL-1.0' => 'Open Software License 1.0',
		'OSL-2.0' => 'Open Software License 2.0',
		'OSL-2.1' => 'Open Software License 2.1',
		'OSL-3.0' => 'Open Software License 3.0',
		'OLDAP-2.8' => 'OpenLDAP Public License v2.8',
		'OpenSSL' => 'OpenSSL License',
		'PHP-3.0' => 'PHP License v3.0',
		'PHP-3.01' => 'PHP License v3.01',
		'PostgreSQL' => 'PostgreSQL License',
		'Python-2.0' => 'Python License 2.0',
		'QPL-1.0' => 'Q Public License 1.0',
		'RPSL-1.0' => 'RealNetworks Public Source License v1.0',
		'RPL-1.1' => 'Reciprocal Public License 1.1',
		'RPL-1.5' => 'Reciprocal Public License 1.5 ',
		'RHeCos-1.1' => 'Red Hat eCos Public License v1.1',
		'RSCPL' => 'Ricoh Source Code Public License',
		'Ruby' => 'Ruby License',
		'SAX-PD' => 'Sax Public Domain Notice',
		'SGI-B-1.0' => 'SGI Free Software License B v1.0',
		'SGI-B-1.1' => 'SGI Free Software License B v1.1',
		'SGI-B-2.0' => 'SGI Free Software License B v2.0',
		'OFL-1.0' => 'SIL Open Font License 1.0',
		'OFL-1.1' => 'SIL Open Font License 1.1',
		'SimPL-2.0' => 'Simple Public License 2.0',
		'Sleepycat' => 'Sleepycat License',
		'SMLNJ' => 'Standard ML of New Jersey License',
		'SugarCRM-1.1.3' => 'SugarCRM Public License v1.1.3',
		'SISSL' => 'Sun Industry Standards Source License v1.1',
		'SISSL-1.2' => 'Sun Industry Standards Source License v1.2',
		'SPL-1.0' => 'Sun Public License v1.0',
		'Watcom-1.0' => 'Sybase Open Watcom Public License 1.0',
		'NCSA' => 'University of Illinois/NCSA Open Source License',
		'VSL-1.0' => 'Vovida Software License v1.0',
		'W3C' => 'W3C Software Notice and License',
		'WXwindows' => 'wxWindows Library License',
		'Xnet' => 'X.Net License',
		'X11' => 'X11 License',
		'XFree86-1.1' => 'XFree86 License 1.1',
		'YPL-1.0' => 'Yahoo! Public License v1.0',
		'YPL-1.1' => 'Yahoo! Public License v1.1',
		'Zimbra-1.3' => 'Zimbra Public License v1.3',
		'Zlib' => 'zlib License',
		'ZPL-1.1' => 'Zope Public License 1.1',
		'ZPL-2.0' => 'Zope Public License 2.0',
		'ZPL-2.1' => 'Zope Public License 2.1',
		'Unlicense' => 'The Unlicense',
	);


	/**
	 * @param string
	 */
	public function __construct($urlMask = 'http://www.spdx.org/licenses/%key%#licenseText')
	{
		$this->urlMask = $urlMask;
	}


	/**
	 * @param  bool
	 * @return string[]|array
	 */
	public function getLicenses($preferCommon = FALSE)
	{
		if (!$preferCommon) {
			return $this->list;

		} else {
			$others = $this->list;
			$mostCommon = array();
			foreach ($this->getMostCommon() as $license) {
				$mostCommon[$license] = $this->list[$license];
				unset($others[$license]);
			}

			return array(
				'Most common' => $mostCommon,
				'Others' => $others,
			);
		}
	}


	/**
	 * @return string[]|array
	 */
	public function getMostCommon()
	{
		return array('MIT', 'BSD-3-Clause', 'Apache-2.0', 'LGPL-3.0', 'GPL-2.0+', 'GPL-2.0', 'GPL-3.0');
	}


	/**
	 * @param  string license key
	 * @return string|NULL
	 */
	public function getFullName($key)
	{
		if (!$this->isValid($key)) {
			return NULL;
		}
		return $this->list[$key];
	}


	/**
	 * @param  string license key
	 * @return string|NULL
	 */
	public function getUrl($key)
	{
		if (!$this->isValid($key)) {
			return NULL;
		}
		return str_replace('%key%', $key, $this->urlMask);
	}


	/**
	 * @param  string license key
	 * @return bool
	 */
	public function isValid($key)
	{
		return array_key_exists($key, $this->list);
	}
}
