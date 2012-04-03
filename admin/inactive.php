<?php
/**
 *   https://github.com/Bigjoos/
 *   Licence Info: GPL
 *   Copyright (C) 2010 U-232 v.3
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless, putyn.
 **/
if ( ! defined( 'IN_INSTALLER09_ADMIN' ) )
{
	$HTMLOUT='';
	$HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
	echo $HTMLOUT;
	exit();
}

require_once(INCL_DIR.'pager_functions.php');
require_once(INCL_DIR.'user_functions.php');
require_once(CLASS_DIR.'class_check.php');
class_check(UC_STAFF);

$HTMLOUT ="";
$lang = array_merge($lang, load_language('inactive') );
// made by putyn tbdev.net
// email part by x0r tbdev.net
// config
$replyto = "{$INSTALLER09['site_email']}"; // The Reply-to email.
$record_mail = true; // set this true or false . If you set this true every time whene you send a mail the time , userid , and the number of mail sent will be recorded
$days = 50; //number of days of inactivity
// end config
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST["action"]) ? htmlsafechars(trim($_POST["action"])) : '';
    if (empty($_POST["userid"]) && (($action == "deluser") || ($action == "mail")))
        stderr($lang['inactive_error'], "{$lang['inactive_selectuser']}");

    if ($action == "deluser" && (!empty($_POST["userid"]))) {
        sql_query("DELETE FROM users WHERE id IN (" . implode(", ", $_POST['userid']) . ") ");
        stderr($lang['inactive_success'], "{$lang['inactive_deleted']} <a href='".$INSTALLER09['baseurl']."/staffpanel.php?tool=inactive>{$lang['inactive_back']}</a>");
    }
    if ($action == "disable" && (!empty($_POST["userid"]))) {
        sql_query("UPDATE users SET enabled='no' WHERE id IN (" . implode(", ", $_POST['userid']) . ") ");
        stderr($lang['inactive_success'], "{$lang['inactive_disabled']} <a href='".$INSTALLER09['baseurl']."/staffpanel.php?tool=inactive>{$lang['inactive_back']}</a>");
    }

    if ($action == "mail" && (!empty($_POST["userid"]))) {
        $res = sql_query("SELECT id, email, modcomment, username, added, last_access FROM users WHERE id IN (" . implode(", ", $_POST['userid']) . ") ORDER BY last_access DESC ");
        $count = mysqli_num_rows($res);
        while ($arr = mysqli_fetch_array($res)) {
            $id = (int)$arr["id"];
            $username = htmlsafechars($arr["username"]);
            $email = htmlsafechars($arr["email"]);
            $added = get_date($arr["added"], 'DATE');
            $last_access = get_date($arr["last_access"], 'DATE');

             
            $subject = "{$lang['inactive_youracc']}Your account at {$INSTALLER09['site_name']} !";
            $message = "{$lang['inactive_hey']}
            {$lang['inactive_youracc']} {$INSTALLER09['site_name']} {$lang['inactive_marked']} {$INSTALLER09['site_name']}{$lang['inactive_plogin']}\n
            {$lang['inactive_yourusername']} $username\n
            {$lang['inactive_created']} $added\n
            {$lang['inactive_lastaccess']} $last_access\n
            {$lang['inactive_loginat']} {$INSTALLER09['baseurl']}/login.php\n
            {$lang['inactive_forgotten']} {$INSTALLER09['baseurl']}/resetpw.php\n
            {$lang['inactive_welcomeback']} {$INSTALLER09['site_name']}";
            $headers = 'From: ' . $INSTALLER09['site_email'] . "\r\n" . 'Reply-To:' . $replyto . "\r\n" . 'X-Mailer: PHP/' . phpversion();
            $mail = @mail($email, $subject, $message, $headers);
            }

        if ($record_mail) {
            $date = TIME_NOW;
            $userid = (int)$CURUSER["id"];
            if ($count > 0 && $mail)
                sql_query("UPDATE avps SET value_i='$date', value_u='$count', value_s='$userid' WHERE arg='inactivemail'") or sqlerr(__FILE__, __LINE__);
                }

        if ($mail)
            stderr($lang['inactive_success'], "{$lang['inactive_msgsent']} <a href='".$INSTALLER09['baseurl']."/staffpanel.php?tool=inactive'>{$lang['inactive_back']}</a>");
      
        else
            stderr($lang['inactive_error'], "{$lang['inactive_tryagain']}");
    }
}

   $dt = TIME_NOW - ($days * 86400);
   $res = sql_query("SELECT COUNT(id) FROM users WHERE last_access<$dt AND status='confirmed' AND enabled='yes' ORDER BY last_access DESC");
   $row = mysqli_fetch_array($res);
   $count = $row[0];
   $perpage = 15;
   $pager = pager($perpage, $count, "staffpanel.php?tool=inactive&amp;");
   $res = sql_query("SELECT id,username,class,email,uploaded,downloaded,last_access FROM users WHERE last_access<$dt AND status='confirmed' AND enabled='yes' ORDER BY last_access DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
   $count_inactive = mysqli_num_rows($res);
   if ($count_inactive > 0) {
   //if ($count > $perpage)
    $HTMLOUT .= $pager['pagertop'];
    $HTMLOUT .="<script type='text/javascript'>
    /*<![CDATA[*/
    var checkflag = 'false';
    function check(field) {
    if (checkflag == 'false') {
    for (i = 0; i < field.length; i++) {
    field[i].checked = true;}
    checkflag = 'true';
    return 'Uncheck All'; }
    else {
    for (i = 0; i < field.length; i++) {
    field[i].checked = false; }
    checkflag = 'false';
    return 'Check All'; }
    }
    /*]]>*/
    </script>";
   
    $HTMLOUT .="<h2>" . htmlsafechars($count) . "{$lang['inactive_accounts']} " . htmlsafechars($days) . " {$lang['inactive_days']}</h2>
    <form method='post' action='staffpanel.php?tool=inactive&amp;action=inactive'>
    <table class='main' border='1' cellspacing='0' cellpadding='5'>
    <tr>
    <td class='colhead'>{$lang['inactive_username']}</td>
    <td class='colhead'>{$lang['inactive_class']}</td>
    <td class='colhead'>{$lang['inactive_mail']}</td>
    <td class='colhead'>{$lang['inactive_ratio']}</td>
    <td class='colhead'>{$lang['inactive_lastseen']}</td>
    <td class='colhead' align='center'>{$lang['inactive_x']}</td></tr>";

    while ($arr = mysqli_fetch_assoc($res)) {
        $ratio = ($arr["downloaded"] > 0 ? number_format($arr["uploaded"] / $arr["downloaded"], 3) : ($arr["uploaded"] > 0 ? "Inf." : "---"));
        $last_seen = (($arr["last_access"] == "0") ? "never" : "" . get_date($arr["last_access"], 'DATE') . "&nbsp;");
        $class = get_user_class_name($arr["class"]);
        $HTMLOUT .="<tr>
        <td><a href='{$INSTALLER09['baseurl']}/userdetails.php?id=" . (int)$arr["id"] . "'>" .htmlsafechars($arr["username"]) . "</a></td>
        <td>" . $class . "</td>
        <td><a href='mailto:".htmlsafechars($arr["email"])."'>".htmlsafechars($arr["email"])."</a></td>
        <td>" . $ratio . "</td>
        <td>" . $last_seen . "</td>
        <td align='center' bgcolor='#FF0000'><input type='checkbox' name='userid[]' value='" . (int)$arr["id"] . "' /></td></tr>
        ";
    }
    $HTMLOUT .="<tr>
    <td colspan='6' class='colhead' align='center'>
    <select name='action'>
    <option value='mail'>{$lang['inactive_sendmail']}</option>
    <option value='deluser' " . ($CURUSER["class"] < UC_ADMINISTRATOR ? "disabled" : "") . ">{$lang['inactive_deleteusers']}</option>
    <option value='disable'>{$lang['inactive_disaccounts']}</option>
    </select>&nbsp;&nbsp;<input type='submit' name='submit' value='{$lang['inactive_apchanges']}' />&nbsp;&nbsp;<input type='button' value='Check all' onclick='this.value=check(form)' /></td></tr>";

    if ($record_mail) {
        $ress = sql_query("SELECT avps.value_s AS userid, avps.value_i AS last_mail, avps.value_u AS mails, users.username FROM avps LEFT JOIN users ON avps.value_s=users.id WHERE avps.arg='inactivemail' LIMIT 1");
        $date = mysqli_fetch_assoc($ress);
        if ($date["last_mail"] > 0)
            $HTMLOUT .="<tr><td colspan='6' class='colhead' align='center' style='color:red;'>{$lang['inactive_lastmail']} <a href='{$INSTALLER09['baseurl']}/userdetails.php?id=" . htmlsafechars($date["userid"]) . "'>" . htmlsafechars($date["username"]) . "</a> {$lang['inactive_on']} <b>" . get_date($date["last_mail"], 'DATE') . " -  " . $date["mails"] . "</b>{$lang['inactive_email']} " . ($date["mails"] > 1 ? "s" : "") . "  {$lang['inactive_sent']}</td></tr>";
    }
    $HTMLOUT .="</table></form>";
} else {
    $HTMLOUT .="<h2>{$lang['inactive_noaccounts']} " . $days . " {$lang['inactive_days']}</h2>";
}
//if ($count > $perpage)
$HTMLOUT .= $pager['pagerbottom'];
echo stdhead($lang['inactive_users']) . $HTMLOUT . stdfoot();
?>
