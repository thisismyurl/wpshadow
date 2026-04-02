document.addEventListener(
	'DOMContentLoaded',
	function () {
		var chartCanvas = document.getElementById( 'wpshadow-journey-chart' );

		if ( ! chartCanvas ) {
			return;
		}

		var visualizationPayload = chartCanvas.getAttribute( 'data-journey-visualization' );
		var journeyData          = null;

		if ( ! visualizationPayload ) {
			return;
		}

		try {
			journeyData = JSON.parse( visualizationPayload );
		} catch ( error ) {
			return;
		}

		if ( ! journeyData ) {
			return;
		}

		chartCanvas.dataset.chartInitialized = 'true';
	}
);
