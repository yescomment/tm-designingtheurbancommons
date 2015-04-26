<?php
   /*
   Plugin Name: DtUC Vote
   Plugin URI: http://designingtheurbancommons.org
   Description: ballot counter for Designing the Urban Commons voting system
   Version: 0.1
   Author: Jacob Ford
   Author URI: http://jacobford.com
   */

add_action( 'init', 'script_enqueuer' );
add_action("wp_ajax_dtuc_vote", "loggedin_vote");
add_action("wp_ajax_nopriv_dtuc_vote", "stranger_vote");
add_filter('manage_pages_columns', 'add_votes_column');
add_action('manage_pages_custom_column', 'populate_votes_column', 10, 2);

function script_enqueuer() {
   wp_register_script( "dtuc_vote_script", WP_PLUGIN_URL.'/dtuc-vote/dtuc_vote_script.js', array('jquery') );
   wp_localize_script( 'dtuc_vote_script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
   wp_enqueue_script( 'jquery' );
   wp_enqueue_script( 'dtuc_vote_script' );
}

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

// ADD NEW COLUMN
function add_votes_column($cols) {
    $cols['user_votes'] = 'Votes';
    return $cols;
}
 
// SHOW THE HANGING CHADS
function populate_votes_column($column_name, $post_ID) {
    if ($column_name == 'user_votes') {
        $vote_count = get_post_meta($post_ID, "votes", true);
        if ($vote_count) {
            echo $vote_count;
        }
    }
}

?>