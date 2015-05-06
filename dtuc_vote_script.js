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
               jQuery("#vote-container").html("Voted.")
               document.getElementById("vote-link").className = document.getElementById("vote-link").className.replace( /(?:^|\s)vote(?!\S)/g , 'voted' )
               document.getElementById("vote-link").href = "#"
               document.getElementById("vote-link").style.pointerEvents = "none"
            }
            if(response.type == "error") {
               jQuery("vote-container").html(response.error_message)
               document.getElementById("vote-link").className = document.getElementById("vote-link").className.replace( /(?:^|\s)vote(?!\S)/g , 'vote_error' )
            }
            else {
               alert("Something’s gone terrible wrong")
            }
         }
      })   

   })

})