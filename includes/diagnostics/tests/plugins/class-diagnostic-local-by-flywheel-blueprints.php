<?php
/**
 * Local By Flywheel Blueprints Diagnostic
 *
 * Local By Flywheel Blueprints issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1069.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Local By Flywheel Blueprints Diagnostic Class
 *
 * @since 1.1069.0000
 */
class Diagnostic_LocalByFlywheelBlueprints extends Diagnostic_Base {

	protected static $slug = 'local-by-flywheel-blueprints';
	protected static $title = 'Local By Flywheel Blueprints';
	protected static $description = 'Local By Flywheel Blueprints issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Local by Flywheel environment indicators
		$is_local = defined( 'LOCAL_ENV' ) ||
		            strpos( $_SERVER['HTTP_HOST'] ?? '', '.local' ) !== false ||
		            file_exists( '/app/public' ) ||
		            getenv( 'LOCAL_SITE_ID' );
		
		if ( ! $is_local ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Blueprint file exists
		$blueprint_paths = array(
			ABSPATH . 'blueprint.json',
			ABSPATH . '../blueprint.json',
			'/app/blueprint.json',
		);
		
		$has_blueprint = false;
		foreach ( $blueprint_paths as $path ) {
			if ( file_exists( $path ) ) {
				$has_blueprint = true;
				break;
			}
		}
		
		if ( ! $has_blueprint ) {
			$issues[] = __( 'No blueprint file (site not exportable)', 'wpshadow' );
		}
		
		// Check 2: Database snapshot age
		$snapshot_dir = ABSPATH . '../app/sql/';
		if ( is_dir( $snapshot_dir ) ) {
			$snapshots = glob( $snapshot_dir . '*.sql' );
			if ( count( $snapshots ) > 0 ) {
				$latest = max( array_map( 'filemtime', $snapshots ) );
				if ( ( time() - $latest ) > ( 30 * DAY_IN_SECONDS ) ) {
					$issues[] = __( 'Database snapshot over 30 days old', 'wpshadow' );
				}
			}
		}
		
		// Check 3: Production environment variables
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$issues[] = __( 'WP_DEBUG enabled (development setting)', 'wpshadow' );
		}
		
		// Check 4: Local-specific plugins active
		$dev_plugins = array(
			'query-monitor/query-monitor.php',
			'debug-bar/debug-bar.php',
		);
		
		$active_dev = 0;
		foreach ( $dev_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_dev++;
			}
		}
		
		if ( $active_dev === 0 ) {
			$issues[] = __( 'No development plugins active (missing debugging tools)', 'wpshadow' );
		}
		
		// Check 5: Uploads directory size
		$upload_dir = wp_upload_dir();
		if ( is_dir( $upload_dir['basedir'] ) ) {
			$size = 0;
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $upload_dir['basedir'], \RecursiveDirectoryIterator::SKIP_DOTS ),
				\RecursiveIteratorIterator::CATCH_GET_CHILD
			);
			
			foreach ( $iterator as $file ) {
				if ( $file->isFile() ) {
					$size += $file->getSize();
				}
			}
			
			if ( $size > ( 1024 * 1024 * 1024 ) ) { // 1GB
				$issues[] = sprintf( __( 'Uploads: %s (blueprint will be large)', 'wpshadow' ), size_format( $size ) );
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of blueprint issues */
				__( 'Local by Flywheel has %d blueprint issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/local-by-flywheel-blueprints',
		);
	}
}
