<?php
/**
 *   https://github.com/Bigjoos/
 *   Licence Info: GPL
 *   Copyright (C) 2010 U-232 v.3
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless, putyn.
 **/
//=== watched users list for staff to keep track of bad or suspected members personally... 
if ( ! defined( 'IN_INSTALLER09_ADMIN' ) )
{
header('HTTP/1.0 404 Not Found');
$HTMLOUT = '';
$h1_thingie = '';
$HTMLOUT .= '
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL '.htmlsafechars($_SERVER['SCRIPT_NAME'],strrpos($_SERVER['SCRIPT_NAME'],'/')+1).' was not found on this server.</p>
<hr>
<address>'.$_SERVER['SERVER_SOFTWARE'].' Server at '.$INSTALLER09['baseurl'].' Port 80</address>
</body></html>';
echo $HTMLOUT;
exit();
}

require_once INCL_DIR.'user_functions.php';
require_once INCL_DIR.'pager_new.php';
require_once INCL_DIR.'html_functions.php';
require_once INCL_DIR.'bbcode_functions.php';
require_once(CLASS_DIR.'class_check.php');
class_check(UC_STAFF);

$lang = array_merge( $lang );

$HTMLOUT = $H1_thingie = $count2 = '';

//=== to delete members from the watched user list... admin and up only!
if (isset($_GET['remove']))
	{
	if ($CURUSER['class'] < UC_STAFF)
	stderr('Error', 'Thou infectious onion-eyed haggard! Only Admin and up can remove members from the list!');

	$remove_me_Ive_been_good = (isset($_POST['wu']) ? $_POST['wu'] : $_GET['wu']);
	$removed_log = '';

		//=== if single delete use
		if (isset($_GET['wu']))
		{
			if (is_valid_id($remove_me_Ive_been_good)) 
			{
			//=== get mod comments for member
			$res = sql_query('SELECT username, modcomment FROM users WHERE id='.sqlesc($remove_me_Ive_been_good)) or sqlerr(__FILE__, __LINE__);
			$user = mysqli_fetch_assoc($res);
			$modcomment = get_date( TIME_NOW, 'DATE', 1 ) . " - Removed from watched users by $CURUSER[username].\n". $user['modcomment'];
			sql_query('UPDATE users SET watched_user = \'0\', modcomment='.sqlesc($modcomment).' WHERE id='.sqlesc($remove_me_Ive_been_good)) or sqlerr(__FILE__,__LINE__);
			$mc1->begin_transaction('MyUser_'.$remove_me_Ive_been_good);
         $mc1->update_row(false, array('watched_user' => 0));
         $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
         $mc1->begin_transaction('user'.$remove_me_Ive_been_good);
         $mc1->update_row(false, array('watched_user' => 0));
         $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
         $mc1->begin_transaction('user_stats_'.$remove_me_Ive_been_good);
         $mc1->update_row(false, array('modcomment' => $modcomment));
         $mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
         $count = 1;
			$removed_log = '<a href="userdetails.php?id='.$remove_me_Ive_been_good.'" class="altlink">'.htmlsafechars($user['username']).'</a>';
			}
		}
		else
		{
		
			foreach ($remove_me_Ive_been_good as $id)
			{
			if (is_valid_id($id)) 
				{
				//=== get mod comments for member
				$res = sql_query('SELECT username, modcomment FROM users WHERE id='.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
				$user = mysqli_fetch_assoc($res);
				$modcomment = get_date( TIME_NOW, 'DATE', 1 ) . " - Removed from watched users by $CURUSER[username].\n". $user['modcomment'];
				sql_query('UPDATE users SET watched_user = \'0\', modcomment='.sqlesc($modcomment).' WHERE id='.sqlesc($id)) or sqlerr(__FILE__,__LINE__);
				$mc1->begin_transaction('MyUser_'.$id);
            $mc1->update_row(false, array('watched_user' => 0));
            $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
            $mc1->begin_transaction('user'.$id);
            $mc1->update_row(false, array('watched_user' => 0));
            $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
            $mc1->begin_transaction('user_stats_'.$id);
            $mc1->update_row(false, array('modcomment' => $modcomment));
            $mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
            $count = (++$count);
				$removed_log .= '<a href="userdetails.php?id='.$id.'" class="altlink">'.htmlsafechars($user['username']).'</a> ';
				}
			}
		}
	//=== Check if members were removed
	if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) == 0)
   stderr('Error','No one was deleted!');
	else
   write_log('<b>'.$CURUSER['username'].'</b> Removed:<br />'.$removed_log.' <br />from watched users');
	$H1_thingie = '<h1>'.$count.' Member'.($count == 1 ? '' : 's').' removed from list.</h1>';
}

