<?php
/**
 * Relevanssi Stopwords Configuration Diagnostic
 *
 * Relevanssi stopwords not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.402.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Relevanssi Stopwords Configuration Diagnostic Class
 *
 * @since 1.402.0000
 */
class Diagnostic_RelevanssiStopwordsConfiguration extends Diagnostic_Base {

	protected static $slug = 'relevanssi-stopwords-configuration';
	protected static $title = 'Relevanssi Stopwords Configuration';
	protected static $description = 'Relevanssi stopwords not configured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'RELEVANSSI_PREMIUM_VERSION' ) && ! function_exists( 'relevanssi_search' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Stopwords configured
		$stopwords = get_option( 'relevanssi_stopwords', '' );
		if ( empty( $stopwords ) ) {
			$issues[] = 'no stopwords configured (may affect search quality)';
		}

		// Check 2: Stopwords count
		if ( ! empty( $stopwords ) ) {
			$stopword_array = explode( ',', $stopwords );
			$count = count( $stopword_array );
			if ( $count < 10 ) {
				$issues[] = "few stopwords defined ({$count} words, consider adding more)";
			}
		}

		// Check 3: Language-specific stopwords
		$locale = get_locale();
		if ( ! empty( $stopwords ) && 'en_US' !== $locale ) {
			$has_locale_stopwords = get_option( 'relevanssi_stopwords_' . $locale, '' );
			if ( empty( $has_locale_stopwords ) ) {
				$issues[] = "non-English site but no {$locale} stopwords configured";
			}
		}

		// Check 4: Index size with stopwords
		global $wpdb;
		$index_size = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}relevanssi"
		);
		if ( $index_size > 100000 && empty( $stopwords ) ) {
			$issues[] = "large index ({$index_size} entries) without stopwords (performance impact)";
		}

		// Check 5: Common words in index
		$common_words = get_option( 'relevanssi_common_words', array() );
		if ( ! empty( $common_words ) && is_array( $common_words ) ) {
			$common_count = count( $common_words );
			if ( $common_count > 50 && empty( $stopwords ) ) {
				$issues[] = "{$common_count} common words found (add them as stopwords)";
			}
		}

		// Check 6: Minimum word length setting
		$min_word_length = get_option( 'relevanssi_min_word_length', 3 );
		if ( $min_word_length < 3 && empty( $stopwords ) ) {
			$issues[] = 'short words indexed without stopwords (index bloat)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Relevanssi stopwords configuration issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/relevanssi-stopwords-configuration',
			);
		}

		return null;
	}
}
