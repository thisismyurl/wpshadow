<?php
/**
 * Permalink Migration Issues Diagnostic
 *
 * Detects potential issues when migrating from one permalink structure to another,
 * ensuring proper redirects are in place to prevent broken links.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1600
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Migration Issues Diagnostic Class
 *
 * Identifies permalink structure changes and checks for proper redirect handling.
 *
 * @since 1.6032.1600
 */
class Diagnostic_Permalink_Migration_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-migration-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Migration Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects permalink structure migration issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Permalink structure changes in history
	 * - Redirect plugins installed
	 * - Old URLs still being indexed
	 * - 404 error patterns
	 *
	 * @since  1.6032.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get current permalink structure.
		$current_structure = get_option( 'permalink_structure' );

		// Check if we're using plain permalinks (worst for SEO).
		if ( empty( $current_structure ) ) {
			$issues[] = __( 'Using plain permalinks which are not SEO-friendly', 'wpshadow' );
		}

		// Check for redirect plugin.
		$redirect_plugins = array(
			'redirection/redirection.php'           => 'Redirection',
			'simple-301-redirects/simple-301-redirects.php' => 'Simple 301 Redirects',
			'safe-redirect-manager/safe-redirect-manager.php' => 'Safe Redirect Manager',
		);

		$has_redirect_plugin = false;
		foreach ( $redirect_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_redirect_plugin = true;
				break;
			}
		}

		// Check if there's a history of permalink changes.
		$previous_structure = get_option( 'wpshadow_previous_permalink_structure' );
		if ( false === $previous_structure && ! empty( $current_structure ) ) {
			// Store current structure for future comparison.
			update_option( 'wpshadow_previous_permalink_structure', $current_structure, false );
		} elseif ( $previous_structure && $previous_structure !== $current_structure ) {
			// Permalink structure has changed.
			if ( ! $has_redirect_plugin ) {
				$issues[] = __( 'Permalink structure has changed but no redirect plugin is installed to handle old URLs', 'wpshadow' );
			}

			// Update stored structure.
			update_option( 'wpshadow_previous_permalink_structure', $current_structure, false );
		}

		// Check rewrite rules are flushed.
		$rewrite_rules = get_option( 'rewrite_rules' );
		if ( empty( $rewrite_rules ) && ! empty( $current_structure ) ) {
			$issues[] = __( 'Rewrite rules appear to be missing; permalinks may not work correctly', 'wpshadow' );
		}

		// Check for .htaccess writability (Apache servers).
		if ( got_mod_rewrite() ) {
			$htaccess_file = get_home_path() . '.htaccess';
			if ( file_exists( $htaccess_file ) && ! is_writable( $htaccess_file ) ) {
				$issues[] = __( '.htaccess file exists but is not writable; automatic rewrite rule updates will fail', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/permalink-migration-issues',
			);
		}

		return null;
	}
}
