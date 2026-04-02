<?php
/**
 * Mobile Content Hierarchy Diagnostic
 *
 * Tests if headings follow a clear hierarchy on mobile.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Content Hierarchy Diagnostic Class
 *
 * Checks for heading order issues on the homepage.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Content_Hierarchy extends Diagnostic_Base {

	protected static $slug = 'mobile-content-hierarchy';
	protected static $title = 'Mobile Content Hierarchy';
	protected static $description = 'Tests if headings follow a clear hierarchy on mobile';
	protected static $family = 'design';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$response = wp_remote_get( home_url( '/' ) );
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return null;
		}

		libxml_use_internal_errors( true );
		$dom = new \DOMDocument();
		$dom->loadHTML( $body );
		libxml_clear_errors();

		$headings = array();
		for ( $i = 1; $i <= 6; $i++ ) {
			foreach ( $dom->getElementsByTagName( 'h' . $i ) as $node ) {
				$headings[] = $i;
			}
		}

		if ( empty( $headings ) ) {
			return null;
		}

		$h1_count = count( array_filter( $headings, static function( $h ) { return 1 === $h; } ) );
		if ( $h1_count > 1 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Multiple H1 headings detected. Use a single H1 and consistent hierarchy for mobile readability.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-content-hierarchy',
				'persona'      => 'publisher',
			);
		}

		for ( $i = 1; $i < count( $headings ); $i++ ) {
			if ( $headings[ $i ] - $headings[ $i - 1 ] > 1 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Heading levels skip on the page (e.g., H2 to H4). Keep headings in order for clarity and accessibility.', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/mobile-content-hierarchy',
					'persona'      => 'publisher',
				);
			}
		}

		return null;
	}
}
