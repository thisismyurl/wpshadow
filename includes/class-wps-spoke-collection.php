<?php
/**
 * Spoke Collection Management System
 *
 * Manages the gamified collection of format-specific spoke plugins with
 * milestone tracking, unlock states, and progression mechanics.
 *
 * @package WPS_WP_SUPPORT_THISISMYURL
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Spoke Collection Manager
 *
 * Handles spoke status tracking, milestone detection, and progression mechanics.
 */
final class WPS_Spoke_Collection {

	/**
	 * Option key for milestone data storage.
	 */
	private const MILESTONE_OPTION = 'wps_spoke_milestones';

	/**
	 * Option key for metrics data storage.
	 */
	private const METRICS_OPTION = 'wps_spoke_metrics';

	/**
	 * Spoke definitions with metadata.
	 *
	 * @var array<string, array{name: string, class: string, description: string, icon: string, benefits: string, browser_support: int, format: string}>
	 */
	private const SPOKES = array(
		'avif' => array(
			'name'            => 'AVIF',
			'class'           => 'WPS_Module_AVIF',
			'description'     => 'Next-generation image format with superior compression',
			'icon'            => 'dashicons-format-image',
			'benefits'        => '50% smaller than JPEG, excellent quality retention',
			'browser_support' => 90,
			'format'          => 'image/avif',
		),
		'webp' => array(
			'name'            => 'WebP',
			'class'           => 'WPS_Module_WebP',
			'description'     => 'Modern image format with broad browser support',
			'icon'            => 'dashicons-format-gallery',
			'benefits'        => '30% smaller than PNG, widespread compatibility',
			'browser_support' => 95,
			'format'          => 'image/webp',
		),
		'svg'  => array(
			'name'            => 'SVG',
			'class'           => 'WPS_Module_SVG',
			'description'     => 'Scalable vector graphics for crisp logos and icons',
			'icon'            => 'dashicons-admin-customizer',
			'benefits'        => 'Infinite scalability, small file size',
			'browser_support' => 99,
			'format'          => 'image/svg+xml',
		),
		'tiff' => array(
			'name'            => 'TIFF',
			'class'           => 'WPS_Module_TIFF',
			'description'     => 'High-quality image format for professional photography',
			'icon'            => 'dashicons-camera',
			'benefits'        => 'Lossless compression, excellent for archival',
			'browser_support' => 75,
			'format'          => 'image/tiff',
		),
		'bmp'  => array(
			'name'            => 'BMP',
			'class'           => 'WPS_Module_BMP',
			'description'     => 'Uncompressed bitmap format for legacy compatibility',
			'icon'            => 'dashicons-images-alt',
			'benefits'        => 'Universal compatibility, simple format',
			'browser_support' => 98,
			'format'          => 'image/bmp',
		),
		'gif'  => array(
			'name'            => 'GIF',
			'class'           => 'WPS_Module_GIF',
			'description'     => 'Animated image format with palette-based compression',
			'icon'            => 'dashicons-format-video',
			'benefits'        => 'Animation support, wide compatibility',
			'browser_support' => 99,
			'format'          => 'image/gif',
		),
		'heic' => array(
			'name'            => 'HEIC',
			'class'           => 'WPS_Module_HEIC',
			'description'     => 'Apple\'s modern image format with excellent compression',
			'icon'            => 'dashicons-smartphone',
			'benefits'        => 'Better compression than JPEG, iOS native',
			'browser_support' => 65,
			'format'          => 'image/heic',
		),
		'raw'  => array(
			'name'            => 'RAW',
			'class'           => 'WPS_Module_RAW',
			'description'     => 'Professional camera raw format for maximum quality',
			'icon'            => 'dashicons-camera-alt',
			'benefits'        => 'Maximum quality, professional workflow support',
			'browser_support' => 50,
			'format'          => 'image/x-dcraw',
		),
	);

