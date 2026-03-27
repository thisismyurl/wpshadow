<?php
/**
 * Scoring Engine for WPShadow
 *
 * Performance, cost, and sustainability scoring.
 *
 * @package WPShadow
 * @subpackage Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calculate performance score.
 *
 * @return array Score payload.
 */
function wpshadow_calculate_performance_score() {
	$score = 85; // Base score assumes caching enabled.
	$label = 'Good';
	$color = '#10b981';

	// Penalize if caching is disabled.
	if ( defined( 'WP_CACHE' ) && ! WP_CACHE ) {
		$score -= 10;
	}

	// Penalize heavy query counts when available.
	if ( function_exists( 'get_num_queries' ) ) {
		$queries = get_num_queries();
		if ( $queries > 120 ) {
			$score -= 12;
		} elseif ( $queries > 80 ) {
			$score -= 6;
		}
	}

	$score = max( 0, min( 100, $score ) );

	if ( $score < 60 ) {
		$label = 'Needs Work';
		$color = '#ef4444';
	} elseif ( $score < 80 ) {
		$label = 'Fair';
		$color = '#f59e0b';
	}

	return array(
		'score' => $score,
		'label' => $label,
		'color' => $color,
	);
}

/**
 * Calculate cost efficiency score.
 *
 * @return array Score payload.
 */
function wpshadow_calculate_cost_score() {
	$score = 90; // Base score assumes lean stack.
	$label = 'Excellent';
	$color = '#10b981';

	$active_plugins = count( get_option( 'active_plugins', array() ) );
	if ( $active_plugins > 30 ) {
		$score -= 15;
	} elseif ( $active_plugins > 20 ) {
		$score -= 8;
	}

	$themes = wp_get_themes();
	if ( count( $themes ) > 5 ) {
		$score -= 5;
	}

	$score = max( 0, min( 100, $score ) );

	if ( $score < 60 ) {
		$label = 'Needs Work';
		$color = '#ef4444';
	} elseif ( $score < 80 ) {
		$label = 'Fair';
		$color = '#f59e0b';
	}

	return array(
		'score' => $score,
		'label' => $label,
		'color' => $color,
	);
}

/**
 * Calculate eco/sustainability score.
 *
 * @return array Score payload.
 */
function wpshadow_calculate_eco_score() {
	$score = 75; // Base score assumes standard CDN/compression.
	$label = 'Good';
	$color = '#10b981';

	$using_cdn = defined( 'WP_CONTENT_URL' ) && strpos( WP_CONTENT_URL, 'cdn' ) !== false;
	if ( ! $using_cdn ) {
		$score -= 6;
	}

	$compression_enabled = ini_get( 'zlib.output_compression' );
	if ( empty( $compression_enabled ) ) {
		$score -= 6;
	}

	$active_plugins = count( get_option( 'active_plugins', array() ) );
	if ( $active_plugins > 25 ) {
		$score -= 6;
	}

	$score = max( 0, min( 100, $score ) );

	if ( $score < 60 ) {
		$label = 'Needs Work';
		$color = '#ef4444';
	} elseif ( $score < 80 ) {
		$label = 'Fair';
		$color = '#f59e0b';
	}

	return array(
		'score' => $score,
		'label' => $label,
		'color' => $color,
	);
}
