<?php
/**
 * Permalink Rewrite Rules Diagnostic
 *
 * Verifies permalink rewrite rules are properly configured and not corrupted.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1745
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Rewrite Rules Diagnostic Class
 *
 * Checks the integrity and functionality of WordPress rewrite rules.
 *
 * @since 1.6032.1745
 */
class Diagnostic_Permalink_Rewrite_Rules extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-rewrite-rules';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Rewrite Rules';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies rewrite rules are properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1745
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_rewrite;

		$issues = array();

		// Check if rewrite rules are set.
		$rules = get_option( 'rewrite_rules' );
		if ( empty( $rules ) || ! is_array( $rules ) ) {
			$issues[] = __( 'Rewrite rules are missing or corrupted', 'wpshadow' );
		} else {
			// Check if rules count is suspiciously low.
			if ( count( $rules ) < 10 ) {
				$issues[] = __( 'Unusually few rewrite rules registered', 'wpshadow' );
			}

			// Check if rules count is suspiciously high.
			if ( count( $rules ) > 1000 ) {
				$issues[] = sprintf(
					/* translators: %d: number of rules */
					__( 'Excessive rewrite rules (%d) may impact performance', 'wpshadow' ),
					count( $rules )
				);
			}
		}

		// Check if rewrite rules need flushing.
		if ( function_exists( 'flush_rewrite_rules' ) ) {
			// Get current rules hash.
			$current_hash = md5( serialize( $wp_rewrite->rewrite_rules() ) );
			$stored_hash  = get_option( 'wpshadow_rewrite_rules_hash' );

			if ( $stored_hash && $stored_hash !== $current_hash ) {
				$issues[] = __( 'Rewrite rules may need to be flushed', 'wpshadow' );
			}

			// Store current hash for next check.
			if ( ! $stored_hash ) {
				update_option( 'wpshadow_rewrite_rules_hash', $current_hash, false );
			}
		}

		// Check for permalink structure mismatch.
		$permalink_structure = get_option( 'permalink_structure' );
		if ( ! empty( $permalink_structure ) ) {
			$wp_rewrite->init();
			if ( $wp_rewrite->permalink_structure !== $permalink_structure ) {
				$issues[] = __( 'Permalink structure mismatch between database and active configuration', 'wpshadow' );
			}
		}

		// Check if any rules have empty values.
		if ( ! empty( $rules ) && is_array( $rules ) ) {
			$empty_rules = array_filter(
				$rules,
				function( $value ) {
					return empty( $value );
				}
			);

			if ( ! empty( $empty_rules ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of empty rules */
					_n(
						'%d rewrite rule has an empty value',
						'%d rewrite rules have empty values',
						count( $empty_rules ),
						'wpshadow'
					),
					count( $empty_rules )
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/permalink-rewrite-rules',
			);
		}

		return null;
	}
}
