<?php
/**
 * Author Archive Pages Disabled Diagnostic
 *
 * Checks if author archives are enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2320
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author Archive Pages Disabled Diagnostic Class
 *
 * Detects disabled author archives.
 *
 * @since 1.2601.2320
 */
class Diagnostic_Author_Archive_Pages_Disabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'author-archive-pages-disabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Author Archive Pages Disabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if author archive pages are enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2320
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Some privacy-focused sites disable author archives
		// Check if has_filter for pre_get_posts disables them
		if ( has_filter( 'author_rewrite_rules' ) ) {
			$rewrite = get_option( 'rewrite_rules' );
			if ( $rewrite && ! preg_match( '/author/', serialize( $rewrite ) ) ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Author archive pages are disabled. Enable them for SEO and author branding unless privacy is a concern.', 'wpshadow' ),
					'severity'      => 'low',
					'threat_level'  => 10,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/author-archive-pages-disabled',
				);
			}
		}

		return null;
	}
}
