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

/* Set some variables that will be used later */
$settings['verzija'] = '1.7';
$settings['number_of_entries'] = '';
$settings['number_of_pages'] = '';
$settings['pages_top'] = '';

/* Template path to use */
$settings['tpl_path'] = './templates/'.$settings['template'].'/';

/* Set target window for URLs */
$settings['target'] = $settings['url_blank'] ? ' target="_blank"' : '';

/* First thing to do is make sure the IP accessing GBook hasn't been banned */
gbook_CheckIP();

/* Get the action parameter */
$a = isset($_REQUEST['a']) ? gbook_input($_REQUEST['a']) : '';

/* And this will start session which will help prevent multiple submissions and spam */
if ($a=='sign' || $a=='add')
{
    session_name('GBOOK');
    session_start();

    $myfield['name']=str_replace(array('.','/'),'',sha1('name'.$settings['filter_sum']));
    $myfield['cmnt']=str_replace(array('.','/'),'',sha1('comments'.$settings['filter_sum']));
    $myfield['bait']=str_replace(array('.','/'),'',sha1('bait'.$settings['filter_sum']));
    $myfield['answ']=str_replace(array('.','/'),'',sha1('answer'.$settings['filter_sum']));
}

/* Don't cache any of the pages */
printNoCache();

/* Check actions */
if ($a)
{
	/* Session is blocked, show an error */
    if (!empty($_SESSION['block']))
    {
        problem($lang['e01'],0);
    }

    /* Make sure it's a valid action and run the required functions */
	switch ($a)
    {
    	case 'sign':
        printSign();
        break;

        case 'delete':
        confirmDelete();
        break;

        case 'viewprivate':
        confirmViewPrivate();
        break;

        case 'add':
        addEntry();
        break;

        case 'confirmdelete':
        doDelete();
        break;

        case 'showprivate':
        showPrivate();
        break;

        case 'reply':
        writeReply();
        break;

        case 'postreply':
        postReply();
        break;

        case 'viewIP':
        confirmViewIP();
        break;

        case 'showIP':
        showIP();
        break;

        case 'viewEmail':
        confirmViewEmail();
        break;

        case 'showEmail':
        showEmail();
        break;

        case 'approve':
        approveEntry();
        break;

        default:
        problem($lang['e11']);
	} // END Switch $a

} // END If $a

/* Prepare and show the GBook entries */
$settings['notice'] = defined('NOTICE') ? NOTICE : '';

$page = (isset($_REQUEST['page'])) ? intval($_REQUEST['page']) : 0;
if ($page > 0)
{
    $start = ($page*10)-9;
    $end   = $start+9;
}
else
{
    $page  = 1;
    $start = 1;
    $end   = 10;
}

$lines = file($settings['logfile']);
$total = count($lines);

if ($total > 0)
{
    if ($end > $total)
    {
    	$end = $total;
    }
    $pages = ceil($total/10);

    $settings['number_of_entries'] = sprintf($lang['t01'],$total,$pages);
    $settings['number_of_pages'] = ($pages > 1) ? sprintf($lang['t75'],$pages) : '';

    if ($pages > 1)
    {
        $prev_page = ($page-1 <= 0) ? 0 : $page-1;
        $next_page = ($page+1 > $pages) ? 0 : $page+1;

        if ($prev_page)
        {
            $settings['pages_top'] .= '<a href="gbook.php?page=1">'.$lang['t02'].'</a> ';
        	if ($prev_page != 1)
            {
        		$settings['pages_top'] .= '<a href="gbook.php?page='.$prev_page.'">'.$lang['t03'].'</a> ';
            }
        }

        for ($i=1; $i<=$pages; $i++)
        {
            if ($i <= ($page+5) && $i >= ($page-5))
            {
               if ($i == $page)
               {
               	$settings['pages_top'] .= ' <b>'.$i.'</b> ';
               }
               else
               {
               	$settings['pages_top'] .= ' <a href="gbook.php?page='.$i.'">'.$i.'</a> ';
               }
            }
        }

        if ($next_page)
        {
        	if ($next_page != $pages)
            {
	            $settings['pages_top'] .= ' <a href="gbook.php?page='.$next_page.'">'.$lang['t04'].'</a>';
            }
        	$settings['pages_top'] .= ' <a href="gbook.php?page='.$pages.'">'.$lang['t05'].'</a>';
        }

    } // END If $pages > 1

} // END If $total > 0

