jQuery(document).ready(function($){
	$('#badgeos_congrats').on( 'click', '.send-to-credly', function(event) {
		event.preventDefault();

		var button         = $(this);
		var achievement_id = button.attr('data-achievement-id');

		button.html('Please wait...');

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: badgeosCongrats.ajaxUrl,
			data: {
				'action': 'achievement_send_to_credly',
				'ID': achievement_id
			},
			success: function( response ) {
				button.html('Success!');
				button.remove();
			}
		});
	});
});
