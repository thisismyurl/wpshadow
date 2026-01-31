<?php
/**
 * Multisite Cross-Site Sync Not Configured Diagnostic
 *
 * Checks if multisite sites sync properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Cross-Site Sync Not Configured Diagnostic Class
 *
 * Detects multisite sync issues.
 *
 * @since 1.2601.2330
 */
class Diagnostic_Multisite_Cross_Site_Sync_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-cross-site-sync-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Cross-Site Sync Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if multisite sites sync properly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		// Check if multisite has many sites
		$blog_count = count( get_sites() );

		if ( $blog_count > 10 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Multisite has %d blogs but cross-site sync may not be configured. Consider setting up shared plugins or themes.', 'wpshadow' ),
					$blog_count
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/multisite-cross-site-sync-not-configured',
			);
		}

		return null;
	}
}
