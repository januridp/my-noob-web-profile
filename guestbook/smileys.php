<?php
/*******************************************************************************
*  Title: GBook - PHP Guestbook
*  Version: 1.7 from 20th August 2009
*  Author: Klemen Stirn
*  Website: http://www.phpjunkyard.com
********************************************************************************
*  COPYRIGHT NOTICE
*  Copyright 2004-2009 Klemen Stirn. All Rights Reserved.

*  The GBook may be used and modified free of charge by anyone
*  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
*  By using this code you agree to indemnify Klemen Stirn from any
*  liability that might arise from it's use.

*  Selling the code for this program, in part or full, without prior
*  written consent is expressly forbidden.

*  Using this code, in part or full, to create derivate work,
*  new scripts or products is expressly forbidden. Obtain permission
*  before redistributing this software over the Internet or in
*  any other medium. In all cases copyright and header must remain intact.
*  This Copyright is in full effect in any country that has International
*  Trade Agreements with the United States of America or
*  with the European Union.

*  Removing any of the copyright notices without purchasing a license
*  is expressly forbidden. To remove GBook copyright notice you must purchase
*  a license for this script. For more information on how to obtain
*  a license please visit the page below:
*  http://www.phpjunkyard.com/copyright-removal.php
*******************************************************************************/

define('IN_SCRIPT',true);

require('settings.php');
require($settings['language']);

/* Template path to use */
$settings['tpl_path'] = './templates/'.$settings['template'].'/';

/* Get file with emoticons settings */
require($settings['tpl_path'].'emoticons.php');

$list_emoticons = '';
foreach ($settings['emoticons'] as $code => $image)
{
	$list_emoticons .= '<a href="javascript:void(0)" onclick="Javascript:insertSmiley(\''.$code.'\');return false;"><img src="'.$settings['tpl_path'].'images/emoticons/'.$image.'" alt="'.$code.'" title="'.$code.'" class="gbook_emoticon" /></a> ';
}

require($settings['tpl_path'].'emoticons_popup.php');
exit();
?>