//=== to add members to the watched user list... all staff!
if (isset($_GET['add']))
	{
	$member_whos_been_bad = (int)$_GET['id'];
		if (is_valid_id($member_whos_been_bad)) 
			{
			
			//=== make sure they are not being watched...
			$res = sql_query('SELECT modcomment, watched_user, watched_user_reason, username FROM users WHERE id='.sqlesc($member_whos_been_bad)) or sqlerr(__FILE__, __LINE__);
			$user = mysqli_fetch_assoc($res);
		  if ($user['watched_user'] > 0)
			stderr('Error', htmlsafechars($user['username']).' is on the watched user list already! <a href="userdetails.php?id='.$member_whos_been_bad.'" >back to '.htmlsafechars($user['username']).'\'s profile</a>');
					
  //== ok they are not watched yet let's add the info part 1
  if ($_GET['add'] && $_GET['add'] == 1)
  {
 $naughty_box = '
 <form method="post" action="staffpanel.php?tool=watched_users&amp;action=watched_users&amp;add=2&amp;id='.$member_whos_been_bad.'">
 <table width="600">
 <tr>
 <td class="colhead">Add '.$user['username'].'To Watched Users</td>
 </tr>
 <tr>
 <td align="center"><b>please fill in the reason for adding '.htmlsafechars($user['username']).' to the watched user list.</b><br />
 <textarea cols="60" rows="6" name="reason">'.htmlsafechars($user['watched_user_reason']).'</textarea><br /></td>
 </tr>
 <tr>
 <td class="colhead">
 <input type="submit" class="button_big" value="add to watched users!"" onmouseover="this.className=\'button_big_hover\'" onmouseout="this.className=\'button_big\'" /><form><td>
    </tr>
    </table>';
    
    stderr('watched Users', $naughty_box);
    }
			
			
			//=== all is good, let's enter them \o/
			$watched_user_reason = htmlsafechars($_POST['reason']);
			$modcomment = get_date( TIME_NOW, 'DATE', 1 ) . " - Added to watched users by $CURUSER[username].\n". $user['modcomment'];
			sql_query('UPDATE users SET watched_user = '.TIME_NOW.', modcomment='.sqlesc($modcomment).', watched_user_reason = '.sqlesc($watched_user_reason).' WHERE id='.sqlesc($member_whos_been_bad)) or sqlerr(__FILE__,__LINE__);
         $mc1->begin_transaction('MyUser_'.$member_whos_been_bad);
         $mc1->update_row(false, array('watched_user' => TIME_NOW));
         $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
         $mc1->begin_transaction('user'.$member_whos_been_bad);
         $mc1->update_row(false, array('watched_user' => TIME_NOW, 'watched_user_reason' => $watched_user_reason));
         $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
         $mc1->begin_transaction('user_stats_'.$member_whos_been_bad);
         $mc1->update_row(false, array('modcomment' => $modcomment));
         $mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
			}
			//=== Check if member was added
			if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) > 0)
			{
				$H1_thingie = '<h1>Sucess!'. htmlsafechars($user['username']).' added!</h1>';
				write_log('<b>'.$CURUSER['username'].'</b> added <a href="userdetails.php?id='.$member_whos_been_bad.'" class="altlink">'.htmlsafechars($user['username']).'</a> to the <a href="staffpanel.php?tool=watched_users&amp;action=watched_users" class="altlink">watched users list</a>.');
			}
	}

//=== get number of watched members
$watched_users = number_format(get_row_count('users', 'WHERE watched_user != \'0\''));
//=== get sort / asc desc, and be sure it's safe
$good_stuff = array('username','watched_user','invited_by');
$ORDER_BY = ((isset($_GET['sort']) && in_array($_GET['sort'],$good_stuff,true)) ? $_GET['sort'].' ' : 'watched_user ');
$ASC = (isset($_GET['ASC']) ? ($_GET['ASC'] == 'ASC' ? 'DESC' : 'ASC') : 'DESC');
$i = 1;

