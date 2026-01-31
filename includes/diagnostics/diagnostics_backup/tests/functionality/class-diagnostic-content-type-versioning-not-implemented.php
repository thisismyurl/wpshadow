<?php
/**
 * Content Type Versioning Not Implemented Diagnostic
 *
 * Checks if content type versioning is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Type Versioning Not Implemented Diagnostic Class
 *
 * Detects missing content type versioning.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Content_Type_Versioning_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-type-versioning-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Type Versioning Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if content type versioning is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if custom post types have versioning support
		$post_types = get_post_types( array( '_builtin' => false ), 'objects' );

		if ( empty( $post_types ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content type versioning is not implemented. Add revision tracking and version history for important content types.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-type-versioning-not-implemented',
			);
		}

		return null;
	}
}
