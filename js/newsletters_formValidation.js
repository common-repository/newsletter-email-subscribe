(function ($) {
	jQuery(document).ready(function ($) {
		$('#nels_submit_btn').on('click', function(e){
			$('#newsletter').validate({
				messages:{		
					newsletter_email:{
						required: "Please enter email adrress",
						email: "Please enter valid email address. ie. abcd@efgh.com",	
					},
				},
				rules: {
					newsletter_email:
					{
						required: true,
						email: true
					},
				},
				submitHandler: function(e) {
					$( ".formloader").css('display','inline-block');
					var email = $('#newsletter_email').val();
					var m_a_id = $('#m_a_id').val();
					var m_a_k = $('#m_a_k').val();
					var r_e_a = $('#r_e_a').val();
                	$.ajax({
	                   url: nels_ajax_obj.ajaxurl, 
	                    type: "POST",             
	                    data: { 
	                    	'email': email,
	                    	'm_a_id': m_a_id,
	                    	'm_a_k': m_a_k,
	                    	'r_e_a': r_e_a,
	                    	'action': 'nelsFormFunctionAjx',
	                    },
	                    success:function(data) {
	                    	if(data == 1){
								$('.formloader').hide();
								$( "#Success").slideDown( "slow" );
								setTimeout(function() {
									$( "#Success").slideUp();
								}, 5000);
								$("#newsletter")[0].reset();
							}
							else if(data == 2){
								$('.formloader').hide();
								$( "#AllreadyEmailError").slideDown( "slow" );
								setTimeout(function() {
									$( "#AllreadyEmailError").slideUp();
								}, 5000);
							}
							else{
								$('.formloader').hide();
								$( "#Error").slideDown( "slow" );
								setTimeout(function() {
									$( "#Error").slideUp();
								}, 5000);
							}	           
				        },
				        error: function(data) {
							$('.formloader').hide();
							$( "#Error").slideDown( "slow" );
							setTimeout(function() {
								$( ".Error").slideUp();
							}, 5000);
						}
	                });
	                return false;
				},
				
			});
		});
		
	});	
})(jQuery);