printTopHTML();

if ($total == 0)
{
    include($settings['tpl_path'].'no_comments.php');
}
else
{
	printEntries($lines,$start,$end);
}

printDownHTML();
exit();


/***** START FUNCTIONS ******/

function approveEntry()
{
	global $settings, $lang;

	$approve = intval($_GET['do']);

	$hash = gbook_input($_GET['id'],$lang['e24']);
	$hash = preg_replace('/[^a-z0-9]/','',$hash);
	$file = 'apptmp/'.$hash.'.txt';

	/* Check if the file hash is correct */
	if (!file_exists($file))
	{
   		problem($lang['e25']);
	}

	/* Reject the link */
	if (!$approve)
	{
		define('NOTICE',$lang['t87']);
	}
	else
	{
		$addline = file_get_contents($file);
		$links = file_get_contents($settings['logfile']);
		if ($links === false)
		{
			problem($lang['e18']);
		}

		$addline .= $links;

		$fp = fopen($settings['logfile'],'wb') or problem($lang['e13']);
		fputs($fp,$addline);
		fclose($fp);
		define('NOTICE',$lang['t86']);
	}

    /* Delete the temporary file */
	unlink($file);

} // END approveEntry()


function showEmail()
{
	global $settings, $lang;

    $error_buffer = '';

	$num = isset($_POST['num']) ? intval($_POST['num']) : false;
    if ($num === false)
    {
    	problem($lang['e02']);
    }

    /* Check password */
    if (empty($_POST['pass']))
    {
    	$error_buffer .= $lang['e09'];
    }
    elseif ( gbook_input($_POST['pass']) != $settings['apass'] )
    {
    	$error_buffer .= $lang['e12'];
    }

    /* Any errors? */
    if ($error_buffer)
    {
    	confirmViewEmail($error_buffer);
    }

	/* All OK, show the IP address */
	$lines = file($settings['logfile']);

	$myline = explode("\t",$lines[$num]);

	define('NOTICE', $lang['t65'].' <a href="mailto&#58;'.$myline[2].'">'.$myline[2].'</a>');

} // END showEmail


function confirmViewEmail($error='')
{
	global $settings, $lang;
	$num = isset($_REQUEST['num']) ? intval($_REQUEST['num']) : false;
    if ($num === false)
    {
    	problem($lang['e02']);
    }

    $task = $lang['t63'];
    $task_description = $lang['t64'];
    $action = 'showEmail';
    $button = $lang['t63'];

    printTopHTML();
    require($settings['tpl_path'].'admin_tasks.php');
    printDownHTML();

} // END confirmViewEmail


function showIP()
{
	global $settings, $lang;

    $error_buffer = '';

	$num = isset($_POST['num']) ? intval($_POST['num']) : false;
    if ($num === false)
    {
    	problem($lang['e02']);
    }

    /* Check password */
    if (empty($_POST['pass']))
    {
    	$error_buffer .= $lang['e09'];
    }
    elseif ( gbook_input($_POST['pass']) != $settings['apass'] )
    {
    	$error_buffer .= $lang['e12'];
    }

    /* Any errors? */
    if ($error_buffer)
    {
    	confirmViewIP($error_buffer);
    }

	/* All OK, show the IP address */
	$lines = file($settings['logfile']);

	$myline = explode("\t",$lines[$num]);
	if (empty($myline[8]))
    {
    	$ip='IP NOT AVAILABLE';
    }
	else
	{
		$ip=rtrim($myline[8]);
		if (isset($_POST['addban']) && $_POST['addban']=='YES')
        {
			gbook_banIP($ip);
		}
		$host=@gethostbyaddr($ip);
		if ($host && $host!=$ip)
        {
        	$ip.=' ('.$host.')';
        }
	}

	define('NOTICE', $lang['t69'] . '<br class="clear" />' . $ip);

} // END showIP


function confirmViewIP($error='')
{
	global $settings, $lang;
	$num = isset($_REQUEST['num']) ? intval($_REQUEST['num']) : false;
    if ($num === false)
    {
    	problem($lang['e02']);
    }

    $task = $lang['t09'];
    $task_description = $lang['t10'];
    $action = 'showIP';
    $button = $lang['t24'];

    $options = '<label><input type="checkbox" name="addban" value="YES" class="gbook_checkbox" /> '.$lang['t23'].'</label>';

    printTopHTML();
    require($settings['tpl_path'].'admin_tasks.php');
    printDownHTML();

} // END confirmViewIP


