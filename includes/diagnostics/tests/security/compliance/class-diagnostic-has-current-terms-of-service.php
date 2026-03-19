<?php
/**
 * Terms of Service Reviewed Diagnostic
 *
 * Tests if terms of service are current and reviewed.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Terms of Service Reviewed Diagnostic Class
 *
 * Verifies a terms page exists and is reviewed periodically.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Has_Current_Terms_Of_Service extends Diagnostic_Base {

	protected static $slug = 'has-current-terms-of-service';
	protected static $title = 'Terms of Service Reviewed';
	protected static $description = 'Tests if terms of service are current and reviewed';
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$terms_page = self::find_terms_page();
		if ( ! $terms_page ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No Terms of Service page found. Add terms to clarify user responsibilities and reduce disputes.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/terms-of-service-reviewed',
				'persona'      => 'enterprise-corp',
			);
		}

		$last_review = (int) get_option( 'wpshadow_terms_last_reviewed' );
		if ( $last_review ) {
			$days = floor( ( time() - $last_review ) / DAY_IN_SECONDS );
			if ( $days <= 365 ) {
				return null;
			}
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: days */
					__( 'Terms were last reviewed %d days ago. Review yearly to stay current with policy changes.', 'wpshadow' ),
					$days
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/terms-of-service-reviewed',
				'persona'      => 'enterprise-corp',
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Terms page exists but review history not tracked. Add a yearly review reminder.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 15,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/terms-of-service-reviewed',
			'persona'      => 'enterprise-corp',
		);
	}

	/**
	 * Find a terms page by title search.
	 *
	 * @since 1.6093.1200
	 * @return int|null Page ID if found.
	 */
	private static function find_terms_page() {
		if ( ! function_exists( 'get_posts' ) ) {
			return null;
		}

		$keywords = array(
			'terms of service',
			'terms and conditions',
			'terms',
		);

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => 'page',
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				return (int) $posts[0];
			}
		}

		return null;
	}
}
