<?php
/**
 * Content Review Workflow
 *
 * Checks if peer review or approval process is configured for content,
 * ensuring quality control measures are in place.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6029.1101
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Review Workflow Diagnostic Class
 *
 * Verifies that content review and approval workflows are configured
 * for quality control.
 *
 * @since 1.6029.1101
 */
class Diagnostic_Content_Review_Workflow extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-review-workflow';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Review Workflow';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if peer review or approval process is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1101
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_content_review_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$workflow_status = self::check_review_workflow();

		if ( $workflow_status['has_workflow'] ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No content review or approval workflow detected. Content published without peer review.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-review-workflow',
			'meta'         => array(
				'contributors_count' => $workflow_status['contributors_count'],
				'pending_count'      => $workflow_status['pending_count'],
			),
			'details'      => array(
				__( 'No workflow plugins or custom post status detected', 'wpshadow' ),
				__( 'All contributors can publish directly without review', 'wpshadow' ),
				__( 'Increases risk of publishing errors or inappropriate content', 'wpshadow' ),
			),
			'recommendation' => __( 'Install a workflow plugin like Edit Flow, PublishPress, or configure custom post statuses for review.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Check for review workflow.
	 *
	 * @since  1.6029.1101
	 * @return array Workflow status.
	 */
	private static function check_review_workflow() {
		// Check for popular workflow plugins.
		$workflow_plugins = array(
			'edit-flow/edit_flow.php',
			'publishpress/publishpress.php',
			'oasis-workflow/oasiswf.php',
			'content-workflow/content-workflow.php',
		);

		$has_workflow = false;
		foreach ( $workflow_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_workflow = true;
				break;
			}
		}

		// Check for pending posts (indicator of review process).
		$pending_count = wp_count_posts()->pending ?? 0;

		// Count contributors who can publish without review.
		$contributors = count_users();
		$contributors_count = $contributors['avail_roles']['contributor'] ?? 0;

		return array(
			'has_workflow'       => $has_workflow || ( $pending_count > 0 ),
			'contributors_count' => $contributors_count,
			'pending_count'      => $pending_count,
		);
	}
}
