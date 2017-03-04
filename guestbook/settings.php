<?php
/* >>> SETUP YOUR GUESTBOOK <<< */
/* Detailed information found in the readme.htm file */
/* File version: 1.7 $ Timestamp: 20th Aug 2009 12:55 */

/* Password for admin area */
$settings['apass']='k3m13u';

/* Website title */
$settings['website_title']="Januri's Profile";

/* Website URL */
$settings['website_url']='#';

/* Guestbook title */
$settings['gbook_title']="";

/* Your e-mail address. */
$settings['admin_email']='januridwiprasetyo@facebook.com';

/* URL of the gbook.php file. */
$settings['gbook_url']='http://januri.smkn43jkt.sch.id/guestbook/gbook.php';

/* If you want to use a logical anti-SPAM question type it here */
$settings['spam_question']='Type <b>ABCDE</b> below to show you are not a robot:';

/* Correct answer to the anti-SPAM logical question */
$settings['spam_answer']='ABCDE';

/* Send you an e-mail when a new entry is added? 1 = YES, 0 = NO */
$settings['notify']=0;

/* Notify visitor when you reply to his/her guestbook entry? 1 = YES, 0 = NO */
$settings['notify_visitor']=0;

/* Manually approve new guestbook entris? 1 = YES, 0 = NO */
$settings['man_approval']=0;

/* Template to use. On many servers template names are CaSe SeNSiTiVe! */
$settings['template']='default';

/* Name of the file where guestbook entries will be stored */
$settings['logfile']='entries.txt';

/* Use "Your website" field? 1 = YES, 0 = NO */
$settings['use_url']=0;

/* Open URLs in a new window? 1 = YES, 0 = NO */
$settings['url_blank']=1;

/* Allow private posts (readable only by admin)? 1 = YES, 0 = NO */
$settings['use_private']=1;

/* Hide e-mail addresses? 1 = YES, 0 = NO */
$settings['hide_emails']=1;

/* Allow smileys? 1 = YES, 0 = NO */
$settings['smileys']=1;

/* Maximum number of smileys per post. Set to 0 for unlimited. */
$settings['max_smileys']=20;

/* Filter bad words? 1 = YES, 0 = NO */
$settings['filter']=1;

/* Filter language. Please refer to readme for info on how to add more bad words to the list! */
$settings['filter_lang']='en';

/* Prevent automated submissions (recommended YES)? 0 = NO, 1 = YES, GRAPHICAL, 2 = YES, TEXT */
$settings['autosubmit']=1;

/* Checksum - just type some digits or chars. Used to help prevent SPAM */
$settings['filter_sum']='tz38wd24fg4p2';

/* Use JunkMark(tm) SPAM filter (recommended YES)? 1 = YES, 0 = NO */
$settings['junkmark_use']=1;

/* JunkMark(tm) score limit after which messages are marked as SPAM */
$settings['junkmark_limit']=61;

/* Ban IP address if JunkMark(tm) score is 100 (100% SPAM)? 1 = YES, 0 = NO */
$settings['junkmark_ban100']=1;

/* Ignore proxy servers from JunkMark check? 1 = YES, 0 = NO */
$settings['ignore_proxies']=0;

/* Show "NO GUESTBOOK SPAM" banner? 1 = YES, 0 = NO */
$settings['show_nospam']=1;

/* Prevent multiple submissions in the same session? 1 = YES, 0 = NO */
$settings['one_per_session']=1;

/* Maximum length of the comment (chars). Set to 0 for unlimited length */
$settings['max_comlen']=1000;

/* Maximum chars word length */
$settings['max_word']=75;

/* Language file */
$settings['language']='language.inc.php';

/* Allow IPv6 format? 1 = YES, 0 = NO */
$settings['allow_IPv6']=0;


/* DO NOT EDIT BELOW */
if (!defined('IN_SCRIPT')) {die('Invalid attempt!');}
ini_set('display_errors', 0);
ini_set('log_errors', 1);
?>