function postReply()
{
	global $settings, $lang;

    $error_buffer = '';

	$num = isset($_POST['num']) ? intval($_POST['num']) : false;
    if ($num === false)
    {
    	problem($lang['e02']);
    }

    /* Check password */
    if (empty($_POST['pass']))
    {
    	$error_buffer .= $lang['e09'] . '<br />';
    }
    elseif ( gbook_input($_POST['pass']) != $settings['apass'] )
    {
    	$error_buffer .= $lang['e12'];
    }

    /* Check message */
    $comments = (isset($_POST['comments'])) ? gbook_input($_REQUEST['comments']) : false;
    if (!$comments)
    {
    	$error_buffer .= $lang['e10'];
        $comments = '';
    }

    /* Any errors? */
    if ($error_buffer)
    {
    	writeReply($error_buffer, $comments);
    }

	/* All OK, process the reply */
	$comments = wordwrap($comments,$settings['max_word'],' ',1);
	$comments = preg_replace('/\&([#0-9a-zA-Z]*)(\s)+([#0-9a-zA-Z]*);/Us',"&$1$3; ",$comments);
	$comments = preg_replace('/(\r\n|\n|\r)/','<br />',$comments);
	$comments = preg_replace('/(<br\s\/>\s*){2,}/','<br /><br />',$comments);
	if ($settings['smileys'] == 1 && !isset($_REQUEST['nosmileys']) )
    {
    	$comments = processsmileys($comments);
    }

	$myline = array(0=>'',1=>'',2=>'',3=>'',4=>'',5=>'',6=>'',7=>'',8=>'');
	$lines  = file($settings['logfile']);
	$myline = explode("\t",$lines[$num]);
	foreach ($myline as $k=>$v)
    {
		$myline[$k]=rtrim($v);
	}
	$myline[7] = $comments;
	$lines[$num] = implode("\t",$myline)."\n";
	$lines = implode('',$lines);
	$fp = fopen($settings['logfile'],'wb') or problem($lang['e13']);
	fputs($fp,$lines);
	fclose($fp);

    /* Notify visitor? */
    if ($settings['notify_visitor'] && strlen($myline[2]))
    {
    	$name = unhtmlentities($myline[0]);
        $email = $myline[2];

	    $char = array('.','@');
	    $repl = array('&#46;','&#64;');
	    $email=str_replace($repl,$char,$email);
		$message = sprintf($lang['t76'],$name)."\n\n";
        $message.= sprintf($lang['t77'],$settings['gbook_title'])."\n\n";
        $message.= "$lang[t78]\n";
        $message.= "$settings[gbook_url]\n\n";
        $message.= "$lang[t79]\n\n";
        $message.= "$settings[website_title]\n";
        $message.= "$settings[website_url]\n";

	    mail($email,$lang['t80'],$message,"From: $settings[website_title] <$settings[admin_email]>\nReply-to: $settings[admin_email]\nReturn-path: $settings[admin_email]\nContent-type: text/plain; charset=".$lang['enc']);
    }

	define('NOTICE', $lang['t12']);

} // END postReply


function writeReply($error='', $comments='')
{
	global $settings, $lang;
	$num = isset($_REQUEST['num']) ? intval($_REQUEST['num']) : false;
    if ($num === false)
    {
    	problem($lang['e02']);
    }

    $nosmileys = isset($_REQUEST['nosmileys']) ? 'checked="checked"' : '';

    printTopHTML();
    require($settings['tpl_path'].'admin_reply.php');
    printDownHTML();

} // END writeReply


function check_secnum($secnumber,$checksum)
{
	global $settings, $lang;
	$secnumber.=$settings['filter_sum'].date('dmy');
	if ($secnumber == $checksum)
	{
		unset($_SESSION['checked']);
		return true;
	}
	else
	{
		return false;
	}
} // END check_secnum


function filter_bad_words($text)
{
	global $settings, $lang;
	$file = 'badwords/'.$settings['filter_lang'].'.php';

	if (file_exists($file))
	{
		include_once($file);
	}
	else
	{
		problem($lang['e14']);
	}

	foreach ($settings['badwords'] as $k => $v)
	{
		$text = preg_replace("/\b$k\b/i",$v,$text);
	}

	return $text;
} // END filter_bad_words


