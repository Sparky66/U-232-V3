<?php
//==Uploaded/downloaded
    if  ($user['paranoia'] < 2 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF){
    $days = round((TIME_NOW - $user['added'])/86400);
    if ($INSTALLER09['ratio_free']) 
    {
    $HTMLOUT .="<tr><td class='rowhead'>Happy days</td><td align='left'>Ratio free tracker in effect</td></tr>
    <tr><td class='rowhead'>{$lang['userdetails_uploaded']}</td><td align='left'>".mksize($user_stats['uploaded'])." {$lang['userdetails_daily']}".($days > 1 ? mksize($user_stats['uploaded']/$days) : mksize($user_stats['uploaded']))."</td></tr>\n";
    } else {
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_downloaded']}</td><td align='left'>".mksize($user_stats['downloaded'])." {$lang['userdetails_daily']}".($days > 1 ? mksize($user_stats['downloaded']/$days) : mksize($user_stats['downloaded']))."</td></tr>
    <tr><td class='rowhead'>{$lang['userdetails_uploaded']}</td><td align='left'>".mksize($user_stats['uploaded'])." {$lang['userdetails_daily']}".($days > 1 ? mksize($user_stats['uploaded']/$days) : mksize($user_stats['uploaded']))."</td></tr>\n";
    }
    }
//==end
// End Class

// End File
