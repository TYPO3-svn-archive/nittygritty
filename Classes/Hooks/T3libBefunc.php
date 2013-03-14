<?php

/* **************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Thomas Skierlo <tsk@pix-pro.eu>
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
 * ************************************************************* */

/**
 * Hook into t3lib_befunc to change flexform behaviour
 * depending on action selection
 *
 * @package TYPO3
 * @subpackage tx_nittygritty
 */
class Tx_Nittygritty_Hooks_T3libBefunc {

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 *
	 */
	protected $configurationManager;

	/**
	 *  @var mixed
	 */
	protected $settings = NULL;

	/**
	 *  @var mixed
	 */
	protected $tsSettings = NULL;

	/**
	 * DS sheets which may be removed
	 *
	 * @var array
	 */
	public $sheetRemoveAllowed = array('s_ssponly', 's_xyzonly');

	/**
	 * Hook function of t3lib_befunc
	 * It is used to change the flexform if it is about nittygritty
	 *
	 * @param array &$dataStructure Flexform structure
	 * @param array $conf Field config array
	 * @param array $row row of current record
	 * @param string $table table name
	 * @param string $fieldName Optional fieldname passed to hook object
	 * @return void
	 */
	public function getFlexFormDS_postProcessDS(&$dataStructure, $conf, $row, $table, $fieldName) {
		if ($table === 'tt_content' && $row['list_type'] === 'nittygritty_pi1' && is_array($dataStructure)) {
			if (is_null($this->tsSettings)) {
				$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
				$configurationManager = $objectManager->get('Tx_Extbase_Configuration_ConfigurationManagerInterface');
				$frameworkConfiguration = $configurationManager->getConfiguration(
						Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'nittygritty', 'Pi1'
				);
				$this->tsSettings = $frameworkConfiguration['settings'];
			}
			$this->updateFlexforms($dataStructure, $row);
		}
	}

	/**
	 * Update flexform configuration if an action is selected
	 *
	 * @param array|string &$dataStructure flexform structure
	 * @param array $row row of current record
	 * @return void
	 */
	protected function updateFlexforms(array &$dataStructure, array $row) {
		$controllerAction = '';
		$currentFlexform = t3lib_div::xml2array($row['pi_flexform']);

		if (is_array($currentFlexform)) {
			if (is_array($currentFlexform['data'])) {
				$controllerActions = $currentFlexform['data']['sDEF']['lDEF']['switchableControllerActions']['vDEF'];
				if (!empty($controllerActions)) {
					$caParts = t3lib_div::trimExplode(';', $controllerActions, TRUE);
					$controllerAction = $caParts[0];
				}
			} else {
				// New element, pick default (first) C/A combination
				$controllerAction = 'Element->showSsp';
			}
			if (!empty($controllerAction)) {
				// Modify the flexform structure depending on the first found action
				switch ($controllerAction) {
					case 'Element->showSsp':
						$sheetName = 's_ssponly';
						foreach ($this->tsSettings['socialshareprivacy']['services'] as $serviceName => $value) {
							if ($this->tsSettings['socialshareprivacy']['services'][$serviceName]['status'] === 'off') {
								$removePath = "settings.socialshareprivacy.services.$serviceName";
								$this->removeFromDs($dataStructure, $sheetName, $removePath);
							}
						}
						$this->removeForeignSheetsFromDS($dataStructure, $sheetName);
						break;
					case 'Element->showXyz':
						$sheetName = 's_xyzonly';
						$this->removeForeignSheetsFromDS($dataStructure, $sheetName);
						break;
					default:
				}
			}
		}
	}

	/**
	 * Remove fields from flexform structure
	 *
	 * @param array &$dataStructure flexform structure
	 * @param string $sheetName Name of sheet
	 * @param string $removePath First Part to be removed from DS
	 * @return void
	 */
	protected function removeFromDs(array &$dataStructure, $sheetName = '', $removePath = '') {
		foreach ($dataStructure['sheets'][$sheetName]['ROOT']['el'] as $field => $value) {
			if (strpos($field, $removePath) === 0) {
				unset($dataStructure['sheets'][$sheetName]['ROOT']['el'][$field]);
			}
		}
	}

	/**
	 * Remove foreign sheets from Flexform structure
	 *
	 * @param array &$dataStructure Flexform structure
	 * @param string $sheetToKeep sheet which must not be removed
	 * @return void
	 */
	protected function removeForeignSheetsFromDS(array &$dataStructure, $sheetToKeep = '') {
		foreach ($this->sheetRemoveAllowed as $sheetName) {
			if ($sheetName !== $sheetToKeep) {
				unset($dataStructure['sheets'][$sheetName]);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nittygritty/Classes/Hooks/T3libBefunc.php']) {
	require_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nittygritty/Classes/Hooks/T3libBefunc.php']);
}
?>