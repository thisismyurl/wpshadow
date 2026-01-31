<?php
/**
 * Multisite User Sync Not Configured Diagnostic
 *
 * Checks if multisite user synchronization is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite User Sync Not Configured Diagnostic Class
 *
 * Detects missing multisite user synchronization.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Multisite_User_Sync_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-user-sync-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite User Sync Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if multisite user sync is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only check on multisite
		if ( ! is_multisite() ) {
			return null;
		}

		$blog_count = get_blog_count();

		if ( $blog_count > 5 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Multisite has %d sites but user synchronization may not be configured. Users updating profiles should sync across all sites.', 'wpshadow' ),
					absint( $blog_count )
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/multisite-user-sync-not-configured',
			);
		}

		return null;
	}
}
