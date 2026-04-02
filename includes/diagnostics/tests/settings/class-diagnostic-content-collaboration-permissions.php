<?php
/**
 * Content Collaboration Permissions Diagnostic
 *
 * Checks collaboration permissions for content roles.
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
 * Content Collaboration Permissions Diagnostic
 *
 * Validates role permissions for content collaboration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Content_Collaboration_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-collaboration-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Collaboration Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks collaboration permissions for content roles';

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
		$issues = array();
		$details = array();

		$contributor = get_role( 'contributor' );
		if ( $contributor && $contributor->has_cap( 'edit_others_posts' ) ) {
			$issues[] = __( 'Contributors can edit others posts', 'wpshadow' );
		}

		$author = get_role( 'author' );
		if ( $author && $author->has_cap( 'edit_others_posts' ) ) {
			$details['authors_can_edit_others'] = true;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Content collaboration permissions may be overly permissive', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-collaboration-permissions',
				'details'      => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
