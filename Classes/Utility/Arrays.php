<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Thomas Skierlo
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
 * Utility class for handling of array
 *
 * @package TYPO3
 * @subpackage tx_nittygritty
 * @author Thomas Skierlo <tsk@@pix-pro.eu>
 *
 */
class Tx_Nittygritty_Utility_Arrays {

	/**
	 * Removes array keys and value recursively if value is empty or an empty string
	 * @param array $haystack The array to process
	 * @param $defaults Array of default values to remove
	 * @param boolean $removeEmptyStrings If set (default), only empty string and default values will be removed
	 * @param boolean $removeDefaults If set, default values will be removed
	 * @return array Sanitized array
	 *
	 */
	public static function arrayRemoveCertainValuesRecursive($haystack, $defaults = array(), $removeEmptyStrings = TRUE, $removeDefaults = FALSE ) {
		foreach ($haystack as $key => $value) {
			if (is_array($value)) {
				$haystack[$key] = self::arrayRemoveCertainValuesRecursive($haystack[$key], $defaults[$key], $removeEmptyStrings, $removeDefaults);
			}
			if ($removeEmptyStrings && ($haystack[$key] === '')) {
				unset($haystack[$key]);
			} elseif ($removeDefaults && array_key_exists($key, $defaults) && ($defaults[$key] == $haystack[$key])) {
				unset($haystack[$key]);
			} elseif (!$removeEmptyStrings && !$removeDefaults && empty($haystack[$key])) {
				unset($haystack[$key]);					
			}
		}
		return $haystack;
	}
}

?>