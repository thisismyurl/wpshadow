jQuery( document ).ready(
	function ($) {
		$( '#wpshadow_font_size_multiplier' ).on(
			'input',
			function () {
				$( '#wpshadow_font_size_display' ).text( $( this ).val() + '×' );
			}
		);
	}
);
