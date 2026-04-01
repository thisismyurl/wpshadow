<?php
/**
 * Feature Status Helper Functions
 *
 * Determines feature availability based on @since version tags.
 * Non-live features are hidden from UI listings.
 *
 * @package    WPShadow
 * @subpackage Utils
 * @since 0.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse @since version string to determine feature status.
 *
 * Version format: 1.YDDD.HHMM
 * - 1 = major version (not relevant)
 * - Y = last digit of year (6 = 2026)
 * - DDD = Julian day of year (001-365/366)
 * - HHMM = time (not relevant for status)
 *
 * @since 0.6093.1200
 * @param  string $since_version Version string from @since tag.
 * @return array {
 *     Feature status information.
 *
 *     @type string $status       'active', 'coming_soon', or 'hidden'.
 *     @type int    $days_until   Days until activation (0 if active, negative if past).
 *     @type string $launch_date  Human-readable launch date.
 * }
 */
function wpshadow_get_feature_status( string $since_version ): array {
	// Default to active if version can't be parsed
	$default = array(
		'status'      => 'active',
		'days_until'  => 0,
		'launch_date' => '',
	);

	// Parse version:1.0 -> year=6 (2026), julian_day=037
	if ( ! preg_match( '/^1\.(\d)(\d{3})\./', $since_version, $matches ) ) {
		return $default;
	}

	$year_digit  = (int) $matches[1];
	$julian_day  = (int) $matches[2];

	// Convert year digit to full year (6 = 2026, 7 = 2027, etc.)
	$year = 2020 + $year_digit;

	// Get current date info
	$current_year = (int) gmdate( 'Y' );
	$current_day  = (int) gmdate( 'z' ) + 1; // z is 0-indexed, convert to 1-365

	// Calculate feature launch timestamp
	$feature_date = DateTime::createFromFormat( 'Y z', "{$year} " . ( $julian_day - 1 ) );
	if ( ! $feature_date ) {
		return $default;
	}

	// Calculate days until launch
	$today         = new DateTime();
	$today->setTime( 0, 0, 0 ); // Normalize to midnight
	$feature_date->setTime( 0, 0, 0 );

	$interval    = $today->diff( $feature_date );
	$days_until  = (int) $interval->format( '%r%a' ); // Signed day difference

	// Determine status
	$coming_soon_threshold = 65;

	if ( $days_until <= 0 ) {
		// Feature is in the past (active)
		$status = 'active';
	} elseif ( $days_until <= $coming_soon_threshold ) {
		// Feature launches within 65 days
		$status = 'coming_soon';
	} else {
		// Feature is too far in the future (hide it)
		$status = 'hidden';
	}

	return array(
		'status'      => $status,
		'days_until'  => $days_until,
		'launch_date' => $feature_date->format( 'F j, Y' ),
	);
}

/**
 * Check if a feature should be displayed.
 *
 * @since 0.6093.1200
 * @param  string $since_version Version string from @since tag.
 * @return bool True if feature is live and should be shown.
 */
function wpshadow_should_show_feature( string $since_version ): bool {
	$status = wpshadow_get_feature_status( $since_version );
	return 'active' === $status['status'];
}

/**
 * Get feature badge HTML based on status.
 *
 * @since 0.6093.1200
 * @param  string $since_version Version string from @since tag.
 * @return string Badge HTML or empty string.
 */
function wpshadow_get_feature_badge( string $since_version ): string {
	$status = wpshadow_get_feature_status( $since_version );

	switch ( $status['status'] ) {
		case 'active':
			return '';

		case 'coming_soon':
			$tooltip = sprintf(
				/* translators: %s: launch date */
				__( 'Launches %s', 'wpshadow' ),
				$status['launch_date']
			);

			return sprintf(
				'<span class="wps-badge wps-badge--info" title="%s">%s</span>',
				esc_attr( $tooltip ),
				esc_html__( 'Coming Soon', 'wpshadow' )
			);

		case 'hidden':
		default:
			return '';
	}
}

/**
 * Filter an array of items by feature status.
 *
 * Expects items to have a 'since' key with version string.
 *
 * @since 0.6093.1200
 * @param  array $items Array of items with 'since' keys.
 * @return array Filtered items (includes only live features).
 */
function wpshadow_filter_features_by_status( array $items ): array {
	return array_filter( $items, function( $item ) {
		if ( ! isset( $item['since'] ) ) {
			return true; // Show items without @since tag
		}

		return wpshadow_should_show_feature( $item['since'] );
	} );
}
