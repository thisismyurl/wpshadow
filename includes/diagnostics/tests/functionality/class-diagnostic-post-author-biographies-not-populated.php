<?php
/**
 * Post Author Biographies Not Populated Diagnostic
 *
 * Checks if post author biographies are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2347
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Author Biographies Not Populated Diagnostic Class
 *
 * Detects missing author biographies.
 *
 * @since 1.2601.2347
 */
class Diagnostic_Post_Author_Biographies_Not_Populated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-author-biographies-not-populated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Author Biographies Not Populated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if author biographies are populated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2347
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$users = get_users(
			array(
				'meta_compare' => 'NOT EXISTS',
				'meta_key'     => 'description',
			)
		);

		if ( count( $users ) > 2 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d author profiles are missing biographies. Add author bios for better SEO and credibility.', 'wpshadow' ),
					count( $users )
				),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/post-author-biographies-not-populated',
			);
		}

		return null;
	}
}