function showPrivate()
{
	global $settings, $lang;

    $error_buffer = '';

	$num = isset($_POST['num']) ? intval($_POST['num']) : false;
    if ($num === false)
    {
    	problem($lang['e02']);
    }

    /* Check password */
    if (empty($_POST['pass']))
    {
    	$error_buffer .= $lang['e09'];
    }
    elseif ( gbook_input($_POST['pass']) != $settings['apass'] )
    {
    	$error_buffer .= $lang['e15'];
    }

    /* Any errors? */
    if ($error_buffer)
    {
    	confirmViewPrivate($error_buffer);
    }

	/* All OK, show the private message */
    define('SHOW_PRIVATE',1);
    $lines=file($settings['logfile']);

    printTopHTML();
    printEntries($lines,$num+1,$num+1);
    printDownHTML();

} // END showPrivate


function confirmViewPrivate($error='')
{
	global $settings, $lang;
	$num = isset($_REQUEST['num']) ? intval($_REQUEST['num']) : false;
    if ($num === false)
    {
    	problem($lang['e02']);
    }

    $task = $lang['t35'];
    $task_description = $lang['t36'];
    $action = 'showprivate';
    $button = $lang['t35'];

    printTopHTML();
    require($settings['tpl_path'].'admin_tasks.php');
    printDownHTML();

} // END confirmViewPrivate


function processsmileys($text)
{
	global $settings, $lang;

    /* File with emoticon settings */
	require($settings['tpl_path'].'emoticons.php');

	/* Replace some custom emoticon codes into GBook compatible versions */
	$text = preg_replace("/([\:\;])\-([\)op])/ie","str_replace(';p',':p','\\1'.strtolower('\\2'))",$text);
	$text = preg_replace("/([\:\;])\-d/ie","str_replace(';D',':D','\\1'.'D')",$text);

	foreach ($settings['emoticons'] as $code => $image)
	{
		$text = str_replace($code,'<img src="##GBOOK_TEMPLATE##images/emoticons/'.$image.'" border="0" alt="'.$code.'" title="'.$code.'" />',$text);
	}

	return $text;
} // END processsmileys


function doDelete()
{
	global $settings, $lang;

    $error_buffer = '';

	$num = isset($_POST['num']) ? intval($_POST['num']) : false;
    if ($num === false)
    {
    	problem($lang['e02']);
    }

    /* Check password */
    if (empty($_POST['pass']))
    {
    	$error_buffer .= $lang['e09'];
    }
    elseif ( gbook_input($_POST['pass']) != $settings['apass'] )
    {
    	$error_buffer .= $lang['e16'];
    }

    /* Any errors? */
    if ($error_buffer)
    {
    	confirmDelete($error_buffer);
    }

	/* All OK, delete the message */
	$lines=file($settings['logfile']);

    /* Ban poster's IP? */
	if (isset($_POST['addban']) && $_POST['addban']=='YES')
    {
	    gbook_banIP(trim(array_pop(explode("\t",$lines[$num]))));
	}

	unset($lines[$num]);

	$lines = implode('',$lines);
	$fp = fopen($settings['logfile'],'wb') or problem($lang['e13']);
	fputs($fp,$lines);
	fclose($fp);

	define('NOTICE', $lang['t37']);

} // END doDelete


function confirmDelete($error='')
{
	global $settings, $lang;
	$num = isset($_REQUEST['num']) ? intval($_REQUEST['num']) : false;
    if ($num === false)
    {
    	problem($lang['e02']);
    }

    $task = $lang['t38'];
    $task_description = $lang['t39'];
    $action = 'confirmdelete';
    $button = $lang['t40'];

    $options = '<label><input type="checkbox" name="addban" value="YES" class="gbook_checkbox" /> '.$lang['t23'].'</label>';

    printTopHTML();
    require($settings['tpl_path'].'admin_tasks.php');
    printDownHTML();

} // END confirmDelete


function check_mail_url()
{
	global $settings, $lang;
	$v = array('email' => '','url' => '');
	$char = array('.','@');
	$repl = array('&#46;','&#64;');

	$v['email']=htmlspecialchars($_POST['email']);
	if (strlen($v['email']) > 0 && !(preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$v['email'])))
    {
    	$v['email']='INVALID';
    }
	$v['email']=str_replace($char,$repl,$v['email']);

	if ($settings['use_url'])
	{
	    $v['url']=htmlspecialchars($_POST['url']);
	    if ($v['url'] == 'http://' || $v['url'] == 'https://') {$v['url'] = '';}
	    elseif (strlen($v['url']) > 0 && !(preg_match("/(http(s)?:\/\/+[\w\-]+\.[\w\-]+)/i",$v['url'])))
        {
        	$v['url'] = 'INVALID';
        }
	}
	elseif (!empty($_POST['url']))
	{
	    $_SESSION['block'] = 1;
	    problem($lang['e01'],0);
	}
	else
	{
	    $v['url'] = '';
	}

	return $v;
} // END check_mail_url


