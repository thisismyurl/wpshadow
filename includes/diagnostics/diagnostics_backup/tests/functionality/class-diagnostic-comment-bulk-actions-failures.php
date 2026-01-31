<?php
/**
 * Comment Bulk Actions Failures Diagnostic
 *
 * Checks for issues with comment bulk action processing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2309
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Bulk Actions Failures Diagnostic Class
 *
 * Detects bulk action processing issues.
 *
 * @since 1.2601.2309
 */
class Diagnostic_Comment_Bulk_Actions_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-bulk-actions-failures';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Bulk Actions Failures';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for issues with comment bulk action processing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2309
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if user can perform bulk actions
		$current_user = wp_get_current_user();
		if ( ! $current_user->has_cap( 'moderate_comments' ) ) {
			$issues[] = __( 'Current user does not have comment moderation capabilities', 'wpshadow' );
		}

		// Check if there are hooks for bulk comment actions
		global $wp_filter;

		if ( ! isset( $wp_filter['bulk_actions-edit-comments'] ) ) {
			$issues[] = __( 'Bulk comment actions hook not properly registered', 'wpshadow' );
		}

		// Check for plugins that might interfere with bulk actions
		$interfering_plugins = array(
			'elementor/elementor.php',
			'wp-rocket/wp-rocket.php',
			'litespeed-cache/litespeed-cache.php',
		);

		$interference_count = 0;
		foreach ( $interfering_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$interference_count++;
			}
		}

		if ( $interference_count > 1 ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins */
				__( '%d plugins active that may interfere with bulk actions', 'wpshadow' ),
				$interference_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of issues */
					__( 'Found %d comment bulk action issues', 'wpshadow' ),
					count( $issues )
				),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-bulk-actions-failures',
			);
		}

		return null;
	}
}
