<?php
/**
 *   https://github.com/Bigjoos/
 *   Licence Info: GPL
 *   Copyright (C) 2010 U-232 v.3
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless, putyn.
 **/

function docleanup( $data ) {
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(0);
    ignore_user_abort(1);
    //=== Clean silver
    $res = sql_query("SELECT id, silver FROM torrents WHERE silver > 1 AND silver < ".TIME_NOW) or sqlerr(__FILE__, __LINE__);
    $Silver_buffer = array();
    if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $Silver_buffer[] = '(' . $arr['id'] . ', \'0\')';
            $mc1->begin_transaction('torrent_details_'.$arr['id']);
            $mc1->update_row(false, array('silver' => 0));
            $mc1->commit_transaction($INSTALLER09['expires']['torrent_details']);
        }
        $count = count($Silver_buffer);
        if ($count > 0){
        sql_query("INSERT INTO torrents (id, silver) VALUES " . implode(', ', $Silver_buffer) . " ON DUPLICATE key UPDATE silver=values(silver)") or sqlerr(__FILE__, __LINE__);
        write_log("Cleanup - Removed Silver from ".$count." torrents");
        }
        unset ($Silver_buffer, $count);
    }
   //==End
   if($queries > 0)
   write_log("Free clean-------------------- Silver Torrents cleanup Complete using $queries queries --------------------");

   if( false !== mysqli_affected_rows($GLOBALS["___mysqli_ston"]) )
   {
   $data['clean_desc'] = mysqli_affected_rows($GLOBALS["___mysqli_ston"]) . " items updated";
   }

   if( $data['clean_log'] )
   {
   cleanup_log( $data );
   }
        
   }  
  
   function cleanup_log( $data )
   {
   $text = sqlesc($data['clean_title']);
   $added = TIME_NOW;
   $ip = sqlesc($_SERVER['REMOTE_ADDR']);
   $desc = sqlesc($data['clean_desc']);
   sql_query( "INSERT INTO cleanup_log (clog_event, clog_time, clog_ip, clog_desc) VALUES ($text, $added, $ip, {$desc})" ) or sqlerr(__FILE__, __LINE__);
   }
?>
