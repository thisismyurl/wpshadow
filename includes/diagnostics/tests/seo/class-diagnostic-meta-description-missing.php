<?php
/**
 * Meta Description Missing Diagnostic
 *
 * Checks if posts have meta descriptions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta Description Missing Diagnostic Class
 *
 * Checks for posts lacking meta descriptions.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Meta_Description_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-description-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Description Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for missing meta descriptions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for posts without meta descriptions
		$posts_without_meta = $wpdb->get_var(
			"SELECT COUNT(p.ID) FROM {$wpdb->posts} p 
			LEFT JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_yoast_wpseo_metadesc') 
			WHERE p.post_type IN ('post', 'page') AND p.post_status = 'publish' AND (pm.meta_value IS NULL OR pm.meta_value = '')"
		);

		if ( $posts_without_meta > 50 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d published posts/pages lack meta descriptions. This impacts search engine snippet quality.', 'wpshadow' ),
					absint( $posts_without_meta )
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/meta-description-missing',
			);
		}

		return null;
	}
}
