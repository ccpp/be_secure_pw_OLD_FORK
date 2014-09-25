<?php
namespace SpoonerWeb\BeSecurePw\Hook;

use SpoonerWeb\BeSecurePw\Utilities\PasswordExpirationUtility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Andreas Kießling <andreas.kiessling@web.de>
 *  (c) 2014 Christian Plattner <Christian.Plattner@world-direct.at>
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class RestrictModulesHook implements \TYPO3\CMS\Core\SingletonInterface {
	/**
	 * Insert JavaScript code to refresh the module menu, if the password was updated and
	 * the "force" option was set. The menu then only shows a limited set of available backend modules.
	 *
	 * @param array $params
	 * @param mixed $pObj Reference back to the calling object (called from two different hooks, but we do not need it anyway)
	 * @return string
	 */
	public function addRefreshJavaScript(array $params, $pObj) {
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['be_secure_pw']['insertModuleRefreshJS']) {
			$pageRenderer = $GLOBALS['TBE_TEMPLATE']->getPageRenderer();
			$label = $GLOBALS['LANG']->sL('LLL:EXT:be_secure_pw/Resources/Private/Language/locallang.xml:beSecurePw.backendNeedsToReload');
			$pageRenderer->addExtOnReadyCode(
				'alert("' . $label . '");
					top.location.reload();
					'
			);
		}
	}

	/**
	 * If the password is expired, only load the necessary modules to change the password
	 *
	 * @param array $params
	 * @param mixed $pObj
	 */
	public function postUserLookUp(array $params, $pObj) {
		if (PasswordExpirationUtility::isBeUserPasswordExpired()) {
			// remove admin rights, because otherwise we can't restrict access to the modules
			$GLOBALS['BE_USER']->user['admin'] = 0;
			// this grants the user access to the modules
			$GLOBALS['BE_USER']->user['userMods'] = 'user,user_setup';
			// remove all groups from the user, so he can not get access to any other modules than the ones we granted him
			$GLOBALS['BE_USER']->user['usergroup'] = '';
			// allow access to live and workspace, if the user is currently in a workspace, but the access is removed due to missing usergroup
			$GLOBALS['BE_USER']->user['workspace_perms'] = 3;

			// Disable all columns except password
			$GLOBALS['TYPO3_USER_SETTINGS']['columns'] = array(
				'password' => $GLOBALS['TYPO3_USER_SETTINGS']['columns']['password'],
				'password2' => $GLOBALS['TYPO3_USER_SETTINGS']['columns']['password2'],
			);

			// Override showitem to remove tabs and all fields except password
			$GLOBALS['TYPO3_USER_SETTINGS']['showitem'] = '--div--;LLL:EXT:be_secure_pw/Resources/Private/Language/ux_locallang_csh_mod.xml:option_newPassword.description,password,password2';
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS['BE']['XCLASS']['ext/be_secure_pw/hook/class.tx_besecurepwrestrictModules_hook.phpodulesHook.php']) {
	include_once($TYPO3_CONF_VARS['BE']['XCLASS']['ext/be_secure_pw/hook/class.tx_besecurepwrestrictModules_hook.phpodulesHook.php']);
}
?>
