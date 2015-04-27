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
               jQuery("#vote_counter").html(response.vote_count)
               jQuery(".vote").pointerEvents = "none"
            }
            else {
               alert("Something’s gone terrible wrong")
            }
         }
      })   

   })

})