jQuery(document).ready( function() {

   jQuery(".vote").click( function(evt) {
      evt.preventDefault();
      post_id = jQuery(this).attr("data-post_id")
      nonce = jQuery(this).attr("data-nonce")

      jQuery.ajax({
         type : "post",
         dataType : "json",
         url : myAjax.ajaxurl,
         data : {action: "dtuc_vote", post_id : post_id, nonce: nonce},
         success: function(response) {
            if(response.type == "success") {
               // jQuery("#vote_counter").html(response.vote_count) | Vote counts no longer displayed
               jQuery("#vote-a").html("Voted.")
               document.getElementById("vote-a").className = document.getElementById("vote-a").className.replace( /(?:^|\s)vote(?!\S)/g , 'voted' )
               document.getElementById("vote-a").href = "#"
               document.getElementById("vote-a").style.pointerEvents = "none"
            }
            else if(response.type == "error") {
               jQuery("#vote-a").html(response.error_message)
               document.getElementById("vote-a").className = document.getElementById("vote-a").className.replace( /(?:^|\s)vote(?!\S)/g , 'vote_error' )
            }
            else {
               alert("Something’s gone terrible wrong")
            }
         }
      })   

   })

})