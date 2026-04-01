<?php
/**
 * Forum User-Generated Content Copyright (DMCA) Diagnostic
 *
 * Verifies forums have DMCA takedown procedures
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
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
 * Checks for DMCA policy, takedown procedures, moderation tools
 *
 * @since 0.6093.1200
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
 * @since 0.6093.1200
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Check for forum/UGC plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$ugc_plugins = array( 'bbpress', 'buddypress', 'wpforo', 'forum', 'comment' );
		$has_ugc = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $ugc_plugins as $u_plugin ) {
				if ( stripos( $plugin, $u_plugin ) !== false ) {
					$has_ugc = true;
					break 2;
				}
			}
		}

		// Check if comments are enabled (also UGC).
		if ( ! $has_ugc && get_option( 'default_comment_status' ) === 'open' ) {
			$has_ugc = true;
		}

		if ( ! $has_ugc ) {
			return null;
		}

		$issues = array();

		// Check for DMCA notice page.
		$dmca_page = get_page_by_path( 'dmca' );
		if ( ! $dmca_page ) {
			$dmca_page = get_page_by_path( 'copyright' );
		}
		if ( ! $dmca_page ) {
			$issues[] = __( 'No DMCA/copyright takedown notice page found', 'wpshadow' );
		}

		// Check for moderation queue.
		if ( get_option( 'comment_moderation' ) !== '1' ) {
			$issues[] = __( 'Comment moderation not enabled', 'wpshadow' );
		}

		// Check for copyright detection plugins.
		$copyright_plugins = array( 'copyright', 'dmca', 'content-protection', 'copyscape' );
		$has_copyright_tool = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $copyright_plugins as $c_plugin ) {
				if ( stripos( $plugin, $c_plugin ) !== false ) {
					$has_copyright_tool = true;
					break 2;
				}
			}
		}

		if ( ! $has_copyright_tool ) {
			$issues[] = __( 'No copyright protection/detection plugin detected', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'UGC copyright concerns: %s. Sites with user-generated content need DMCA procedures.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ugc-copyright-dmca?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
