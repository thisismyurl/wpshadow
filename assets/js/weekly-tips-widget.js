jQuery( document ).ready(
	function ($) {
		$( '.wpshadow-tip-helpful' ).on(
			'click',
			function () {
				var $btn  = $( this );
				var tipId = $btn.data( 'tip-id' );

				$.post(
					ajaxurl,
					{
						action: 'wpshadow_mark_tip_helpful',
						tip_id: tipId,
						nonce: (window.wpshadowWeeklyTipsWidget && window.wpshadowWeeklyTipsWidget.nonce) ? window.wpshadowWeeklyTipsWidget.nonce : ''
					},
					function () {
						$btn.fadeOut(
							300,
							function () {
								$( '.wpshadow-tip-helpful-thanks' ).fadeIn( 300 );
							}
						);
					}
				);
			}
		);
	}
);
