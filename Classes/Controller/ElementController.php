<?php

/* ***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Thomas Skierlo <tsk@pix-pro.eu>, TSC New Media Consulting
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 *
 *
 * @package nittygritty
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Nittygritty_Controller_ElementController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var tslib_cObj
	 */
	protected $contentObject = NULL;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 * 
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Nittygritty_Service_SettingsDemandService
	 * @inject
	 * 
	 */
	protected $pluginSettingsDemandService = NULL;

	/**
	 * Initializes the view before invoking an action method.
	 *
	 * Override this method to solve assign variables common for all actions
	 * or prepare the view in another way before the action is called.
	 *
	 * @param Tx_Extbase_MVC_View_ViewInterface $view The view to be initialized
	 * @return void
	 * @api
	 */
	protected function initializeView(Tx_Extbase_MVC_View_ViewInterface $view) {
		if (is_null($this->contentObject)) {
			$this->contentObject = $this->configurationManager->getContentObject();
		}
		$this->view->assign('contentObjectData', $this->contentObject->data);
	}

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * Override this method to solve tasks which all actions have in
	 * common.
	 *
	 * @return void
	 * @api
	 */
	protected function initializeAction() {
		/* Merge flexform and setup settings
		 * 
		 */
		$this->settings['action'] = $this->actionMethodName;
	}

	/**
	 * action showSsp
	 *
	 * @return void
	 */
	public function showSspAction() {
		$demand = $this->pluginSettingsDemandService->getDemand('socialshareprivacy', $this->settings, NULL, $this->contentObject);
		if ($demand && is_array($demand)) {
			list($activeServiceCount, $jsCode) = $demand;
			$this->view->assignMultiple(array(
				'jsCode' => $jsCode,
				'activeServiceCount' => $activeServiceCount));
		}
	}

	/**
	 * action showXyz
	 *
	 * @return void
	 */
	public function showXyzAction() {
		
	}
}
?>