function addEntry()
{
	global $settings, $lang, $myfield;

    /* This part will help prevent multiple submissions */
    if ($settings['one_per_session'] && $_SESSION['add'])
    {
        problem($lang['e17'],0);
    }

    /* Check for obvious SPAM */
	if (!empty($_POST['name']) || isset($_POST['comments']) || !empty($_POST[$myfield['bait']]) || ($settings['use_url']!=1 && isset($_POST['url'])) )
	{
		gbook_banIP(gbook_IP(),1);
	}

	$name = gbook_input($_POST[$myfield['name']]);
	$from = gbook_input($_POST['from']);

    $a     = check_mail_url();
    $email = $a['email'];
    $url   = $a['url'];

    $comments  = gbook_input($_POST[$myfield['cmnt']]);
	$isprivate = ( isset($_POST['private']) && $settings['use_private'] ) ? 1 : 0;

    $sign_isprivate = $isprivate ? 'checked="checked"' : '';
    $sign_nosmileys = isset($_REQUEST['nosmileys']) ? 'checked="checked"' : 1;

    $error_buffer = '';

	if (empty($name))
	{
        $error_buffer .= $lang['e03'].'<br class="clear" />';
	}
	if ($email=='INVALID')
	{
        $error_buffer .= $lang['e04'].'<br class="clear" />';
        $email = '';
	}
	if ($url=='INVALID')
	{
        $error_buffer .= $lang['e05'].'<br class="clear" />';
        $url = '';
	}
	if (empty($comments))
	{
        $error_buffer .= $lang['e06'].'<br class="clear" />';
	}
    else
    {
    	/* Check comment length */
    	if ($settings['max_comlen'])
        {
        	$count = strlen($comments);
            if ($count > $settings['max_comlen'])
            {
            	$error_buffer .= sprintf($lang['t73'],$settings['max_comlen'],$count).'<br class="clear" />';
            }
        }

		/* Don't allow flooding with too much emoticons */
        if ($settings['smileys'] == 1 && !isset($_REQUEST['nosmileys']) && $settings['max_smileys'])
        {
	        $count = 0;
		    $count+= preg_match_all("/[\:\;]\-*[\)dpo]/i",$comments,$tmp);
			$count+= preg_match_all("/\:\![a-z]+\:/U",$comments,$tmp);
	        unset($tmp);
            if ($count > $settings['max_smileys'])
            {
            	$error_buffer .= sprintf($lang['t74'],$settings['max_smileys'],$count).'<br class="clear" />';
            }
        }
    }

    /* Use a logical anti-SPAM question? */
    $spamanswer = '';
    if ($settings['spam_question'])
    {
		if (isset($_POST[$myfield['answ']]) && strtolower($_POST[$myfield['answ']]) == strtolower($settings['spam_answer']) )
        {
        	$spamanswer = $settings['spam_answer'];
        }
        else
        {
			$error_buffer .= $lang['t67'].'<br class="clear" />';
        }
    }

	/* Use security image to prevent automated SPAM submissions? */
	if ($settings['autosubmit'])
	{
		$mysecnum = isset($_POST['mysecnum']) ? intval($_POST['mysecnum']) : 0;
		if (empty($mysecnum))
		{
            $error_buffer .= $lang['e07'].'<br class="clear" />';
		}
        else
        {
			require('secimg.inc.php');
			$sc=new PJ_SecurityImage($settings['filter_sum']);
			if (!($sc->checkCode($mysecnum,$_SESSION['checksum'])))
	        {
	            $error_buffer .= $lang['e08'].'<br class="clear" />';
			}
        }
	}

    /* Any errors? */
    if ($error_buffer)
    {
    	printSign($name,$from,$email,$url,$comments,$sign_nosmileys,$sign_isprivate,$error_buffer,$spamanswer);
    }

	/* Check the message with JunkMark(tm)? */
	if ($settings['junkmark_use'])
	{
		$junk_mark = JunkMark($name,$from,$email,$url,$comments);

		if ($settings['junkmark_ban100'] && $junk_mark == 100)
        {
			gbook_banIP(gbook_IP(),1);
		}
        elseif ($junk_mark >= $settings['junkmark_limit'])
		{
			$_SESSION['block'] = 1;
			problem($lang['e01'],0);
		}
	}

    /* Everthing seems fine, let's add the message */
	$delimiter="\t";
	$m = date('m');
	if (isset($lang['m'.$m]))
	{
		$added = $lang['m'.$m] . date(" j, Y");
	}
	else
	{
		$added = date("F j, Y");
	}

    /* Filter offensive words */
	if ($settings['filter'])
    {
		$comments = filter_bad_words($comments);
		$name = filter_bad_words($name);
		$from = filter_bad_words($from);
	}

    /* Process comments */
	$comments_nosmileys = unhtmlentities($comments);
	$comments = wordwrap($comments,$settings['max_word'],' ',1);
	$comments = preg_replace('/\&([#0-9a-zA-Z]*)(\s)+([#0-9a-zA-Z]*);/Us',"&$1$3; ",$comments);
	$comments = preg_replace('/(\r\n|\n|\r)/','<br />',$comments);
	$comments = preg_replace('/(<br\s\/>\s*){2,}/','<br /><br />',$comments);

    /* Process emoticons */
    if ($settings['smileys'] == 1 && !isset($_REQUEST['nosmileys']))
    {
    	$comments = processsmileys($comments);
    }

    /* Create the new entry and add it to the entries file */
	$addline = $name.$delimiter.$from.$delimiter.$email.$delimiter.$url.$delimiter.$comments.$delimiter.$added.$delimiter.$isprivate.$delimiter.'0'.$delimiter.$_SERVER['REMOTE_ADDR']."\n";

    /* Prepare for e-mail... */
    $name = unhtmlentities($name);
    $from = unhtmlentities($from);

    /* Manually approve entries? */
    if ($settings['man_approval'])
    {
		$tmp = md5($_SERVER['REMOTE_ADDR'].$settings['filter_sum']);
		$tmp_file = 'apptmp/'.$tmp.'.txt';

		if (file_exists($tmp_file))
		{
			problem($lang['t81']);
		}

		$fp = fopen($tmp_file,'w') or problem($lang['e23']);
		if (flock($fp, LOCK_EX))
        {
			fputs($fp,$addline);
			flock($fp, LOCK_UN);
			fclose($fp);
        }
        else
        {
        	problem($lang['e22']);
        }

		$char = array('.','@');
		$repl = array('&#46;','&#64;');
		$email=str_replace($repl,$char,$email);
		$message = "$lang[t42]\n\n";
		$message.= "$lang[t82]\n\n";
		$message.= "$lang[t17] $name\n";
		$message.= "$lang[t18] $from\n";
		$message.= "$lang[t20] $email\n";
		$message.= "$lang[t19] $url\n";
		$message.= "$lang[t44]\n";
		$message.= "$comments_nosmileys\n\n";
		$message.= "$lang[t83]\n";
		$message.= "$settings[gbook_url]?id=$tmp&a=approve&do=1\n\n";
		$message.= "$lang[t84]\n";
		$message.= "$settings[gbook_url]?id=$tmp&a=approve&do=0\n\n";
		$message.= "$lang[t46]\n";

		mail($settings['admin_email'],$lang['t41'],$message,"Content-type: text/plain; charset=".$lang['enc']);

		/* Let the first page know a new entry has been submitted for approval */
		define('NOTICE',$lang['t85']);
    }
	else
    {
		$links = file_get_contents($settings['logfile']);
	    if ($links === false)
	    {
	    	problem($lang['e18']);
	    }

		$addline .= $links;

	    $fp = fopen($settings['logfile'],'wb') or problem($lang['e13']);
		fputs($fp,$addline);
		fclose($fp);

	    if ($settings['notify'] == 1)
		{
		    $char = array('.','@');
		    $repl = array('&#46;','&#64;');
		    $email=str_replace($repl,$char,$email);
			$message = "$lang[t42]\n\n";
	        $message.= "$lang[t43]\n\n";
	        $message.= "$lang[t17] $name\n";
	        $message.= "$lang[t18] $from\n";
	        $message.= "$lang[t20] $email\n";
	        $message.= "$lang[t19] $url\n";
	        $message.= "$lang[t44]\n";
	        $message.= "$comments_nosmileys\n\n";
	        $message.= "$lang[t45]\n";
	        $message.= "$settings[gbook_url]\n\n";
	        $message.= "$lang[t46]\n";

		    mail($settings['admin_email'],$lang['t41'],$message,"Content-type: text/plain; charset=".$lang['enc']);
		}


		/* Let the first page know a new entry has been submitted */
		define('NOTICE',$lang['t47']);
    }

	/* Register this session variable */
	$_SESSION['add']=1;

    /* Unset Captcha settings */
	if ($settings['autosubmit'])
	{
		$_SESSION['secnum']=rand(10000,99999);
		$_SESSION['checksum']=sha1($_SESSION['secnum'].$settings['filter_sum']);
		gbook_session_regenerate_id();
    }

} // END addEntry


