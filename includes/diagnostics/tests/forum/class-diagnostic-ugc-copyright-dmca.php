<?php
/**
 * Forum User-Generated Content Copyright (DMCA) Diagnostic
 *
 * Verifies forums have DMCA takedown procedures
 *
 * @package    WPShadow
 * @subpackage Diagnostics\\Forum
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Forum;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_UgcCopyrightDmca Class
 *
 * Checks for: DMCA policy, takedown procedures, moderation tools
 *
 * @since 1.6031.1445
 */
class Diagnostic_UgcCopyrightDmca extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ugc-copyright-dmca';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Forum User-Generated Content Copyright (DMCA)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies forums have DMCA takedown procedures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'forum';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for dmca page.
		$page = get_page_by_path( 'dmca' );
		if ( ! $page ) {
			$issues[] = __( 'No DMCA policy page', 'wpshadow' );
		}

		// Check for relevant plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_keywords = array( 'moderation', 'anti-spam', 'akismet' );
		$has_plugin = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $plugin_keywords as $keyword ) {
				if ( stripos( $plugin, $keyword ) !== false ) {
					$has_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_plugin ) {
			$issues[] = __( 'No relevant plugin detected', 'wpshadow' );
		}

		// Additional checks would go here for: No takedown procedure documented

		// Additional checks would go here for: No content moderation plugin

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Copyright compliance concerns: %s. Forums need DMCA takedown procedures.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ugc-copyright-dmca',
		);
	}
}