$HTMLOUT .= $H1_thingie.'<br />
		<form action="staffpanel.php?tool=watched_users&amp;action=watched_users&amp;remove=1" method="post"  name="checkme" onsubmit="return ValidateForm(this,\'wu\')">
        <h1>Watched Users [ '.$watched_users.' ]</h1>
    <table border="0" cellspacing="5" cellpadding="5" align="center" style="max-width:800px">';
//=== get the member info...
$res = sql_query('SELECT id, username, added, watched_user_reason, watched_user, uploaded, downloaded, warned, suspended, enabled, donor, class, leechwarn, chatpost, pirate, king, invitedby FROM users WHERE watched_user != \'0\' ORDER BY '.$ORDER_BY.$ASC) or sqlerr(__FILE__, __LINE__);
$how_many = mysqli_num_rows($res);
if($how_many > 0)
{
$div_link_number = 1;	

$HTMLOUT .= '
    <tr>
        <td class="colhead"><a href="staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=watched_user&amp;ASC='.$ASC.'">Added</a></td>  
        <td class="colhead"><a href="staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=username&amp;ASC='.$ASC.'">Username</a></td>
        <td class="colhead" align="left" width="400">Suspicion</td>
        <td class="colhead" align="center">Stats</td>
        <td class="colhead" align="center"><a href="staffpanel.php?tool=watched_users&amp;action=watched_users&amp;sort=invited_by&amp;ASC='.$ASC.'">Invited By</a></td>
        '.($CURUSER['class'] >= UC_STAFF ? '<td class="colhead" align="center">&nbsp;</td>' : '').'
    </tr>';
		
	while ($arr = @mysqli_fetch_assoc($res)) 
	{
   //=== change colors
   $count2= (++$count2)%2;
   $class = ($count2 == 0 ? 'one' : 'two');
	$invitor_res = sql_query('SELECT id, username, donor, class, enabled, warned, leechwarn, chatpost, pirate, king, suspended FROM users WHERE id='.sqlesc($arr['invitedby'])) or sqlerr(__FILE__, __LINE__);
	$invitor_arr = mysqli_fetch_assoc($invitor_res);
	$the_flip_box = '
        [ <a id="d'.$div_link_number.'_open" style="font-weight:bold;cursor:pointer;">View reason</a> ]
        <div align="left" id="d'.$div_link_number.'" style="display:none">'.format_comment($arr['watched_user_reason']).'</div>';
$HTMLOUT .= '
    <tr>
        <td align="center" class="'.$class.'">'.get_date( $arr['watched_user'],'').'</td>
        <td align="left" class="'.$class.'">'.print_user_stuff($arr).'</td>
        <td align="left" class="'.$class.'">'.$the_flip_box.'</td>
        <td align="center" class="'.$class.'">'.member_ratio($arr['uploaded'], $INSTALLER09['ratio_free'] ? '0' : $arr['downloaded']).'</td>
        <td align="center" class="'.$class.'">'.($invitor_arr['username'] == '' ? 'open sign-ups' : print_user_stuff($invitor_arr)).'</td>
        '.($CURUSER['class'] >= UC_STAFF ? '
        <td align="center" class="'.$class.'"><input type="checkbox" name="wu[]" value="'.(int)$arr['id'].'" /></td>' : '').'
    </tr>';
	$div_link_number++;
	}
	$div_link_number = 1;	
}
else
$HTMLOUT .= '<tr>
<td align="center" class="one"><h1>The watched members list is empty!</h1></td></tr>';
$HTMLOUT .= '
<tr>
<td align="center" colspan="6" class="colhead"><a class="altlink" href="javascript:SetChecked(1,\'wu[]\')"> select all</a> - <a class="altlink" href="javascript:SetChecked(0,\'wu[]\')">un-select all</a>
        <input type="submit" class="button_big" value="remove selected from watched users" onmouseover="this.className=\'button_big_hover\'" onmouseout="this.className=\'button_big\'" /></td></tr></table></form>
<script type="text/javascript" src="scripts/check_selected.js"></script>';
$HTMLOUT .='
<script type="text/javascript">
 /*<![CDATA[*/
$(document).ready(function()	{';
while ($div_link_number <= $how_many)
{
$HTMLOUT .= '$("#d'.$div_link_number.'_open").click(function() {
  $("#d'.$div_link_number.'").slideToggle("slow", function() {
  });
});';
$div_link_number++;
}
$HTMLOUT .= '});
/*]]>*/
</script>';
echo stdhead('Watched Users') . $HTMLOUT . stdfoot();
?>
