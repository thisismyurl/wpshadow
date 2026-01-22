<?php
/**
 * Diagnostic: Orphaned Metadata
 *
 * Detects postmeta, usermeta, and termmeta rows with no parent record.
 *
 * Philosophy: Show Value (#9) - Prove database waste with numbers
 * KB Link: https://wpshadow.com/kb/orphaned-metadata
 * Training: https://wpshadow.com/training/orphaned-metadata
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orphaned Metadata diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Orphaned_Metadata extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		global $wpdb;

		$orphaned = [];

		// Check orphaned postmeta
		$orphaned_postmeta = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm 
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_postmeta > 0 ) {
			$postmeta_size = $wpdb->get_var(
				"SELECT SUM(LENGTH(meta_key) + LENGTH(meta_value)) FROM {$wpdb->postmeta} pm 
				LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
				WHERE p.ID IS NULL"
			);
			$orphaned['postmeta'] = [
				'count' => $orphaned_postmeta,
				'size'  => round( $postmeta_size / 1024, 2 ),
			];
		}

		// Check orphaned usermeta
		$orphaned_usermeta = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} um 
			LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID 
			WHERE u.ID IS NULL"
		);

		if ( $orphaned_usermeta > 0 ) {
			$usermeta_size = $wpdb->get_var(
				"SELECT SUM(LENGTH(meta_key) + LENGTH(meta_value)) FROM {$wpdb->usermeta} um 
				LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID 
				WHERE u.ID IS NULL"
			);
			$orphaned['usermeta'] = [
				'count' => $orphaned_usermeta,
				'size'  => round( $usermeta_size / 1024, 2 ),
			];
		}

		// Check orphaned termmeta
		$orphaned_termmeta = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->termmeta} tm 
			LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id 
			WHERE t.term_id IS NULL"
		);

		if ( $orphaned_termmeta > 0 ) {
			$termmeta_size = $wpdb->get_var(
				"SELECT SUM(LENGTH(meta_key) + LENGTH(meta_value)) FROM {$wpdb->termmeta} tm 
				LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id 
				WHERE t.term_id IS NULL"
			);
			$orphaned['termmeta'] = [
				'count' => $orphaned_termmeta,
				'size'  => round( $termmeta_size / 1024, 2 ),
			];
		}

		if ( empty( $orphaned ) ) {
			return null;
		}

		$total_count = array_sum( array_column( $orphaned, 'count' ) );
		$total_size = array_sum( array_column( $orphaned, 'size' ) );

		$severity = $total_count > 1000 ? 'medium' : 'low';

		$description = sprintf(
			__( 'Your database contains %s orphaned metadata rows consuming %s KB. Orphaned metadata references deleted posts, users, or terms and serves no purpose. This slows down queries and wastes disk space.', 'wpshadow' ),
			number_format( $total_count ),
			number_format( $total_size )
		);

		$breakdown = [];
		foreach ( $orphaned as $type => $data ) {
			$breakdown[] = sprintf( '%s: %s rows', $type, number_format( $data['count'] ) );
		}

		if ( ! empty( $breakdown ) ) {
			$description .= ' ' . implode( ', ', $breakdown );
		}

		return [
			'id'                => 'orphaned-metadata',
			'title'             => __( 'Orphaned Metadata Rows', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'medium',
			'effort'            => 'low',
			'kb_link'           => 'https://wpshadow.com/kb/orphaned-metadata',
			'training_link'     => 'https://wpshadow.com/training/orphaned-metadata',
			'affected_resource' => sprintf( '%s rows, %s KB', number_format( $total_count ), number_format( $total_size ) ),
			'metadata'          => [
				'total_count'      => $total_count,
				'total_size_kb'    => $total_size,
				'orphaned_details' => $orphaned,
			],
		];
	}
}
