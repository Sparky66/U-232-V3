<?php
//==posts
    if(($forumposts = $mc1->get_value('forum_posts_'.$id)) === false) {
    $res = sql_query("SELECT COUNT(id) FROM posts WHERE user_id=".sqlesc($user['id'])) or sqlerr(__FILE__,__LINE__);
    list($forumposts) = mysqli_fetch_row($res); 
    $mc1->cache_value('forum_posts_'.$id, $forumposts, $INSTALLER09['expires']['forum_posts']);
    }
    if  ($user['paranoia'] < 2 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) 
    {
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_posts']}</td>";
    if ($forumposts && (($user["class"] >= UC_POWER_USER && $user["id"] == $CURUSER["id"]) || $CURUSER['class'] >= UC_STAFF))
    $HTMLOUT .= "<td align='left'><a href='userhistory.php?action=viewposts&amp;id=$id'>".htmlsafechars($forumposts)."</a></td></tr>\n";
    else
    $HTMLOUT .= "<td align='left'>".htmlsafechars($forumposts)."</td></tr>\n";
    }
//==end
// End Class

// End File
