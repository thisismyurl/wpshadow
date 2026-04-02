<?php
/**
 * Meta Box Plugin Conflicts Diagnostic
 *
 * Detects conflicts between meta box plugins. Tests for UI collisions and data
 * conflicts when multiple meta box frameworks are active.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta Box Plugin Conflicts Diagnostic Class
 *
 * Checks for conflicts between meta box plugins.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Meta_Box_Plugin_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-box-plugin-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Box Plugin Conflicts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects conflicts between meta box plugins causing UI or data issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Detect active meta box plugins.
		$meta_box_plugins = array();

		if ( class_exists( 'ACF' ) || function_exists( 'acf' ) ) {
			$meta_box_plugins[] = 'Advanced Custom Fields';
		}

		if ( defined( 'RWMB_VER' ) || class_exists( 'RWMB_Core' ) ) {
			$meta_box_plugins[] = 'Meta Box';
		}

		if ( class_exists( 'CMB2' ) || function_exists( 'cmb2_bootstrap' ) ) {
			$meta_box_plugins[] = 'CMB2';
		}

		if ( defined( 'PODS_VERSION' ) || function_exists( 'pods' ) ) {
			$meta_box_plugins[] = 'Pods';
		}

		if ( class_exists( 'Toolset_Common_Bootstrap' ) ) {
			$meta_box_plugins[] = 'Toolset Types';
		}

		if ( function_exists( 'carbon_fields_boot_plugin' ) || class_exists( 'Carbon_Fields\\Container' ) ) {
			$meta_box_plugins[] = 'Carbon Fields';
		}

		// Check for conflicts when multiple plugins are active.
		if ( count( $meta_box_plugins ) > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: list of active meta box plugins */
				__( 'Multiple meta box plugins active (%s) - may cause conflicts', 'wpshadow' ),
				implode( ', ', $meta_box_plugins )
			);

			// Check for meta key prefix conflicts.
			global $wpdb;

			$prefixes = array();
			if ( in_array( 'Advanced Custom Fields', $meta_box_plugins, true ) ) {
				$prefixes[] = '_acf';
			}
			if ( in_array( 'Meta Box', $meta_box_plugins, true ) ) {
				$prefixes[] = '_rwmb';
			}
			if ( in_array( 'CMB2', $meta_box_plugins, true ) ) {
				$prefixes[] = '_cmb2';
			}
			if ( in_array( 'Pods', $meta_box_plugins, true ) ) {
				$prefixes[] = '_pods';
			}

			// Check for overlapping meta keys.
			$overlap_count = 0;
			foreach ( $prefixes as $prefix ) {
				$other_prefixes = array_diff( $prefixes, array( $prefix ) );
				foreach ( $other_prefixes as $other_prefix ) {
					$overlaps = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT COUNT(DISTINCT pm1.post_id)
							FROM {$wpdb->postmeta} pm1
							INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
							WHERE pm1.meta_key LIKE %s
							AND pm2.meta_key LIKE %s
							LIMIT 100",
							$wpdb->esc_like( $prefix ) . '%',
							$wpdb->esc_like( $other_prefix ) . '%'
						)
					);

					if ( $overlaps > 0 ) {
						++$overlap_count;
					}
				}
			}

			if ( $overlap_count > 0 ) {
				$issues[] = __( 'Posts have meta fields from multiple plugins (potential data conflicts)', 'wpshadow' );
			}
		}

		// Check for excessive meta boxes (indicates plugin bloat).
		global $wp_meta_boxes;
		if ( ! empty( $wp_meta_boxes ) ) {
			$total_meta_boxes = 0;
			foreach ( $wp_meta_boxes as $post_type => $contexts ) {
				foreach ( $contexts as $context => $priorities ) {
					foreach ( $priorities as $priority => $boxes ) {
						$total_meta_boxes += count( $boxes );
					}
				}
			}

			if ( $total_meta_boxes > 50 ) {
				$issues[] = sprintf(
					/* translators: %d: number of registered meta boxes */
					__( '%d meta boxes registered (excessive, will slow admin pages)', 'wpshadow' ),
					$total_meta_boxes
				);
			}
		}

		// Check for duplicate meta box IDs.
		if ( ! empty( $wp_meta_boxes ) ) {
			$all_ids = array();
			foreach ( $wp_meta_boxes as $post_type => $contexts ) {
				foreach ( $contexts as $context => $priorities ) {
					foreach ( $priorities as $priority => $boxes ) {
						$all_ids = array_merge( $all_ids, array_keys( $boxes ) );
					}
				}
			}

			$duplicate_ids = array_diff_assoc( $all_ids, array_unique( $all_ids ) );
			if ( ! empty( $duplicate_ids ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of duplicate IDs */
					__( '%d duplicate meta box IDs detected (causes display conflicts)', 'wpshadow' ),
					count( $duplicate_ids )
				);
			}
		}

		// Check for conflicting nonce fields.
		if ( ! empty( $wp_meta_boxes ) ) {
			$nonce_conflicts = 0;
			$nonce_fields = array();

			foreach ( $wp_meta_boxes as $post_type => $contexts ) {
				foreach ( $contexts as $context => $priorities ) {
					foreach ( $priorities as $priority => $boxes ) {
						foreach ( $boxes as $box_id => $box ) {
							// Detect if meta box adds nonce field.
							if ( is_string( $box['callback'] ) && strpos( $box['callback'], 'wp_nonce_field' ) !== false ) {
								$nonce_fields[] = $box_id;
							}
						}
					}
				}
			}

			if ( count( $nonce_fields ) > 20 ) {
				$issues[] = sprintf(
					/* translators: %d: number of nonce fields */
					__( '%d meta boxes add nonce fields (excessive, may cause save conflicts)', 'wpshadow' ),
					count( $nonce_fields )
				);
			}
		}

		// Check for meta box callbacks that don't exist.
		if ( ! empty( $wp_meta_boxes ) ) {
			$invalid_callbacks = 0;

			foreach ( $wp_meta_boxes as $post_type => $contexts ) {
				foreach ( $contexts as $context => $priorities ) {
					foreach ( $priorities as $priority => $boxes ) {
						foreach ( $boxes as $box_id => $box ) {
							$callback = $box['callback'];
							if ( is_string( $callback ) && ! function_exists( $callback ) ) {
								++$invalid_callbacks;
							} elseif ( is_array( $callback ) && is_string( $callback[0] ) && ! class_exists( $callback[0] ) ) {
								++$invalid_callbacks;
							}
						}
					}
				}
			}

			if ( $invalid_callbacks > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of invalid callbacks */
					__( '%d meta boxes have invalid callbacks (will cause errors)', 'wpshadow' ),
					$invalid_callbacks
				);
			}
		}

		// Check for style/script conflicts.
		global $wp_scripts, $wp_styles;

		$meta_box_scripts = array();
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( preg_match( '/(acf|meta-?box|cmb|pods|carbon[-_]fields)/i', $handle ) ) {
					$meta_box_scripts[] = $handle;
				}
			}
		}

		if ( count( $meta_box_scripts ) > 15 ) {
			$issues[] = sprintf(
				/* translators: %d: number of meta box scripts */
				__( '%d meta box plugin scripts enqueued (may cause conflicts or slow admin)', 'wpshadow' ),
				count( $meta_box_scripts )
			);
		}

		// Check for conflicting jQuery versions.
		if ( ! empty( $wp_scripts->registered ) ) {
			$jquery_versions = array();
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( strpos( $handle, 'jquery' ) !== false && isset( $script->ver ) ) {
					$jquery_versions[ $handle ] = $script->ver;
				}
			}

			if ( count( array_unique( $jquery_versions ) ) > 2 ) {
				$issues[] = __( 'Multiple jQuery versions detected (meta box plugins may conflict)', 'wpshadow' );
			}
		}

		// Check for excessive save_post hooks (indicates conflict potential).
		$save_post_hooks = $GLOBALS['wp_filter']['save_post'] ?? null;
		if ( $save_post_hooks && count( $save_post_hooks ) > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of save_post hooks */
				__( '%d save_post hooks registered (meta box plugins may conflict during save)', 'wpshadow' ),
				count( $save_post_hooks )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/meta-box-plugin-conflicts',
			);
		}

		return null;
	}
}
