<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// here we register "PasswordEvaluator"
// for editing by tca form
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['SpoonerWeb\\BeSecurePw\\Evaluation\\PasswordEvaluator'] = 'EXT:be_secure_pw/Classes/Evaluation/PasswordEvaluator.php';

// for editing per "user settings"
$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/template.php']['preStartPageHook'][] = 'SpoonerWeb\\BeSecurePw\\Hook\\UserSetupHook->preStartPageHook';
$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/template.php']['moduleBodyPostProcess'][] = 'SpoonerWeb\\BeSecurePw\\Hook\\UserSetupHook->moduleBodyPostProcess';
$TYPO3_CONF_VARS['SC_OPTIONS']['ext/setup/mod/index.php']['modifyUserDataBeforeSave'][] = 'SpoonerWeb\\BeSecurePw\\Hook\\UserSetupHook->modifyUserDataBeforeSave';

// password reminder
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
// execution of is hook only needed in backend, but it is in the abstract class and could also be executed from frontend otherwise
// if the backend is set to adminOnly, we can not enforce the change, because the hook removes the admin flag
if ($extConf['forcePasswordChange'] && TYPO3_MODE === 'BE' && intval($TYPO3_CONF_VARS['BE']['adminOnly']) === 0) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['ext/setup/mod/index.php']['setupScriptHook'][] = 'SpoonerWeb\\BeSecurePw\\Hook\\RestrictModulesHook->addRefreshJavaScript';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] = 'SpoonerWeb\\BeSecurePw\\Hook\\RestrictModulesHook->postUserLookUp';
} else {
	$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][] = 'SpoonerWeb\\BeSecurePw\\Hook\\BackendHook->constructPostProcess';
}
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['be_secure_pw'] = 'SpoonerWeb\\BeSecurePw\\Hook\\BackendHook';

?>
