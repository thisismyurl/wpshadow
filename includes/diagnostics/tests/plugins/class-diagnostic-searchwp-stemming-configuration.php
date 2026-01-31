<?php
/**
 * SearchWP Stemming Configuration Diagnostic
 *
 * SearchWP stemming not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.409.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SearchWP Stemming Configuration Diagnostic Class
 *
 * @since 1.409.0000
 */
class Diagnostic_SearchwpStemmingConfiguration extends Diagnostic_Base {

	protected static $slug = 'searchwp-stemming-configuration';
	protected static $title = 'SearchWP Stemming Configuration';
	protected static $description = 'SearchWP stemming not configured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'SearchWP' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify stemming is enabled
		$stemming_enabled = get_option( 'searchwp_stemming', 0 );
		if ( ! $stemming_enabled ) {
			$issues[] = 'Stemming not enabled (reduces search accuracy)';
		}

		// Check 2: Check for language-specific stemming
		$stemming_language = get_option( 'searchwp_stemming_language', '' );
		if ( empty( $stemming_language ) ) {
			$issues[] = 'Stemming language not configured';
		}

		// Check 3: Verify stemming algorithm
		$stemming_algorithm = get_option( 'searchwp_stemming_algorithm', '' );
		if ( $stemming_algorithm !== 'porter2' ) {
			$issues[] = 'Not using recommended Porter2 stemming algorithm';
		}

		// Check 4: Check for custom stopwords
		$stopwords = get_option( 'searchwp_stopwords', array() );
		if ( empty( $stopwords ) ) {
			$issues[] = 'Custom stopwords not configured';
		}

		// Check 5: Verify synonym support
		$synonyms = get_option( 'searchwp_synonyms', array() );
		if ( empty( $synonyms ) ) {
			$issues[] = 'Synonyms not configured';
		}

		// Check 6: Check for stemming cache
		$stemming_cache = get_option( 'searchwp_stemming_cache', 0 );
		if ( ! $stemming_cache ) {
			$issues[] = 'Stemming cache not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d SearchWP stemming configuration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/searchwp-stemming-configuration',
			);
		}

		return null;
	}
}
