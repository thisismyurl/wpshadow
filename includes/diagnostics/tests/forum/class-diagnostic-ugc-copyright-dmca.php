<?php
/**
 * User-Generated Content Copyright and DMCA Compliance Diagnostic
 *
 * Checks if sites with UGC implement proper DMCA procedures, copyright
 * infringement reporting, and takedown processes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Forum
 * @since      1.6031.1452
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Forum;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UGC Copyright DMCA Diagnostic Class
 *
 * Verifies UGC sites have DMCA compliance procedures.
 *
 * @since 1.6031.1452
 */
class Diagnostic_UGC_Copyright_DMCA extends Diagnostic_Base {

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
	protected static $title = 'User-Generated Content Copyright and DMCA Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies UGC sites implement DMCA procedures and copyright protection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'forum';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1452
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for UGC platforms.
		$ugc_plugins = array(
			'bbpress',
			'buddypress',
			'wpforo',
			'user-submitted-posts',
			'frontend-publishing',
		);

		$has_ugc = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $ugc_plugins as $ugc_plugin ) {
				if ( stripos( $plugin, $ugc_plugin ) !== false ) {
					$has_ugc = true;
					break 2;
				}
			}
		}

		if ( ! $has_ugc ) {
			return null; // No UGC.
		}

		$issues = array();

		// Check for DMCA policy page.
		$pages = get_pages();
		$has_dmca_policy = false;

		foreach ( $pages as $page ) {
			if ( stripos( $page->post_title, 'dmca' ) !== false ||
				stripos( $page->post_title, 'copyright' ) !== false ||
				stripos( $page->post_content, 'dmca' ) !== false ) {
				$has_dmca_policy = true;
				break;
			}
		}

		if ( ! $has_dmca_policy ) {
			$issues[] = __( 'No DMCA/copyright policy page found', 'wpshadow' );
		}

		// Check for copyright infringement reporting.
		$has_reporting_system = false;
		foreach ( $pages as $page ) {
			if ( stripos( $page->post_content, 'report copyright' ) !== false ||
				stripos( $page->post_content, 'infringement' ) !== false ||
				stripos( $page->post_content, 'takedown' ) !== false ) {
				$has_reporting_system = true;
				break;
			}
		}

		if ( ! $has_reporting_system ) {
			$issues[] = __( 'No clear copyright infringement reporting process', 'wpshadow' );
		}

		// Check for moderation plugins.
		$has_moderation = false;
		$mod_plugins = array(
			'akismet',
			'antispam',
			'moderation',
			'approve',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $mod_plugins as $mod_plugin ) {
				if ( stripos( $plugin, $mod_plugin ) !== false ) {
					$has_moderation = true;
					break 2;
				}
			}
		}

		if ( ! $has_moderation ) {
			$issues[] = __( 'No content moderation plugin detected', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'UGC copyright concerns: %s. Sites with user-generated content must implement DMCA-compliant copyright policies and takedown procedures to maintain safe harbor protection.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ugc-copyright-dmca',
		);
	}
}
