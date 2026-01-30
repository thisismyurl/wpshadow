<?php
/**
 * Relevanssi Synonym Management Diagnostic
 *
 * Relevanssi synonyms inefficient.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.403.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Relevanssi Synonym Management Diagnostic Class
 *
 * @since 1.403.0000
 */
class Diagnostic_RelevanssiSynonymManagement extends Diagnostic_Base {

	protected static $slug = 'relevanssi-synonym-management';
	protected static $title = 'Relevanssi Synonym Management';
	protected static $description = 'Relevanssi synonyms inefficient';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! ( defined( 'RELEVANSSI_PREMIUM_VERSION' ) || function_exists( 'relevanssi_search' ) ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Synonyms database populated
		$synonyms = get_option( 'relevanssi_synonyms_count', 0 );
		if ( $synonyms <= 0 ) {
			$issues[] = 'Synonyms database not populated';
		}
		
		// Check 2: Synonym expansion enabled
		$expansion = get_option( 'relevanssi_synonym_expansion_enabled', 0 );
		if ( ! $expansion ) {
			$issues[] = 'Synonym expansion not enabled';
		}
		
		// Check 3: Caching enabled
		$cache = get_option( 'relevanssi_synonym_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Synonym caching not enabled';
		}
		
		// Check 4: Performance indexing
		$indexing = get_option( 'relevanssi_synonym_indexing_enabled', 0 );
		if ( ! $indexing ) {
			$issues[] = 'Synonym indexing not optimized';
		}
		
		// Check 5: Query expansion limit
		$limit = absint( get_option( 'relevanssi_synonym_expansion_limit', 0 ) );
		if ( $limit <= 0 ) {
			$issues[] = 'Query expansion limit not configured';
		}
		
		// Check 6: Synonym accuracy monitoring
		$monitor = get_option( 'relevanssi_synonym_accuracy_monitoring', 0 );
		if ( ! $monitor ) {
			$issues[] = 'Synonym accuracy monitoring not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d synonym management issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/relevanssi-synonym-management',
			);
		}
		
		return null;
	}
}