	/**
	 * Milestone definitions.
	 *
	 * @var array<string, array{name: string, description: string, threshold: int, reward: string}>
	 */
	private const MILESTONES = array(
		// System-wide milestones
		'first_spoke'       => array(
			'name'        => 'First Format Unlocked',
			'description' => 'Install your first spoke plugin',
			'threshold'   => 1,
			'reward'      => 'Welcome to the collection!',
		),
		'multi_master'      => array(
			'name'        => 'Multi-Format Master',
			'description' => 'Install 3 or more spoke plugins',
			'threshold'   => 3,
			'reward'      => 'Speed Boost dashboard theme unlocked',
		),
		'full_collection'   => array(
			'name'        => 'Full Collection Achieved',
			'description' => 'Install all 8 spoke plugins',
			'threshold'   => 8,
			'reward'      => 'Master Optimizer admin color scheme unlocked',
		),
		'format_expert'     => array(
			'name'        => 'Format Expert',
			'description' => 'Convert 1,000+ images across all formats',
			'threshold'   => 1000,
			'reward'      => 'Exclusive dashboard skin unlocked',
		),
		// Per-format milestones
		'first_convert'     => array(
			'name'        => 'First Conversion',
			'description' => 'Convert your first image to this format',
			'threshold'   => 1,
			'reward'      => 'Format Pioneer badge',
		),
		'format_apprentice' => array(
			'name'        => 'Format Apprentice',
			'description' => 'Convert 100 images to this format',
			'threshold'   => 100,
			'reward'      => 'Format Pioneer badge',
		),
		'format_master'     => array(
			'name'        => 'Format Master',
			'description' => 'Convert 1,000 images to this format',
			'threshold'   => 1000,
			'reward'      => 'Format Master badge',
		),
		'format_legend'     => array(
			'name'        => 'Format Legend',
			'description' => 'Convert 5,000+ images to this format',
			'threshold'   => 5000,
			'reward'      => 'Format Legend badge',
		),
	);

	/**
	 * Initialize the Spoke Collection system.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Initialize milestone tracking on activation/installation events.
		add_action( 'activated_plugin', array( __CLASS__, 'check_spoke_activation' ), 10, 2 );
		add_action( 'deactivated_plugin', array( __CLASS__, 'check_spoke_deactivation' ), 10, 2 );

		// Hook into image conversion events (to be implemented by spoke plugins).
		add_action( 'wps_image_converted', array( __CLASS__, 'track_conversion' ), 10, 3 );

		// Check for milestone unlocks periodically.
		add_action( 'wps_check_milestones', array( __CLASS__, 'check_milestone_unlocks' ) );
	}

	/**
	 * Get the status of a specific spoke.
	 *
	 * @param string $spoke Spoke identifier (e.g., 'avif', 'webp').
	 * @return array{installed: bool, active: bool, files_processed: int, space_saved: int, status: string}
	 */
	public static function get_status( string $spoke ): array {
		$spoke_slug = sanitize_key( $spoke );

		// Check if spoke is installed.
		$installed = self::is_spoke_installed( $spoke_slug );
		$active    = self::is_spoke_active( $spoke_slug );

		// Get metrics.
		$metrics = self::get_metrics( $spoke_slug );

		// Determine status state.
		$status = 'locked';
		if ( $installed ) {
			if ( $active ) {
				if ( $metrics['files_processed'] >= 1000 ) {
					$status = 'mastered';
				} else {
					$status = 'active';
				}
			} else {
				$status = 'unlocked';
			}
		}

		return array(
			'installed'       => $installed,
			'active'          => $active,
			'files_processed' => $metrics['files_processed'],
			'space_saved'     => $metrics['space_saved'],
			'status'          => $status,
		);
	}

	/**
	 * Get collection-wide progress percentage.
	 *
	 * @return int Progress from 0-100.
	 */
	public static function get_collection_progress(): int {
		$total_spokes     = count( self::SPOKES );
		$installed_spokes = 0;

		foreach ( array_keys( self::SPOKES ) as $spoke ) {
			if ( self::is_spoke_installed( $spoke ) ) {
				++$installed_spokes;
			}
		}

		if ( 0 === $total_spokes ) {
			return 0;
		}

		return (int) round( ( $installed_spokes / $total_spokes ) * 100 );
	}