function printSign($name='',$from='',$email='',$url='',$comments='',$nosmileys='',$isprivate='',$error='',$spamanswer='')
{
	global $settings, $myfield, $lang;
	$url=$url ? $url : 'http://';

    /* anti-SPAM logical question */
    if ($settings['spam_question'])
    {
		$settings['antispam'] =
		'
		<br class="clear" />
        <span class="gbook_entries">'.$settings['spam_question'].'</span><br class="clear" />
		<input type="text" name="'.$myfield['answ'].'" size="45" value="'.$spamanswer.'" />
		';
    }
    else
    {
		$settings['antispam'] = '';
    }

    /* Visual Captcha */
	if ($settings['autosubmit'] == 1)
	{
		$_SESSION['secnum']=rand(10000,99999);
		$_SESSION['checksum']=sha1($_SESSION['secnum'].$settings['filter_sum']);
		gbook_session_regenerate_id();

	    $settings['antispam'] .=
        '
		<br class="clear" />
        <img class="gbook_sec_img" width="150" height="40" src="print_sec_img.php" alt="'.$lang['t62'].'" title="'.$lang['t62'].'" /><br class="clear" />
		<span class="gbook_entries">'.$lang['t56'].'</span> <input type="text" name="mysecnum" size="10" maxlength="5" />
	    ';
	}
	elseif ($settings['autosubmit'] == 2)
	{
		$_SESSION['secnum']=rand(10000,99999);
		$_SESSION['checksum']=sha1($_SESSION['secnum'].$settings['filter_sum']);
		gbook_session_regenerate_id();

	    $settings['antispam'] .=
        '
		<br class="clear" />
        <br class="clear" />
        <span class="gbook_entries"><b>'.$_SESSION['secnum'].'</b></span><br class="clear" />
		<span class="gbook_entries">'.$lang['t56'].'</span> <input type="text" name="mysecnum" size="10" maxlength="5" />
	    ';
	}

    printTopHTML();
    require($settings['tpl_path'].'sign_form.php');
    printDownHTML();

} // END printSign


