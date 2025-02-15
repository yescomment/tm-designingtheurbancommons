<?php
   /*
   Plugin Name: DtUC Vote
   Plugin URI: http://designingtheurbancommons.org
   Description: Custom ballot counter for Designing the Urban Commons voting system.
   Version: 1.0
   Author: Jacob Ford
   Author URI: http://jacobford.com
   */

add_action("init", "script_enqueuer");
add_action("wp_ajax_dtuc_vote", "loggedin_vote");
add_action("wp_ajax_nopriv_dtuc_vote", "stranger_vote");
add_filter("manage_pages_columns", "add_votes_column");
add_action("admin_head", "format_column");
add_action("pre_get_posts", "orderby_votes");
add_action("manage_pages_custom_column", "populate_votes_column", 10, 2);
add_filter("manage_edit-page_sortable_columns", "make_votes_col_sortable");

// Get all the JavaScripts ready
function script_enqueuer() {

   wp_register_script( "dtuc_vote_script", WP_PLUGIN_URL.'/dtuc-vote/dtuc_vote_script.js', array('jquery') );
   wp_localize_script( 'dtuc_vote_script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
   wp_enqueue_script( 'jquery' );
   wp_enqueue_script( 'dtuc_vote_script' );

}

// Block votes if user is logged in
function loggedin_vote() {

   $ipaddress = $_SERVER["REMOTE_ADDR"]; // log user ip

   $result['type'] = "error";
   $result['error_message'] = "Hey! You work here! No voting for logged-in users.";
   $result = json_encode($result);
   echo $result;
   log_vote("[LGIN] Vote for Entry #" . $_REQUEST["post_id"] . " from $ipaddress ignored; user is logged in");
   die();

}

// Count the hanging chads
function stranger_vote() {

   $ipaddress = $_SERVER["REMOTE_ADDR"]; // log user ip
   $lastip = get_post_meta($_REQUEST["post_id"], "last_voter_ip", true);

   if ( !wp_verify_nonce( $_REQUEST['nonce'], "my_user_vote_nonce")) {
      log_vote("[BNON] Possible mischief from $ipaddress attempting to vote for Entry #" . $_REQUEST["post_id"]);
      exit("Seems like you're trying to cheat the voting system. Good design is honest. Don't be dishonest.");
   }

   $vote_count = get_post_meta($_REQUEST["post_id"], "meta_vote_count", true);
   $vote_count = ($vote_count == '') ? 0 : $vote_count;
   $new_vote_count = $vote_count + 1;

   if($ipaddress == $lastip) {
      $result['type'] = "error";
      $result['vote_count'] = $vote_count;
      $result['error_message'] = "Oy! You've already voted for this entry.";
      log_vote("[RPIP] Vote for Entry #" . $_REQUEST["post_id"] . " from $ipaddress ignored; same IP as previous vote");
   }
   else {
      $vote = update_post_meta($_REQUEST["post_id"], "meta_vote_count", $new_vote_count);
      if($vote === false) {
         $result['type'] = "error";
         $result['vote_count'] = $vote_count;
         $result['error_message'] = "Sorry, there was an error.";
         log_vote("[ERRR] Vote for Entry #" . $_REQUEST["post_id"] . " from $ipaddress failed for unknown reason");
      }
      else {
         $result['type'] = "success";
         $result['vote_count'] = $new_vote_count;
         $result['error_message'] = "Voted! No error here!";
         update_post_meta($_REQUEST['post_id'], "last_voter_ip", $ipaddress);
         log_vote("[VOTE] Vote for Entry #" . $_REQUEST["post_id"] . " from $ipaddress");
      }
   }

   if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      $result = json_encode($result);
      echo $result;
   }
   else {
      header("Location: ".$_SERVER["HTTP_REFERER"]);
   }

   die();
}

// Log vote to help catch evil villians
function log_vote($message) {

   $filename = plugin_dir_path( __FILE__ ) . 'dtuc_vote_log.txt';
   $timestamp = date("Y m d H:i:s", time());
   $entry = $timestamp . ' ' . $message . "\r\n";

   file_put_contents($filename, $entry, FILE_APPEND | LOCK_EX); // I think something about this is why it's not working.

}

// Add User Votes column in Pages admin
function add_votes_column($cols) {
   $cols['user_votes'] = 'Votes'; //TODO: Narrower width?
   return $cols;
}

function format_column() {
    echo '<style type="text/css">';
    echo '.column-user_votes { width:8% !important; overflow:hidden }';
    echo '</style>';
}
 
// Display vote tallies
function populate_votes_column($column_name, $post_ID) {
   if ($column_name == 'user_votes') {
      $vote_count = get_post_meta($post_ID, "meta_vote_count", true);
      if ($vote_count) {
         echo $vote_count;
      }
   }
}

// Teach wordpress how to sort by user_votes
function orderby_votes($query) {
   if( ! is_admin() ) {
      return;
   }

   $orderby = $query->get('orderby');

   if( 'user_votes' == $orderby ) {
      $query->set('meta_key','meta_vote_count');
      $query->set('orderby','meta_value_num');
   }
}

// Makes Votes column sortable
function make_votes_col_sortable($cols) {
   $cols['user_votes'] = 'user_votes';
   return $cols;
}

?>