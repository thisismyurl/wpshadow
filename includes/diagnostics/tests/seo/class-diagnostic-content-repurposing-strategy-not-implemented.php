<?php
/**
 * Content Repurposing Strategy Not Implemented Diagnostic
 *
 * Checks if content repurposing is planned.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2351
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Repurposing Strategy Not Implemented Diagnostic Class
 *
 * Detects missing content repurposing.
 *
 * @since 1.2601.2351
 */
class Diagnostic_Content_Repurposing_Strategy_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-repurposing-strategy-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Repurposing Strategy Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if content repurposing is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2351
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if has recently published posts
		$recent_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			 WHERE post_type = 'post' 
			 AND post_status = 'publish' 
			 AND post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);

		if ( absint( $recent_posts ) < 5 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content repurposing strategy is not implemented. Create a content calendar and repurpose existing content across channels.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-repurposing-strategy-not-implemented',
			);
		}

		return null;
	}
}
