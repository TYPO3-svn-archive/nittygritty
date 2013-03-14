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
 * <nittygritty:IncludeJsCssInlineBlock block="{myBlock}" type="js" name="myCode" inFooter="TRUE" compress="FALSE" forceOnTop="FALSE" />
 *
 * @package TYPO3
 * @subpackage tx_nittygritty
 */
class Tx_Nittygritty_ViewHelpers_IncludeJsCssInlineBlockViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {
	
	/**
	 * Include a CSS/JS inline block (footer placement optional)
	 *
	 * @param string $block The code block to include
	 * @param string $type The type of the block, css or js
	 * @param string $name array key of this type block
	 * @param boolean $inFooter If set, block will be placed in footer (js only)
	 * @param boolean $compress if block should be compressed
	 * @param boolean $forceOnTop Flag, if set, block will be inserted at begin of this type block
	 * @return void
	 */
	public function render($block = '', $type = '', $name = '', $inFooter = FALSE, $compress = FALSE, $forceOnTop = FALSE) {
		$allowedTypes = array('css', 'js');
		$block = trim($block);
		$type = trim($type);
		$name = trim($name);
		if (empty($block) || empty($name) || !in_array($type, $allowedTypes)) {
			return;
		}

		/**
		 *  Makes no sense to use the DI here
		 *  it is a singleton, so we get always the same instance
		 *  @var $pageRenderer t3lib_PageRenderer 
		 */
		$pageRenderer = t3lib_div::makeInstance('t3lib_PageRenderer');

		if ($type == 'css') {
			$pageRenderer->addCssInlineBlock($name, $block, $compress, $forceOnTop);
		} elseif (($type == 'js') && !(bool)$inFooter) {
			$pageRenderer->addJsInlineCode($name, $block, $compress, $forceOnTop);
		} elseif (($type == 'js') && (bool)$inFooter) {
			$pageRenderer->addJsFooterInlineCode($name, $block, $compress, $forceOnTop);
		} else {
			return;
		}
	}
}
?>