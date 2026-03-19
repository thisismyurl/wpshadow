<?php
/**
 * Search Console Monitored Diagnostic
 *
 * Tests if Google Search Console is actively reviewed.
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
 * Search Console Monitored Diagnostic Class
 *
 * Verifies that Search Console is connected and reviewed.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Monitors_Search_Console extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'monitors-search-console';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Search Console Monitored';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if Google Search Console is actively reviewed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$verification_keys = array(
			get_option( 'wpseo_google_verify' ),
			get_option( 'rank_math_google_verify' ),
			get_option( 'aioseo_google_verify' ),
		);

		$has_verification = false;
		foreach ( $verification_keys as $key ) {
			if ( ! empty( $key ) ) {
				$has_verification = true;
				break;
			}
		}

		$last_review = (int) get_option( 'wpshadow_search_console_last_review' );

		if ( $has_verification && $last_review ) {
			$days = floor( ( time() - $last_review ) / DAY_IN_SECONDS );
			if ( $days <= 30 ) {
				return null;
			}
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: days */
					__( 'Search Console was last reviewed %d days ago. Review it monthly to catch indexing or penalty issues.', 'wpshadow' ),
					$days
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/search-console-monitored',
				'persona'      => 'publisher',
			);
		}

		if ( ! $has_verification ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Search Console not verified. Connect it to monitor indexing, errors, and search visibility.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/search-console-monitored',
				'persona'      => 'publisher',
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Search Console is connected but review history not tracked. Add a monthly review reminder.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/search-console-monitored',
			'persona'      => 'publisher',
		);
	}
}
