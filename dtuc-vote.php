<?php
   /*
   Plugin Name: DtUC Vote
   Plugin URI: http://designingtheurbancommons.org
   Description: ballot counter for Designing the Urban Commons voting system
   Version: 0.1
   Author: Jacob Ford
   Author URI: http://jacobford.com
   */

add_action("wp_ajax_my_user_vote", "my_user_vote");
add_action("wp_ajax_nopriv_my_user_vote", "my_must_login");

function loggedin_vote() {
   echo "You cheater! You work here!";
   die();
}

function stranger_vote() {

   if ( !wp_verify_nonce( $_REQUEST['nonce'], "my_user_vote_nonce")) {
      exit("Don't be naughty. Good design is honest.");
   }   

   $vote_count = get_post_meta($_REQUEST["post_id"], "votes", true);
   $vote_count = ($vote_count == '') ? 0 : $vote_count;
   $new_vote_count = $vote_count + 1;

   $vote = update_post_meta($_REQUEST["post_id"], "votes", $new_vote_count);

   if($vote === false) {
      $result['type'] = "error";
      $result['vote_count'] = $vote_count;
   }
   else {
      $result['type'] = "success";
      $result['vote_count'] = $new_vote_count;
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

?>