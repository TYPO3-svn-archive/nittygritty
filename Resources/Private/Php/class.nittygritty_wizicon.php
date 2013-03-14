<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Thomas Skierlo <tsk@pix-pro.eu>, TSC New Media Consulting
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Add nittygritty extension to the wizard in page module
 *
 * @package TYPO3
 * @subpackage tx_nittygritty
 */
class nittygritty_pi1_wizicon {

	const KEY = 'nittygritty';

	/**
	 * Processing the wizard items array
	 *
	 * @param array $wizardItems The wizard items
	 * @return array array with wizard items
	 */
	public function proc($wizardItems) {
		$wizardItems['plugins_tx_' . self::KEY] = array(
			'icon'			=> t3lib_extMgm::extRelPath(self::KEY) . 'Resources/Public/Icons/ce_wiz.gif',
			'title'			=> $GLOBALS['LANG']->sL('LLL:EXT:nittygritty/Resources/Private/Language/locallang_be.xlf:pi1_title'),
			'description'	=> $GLOBALS['LANG']->sL('LLL:EXT:nittygritty/Resources/Private/Language/locallang_be.xlf:pi1_plus_wiz_description'),
			'params'		=> '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=' . self::KEY . '_pi1'
		);

		return $wizardItems;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nittygritty/Resources/Private/Php/class.nittygritty_wizicon.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nittygritty/Resources/Private/Php/class.nittygritty_wizicon.php']);
}

?>