	/**
	 * Get all spokes with their current status.
	 *
	 * @return array<string, array> Associative array of spoke statuses.
	 */
	public static function get_all_spokes(): array {
		$spokes = array();

		foreach ( self::SPOKES as $spoke_id => $spoke_data ) {
			$status                    = self::get_status( $spoke_id );
			$spokes[ $spoke_id ]       = array_merge( $spoke_data, $status );
			$spokes[ $spoke_id ]['id'] = $spoke_id;
		}

		return $spokes;
	}

	/**
	 * Check if a spoke is installed.
	 *
	 * @param string $spoke Spoke identifier.
	 * @return bool True if installed.
	 */
	private static function is_spoke_installed( string $spoke ): bool {
		$spoke_slug  = $spoke . '-support-thisismyurl';
		$plugin_file = $spoke_slug . '/' . $spoke_slug . '.php';

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		return isset( $plugins[ $plugin_file ] );
	}

	/**
	 * Check if a spoke is active.
	 *
	 * @param string $spoke Spoke identifier.
	 * @return bool True if active.
	 */
	private static function is_spoke_active( string $spoke ): bool {
		$spoke_slug  = $spoke . '-support-thisismyurl';
		$plugin_file = $spoke_slug . '/' . $spoke_slug . '.php';

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin_file ) || ( is_multisite() && is_plugin_active_for_network( $plugin_file ) );
	}

	/**
	 * Get metrics for a specific spoke.
	 *
	 * @param string $spoke Spoke identifier.
	 * @return array{files_processed: int, space_saved: int, quality_retention: float, processing_time: int, last_processed: int}
	 */
	private static function get_metrics( string $spoke ): array {
		$metrics = get_option( self::METRICS_OPTION, array() );

		if ( ! isset( $metrics[ $spoke ] ) ) {
			return array(
				'files_processed'   => 0,
				'space_saved'       => 0,
				'quality_retention' => 0.0,
				'processing_time'   => 0,
				'last_processed'    => 0,
			);
		}

		return $metrics[ $spoke ];
	}

	/**
	 * Update metrics for a specific spoke.
	 *
	 * @param string $spoke Spoke identifier.
	 * @param array  $data Metrics data to update.
	 * @return bool True on success.
	 */
	public static function update_metrics( string $spoke, array $data ): bool {
		$metrics = get_option( self::METRICS_OPTION, array() );

		if ( ! isset( $metrics[ $spoke ] ) ) {
			$metrics[ $spoke ] = array(
				'files_processed'   => 0,
				'space_saved'       => 0,
				'quality_retention' => 0.0,
				'processing_time'   => 0,
				'last_processed'    => 0,
			);
		}

		// Merge new data with existing metrics.
		$metrics[ $spoke ] = array_merge( $metrics[ $spoke ], $data );

		return update_option( self::METRICS_OPTION, $metrics );
	}

	/**
	 * Check for milestone unlocks across all spokes.
	 *
	 * @return array<string, array> Newly unlocked milestones.
	 */
	public static function check_milestone_unlocks(): array {
		$milestones  = get_option( self::MILESTONE_OPTION, array() );
		$new_unlocks = array();

		// Check system-wide milestones.
		$installed_count = 0;
		$total_converted = 0;

		foreach ( array_keys( self::SPOKES ) as $spoke ) {
			if ( self::is_spoke_installed( $spoke ) ) {
				++$installed_count;
			}

			$metrics          = self::get_metrics( $spoke );
			$total_converted += $metrics['files_processed'];
		}

		// First spoke milestone.
		if ( $installed_count >= 1 && empty( $milestones['first_spoke'] ) ) {
			$milestones['first_spoke']  = time();
			$new_unlocks['first_spoke'] = self::MILESTONES['first_spoke'];

			// Log achievement.
			WPS_Activity_Logger::log(
				'milestone',
				'First Format Unlocked',
				array(
					'milestone' => 'first_spoke',
					'timestamp' => time(),
				)
			);
		}

		// Multi-format master milestone.
		if ( $installed_count >= 3 && empty( $milestones['multi_master'] ) ) {
			$milestones['multi_master']  = time();
			$new_unlocks['multi_master'] = self::MILESTONES['multi_master'];

			WPS_Activity_Logger::log(
				'milestone',
				'Multi-Format Master',
				array(
					'milestone' => 'multi_master',
					'timestamp' => time(),
					'count'     => $installed_count,
				)
			);
		}

		// Full collection milestone.
		if ( $installed_count >= 8 && empty( $milestones['full_collection'] ) ) {
			$milestones['full_collection']  = time();
			$new_unlocks['full_collection'] = self::MILESTONES['full_collection'];

			WPS_Activity_Logger::log(
				'milestone',
				'Full Collection Achieved',
				array(
					'milestone' => 'full_collection',
					'timestamp' => time(),
				)
			);
		}

		// Format expert milestone.
		if ( $total_converted >= 1000 && empty( $milestones['format_expert'] ) ) {
			$milestones['format_expert']  = time();
			$new_unlocks['format_expert'] = self::MILESTONES['format_expert'];

			WPS_Activity_Logger::log(
				'milestone',
				'Format Expert',
				array(
					'milestone' => 'format_expert',
					'timestamp' => time(),
					'total'     => $total_converted,
				)
			);
		}

		// Check per-format milestones.
		foreach ( array_keys( self::SPOKES ) as $spoke ) {
			$metrics   = self::get_metrics( $spoke );
			$converted = $metrics['files_processed'];

			$milestone_key = $spoke . '_first_convert';
			if ( $converted >= 1 && empty( $milestones[ $milestone_key ] ) ) {
				$milestones[ $milestone_key ]  = time();
				$new_unlocks[ $milestone_key ] = array_merge(
					self::MILESTONES['first_convert'],
					array( 'spoke' => $spoke )
				);
			}

			$milestone_key = $spoke . '_apprentice';
			if ( $converted >= 100 && empty( $milestones[ $milestone_key ] ) ) {
				$milestones[ $milestone_key ]  = time();
				$new_unlocks[ $milestone_key ] = array_merge(
					self::MILESTONES['format_apprentice'],
					array( 'spoke' => $spoke )
				);
			}

			$milestone_key = $spoke . '_master';
			if ( $converted >= 1000 && empty( $milestones[ $milestone_key ] ) ) {
				$milestones[ $milestone_key ]  = time();
				$new_unlocks[ $milestone_key ] = array_merge(
					self::MILESTONES['format_master'],
					array( 'spoke' => $spoke )
				);
			}

			$milestone_key = $spoke . '_legend';
			if ( $converted >= 5000 && empty( $milestones[ $milestone_key ] ) ) {
				$milestones[ $milestone_key ]  = time();
				$new_unlocks[ $milestone_key ] = array_merge(
					self::MILESTONES['format_legend'],
					array( 'spoke' => $spoke )
				);
			}
		}

		// Save milestone data.
		update_option( self::MILESTONE_OPTION, $milestones );

		return $new_unlocks;
	}

	/**
	 * Track a spoke plugin activation.
	 *
	 * @param string $plugin Plugin file.
	 * @param bool   $network_wide Whether network-wide activation.
	 * @return void
	 */
	public static function check_spoke_activation( string $plugin, bool $network_wide = false ): void {
		// Check if this is a spoke plugin.
		foreach ( array_keys( self::SPOKES ) as $spoke ) {
			$spoke_slug = $spoke . '-support-thisismyurl';
			if ( str_contains( $plugin, $spoke_slug ) ) {
				// Check for milestone unlocks.
				$unlocks = self::check_milestone_unlocks();

				// Log activation.
				WPS_Activity_Logger::log(
					'spoke_activation',
					sprintf( '%s Spoke Activated', strtoupper( $spoke ) ),
					array(
						'spoke'        => $spoke,
						'network_wide' => $network_wide,
						'timestamp'    => time(),
					)
				);

				break;
			}
		}
	}

	/**
	 * Track a spoke plugin deactivation.
	 *
	 * @param string $plugin Plugin file.
	 * @param bool   $network_wide Whether network-wide deactivation.
	 * @return void
	 */
	public static function check_spoke_deactivation( string $plugin, bool $network_wide = false ): void {
		// Check if this is a spoke plugin.
		foreach ( array_keys( self::SPOKES ) as $spoke ) {
			$spoke_slug = $spoke . '-support-thisismyurl';
			if ( str_contains( $plugin, $spoke_slug ) ) {
				// Log deactivation.
				WPS_Activity_Logger::log(
					'spoke_deactivation',
					sprintf( '%s Spoke Deactivated', strtoupper( $spoke ) ),
					array(
						'spoke'        => $spoke,
						'network_wide' => $network_wide,
						'timestamp'    => time(),
					)
				);

				break;
			}
		}
	}

	/**
	 * Track an image conversion event.
	 *
	 * @param string $spoke Spoke identifier.
	 * @param int    $attachment_id Attachment ID.
	 * @param array  $conversion_data Conversion metadata.
	 * @return void
	 */
	public static function track_conversion( string $spoke, int $attachment_id, array $conversion_data ): void {
		$metrics = self::get_metrics( $spoke );

		// Increment files processed.
		++$metrics['files_processed'];

		// Add space saved if provided.
		if ( isset( $conversion_data['space_saved'] ) ) {
			$metrics['space_saved'] += (int) $conversion_data['space_saved'];
		}

		// Update quality retention (running average).
		if ( isset( $conversion_data['quality_retention'] ) ) {
			$count                        = $metrics['files_processed'];
			$metrics['quality_retention'] = (
				( $metrics['quality_retention'] * ( $count - 1 ) ) +
				(float) $conversion_data['quality_retention']
			) / $count;
		}

		// Update processing time (running average).
		if ( isset( $conversion_data['processing_time'] ) ) {
			$count                      = $metrics['files_processed'];
			$metrics['processing_time'] = (int) (
				( $metrics['processing_time'] * ( $count - 1 ) ) +
				(int) $conversion_data['processing_time']
			) / $count;
		}

		// Update last processed timestamp.
		$metrics['last_processed'] = time();

		// Save metrics.
		self::update_metrics( $spoke, $metrics );

		// Check for milestone unlocks.
		$unlocks = self::check_milestone_unlocks();

		// Trigger milestone popup if new unlocks.
		if ( ! empty( $unlocks ) ) {
			set_transient( 'wps_new_milestones', $unlocks, 300 ); // 5 minutes.
		}
	}

	/**
	 * Get all unlocked milestones.
	 *
	 * @return array<string, int> Milestone keys with unlock timestamps.
	 */
	public static function get_unlocked_milestones(): array {
		return get_option( self::MILESTONE_OPTION, array() );
	}

	/**
	 * Get collection-wide statistics.
	 *
	 * @return array{total_spokes: int, installed: int, active: int, total_files: int, total_saved: int, progress: int}
	 */
	public static function get_collection_stats(): array {
		$total_spokes = count( self::SPOKES );
		$installed    = 0;
		$active       = 0;
		$total_files  = 0;
		$total_saved  = 0;

		foreach ( array_keys( self::SPOKES ) as $spoke ) {
			if ( self::is_spoke_installed( $spoke ) ) {
				++$installed;
			}

			if ( self::is_spoke_active( $spoke ) ) {
				++$active;
			}

			$metrics      = self::get_metrics( $spoke );
			$total_files += $metrics['files_processed'];
			$total_saved += $metrics['space_saved'];
		}

		return array(
			'total_spokes' => $total_spokes,
			'installed'    => $installed,
			'active'       => $active,
			'total_files'  => $total_files,
			'total_saved'  => $total_saved,
			'progress'     => self::get_collection_progress(),
		);
	}
}
