(function( $ ) {

	$(document).ready(function(){

		var percent = parseInt($('#percent').val()) + parseInt($('#views').val()) + parseInt($('#user').val());

		$('.balancer-percent').on('change',function(){	
			var percent2 = parseInt($('#percent').val()) + parseInt($('#views').val()) + parseInt($('#user').val());

			if(percent2 == 100){
				$('#percent-button').prop('disabled',false);
			} else {
				$('#percent-button').prop('disabled',true);
			}


		});

		if(percent == 100){
			$('#percent-button').prop('disabled',false);
		} else {
			$('#percent-button').prop('disabled',true);
		}

	});
})( jQuery );
