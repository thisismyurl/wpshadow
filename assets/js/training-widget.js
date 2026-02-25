jQuery( document ).ready(
	function ($) {
		$( '.wpshadow-training-widget__dismiss' ).on(
			'click',
			function () {
				var widget  = $( this ).closest( '.wpshadow-training-widget' );
				var context = widget.data( 'context' );

				widget.fadeOut( 300 );

				$.post(
					ajaxurl,
					{
						action: 'wpshadow_dismiss_training_widget',
						context: context,
						nonce: wpshadowTrainingWidget.nonce
					}
				);
			}
		);

		$( '.wpshadow-training-click' ).on(
			'click',
			function () {
				var course = $( this ).data( 'course' );

				$.post(
					ajaxurl,
					{
						action: 'wpshadow_track_training_click',
						course: course,
						nonce: wpshadowTrainingWidget.nonce
					}
				);
			}
		);
	}
);
