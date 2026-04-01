<?php
/**
 * Draft Visibility Controls Diagnostic
 *
 * Checks draft post visibility controls and role permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Draft Visibility Controls Diagnostic
 *
 * Ensures drafts are not broadly visible to unintended roles.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Draft_Visibility_Controls extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'draft-visibility-controls';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Draft Visibility Controls';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks draft post visibility controls and role permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		$draft_count = (int) wp_count_posts( 'post' )->draft;
		$details['draft_count'] = $draft_count;

		$subscriber = get_role( 'subscriber' );
		if ( $subscriber && $subscriber->has_cap( 'read_private_posts' ) ) {
			$issues[] = __( 'Subscribers can read private posts', 'wpshadow' );
		}

		if ( $draft_count > 50 ) {
			$issues[] = __( 'Large number of drafts - review visibility and access controls', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Draft visibility controls may be too permissive', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/draft-visibility-controls?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
