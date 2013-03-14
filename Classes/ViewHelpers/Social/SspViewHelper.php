<?php

/* ***************************************************************
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
 * ViewHelper to add Social Share Buttons with 2-click privacy
 * Details: http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Examples
 * ==============
 * IMPORTANT: Setup rules! Only services enabled in setup can be controlled in VH
 *
 * <nittygritty:social.ssp xing="off" twitter="off" fbAction="like" />
 * Result: Social Share 2-Click Buttons for Facebook (like action) and Google+
 * 
 * <nittygritty:social.ssp />
 * Result: Social Share 2-Click Buttons for aöö services enabled in Setup
 *
 * @package TYPO3
 * @subpackage tx_nittygritty
 */
class Tx_Nittygritty_ViewHelpers_Social_SspViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractTagBasedViewHelper {

	/**
	 * @var tslib_cObj
	 */
	protected $contentObject = NULL;

	/**
	 * @var mixed
	 */
	protected $settings = NULL;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Nittygritty_Service_SettingsDemandService
	 * @inject
	 *
	 */
	protected $pluginSettingsDemandService;

	/**
	 * @var	string
	 */
	protected $tagName = 'div';

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->contentObject = $this->configurationManager->getContentObject();
	}

	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Initializes the view helper before invoking the render method.
	 *
	 * This method runs before the view helper content is rendered.
	 *
	 * @return void
	 * @api
	 */
	public function initialize() {
		parent::initialize();
		$this->settings = $this->pluginSettingsDemandService->getSettings();
	}

	/**
	 * Render Social Share Privacy viewhelper
	 * @param string $facebook Facebook status, may be "on" or "off"
	 * @param string $fbAction Facebook action, may be "recommend (default)" or "like"
	 * @param string $twitter Twitter status, may be "on" or "off"
	 * @param string $twitterText Twitter text, leave empty for automatic
	 * @param string $gplus Google+ status, may be "on" or "off"
	 * @param string $xing Xing status, may be "on" or "off"
	 * @param string $uri Share uri in Typolink format used for all services, leave empty for automatic
	 * @return string
	 */
	public function render($facebook = 'on', $fbAction = 'recommend', $twitter = 'on', $twitterText = '', $gplus = 'on', $xing = 'on', $uri = '') {
		$status = array('on', 'off');
		$fbActions = array('recommend', 'like');
		$setup = array(
			'uri' => $uri,
			'services' => array(
				'facebook' => array(
					'status' => $facebook,
					'action' => $fbAction,
				),
				'twitter' => array(
					'status' => $twitter,
					'tweet_text' => $twitterText,
				),
				'gplus' => array(
					'status' => $gplus,
				),
				'xing' => array(
					'status' => $xing,
				),
			),
		);
		foreach ($setup['services'] as $k => $v) {
			if (in_array($setup['services'][$k]['status'], $status)) {
				$this->settings['socialshareprivacy']['services'][$k]['status'] = $setup['services'][$k]['status'];
			}
		}
		if (in_array($setup['services']['facebook']['action'], $fbActions)) {
			$this->settings['socialshareprivacy']['services']['facebook']['action'] = $setup['services']['facebook']['action'];
		}
		if ($setup['services']['twitter']['tweet_text'] !== '') {
			$this->settings['socialshareprivacy']['services']['twitter']['tweet_text'] = $setup['services']['twitter']['tweet_text'];
		}
		if ($setup['uri'] !== '') {
			$this->settings['socialshareprivacy']['uri'] = $setup['uri'];
		}
		$demand = $this->pluginSettingsDemandService->getDemand('socialshareprivacy', $this->settings, NULL, $this->contentObject);
		if ($demand && is_array($demand)) {
			list($activeServiceCount, $jsCode) = $demand;
			if ($activeServiceCount > 0) {
				/**
				 *  Makes no sense to use the DI here
				 *  it is a singleton, so we get always the same instance
				 *  @var $pageRenderer t3lib_PageRenderer 
				 */
				$pageRenderer = t3lib_div::makeInstance('t3lib_PageRenderer');

				if (empty($this->settings['general']['noCssInclude'])) {
					$pageRenderer->addCssFile($this->settings['socialshareprivacy']['css_path'], 'stylesheet', 'screen', 'socialshareprivacy', TRUE, FALSE, '', FALSE);
				}
				if (empty($this->settings['general']['noJsLibraryInclude'])) {
					if (empty($this->settings['general']['jsInFooter'])) {
						$pageRenderer->addJsFile($this->settings['socialshareprivacy']['js_path'], 'text/javascript', $compress = TRUE, $forceOnTop = FALSE, $allWrap = '', $excludeFromConcatenation = FALSE);
						$pageRenderer->addJsInlineCode('ssp', $jsCode, $compress = TRUE, $forceOnTop = FALSE);
					} else {
						$pageRenderer->addJsFooterFile($this->settings['socialshareprivacy']['js_path'], 'text/javascript', $compress = TRUE, $forceOnTop = FALSE, $allWrap = '', $excludeFromConcatenation = FALSE);
						$pageRenderer->addJsFooterInlineCode('ssp', $jsCode, $compress = TRUE, $forceOnTop = FALSE);
					}
				}
				$this->tag->addAttribute('id', 'socialshareprivacy-' . $this->contentObject->data['uid']);
				$this->tag->forceClosingTag(TRUE);
				return $this->tag->render();
			}
		}
	}
}

?>