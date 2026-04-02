<?php
/**
 * Editorial Workflow Plugin Status Diagnostic
 *
 * Checks if editorial workflow tooling is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Editorial Workflow Plugin Status Diagnostic
 *
 * Flags missing workflow tooling for larger teams.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Editorial_Workflow_Plugin_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'editorial-workflow-plugin-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Editorial Workflow Plugin Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if editorial workflow tooling is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );
		$workflow_plugins = array(
			'edit-flow/edit_flow.php' => 'Edit Flow',
			'publishpress/publishpress.php' => 'PublishPress',
			'oasis-workflow/oasis-workflow.php' => 'Oasis Workflow',
		);

		$workflow_enabled = false;
		foreach ( $workflow_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$workflow_enabled = true;
				break;
			}
		}

		$user_count = count_users();
		$total_users = $user_count['total_users'] ?? 0;

		if ( $total_users > 5 && ! $workflow_enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Editorial workflow tools are not configured for a multi-user site', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/editorial-workflow-plugin-status',
				'details'      => array(
					'user_count' => $total_users,
				),
			);
		}

		return null;
	}
}
