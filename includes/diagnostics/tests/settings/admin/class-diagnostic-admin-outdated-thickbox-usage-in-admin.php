<?php
/**
 * Admin Outdated Thickbox Usage In Admin Diagnostic
 *
 * Detects ThickBox usage in admin as an indicator of outdated modal patterns.
 * Modern WordPress admin favors wp.media or custom dialogs with wp-a11y support.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Outdated Thickbox Usage In Admin Diagnostic Class
 *
 * Warns when ThickBox is enqueued in admin. While still available, it is
 * considered legacy and can have accessibility gaps compared to modern modals.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Outdated_Thickbox_Usage_In_Admin extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-outdated-thickbox-usage-in-admin';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Outdated ThickBox Usage In Admin';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects ThickBox being enqueued in admin (legacy modal pattern)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		global $wp_scripts;

		if ( $wp_scripts && $wp_scripts->is_enqueued( 'thickbox' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'ThickBox is enqueued in the admin. Consider migrating to wp.media or modern accessible modal patterns.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-outdated-thickbox-usage-in-admin',
				'meta'         => array(
					'handle' => 'thickbox',
				),
			);
		}

		return null;
	}
}
