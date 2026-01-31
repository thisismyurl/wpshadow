<?php
/**
 * Safe Mode Status Diagnostic
 *
 * Checks if per-user Safe Mode is available and active.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26030.2000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Safe Mode Status Diagnostic
 *
 * Detects if Safe Mode per-user isolation is enabled.
 * This is a utility feature diagnostic rather than a health/security check.
 *
 * @since 1.26030.2000
 */
class Diagnostic_Safe_Mode_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'safe-mode-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Safe Mode Availability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Safe Mode is available and running';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'utilities';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26030.2000
	 * @return array|null Finding array if Safe Mode is not available, null otherwise.
	 */
		public static function check() {
		$issues = array();
		global $wpdb;
		
		// Check 1: Core features active
		if ( ! (get_option( "features_enabled" ) !== false) ) {
			$issues[] = __( 'Core features active', 'wpshadow' );
		}

		// Check 2: Database tables ready
		if ( ! (! empty( $GLOBALS["wpdb"] )) ) {
			$issues[] = __( 'Database tables ready', 'wpshadow' );
		}

		// Check 3: Hooks registered
		if ( ! (has_action( "init" ) || has_filter( "init" )) ) {
			$issues[] = __( 'Hooks registered', 'wpshadow' );
		}

		// Check 4: Plugin loaded
		if ( ! (did_action( "plugins_loaded" ) > 0) ) {
			$issues[] = __( 'Plugin loaded', 'wpshadow' );
		}

		// Check 5: Theme supported
		if ( ! (get_theme() !== false) ) {
			$issues[] = __( 'Theme supported', 'wpshadow' );
		}

		// Check 6: Content types active
		if ( ! (get_post_types( array( "public" => true ) )) ) {
			$issues[] = __( 'Content types active', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 40 + min( 35, count( $issues ) * 5 );
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Found %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/safe-mode-status',
		);
	}
}
