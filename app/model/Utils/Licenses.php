<?php

namespace NetteAddons\Model\Utils;



/**
 * @author Patrik Votoček
 * @see http://www.spdx.org/licenses/
 */
class Licenses extends \Nette\Object
{
	/** @var string */
	private $urlMask;

	/** @var array */
	private $list = array(
		'AFL-1.1' => 'Academic Free License v1.1License Text',
		'AFL-1.2' => 'Academic Free License v1.2License Text',
		'AFL-2.0' => 'Academic Free License v2.0License Text',
		'AFL-2.1' => 'Academic Free License v2.1License Text',
		'AFL-3.0' => 'Academic Free License v3.0License Text',
		'APL-1.0' => 'Adaptive Public License 1.0License Text',
		'ANTLR-PD' => 'ANTLR Software Rights NoticeLicense Text',
		'Apache-1.0' => 'Apache License 1.0License Text',
		'Apache-1.1' => 'Apache License 1.1License Text',
		'Apache-2.0' => 'Apache License 2.0License Text',
		'APSL-1.0' => 'Apple Public Source License 1.0License Text',
		'APSL-1.1' => 'Apple Public Source License 1.1License Text',
		'APSL-1.2' => 'Apple Public Source License 1.2License Text',
		'APSL-2.0' => 'Apple Public Source License 2.0License Text',
		'Artistic-1.0' => 'Artistic License 1.0License Text',
		'Artistic-2.0' => 'Artistic License 2.0License Text',
		'AAL' => 'Attribution Assurance LicenseLicense Text',
		'BSL-1.0' => 'Boost Software License 1.0License Text',
		'BSD-2-Clause' => 'BSD 2-clause "Simplified" LicenseLicense Text',
		'BSD-2-Clause-NetBSD' => 'BSD 2-clause "NetBSD" LicenseLicense Text',
		'BSD-2-Clause-FreeBSD' => 'BSD 2-clause "FreeBSD" LicenseLicense Text',
		'BSD-3-Clause' => 'BSD 3-clause "New" or "Revised" LicenseLicense Text',
		'BSD-4-Clause' => 'BSD 4-clause "Original" or "Old" LicenseLicense Text',
		'BSD-4-Clause-UC' => 'BSD-4-Clause (University of California-Specific)License Text',
		'CECILL-1.0' => 'CeCILL Free Software License Agreement v1.0License Text',
		'CECILL-1.1' => 'CeCILL Free Software License Agreement v1.1License Text',
		'CECILL-2.0' => 'CeCILL Free Software License Agreement v2.0License Text',
		'CECILL-B' => 'CeCILL-B Free Software License AgreementLicense Text',
		'CECILL-C' => 'CeCILL-C Free Software License AgreementLicense Text',
		'ClArtistic' => 'Clarified Artistic LicenseLicense Text',
		'CNRI-Python-GPL-Compatible' => 'CNRI Python Open Source GPL Compatible License AgreementLicense Text',
		'CNRI-Python' => 'CNRI Python LicenseLicense Text',
		'CDDL-1.0' => 'Common Development and Distribution License 1.0License Text',
		'CDDL-1.1' => 'Common Development and Distribution License 1.1License Text',
		'CPAL-1.0' => 'Common Public Attribution License 1.0 License Text',
		'CPL-1.0' => 'Common Public License 1.0License Text',
		'CATOSL-1.1' => 'Computer Associates Trusted Open Source License 1.1License Text',
		'CC-BY-1.0' => 'Creative Commons Attribution 1.0License Text',
		'CC-BY-2.0' => 'Creative Commons Attribution 2.0License Text',
		'CC-BY-2.5' => 'Creative Commons Attribution 2.5License Text',
		'CC-BY-3.0' => 'Creative Commons Attribution 3.0License Text',
		'CC-BY-ND-1.0' => 'Creative Commons Attribution No Derivatives 1.0License Text',
		'CC-BY-ND-2.0' => 'Creative Commons Attribution No Derivatives 2.0License Text',
		'CC-BY-ND-2.5' => 'Creative Commons Attribution No Derivatives 2.5License Text',
		'CC-BY-ND-3.0' => 'Creative Commons Attribution No Derivatives 3.0License Text',
		'CC-BY-NC-1.0' => 'Creative Commons Attribution Non Commercial 1.0License Text',
		'CC-BY-NC-2.0' => 'Creative Commons Attribution Non Commercial 2.0License Text',
		'CC-BY-NC-2.5' => 'Creative Commons Attribution Non Commercial 2.5License Text',
		'CC-BY-NC-3.0' => 'Creative Commons Attribution Non Commercial 3.0License Text',
		'CC-BY-NC-ND-1.0' => 'Creative Commons Attribution Non Commercial No Derivatives 1.0License Text',
		'CC-BY-NC-ND-2.0' => 'Creative Commons Attribution Non Commercial No Derivatives 2.0License Text',
		'CC-BY-NC-ND-2.5' => 'Creative Commons Attribution Non Commercial No Derivatives 2.5License Text',
		'CC-BY-NC-ND-3.0' => 'Creative Commons Attribution Non Commercial No Derivatives 3.0License Text',
		'CC-BY-NC-SA-1.0' => 'Creative Commons Attribution Non Commercial Share Alike 1.0License Text',
		'CC-BY-NC-SA-2.0' => 'Creative Commons Attribution Non Commercial Share Alike 2.0License Text',
		'CC-BY-NC-SA-2.5' => 'Creative Commons Attribution Non Commercial Share Alike 2.5License Text',
		'CC-BY-NC-SA-3.0' => 'Creative Commons Attribution Non Commercial Share Alike 3.0License Text',
		'CC-BY-SA-1.0' => 'Creative Commons Attribution Share Alike 1.0License Text',
		'CC-BY-SA-2.0' => 'Creative Commons Attribution Share Alike 2.0License Text',
		'CC-BY-SA-2.5' => 'Creative Commons Attribution Share Alike 2.5License Text',
		'CC-BY-SA-3.0' => 'Creative Commons Attribution Share Alike 3.0License Text',
		'CC0-1.0' => 'Creative Commons Zero v1.0 UniversalLicense Text',
		'CUA-OPL-1.0' => 'CUA Office Public License v1.0License Text',
		'EPL-1.0' => 'Eclipse Public License 1.0License Text',
		'eCos-2.0' => 'eCos license version 2.0License Text',
		'ECL-1.0' => 'Educational Community License v1.0License Text',
		'ECL-2.0' => 'Educational Community License v2.0License Text',
		'EFL-1.0' => 'Eiffel Forum License v1.0License Text',
		'EFL-2.0' => 'Eiffel Forum License v2.0License Text',
		'Entessa' => 'Entessa Public License v1.0License Text',
		'ErlPL-1.1' => 'Erlang Public License v1.1License Text',
		'EUDatagrid' => 'EU DataGrid Software LicenseLicense Text',
		'EUPL-1.0' => 'European Union Public License 1.0License Text',
		'EUPL-1.1' => 'European Union Public License 1.1License Text',
		'Fair' => 'Fair LicenseLicense Text',
		'Frameworx-1.0' => 'Frameworx Open License 1.0License Text',
		'AGPL-3.0' => 'GNU Affero General Public License v3.0License Text',
		'GFDL-1.1' => 'GNU Free Documentation License v1.1License Text',
		'GFDL-1.2' => 'GNU Free Documentation License v1.2License Text',
		'GFDL-1.3' => 'GNU Free Documentation License v1.3License Text',
		'GPL-1.0' => 'GNU General Public License v1.0 onlyLicense Text',
		'GPL-1.0+' => 'GNU General Public License v1.0 or laterLicense Text',
		'GPL-2.0' => 'GNU General Public License v2.0 onlyLicense Text',
		'GPL-2.0+' => 'GNU General Public License v2.0 or laterLicense Text',
		'GPL-2.0-with-autoconf-exception' => 'GNU General Public License v2.0 w/Autoconf exceptionLicense Text',
		'GPL-2.0-with-bison-exception' => 'GNU General Public License v2.0 w/Bison exceptionLicense Text',
		'GPL-2.0-with-classpath-exception' => 'GNU General Public License v2.0 w/Classpath exceptionLicense Text',
		'GPL-2.0-with-font-exception' => 'GNU General Public License v2.0 w/Font exceptionLicense Text',
		'GPL-2.0-with-GCC-exception' => 'GNU General Public License v2.0 w/GCC Runtime Library exceptionLicense Text',
		'GPL-3.0' => 'GNU General Public License v3.0 onlyLicense Text',
		'GPL-3.0+' => 'GNU General Public License v3.0 or laterLicense Text',
		'GPL-3.0-with-autoconf-exception' => 'GNU General Public License v3.0 w/Autoconf exceptionLicense Text',
		'GPL-3.0-with-GCC-exception' => 'GNU General Public License v3.0 w/GCC Runtime Library exceptionLicense Text',
		'LGPL-2.1' => 'GNU Lesser General Public License v2.1 onlyLicense Text',
		'LGPL-2.1+' => 'GNU Lesser General Public License v2.1 or laterLicense Text',
		'LGPL-3.0' => 'GNU Lesser General Public License v3.0 onlyLicense Text',
		'LGPL-3.0+' => 'GNU Lesser General Public License v3.0 or laterLicense Text',
		'LGPL-2.0' => 'GNU Library General Public License v2 onlyLicense Text',
		'LGPL-2.0+' => 'GNU Library General Public License v2 or laterLicense Text',
		'gSOAP-1.3b' => 'gSOAP Public License v1.3bLicense Text',
		'HPND' => 'Historic Permission Notice and DisclaimerLicense Text',
		'IPL-1.0' => 'IBM Public License v1.0License Text',
		'IPA' => 'IPA Font LicenseLicense Text',
		'ISC' => 'ISC LicenseLicense Text',
		'LPPL-1.0' => 'LaTeX Project Public License v1.0License Text',
		'LPPL-1.1' => 'LaTeX Project Public License v1.1License Text',
		'LPPL-1.2' => 'LaTeX Project Public License v1.2License Text',
		'LPPL-1.3c' => 'LaTeX Project Public License v1.3cLicense Text',
		'Libpng' => 'libpng LicenseLicense Text',
		'LPL-1.0' => 'Lucent Public License Version 1.0 (Plan9)License Text',
		'LPL-1.02' => 'Lucent Public License v1.02License Text',
		'MS-PL' => 'Microsoft Public LicenseLicense Text',
		'MS-RL' => 'Microsoft Reciprocal LicenseLicense Text',
		'MirOS' => 'MirOS LicenceLicense Text',
		'MIT' => 'MIT LicenseLicense Text',
		'Motosoto' => 'Motosoto LicenseLicense Text',
		'MPL-1.0' => 'Mozilla Public License 1.0License Text',
		'MPL-1.1' => 'Mozilla Public License 1.1 License Text',
		'MPL-2.0' => 'Mozilla Public License 2.0License Text',
		'MPL-2.0-no-copyleft-exception' => 'Mozilla Public License 2.0 (no copyleft exception)License Text',
		'Multics' => 'Multics LicenseLicense Text',
		'NASA-1.3' => 'NASA Open Source Agreement 1.3License Text',
		'Naumen' => 'Naumen Public LicenseLicense Text',
		'NGPL' => 'Nethack General Public LicenseLicense Text',
		'Nokia' => 'Nokia Open Source LicenseLicense Text',
		'NPOSL-3.0' => 'Non-Profit Open Software License 3.0License Text',
		'NTP' => 'NTP LicenseLicense Text',
		'OCLC-2.0' => 'OCLC Research Public License 2.0License Text',
		'ODbL-1.0' => 'ODC Open Database License v1.0License Text',
		'PDDL-1.0' => 'ODC Public Domain Dedication & License 1.0License Text',
		'OGTSL' => 'Open Group Test Suite LicenseLicense Text',
		'OSL-1.0' => 'Open Software License 1.0License Text',
		'OSL-2.0' => 'Open Software License 2.0License Text',
		'OSL-2.1' => 'Open Software License 2.1License Text',
		'OSL-3.0' => 'Open Software License 3.0License Text',
		'OLDAP-2.8' => 'OpenLDAP Public License v2.8License Text',
		'OpenSSL' => 'OpenSSL LicenseLicense Text',
		'PHP-3.0' => 'PHP License v3.0License Text',
		'PHP-3.01' => 'PHP LIcense v3.01License Text',
		'PostgreSQL' => 'PostgreSQL LicenseLicense Text',
		'Python-2.0' => 'Python License 2.0License Text',
		'QPL-1.0' => 'Q Public License 1.0License Text',
		'RPSL-1.0' => 'RealNetworks Public Source License v1.0License Text',
		'RPL-1.5' => 'Reciprocal Public License 1.5 License Text',
		'RHeCos-1.1' => 'Red Hat eCos Public License v1.1License Text',
		'RSCPL' => 'Ricoh Source Code Public LicenseLicense Text',
		'Ruby' => 'Ruby LicenseLicense Text',
		'SAX-PD' => 'Sax Public Domain NoticeLicense Text',
		'OFL-1.0' => 'SIL Open Font License 1.0License Text',
		'OFL-1.1' => 'SIL Open Font License 1.1License Text',
		'SimPL-2.0' => 'Simple Public License 2.0License Text',
		'Sleepycat' => 'Sleepycat LicenseLicense Text',
		'SugarCRM-1.1.3' => 'SugarCRM Public License v1.1.3License Text',
		'SPL-1.0' => 'Sun Public License v1.0License Text',
		'Watcom-1.0' => 'Sybase Open Watcom Public License 1.0License Text',
		'NCSA' => 'University of Illinois/NCSA Open Source LicenseLicense Text',
		'VSL-1.0' => 'Vovida Software License v1.0License Text',
		'W3C' => 'W3C Software and Notice LicenseLicense Text',
		'WXwindows' => 'wxWindows Library LicenseLicense Text',
		'Xnet' => 'X.Net LicenseLicense Text',
		'XFree86-1.1' => 'XFree86 License 1.1License Text',
		'YPL-1.0' => 'Yahoo! Public License v1.0License Text',
		'YPL-1.1' => 'Yahoo! Public License v1.1License Text',
		'Zimbra-1.3' => 'Zimbra Public License v1.3License Text',
		'Zlib' => 'zlib LicenseLicense Text',
		'ZPL-1.1' => 'Zope Public License 1.1License Text',
		'ZPL-2.0' => 'Zope Public License 2.0License Text',
		'ZPL-2.1' => 'Zope Public License 2.1License Text',
	);



	public function __construct($urlMask = 'http://www.spdx.org/licenses/%key%#licenseText')
	{
		$this->urlMask = $urlMask;
	}



	/**
	 * @return string[]|array
	 */
	public function getLicenses()
	{
		return $this->list;
	}



	/**
	 * @param string license key
	 * @return string|NULL
	 */
	public function getLicense($key)
	{
		if (!$this->validate($key)) {
			return NULL;
		}
		return $this->list[$key];
	}



	/**
	 * @param string license key
	 * @return string|NULL
	 */
	public function getUrl($key)
	{
		if (!$this->validate($key)) {
			return NULL;
		}
		return str_replace('%key%', $key, $this->urlMask);
	}



	/**
	 * @param string license key
	 * @return bool
	 */
	public function validate($key)
	{
		return array_key_exists($key, $this->list);
	}
}
