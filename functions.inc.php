<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//  Copyright 2006 Philippe Lindheimer
//

if (! function_exists('debug_string_backtrace'))
{
	function debug_string_backtrace() {
		ob_start();
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$trace = ob_get_contents();
		ob_end_clean();
		return $trace;
	}
}


/** Not necessary hook is executed */
// function queueprio_destinations() {
// 	FreePBX::Modules()->deprecatedFunction();
// 	return \FreePBX::Queueprio()->destinations();
// }


/** Commented function since it overlaps with the hook **/
// function queueprio_check_destinations($dest=true) {
//  	FreePBX::Modules()->deprecatedFunction();
//  	return \FreePBX::Queueprio()->destinations_check($dest);
// }



/** Commented function since with the FREEPBX-23720 patch the necessary hook is implemented **/
// function queueprio_getdestinfo($dest) {
// 	FreePBX::Modules()->deprecatedFunction();
// 	return \FreePBX::Queueprio()->destinations_getdestinfo($dest);
// }

/** Commanded function since it overlaps with the new Hook (doDialplanHook) */
// function queueprio_get_config($engine) {
//  dbug(debug_string_backtrace());
// 	FreePBX::Modules()->deprecatedFunction();
// 	global $ext;
//  	\FreePBX::Queueprio()->doDialplanHook($ext, $engine);
// }


/** Commented function since it overlaps with the hook **/
// function queueprio_change_destination($old_dest, $new_dest) {
// 	FreePBX::Modules()->deprecatedFunction();
// 	\FreePBX::Queueprio()->destinations_change($old_dest, $new_dest);
// }


// TODO: There is no hook on the _redirect_standard_helper function in the view.functions.php file.
function queueprio_getdest($exten) {
	return [\FreePBX::Queueprio()->getDest($exten)];
}

?>