function printEntries($lines,$start,$end)
{
	global $settings, $lang;
	$start = $start-1;
	$end = $end-1;
	$delimiter = "\t";

    $template = file_get_contents($settings['tpl_path'].'comments.php');

	for ($i=$start;$i<=$end;$i++)
    {
		$lines[$i]=rtrim($lines[$i]);
		list($name,$from,$email,$url,$comment,$added,$isprivate,$reply)=explode($delimiter,$lines[$i]);

		if (!empty($isprivate) && !empty($settings['use_private']) && !defined('SHOW_PRIVATE'))
		{
			$comment = '
			<br class="clear" />
			<i><a href="gbook.php?a=viewprivate&amp;num='.$i.'">'.$lang['t58'].'</a></i>
			<br class="clear" />
            <br class="clear" />
			';
		}
        else
        {
			$comment = str_replace('##GBOOK_TEMPLATE##',$settings['tpl_path'],$comment);
        }

		if (!empty($reply))
		{
			$comment .= '<br class="clear" /><br class="clear" /><i><b>'.$lang['t30'].'</b> '.str_replace('##GBOOK_TEMPLATE##',$settings['tpl_path'],$reply).'</i>';
		}

		if ($email)
		{
			if ($settings['hide_emails'])
			{
				$email = '<a href="gbook.php?a=viewEmail&amp;num='.$i.'" class="gbook_submitted">'.$lang['t27'].'</a>';
			}
			else
			{
				$email = '<a href="mailto&#58;'.$email.'" class="gbook_submitted">'.$email.'</a>';
			}
		}

		if ($settings['use_url'] && $url)
		{
			$url = '<a href="'.$url.'" class="gbook_submitted" '.$settings['target'].' rel="nofollow">'.$url.'</a>';
		}
		else
		{
			$url = '';
		}

		eval(' ?>'.$template.'<?php ');
	} // END For

} // END printEntries


