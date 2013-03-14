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
 * ViewHelper to include a css/js file
 * 
 * Example1: Usage with css 
 * <nittygritty:includeJsCssFile path="{settings.path}" name="myCssLib" media="screen" compress="0" forceOnTop="0" excludeFromConcatenation="1"/>
 *
 * @package TYPO3
 * @subpackage tx_nittygritty
 */
class Tx_Nittygritty_ViewHelpers_IncludeJsCssFileViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Include a CSS/JS file in header or in footer
	 *
	 * @param string $path path to the file
	 * @param string $name name/title of the file
	 * @param string $media css only, media type
	 * @param string $allWrap Wrap of the whole element	 * 
	 * @param boolean $inFooter If set, file will be placed in footer (js only)
	 * @param boolean $compress if file should be compressed
	 * @param boolean $forceOnTop Flag if added library should be inserted at begin of this type block
	 * @param boolean $excludeFromConcatenation
	 * @return void
	 */
	public function render($path, $name = '', $media = '', $allWrap = '', $inFooter = FALSE, $compress = FALSE, $forceOnTop = FALSE, $excludeFromConcatenation = FALSE) {
		$path = trim($path);
		if (empty($path)) {
			return;
		}
		$path = $GLOBALS['TSFE']->tmpl->getFileName($path);
		$pathParts = pathinfo($path);
		switch (strtolower($pathParts['extension'])) {
			case 'css':
				$extension = 'css';
				$title = (empty($name)) ? $pathParts['filename'] : $name;
				$rel = 'stylesheet';
				$media = (empty($media)) ? 'all' : $media;
				break;
			case 'js':
				$extension = 'js';
				$library = (empty($name)) ? FALSE : TRUE;
				$type = 'text/javascript';
				break;
			default:
				return;
		}

		/**
		 *  Makes no sense to use the DI here
		 *  it is a singleton, so we get always the same instance
		 *  @var $pageRenderer t3lib_PageRenderer 
		 */
		$pageRenderer = t3lib_div::makeInstance('t3lib_PageRenderer');

		if ($extension == 'css') {
			$pageRenderer->addCssFile($path, $rel, $media, $title, $compress, $forceOnTop, $allWrap, $excludeFromConcatenation);
		} elseif (($extension == 'js') && $library && !$inFooter) {
			$pageRenderer->addJsLibrary($name, $path, $type, $compress, $forceOnTop, $allWrap, $excludeFromConcatenation);
		} elseif (($extension == 'js') && $library && $inFooter) {
			$pageRenderer->addJsFooterLibrary($name, $path, $type, $compress, $forceOnTop, $allWrap, $excludeFromConcatenation);
		} elseif (($extension == 'js') && !$library && !$inFooter) {
			$pageRenderer->addJsFile($path, $type, $compress, $forceOnTop, $allWrap, $excludeFromConcatenation);
		} elseif (($extension == 'js') && !$library && $inFooter) {
			$pageRenderer->addJsFooterFile($path, $type, $compress, $forceOnTop, $allWrap, $excludeFromConcatenation);
		} else {
			return;
		}
	}
}
?>