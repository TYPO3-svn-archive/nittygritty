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
 * ViewHelper to inject/merge News Detail Meta properties with page Meta properties
 * 
 * Example
 * <nittygritty:news.injectNewsMeta newsItem="{newsItem}" />
 * 
 * Result
 * Rendered meta data, page title aso
 *
 * @package TYPO3
 * @subpackage tx_nittygritty
 * @subpackage tx_news
 */
class Tx_Nittygritty_ViewHelpers_News_InjectNewsMetaViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @var mixed
	 */
	protected $settings = NULL;

	/**
	 * @var string RFC3066 language
	 */
	protected $language = NULL;

	/**
	 * @var string
	 */
	protected $pageTitle = NULL;

	/**
	 * @var string
	 */
	protected $description = NULL;

	/**
	 * @var array
	 */
	protected $keywords = NULL;

	/**
	 * @var array
	 */
	protected $authors = NULL;

	/**
	 * @var array
	 */
	protected $authorsLinks = NULL;

	/**
	 * @var array
	 */
	protected $newsTags = NULL;

	/**
	 * @var string
	 */
	protected $url = NULL;

	/**
	 * @var string
	 */
	protected $siteName = NULL;

	/**
	 * @var string
	 */
	protected $imageUrl = NULL;

	/**
	 * @var string
	 */
	protected $metaTitle = NULL;

	/**
	 * @var array
	 */
	protected $tags = array();

	/**
	 * @var array Array of prefixes for head tag
	 */
	protected $prefix = array();

	/**
	 * @var array Array of profiles for head tag
	 */
	protected $profile = array();

	/**
	 * @var array Array of tags to be prepended to generated meta tags
	 */
	protected $prepend = array();

	/**
	 * @var tslib_cObj
	 */
	protected $contentObject = NULL;

	/**
	 * @var Tx_News_Domain_Model_News $newsItem
	 */
	protected $newsItem;

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
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->contentObject = $this->configurationManager->getContentObject();
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
	 * Inject News Meta properties into current page (which should be a news detail page)
	 * @param Tx_News_Domain_Model_News $newsItem
	 * @return void
	 */
	public function render($newsItem = NULL) {
		if (!is_null($newsItem)) {
			$this->settle($newsItem);
			foreach ($this->settings['news']['semantic']['metaBase'] as $category => $v) {
				if (!empty($category['build'])) {
					$this->buildMetaCategory($category);
				}
			}
			$this->addTags($this->tags);
		}
	}

	/**
	 * 
	 * Settles data from News item
	 * @param Tx_News_Domain_Model_News $newsItem
	 * return void
	 */
	protected function settle($newsItem) {
		$this->newsItem = $newsItem;
		$this->setPageTitle();
		$this->setDescription();
		$this->setKeywords();
		$this->setAuthors();
		$this->setAuthorsLinks();
		$this->setLanguage();
		$this->setUrl();
		$this->setSiteName();
		$this->setImageUrl();
		$this->setMetaTitle();
		$this->setNewsTags();
	}

	/**
	 * 
	 * Sets page title from news title or alternativeTitle
	 *
	 * return void
	 */
	protected function setPageTitle() {
		$pageTitleText = (empty($this->settings['news']['semantic']['general']['pageTitle']['useAlternate'])) ? $this->newsItem->getTitle() : $this->newsItem->getAlternativeTitle();
		$pageTitleAction = $this->settings['news']['semantic']['general']['pageTitle']['action'];

		if (!empty($pageTitleAction)) {
			switch ($pageTitleAction) {
				case 'prepend':
					$pageTitleText .= ': ' . $GLOBALS['TSFE']->page['title'];
					break;
				case 'append':
					$pageTitleText = $GLOBALS['TSFE']->page['title'] . ': ' . $pageTitleText;
					break;
				default:
			}
			$this->pageTitle = html_entity_decode($pageTitleText, ENT_QUOTES, "UTF-8");
			$GLOBALS['TSFE']->page['title'] = $this->pageTitle;
			$GLOBALS['TSFE']->indexedDocTitle = $this->pageTitle;
		}
	}

	/**
	 * 
	 * Sets description from news teaser or bodytext
	 *
	 * return void
	 */
	protected function setDescription() {
		$descriptionCrop = intval($this->settings['news']['semantic']['general']['description']['crop']);
		$descriptionAction = $this->settings['news']['semantic']['general']['description']['action'];
		$descriptionText = $this->newsItem->getTeaser();
		if (empty($descriptionText)) {
			$descriptionText = $this->newsItem->getBodytext();
		}
		if (!empty($descriptionText) && !empty($descriptionAction)) {
			if (!empty($GLOBALS['TSFE']->page['description'])) {
				switch ($descriptionAction) {
					case 'prepend':
						$descriptionText .= ': ' . $GLOBALS['TSFE']->page['description'];
						break;
					case 'append':
						$descriptionText = $GLOBALS['TSFE']->page['description'] . ': ' . $descriptionText;
						break;
					default:
				}
			}
			$this->description = $this->contentObject->crop($descriptionText, $descriptionCrop . '|...|' . TRUE);
		}
	}

	/**
	 * 
	 * Sets keywords from news keywords
	 *
	 * return void
	 */
	protected function setKeywords() {
		$keywords = $this->newsItem->getKeywords();
		$keywordsAction = $this->settings['news']['semantic']['general']['keywords']['action'];
		$pageKeywords = $GLOBALS['TSFE']->page['keywords'];
		if (!empty($keywordsAction) && !empty($pageKeywords)) {
			switch ($keywordsAction) {
				case 'prepend':
					$keywords = (empty($keywords)) ? $pageKeywords : $keywords . ', ' . $pageKeywords;
					break;
				case 'append':
					$keywords = (empty($keywords)) ? $pageKeywords : $pageKeywords . ',' . $keywords;
					break;
				default:
			}
		}
		$keywordsArray = t3lib_div::trimExplode(',', $keywords);
		$keywordsLimit = intval($this->settings['news']['semantic']['general']['keywords']['limit']);
		$this->keywords = (empty($keywordsLimit)) ? array_unique($keywordsArray) : $this->keywords = array_slice(array_unique($keywordsArray), 0, $keywordsLimit);
	}

	/**
	 * 
	 * sets authors array from news author(s)
	 *
	 * return void
	 */
	protected function setAuthors() {
		// authors, yes, we can handle more than one per article
		$authorsStg = $this->newsItem->getAuthor();
		if (!empty($authorsStg)) {
			$authors = t3lib_div::trimExplode(',', $authorsStg);
			$allowedCount = intval($this->settings['news']['semantic']['general']['author']['max']);
			$this->authors = (empty($allowedCount)) ? $authors : array_slice($authors, 0, $allowedCount);
		}
	}

	/**
	 * 
	 * Sets authors links, if available
	 * 
	 * return void
	 */
	protected function setAuthorsLinks() {
		$authors = $this->getAuthors();
		if (!empty($authors)) {
			foreach ($this->settings['site']['authors'] as $author) {
				if (in_array(trim($author['name']), $authors) && !empty($author['uri'])) {
					$typolink_conf = array(
						"parameter" => $author['uri'],
						"forceAbsoluteUrl" => 1,
						"useCacheHash" => 0);
					$this->authorsLinks[] = $this->getUri($typolink_conf);
				}
			}
		}
	}

	/**
	 * 
	 * Sets language from FE
	 *
	 * return void
	 */
	protected function setLanguage() {
		$this->language = $GLOBALS['TSFE']->config['config']['language'];
	}

	/**
	 * 
	 * Sets Url
	 *
	 * return void
	 */
	protected function setUrl() {
		$this->url = htmlspecialchars(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'), ENT_QUOTES);
	}

	/**
	 * 
	 * Sets sitename from setup
	 *
	 * return void
	 */
	protected function setSiteName() {
		$this->siteName = trim($this->settings['site']['siteName']);
	}

	/**
	 * 
	 * Sets preview image url from news item
	 *
	 * return void
	 */
	protected function setImageUrl() {
		$previewImageElement = $this->newsItem->getFirstImagePreview();
		if (!empty($previewImageElement)) {
			$imgUrl = $GLOBALS['TSFE']->absRefPrefix . 'uploads/tx_news/' . t3lib_div::rawUrlEncodeFP($previewImageElement->getImage());
			if (!t3lib_div::isFirstPartOfStr($imgUrl, t3lib_div::getIndpEnv('TYPO3_SITE_URL'))) {
				$imgUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $imgUrl;
			}
			$this->imageUrl = $imgUrl;
		}
	}

	/**
	 * 
	 * Sets meta title from news item
	 *
	 * return void
	 */
	protected function setMetaTitle() {
		$partStg = trim($this->settings['news']['semantic']['general']['metaTitle']);
		if (is_int(strpos($partStg, '+'))) {
			$parts = t3lib_div::trimExplode('+', $partStg);
			foreach ($parts as $part) {
				$action = 'get' . ucfirst($part);
				$value = $this->newsItem->$action();
				if ($value !== '') {
					$data[] = $value;
				}
			}
			$this->metaTitle = implode(': ', $data);
		} elseif (is_int(strpos($partStg, '//'))) {
			$parts = t3lib_div::trimExplode('//', $partStg);
			foreach ($parts as $part) {
				$action = 'get' . ucfirst($part);
				$data = $this->newsItem->$action();
				if ($data !== '') {
					$this->metaTitle = $data;
					break;
				}
			}
		} else {
			$action = 'get' . ucfirst($partStg);
			$this->metaTitle = $this->newsItem->$action();
		}
	}

	/**
	 * 
	 * Sets newsTags array from newsItem
	 *
	 * return void
	 */
	protected function setNewsTags() {
		$newsTags = $this->newsItem->getTags();
		foreach ($newsTags as $tag) {
			$this->newsTags[] = $tag->getTitle();
		}
	}

	/**
	 * 
	 * Get page title
	 *
	 * return string
	 */
	protected function getPageTitle() {
		return $this->pageTitle;
	}

	/**
	 * 
	 * Get FE language
	 *
	 * return string
	 */
	protected function getLanguage() {
		return $this->language;
	}

	/**
	 * 
	 * Get news title
	 *
	 * return string
	 */
	protected function getTitle() {
		return $this->newsItem->getTitle();
	}

	/**
	 * 
	 * Get alternative news title
	 *
	 * return string
	 */
	protected function getAlternativeTitle() {
		return $this->newsItem->getAlternativeTitle();
	}

	/**
	 * 
	 * Gets Meta description text
	 * 
	 * return string
	 */
	protected function getDescription() {
		return $this->description;
	}

	/**
	 * 
	 * Gets keywords as array
	 * 
	 * return array
	 */
	protected function getKeywords() {
		return $this->keywords;
	}

	/**
	 * 
	 * Gets keywords as string
	 * 
	 * return string
	 */
	protected function getKeywordsString() {
		return implode(', ', $this->keywords);
	}

	/**
	 * 
	 * Gets authors names as array
	 * 
	 * return array
	 */
	protected function getAuthors() {
		return $this->authors;
	}
	
	/**
	 * 
	 * Gets first author name as string
	 * 
	 * return string
	 */
	protected function getFirstAuthor() {
		return $this->authors[0];
	}
	
	/**
	 * 
	 * Gets authors links
	 * 
	 * return array
	 */
	protected function getAuthorsLinks() {
		return $this->authorsLinks;
	}

	/**
	 * 
	 * Get url of current page
	 *
	 * return string
	 */
	protected function getUrl() {
		return $this->url;
	}

	/**
	 * 
	 * Get sitename
	 *
	 * return string
	 */
	protected function getSiteName() {
		return $this->siteName;
	}

	/**
	 * 
	 * Get preview image url
	 *
	 * return string
	 */
	protected function getImageUrl() {
		return $this->imageUrl;
	}

	/**
	 * 
	 * Get meta title
	 *
	 * return string
	 */
	protected function getMetaTitle() {
		return $this->metaTitle;
	}

	/**
	 * 
	 * Get newsTags
	 *
	 * return array
	 */
	protected function getNewsTags() {
		return $this->newsTags;
	}

	/**
	 * 
	 * Get newsTags
	 *
	 * return array
	 */
	protected function getFirstCategory() {
		return $this->newsItem->getFirstCategory()->getTitle();
	}

	/**
	 * 
	 * Gets datetime formatted string 
	 *
	 * @param string
	 * return string
	 */
	protected function getCreated($format = 'Y-m-d') {
		return ($this->newsItem->getDatetime()->format($format));
	}

	/**
	 * 
	 * Gets timestamp formatted string 
	 * 
	 * @param string
	 * return string
	 */
	protected function getModified($format = 'Y-m-d') {
		$tStamp = $this->newsItem->getTstamp();
		if ($tStamp instanceof dateTime) {
			return $tStamp->format($format);
		}
	}

	/**
	 * 
	 * Gets endtime formatted string 
	 *
	 * return string
	 */
	protected function getExpires($format = 'Y-m-d') {
		$expires = $this->newsItem->getEndtime();
		if ($expires instanceof dateTime) {
			return $expires->format($format);
		}
	}

	/**
	 * 
	 * Builds tags for selected category (meta,op,dc)
	 * @param string $category The category to render (meta, og, dc)
	 * @param array $params
	 * return void
	 */
	protected function buildMetaCategory($category = NULL) {
		if (!empty($category) && array_key_exists($category, $this->settings['news']['semantic']['meta'])) {

			$config = $this->getConfig($category);
			$buildTags = t3lib_div::trimExplode(',', $config['build']);

			foreach ($buildTags as $tag) {
				if (array_key_exists($tag, $this->settings['news']['semantic']['meta'][$category])) {
					$rules = $this->settings['news']['semantic']['meta'][$category][$tag];
				} elseif (is_int(strpos($tag, ':'))) {
					$pathParts = t3lib_div::trimExplode(':', $tag);
					if (array_key_exists($pathParts[1], $this->settings['news']['semantic']['meta'][$category][$pathParts[0]])) {
						$rules = $this->settings['news']['semantic']['meta'][$category][$pathParts[0]][$pathParts[1]];
					}
				} elseif (is_int(strpos($tag, '.'))) {
					$pathParts = t3lib_div::trimExplode('.', $tag);
					if (array_key_exists($pathParts[1], $this->settings['news']['semantic']['meta'][$category][$pathParts[0]])) {
						$rules = $this->settings['news']['semantic']['meta'][$category][$pathParts[0]][$pathParts[1]];
					}
				} else {
					continue;
				}
				if (isset($rules) && !empty($rules) && is_array($rules)) {
					$prefType = $config['nsType'];
					if (isset($pathParts) && !empty($prefType)) {
						if (!array_key_exists($pathParts[0], $this->{$prefType}) && array_key_exists($pathParts[0], $config['ns'])) {
							$this->{$prefType}[$pathParts[0]] = $config['ns'][$pathParts[0]];
						}
						if (isset($config['prepend']) && array_key_exists($pathParts[0], $config['prepend'])) {
							$this->prepend[$pathParts[0]] = $config['prepend'][$pathParts[0]];
						}
					}
					$scheme = (isset($rules['scheme'])) ? $rules['scheme'] : NULL;

					if (isset($rules['value'])) {
						$data = $rules['value'];
					} elseif (isset($rules['action']) && method_exists($this, $rules['action'])) {
						$action = $rules['action'];
						$data = (in_array($action, array('getCreated', 'getModified', 'getExpires'))) ? $this->$action($config['dateFormat']) : $this->$action();
					} else {
						continue;
					}
					if (!empty($data)) {
						if (is_array($data)) {
							if (isset($rules['multiple']) && !empty($rules['multiple'])) {
								foreach ($data as $part) {
									$this->addTag($config['tagName'], $config['type'], $tag, $part, $scheme);
								}
							} else {
								$this->addTag($config['tagName'], $config['type'], $tag, $data[0], $scheme);
							}
						} else {
							$this->addTag($config['tagName'], $config['type'], $tag, $data, $scheme);
						}
					}
				}
			}
		}
	}

	/**
	 * 
	 * return string The head tag, depending on needed content
	 */
	protected function getHeadTag() {
		$headTagContent = '';
		if (count($this->profile) > 0) {
			$headTagContent .= sprintf(' profile="%s"', implode(',', $this->profile));
		}
		if (count($this->prefix) > 0) {
			foreach ($this->prefix as $k => $v) {
				$data[$k] = sprintf('%s: %s', $k, $v);
			}
			$headTagContent .= sprintf(' prefix="%s"', implode(' ', $data));
		}
		if (!empty($headTagContent)) {
			return sprintf('<head%s >', $headTagContent);
		}
	}

	/**
	 * 
	 * @param array $tags
	 * return void
	 */
	protected function addTags($tags = NULL) {
		/**
		 *  Makes no sense to use the DI here
		 *  it is a singleton, so we always get the same instance
		 *  @var $pageRenderer t3lib_PageRenderer 
		 */
		$headTagContent = $this->getHeadTag();
		if (!empty($headTagContent)) {
			$GLOBALS['TSFE']->pSetup['headTag'] = $headTagContent;
		}
		$pageRenderer = t3lib_div::makeInstance('t3lib_PageRenderer');
		if (!empty($this->prepend)) {
			foreach ($this->prepend as $tag) {
				$pageRenderer->addMetaTag($tag);
			}
		}

		if (!empty($tags)) {
			foreach ($tags as $tag) {
				$pageRenderer->addMetaTag($tag);
			}
		}
	}

	/**
	 * Add tag
	 *
	 * @var string $tagName
	 * @var string $type
	 * @var string $content
	 * @var string $scheme
	 * @return void
	 * 
	 */
	protected function addTag($tagName = 'meta', $type = 'name', $property = '', $content = '', $scheme = '') {

		if (!empty($content) && !empty($property)) {
			$tag = (empty($scheme)) ? sprintf('<%s %s="%s" content="%s" >', $tagName, $type, $property, $content) : sprintf('<%s %s="%s" content="%s" scheme="%s" >', $tagName, $type, $property, $content, $scheme);
			array_push($this->tags, $tag);
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
	 * Get Configuration of property.
	 *
	 * @var string $config
	 *
	 * @return array
	 * 
	 */
	protected function getConfig($category = NULL) {
		if (!empty($category)) {
			$setup = $this->settings['news']['semantic']['metaBase'][$category];
			switch ($category) {
				case 'og':
					$defaults = array(
						'tagName' => 'meta',
						'type' => 'property',
						'dateFormat' => 'Y-m-d',
						'nsType' => 'prefix',
						'ns' => array(
							'fb' => 'http://ogp.me/ns/fb#',
							'og' => 'http://ogp.me/ns#',
							'article' => 'http://ogp.me/ns/article#',
						),
					);
					break;
				case 'dc':
					$defaults = array(
						'tagName' => 'meta',
						'type' => 'name',
						'dateFormat' => 'Y-m-d',
						'nsType' => 'profile',
						'ns' => array(
							'DC' => 'http://dublincore.org/documents/dcq-html/',
						),
						'prepend' => array(
							'DC' => '<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />',
							'DCTERMS' => '<link rel="schema.DCTERMS" href="http://purl.org/dc/terms/" />',
						),
					);
					break;
				default:
					$defaults = array(
						'tagName' => 'meta',
						'type' => 'name',
						'dateFormat' => 'Y-m-d',
					);
			}
			return array_merge($defaults, $setup);
		}
	}
}

?>