function problem($myproblem,$backlink=1)
{
	global $settings, $lang;

    $backlink = $backlink ? '<div style="text-align:center"><a href="Javascript:history.go(-1)">'.$lang['t59'].'</a></div>' : '';

	printTopHTML();
    require($settings['tpl_path'].'error.php');
	printDownHTML();
} // END problem


function printNoCache()
{
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
} // END printNoCache


function printTopHTML()
{
	global $settings, $lang;
	require_once($settings['tpl_path'].'overall_header.php');
} // END printTopHTML


function printDownHTML()
{
	global $settings, $lang;
    require_once($settings['tpl_path'].'overall_footer.php');
}  // END printDownHTML

function gbook_input($in,$error=0)
{
    $in = trim($in);
    if (strlen($in))
    {
        $in = htmlspecialchars($in);
        $in = preg_replace('/&amp;(\#[0-9]+;)/','&$1',$in);
    }
    elseif ($error)
    {
        problem($error);
    }
    return stripslashes($in);
} // END gbook_input()

function gbook_isNumber($in,$error=0)
{
    $in = trim($in);
    if (preg_match("/\D/",$in) || $in=="")
    {
        if ($error)
        {
                problem($error);
        }
        else
        {
                return '0';
        }
    }
    return $in;
} // END gbook_isNumber()


function JunkMark($name,$from,$email,$url,$comments)
{
	/*
	JunkMark(TM) SPAM filter
	v1.4.2 from 17th Dec 2009
	(c) Copyright 2006-2009 Klemen Stirn. All rights reserved.

	The function returns a number between 0 and 100. Larger numbers mean
	more probability that the message is SPAM. Recommended limit is 60
	(block message if score is 60 or more)

	THIS CODE MAY ONLY BE USED IN THE "GBOOK" SCRIPT FROM PHPJUNKYARD.COM
	AND DERIVATIVE WORKS OF THE GBOOK SCRIPT.

	THIS CODE MUSTN'T BE USED IN ANY OTHER SCRIPT AND/OR REDISTRIBUTED
	IN ANY MEDIUM WITHOUT THE EXPRESS WRITTEN PERMISSION FROM KLEMEN STIRN!
	*/

	global $settings;

return
eval(gzinflate(base64_decode('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
obqEoF7bK6UBAErwP877///vt//w8=')));
} // END JunkMark()

function gbook_IP()
{
	global $settings, $lang;
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!preg_match('/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/',$ip))
    {
        if ($settings['allow_IPv6'] && preg_match('/^[0-9A-Fa-f\:]+$/',$ip))
        {
            return $ip;
        }
        die($lang['e20']);
    }
    return $ip;
} // END gbook_IP()


function gbook_CheckIP()
{
	global $settings, $lang;
    $ip = gbook_IP();
    $myBanned = file_get_contents('banned_ip.txt');
    if (strpos($myBanned,$ip) !== false)
    {
        die($lang['e21']);
    }
    return true;
} // END gbook_CheckIP()


function gbook_banIP($ip,$doDie=0)
{
	global $settings, $lang;
    $fp=fopen('banned_ip.txt','a');
    fputs($fp,$ip.'%');
    fclose($fp);
    if ($doDie)
    {
        die($lang['e21']);
    }
    return true;
} // END gbook_banIP()


function gbook_session_regenerate_id()
{
    if (version_compare(phpversion(),'4.3.3','>='))
    {
		session_regenerate_id();
    }
    else
    {
        $randlen = 32;
        $randval = '0123456789abcdefghijklmnopqrstuvwxyz';
        $random = '';
        $randval_len = 35;
        for ($i = 1; $i <= $randlen; $i++)
        {
            $random .= substr($randval, rand(0,$randval_len), 1);
        }

        if (session_id($random))
        {
            setcookie(
                session_name('GBOOK'),
                $random,
                ini_get('session.cookie_lifetime'),
                '/'
            );
            return true;
        }
        else
        {
            return false;
        }
    }
} // END gbook_session_regenerate_id()


function unhtmlentities($in)
{
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	return strtr($in,$trans_tbl);
} // END unhtmlentities()

?>
