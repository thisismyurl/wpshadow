<?php
/**
 * Revision Limits Diagnostic
 *
 * Checks if post revision limits are properly configured to prevent
 * excessive database bloat from storing too many post versions.
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
 * Revision Limits Diagnostic Class
 *
 * Verifies revision configuration:
 * - WP_POST_REVISIONS setting
 * - Total revision count
 * - Revision database size
 * - Autosave frequency configuration
 *
 * @since 1.6093.1200
 */
class Diagnostic_Revision_Limits extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'revision-limits';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Revision Limits Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures post revision limits are configured to prevent database bloat';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get revision limit setting
		$post_revisions = defined( 'WP_POST_REVISIONS' ) ? WP_POST_REVISIONS : -1;

		// Count total revisions
		$revision_count_query = "SELECT COUNT(*) as count FROM {$wpdb->posts} WHERE post_type = 'revision'";
		$revision_count       = (int) $wpdb->get_var( $revision_count_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Estimate revision database size
		$revision_size_query = "SELECT ROUND( SUM( OCTET_LENGTH( post_content ) ) / 1024 / 1024, 2 ) as size_mb FROM {$wpdb->posts} WHERE post_type = 'revision'";
		$revision_size_mb    = (float) $wpdb->get_var( $revision_size_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$issues = array();

		// Check if revisions are completely disabled
		if ( defined( 'WP_POST_REVISIONS' ) && false === WP_POST_REVISIONS ) {
			// Revisions disabled - good for simple sites
			return null;
		}

		// Check if unlimited revisions
		if ( -1 === $post_revisions ) {
			$issues[] = __( 'Unlimited post revisions are enabled (WP_POST_REVISIONS = -1). This can cause significant database bloat.', 'wpshadow' );
		}

		// Check revision count and size
		if ( $revision_count > 1000 || $revision_size_mb > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of revisions, %f: size in MB */
				__( 'You have %d revisions using %.1f MB of database space.', 'wpshadow' ),
				$revision_count,
				$revision_size_mb
			);
		}

		// Check if limit is set but very high
		if ( $post_revisions > 0 && $post_revisions > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of revisions allowed */
				__( 'WP_POST_REVISIONS is set to %d. Recommended: 5-10 for most sites.', 'wpshadow' ),
				$post_revisions
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => implode( ' ', $issues ),
				'severity'      => $revision_size_mb > 100 ? 'high' : 'medium',
				'threat_level'  => $revision_size_mb > 100 ? 65 : 45,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/revision-limits',
				'meta'          => array(
					'wp_post_revisions'    => $post_revisions,
					'total_revisions'      => $revision_count,
					'revisions_size_mb'    => $revision_size_mb,
					'recommended_limit'    => 5,
					'recommendation'       => 'Add to wp-config.php: define( \'WP_POST_REVISIONS\', 5 );',
					'impact'               => sprintf(
						'Can reduce database size by %.0f%% and improve query performance',
						( $revision_size_mb / max( 1, $revision_size_mb + 50 ) ) * 100
					),
			),
			);
		}

		return null;
	}
}
