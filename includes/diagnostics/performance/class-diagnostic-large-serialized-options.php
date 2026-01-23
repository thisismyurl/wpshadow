<?php
/**
 * Diagnostic: Large Serialized Options
 *
 * Detects options with large serialized arrays slowing down autoload.
 *
 * Philosophy: Show Value (#9) - Measure option bloat impact
 * KB Link: https://wpshadow.com/kb/large-serialized-options
 * Training: https://wpshadow.com/training/large-serialized-options
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Large Serialized Options diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Large_Serialized_Options extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		global $wpdb;

		// Find large serialized options
		$large_options = $wpdb->get_results(
			"SELECT 
				option_name,
				LENGTH(option_value) as size,
				autoload,
				CASE 
					WHEN option_value LIKE 'a:%' THEN 'array'
					WHEN option_value LIKE 'O:%' THEN 'object'
					ELSE 'other'
				END as type
			FROM {$wpdb->options}
			WHERE LENGTH(option_value) > 102400
			AND (option_value LIKE 'a:%' OR option_value LIKE 'O:%')
			ORDER BY size DESC
			LIMIT 20",
			ARRAY_A
		);

		if ( empty( $large_options ) ) {
			return null;
		}

		// Calculate impact
		$autoloaded_large = array_filter( $large_options, function( $opt ) {
			return $opt['autoload'] === 'yes';
		} );

		$total_size = array_sum( array_column( $large_options, 'size' ) );
		$autoloaded_size = array_sum( array_column( $autoloaded_large, 'size' ) );
		
		$total_size_kb = round( $total_size / 1024, 2 );
		$autoloaded_size_kb = round( $autoloaded_size / 1024, 2 );

		$severity = $autoloaded_size_kb > 500 ? 'medium' : 'low';

		$description = sprintf(
			__( 'Your site has %d options with large serialized arrays (> 100 KB each). Total: %s KB. Large serialized options slow down unserialize() operations on every page load.', 'wpshadow' ),
			count( $large_options ),
			number_format( $total_size_kb )
		);

		if ( ! empty( $autoloaded_large ) ) {
			$description .= sprintf(
				' ' . __( '%d are autoloaded (%s KB loaded on every request).', 'wpshadow' ),
				count( $autoloaded_large ),
				number_format( $autoloaded_size_kb )
			);
		}

		// List top culprits
		$culprits = [];
		foreach ( array_slice( $large_options, 0, 5 ) as $option ) {
			$culprits[] = sprintf(
				'%s: %s KB%s',
				$option['option_name'],
				number_format( $option['size'] / 1024, 2 ),
				$option['autoload'] === 'yes' ? ' (autoloaded)' : ''
			);
		}

		if ( ! empty( $culprits ) ) {
			$description .= ' ' . __( 'Top culprits: ', 'wpshadow' ) . implode( ', ', $culprits );
		}

		return [
			'id'                => 'large-serialized-options',
			'title'             => __( 'Large Serialized Options', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'high',
			'effort'            => 'medium',
			'kb_link'           => 'https://wpshadow.com/kb/large-serialized-options',
			'training_link'     => 'https://wpshadow.com/training/large-serialized-options',
			'affected_resource' => sprintf( '%d options, %s KB', count( $large_options ), number_format( $total_size_kb ) ),
			'metadata'          => [
				'large_options'       => $large_options,
				'autoloaded_count'    => count( $autoloaded_large ),
				'total_size_kb'       => $total_size_kb,
				'autoloaded_size_kb'  => $autoloaded_size_kb,
			],
		];
	}

}