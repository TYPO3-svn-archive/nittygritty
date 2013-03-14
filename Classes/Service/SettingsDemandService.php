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
 * Provides a way to get configuration/setup for view helpers and other
 * Provides a way to build demand from settings
 *
 * @package TYPO3
 * @subpackage tx_nittygritty
 */
class Tx_Nittygritty_Service_SettingsDemandService implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
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
	 *  @var tslib_cObj
	 */
	protected $contentObject = NULL;

	/**
	 * @var array
	 */
	protected $defaultsSocialshareprivacy = array(
		'cookie_path' => '/',
		'cookie_expires' => '365',
		'info_link' => 'http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html',
		'uri' => '',
		'services' => array(
			'facebook' => array(
				'status' => 'on',
				'perma_option' => 'on',
				'action' => 'recommend',
				'language' => 'de_DE',
				'display_name' => 'Facebook',
				'referrer_track' => '',
			),
			'twitter' => array(
				'status' => 'on',
				'perma_option' => 'on',
				'tweet_text' => '',
				'language' => 'en',
				'display_name' => 'Twitter',
				'referrer_track' => '',
			),
			'gplus' => array(
				'status' => 'on',
				'perma_option' => 'on',
				'language' => 'de',
				'display_name' => 'Google+',
				'referrer_track' => '',
			),
			'xing' => array(
				'status' => 'on',
				'perma_option' => 'on',
				'language' => 'de',
				'display_name' => 'Xing',
				'referrer_track' => '',
			),
		),
	);

	protected $allLangsSsp = array(
		'facebook' => array(
			'en' => 'en_GB',
			'da' => 'da_DK',
			'de' => 'de_DE',
			'es' => 'es_ES',
			'fr' => 'fr_FR',
			'it' => 'it_IT',
			'nl' => 'nl_NL',
			'pt' => 'pt_PT',
			'ru' => 'ru_RU',
			'pl' => 'pl_PL',
		),
		'twitter' => array(
			'en' => 'en',
			'da' => 'da',
			'de' => 'de',
			'es' => 'es',
			'fr' => 'fr',
			'it' => 'it',
			'nl' => 'nl',
			'pt' => 'pt',
			'ru' => 'ru',
			'pl' => 'pl',
		),
		'gplus' => array(
			'en' => 'en',
			'da' => 'da',
			'de' => 'de',
			'es' => 'es',
			'fr' => 'fr',
			'it' => 'it',
			'nl' => 'nl',
			'pt' => 'pt',
			'ru' => 'ru',
			'pl' => 'pl',
		),
		'xing' => array(
			'en' => 'en',
			'de' => 'de',
		),
	);

	/**
	 * Injects the Configuration Manager
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager An instance of the Configuration Manager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * Returns all settings.
	 *
	 * @return array
	 */
	public function getSettings() {
		if (is_null($this->settings)) {
			$this->settings = $this->configurationManager->getConfiguration('Settings', 'nittygritty', 'Pi1');
		}
		return $this->settings;
	}

	/**
	 * Returns all settings from setup.txt. Not effected by Flexform
	 *
	 * @return array
	 */
	public function getTsSettings() {
		if (is_null($this->tsSettings)) {
				$frameworkConfiguration = $this->configurationManager->getConfiguration(
						Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'nittygritty', 'Pi1'
				);
				$this->tsSettings = $frameworkConfiguration['settings'];
		}
		return $this->tsSettings;
	}

	/**
	 * Returns demand of processed settings for an item
	 * @param string $item The key to determine the proper subarray of settings
	 * @param array $settings Merged Setup/Flexform settings
	 * @param array $tsSettings Raw Setup settings
	 * @param tslib_cObj $contentObject
	 *
	 * @return array
	 */
	public function getDemand($item, $settings = NULL, $tsSettings = NULL, $contentObject = NULL) {
		$this->settings = (is_null($settings)) ? $this->getSettings() : $settings;
		$this->tsSettings = (is_null($tsSettings)) ? $this->getTsSettings() : $tsSettings;
		$this->contentObject = ($contentObject === NULL) ? $this->configurationManager->getContentObject() : $contentObject;
		return ($this->settings === NULL || !array_key_exists($item, $this->settings)) ? FALSE : $this->processSettings($item);
	}

	/**
	 * Returns the processed settings of element $name = socialshareprivacy,
	 * If the path is invalid or no entry is found, false is returned.
	 *
	 * @param string $item
	 * @return array
	 */
	protected function processSettings($item) {
		switch ($item) {
			case 'socialshareprivacy':
				return $this->processSSP();
				break;
			default:
				return FALSE;
		}
	}

	/**
	 * Returns the processed settings of element $name = socialshareprivacy,
	 * If the path is invalid or no entry is found, false is returned.
	 *
	 * @param void
	 * @return array
	 */
	protected function processSsp() {
		$config = $this->settings['socialshareprivacy'];
		$activeServicesNames = array();
		foreach ($config['services'] as $k => $v) {
			if ($config['services'][$k]['status'] == 'on' && ($this->tsSettings['socialshareprivacy']['services'][$k]['status'] == 'on')) {
				$serviceName = (isset($config['services'][$k]['display_name'])) ? $config['services'][$k]['display_name'] : ucfirst($k);
				array_push($activeServicesNames, $serviceName);
				$config['services'][$k]['txt_info'] = Tx_Extbase_Utility_Localization::translate('socialshareprivacy.services.text_info', 'nittygritty', array($serviceName, '<em>I</em>'));
				$config['services'][$k][sprintf('txt_%s_off', $k)] = Tx_Extbase_Utility_Localization::translate('socialshareprivacy.services.txt_service_off', 'nittygritty', array($serviceName));
				$config['services'][$k][sprintf('txt_%s_on', $k)] = Tx_Extbase_Utility_Localization::translate('socialshareprivacy.services.txt_service_on', 'nittygritty', array($serviceName));
				$config['services'][$k]['language'] = $this->getLocaleForService($k, $config['services'][$k]['language']);
				$action = (($k == 'facebook') && ($config['services'][$k]['action'] === 'like')) ? $config['services'][$k]['action'] : NULL;
				$config['services'][$k]['dummy_img'] = $this->getLocalizedDummyImage($k, $config['services'][$k]['language'], $action);
			}
		}
		$activeServiceCount = count($activeServicesNames);
		if ($activeServiceCount > 0) {
			$infoUriStrg = trim($config['info_link']);
			if ($infoUriStrg !== '') {
				$typolink_conf = array(
					"parameter" => $infoUriStrg,
					"forceAbsoluteUrl" => 1,
					"useCacheHash" => 0);
				$config['info_link'] = $this->getUri($typolink_conf);
			}
			$shareUriStrg = trim($config['uri']);
			if ($shareUriStrg !== '') {
				$typolink_conf = array(
					"parameter" => $shareUriStrg,
					"forceAbsoluteUrl" => 1,
					"useCacheHash" => 0);
				$config['uri'] = $this->getUri($typolink_conf);
			}
			if ($activeServiceCount > 1) {
				$last = array_pop($activeServicesNames);
				$allServicesLocalized = sprintf('%s %s %s', implode(', ', $activeServicesNames), Tx_Extbase_Utility_Localization::translate('common.or', 'nittygritty'), $last);
			} else {
				$allServicesLocalized = $activeServicesNames[0];
			}
			$config['txt_help'] = Tx_Extbase_Utility_Localization::translate('socialshareprivacy.txt_help', 'nittygritty', array($allServicesLocalized, '<em>I</em>'));
			$config['settings_perma'] = Tx_Extbase_Utility_Localization::translate('socialshareprivacy.settings_perma', 'nittygritty');

			unset($config['imageDirPath']);
			unset($config['js_path']);
			unset($config['css_path']); // Otherwise css will be included by JS too, which we don't want

			$config = Tx_Nittygritty_Utility_Arrays::arrayRemoveCertainValuesRecursive($config, $this->defaultsSocialshareprivacy, TRUE, TRUE);
			$jsOptions = json_encode($config);
			$jsCode = sprintf('$(\'#socialshareprivacy-%s\').socialSharePrivacy(%s);', $this->contentObject->data['uid'], $jsOptions);
			$jsCode = sprintf('if($(\'#socialshareprivacy-%s\').length > 0){%s}', $this->contentObject->data['uid'], $jsCode);
			$jsCode = sprintf('jQuery(document).ready(function($){%s});', $jsCode);
			return array($activeServiceCount, $jsCode);
		}
	}

	/**
	 * Build absolute Url from string representation.
	 *
	 * @var string An url string, like: www.test.com or a local id, like 4
	 *
	 * @return string
	 *
	 */
	protected function getUri($typolink_conf) {
		return $this->contentObject->typoLink_URL($typolink_conf);
	}

	/**
	 * Check or build Locale for social service.
	 *
	 * @var string $service A string with service name, like: facebook
	 * @var string $language optional ISO language code, may be 'auto', ''
	 *
	 * @return string
	 *
	 */
	protected function getLocaleForService($service, $language = NULL) {
		if(array_key_exists($language, $this->allLangsSsp[$service])) {
			return $this->allLangsSsp[$service][$language];
		} else {
			$language = $GLOBALS['TSFE']->config['config']['language'];
			return (array_key_exists($language, $this->allLangsSsp[$service]))
					? $this->allLangsSsp[$service][$language]
					: $this->allLangsSsp[$service]['en'];
		}
	}

	/**
	 * Get localized dummy image for social service.
	 *
	 * @var string $service A string with service name, like: facebook
	 * @var string $language optional ISO language code, may be 'auto',
	 * @var string $action Only used for Facebook, otherwise NULL ''
	 *
	 * @return string
	 *
	 */
	protected function getLocalizedDummyImage($service, $language = 'en', $action = NULL) {
		if ($this->settings['socialshareprivacy']['imageDirPath'] !== '') {
			$langDir = substr($language, 0, 2);
			if (is_string($action)) {
				return sprintf('%s%s/dummy_%s_%s.png', $this->settings['socialshareprivacy']['imageDirPath'], $langDir, $service, $action);
			} else {
				return sprintf('%s%s/dummy_%s.png', $this->settings['socialshareprivacy']['imageDirPath'], $langDir, $service);
			}
		}
	}
}

?>