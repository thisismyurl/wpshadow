<?php
/**
 * Customization Audit & Risk Assessment System
 *
 * Identifies non-standard customizations and unique configurations.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customization Audit Manager Class
 */
class WPSHADOW_Customization_Audit {

	/**
	 * Audit reports option key.
	 */
	private const REPORTS_KEY = 'wpshadow_customization_audit_reports';

	/**
	 * Risk level constants.
	 */
	private const RISK_LOW    = 'low';
	private const RISK_MEDIUM = 'medium';
	private const RISK_HIGH   = 'high';

	/**
	 * Initialize Customization Audit system.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'wp_ajax_WPSHADOW_generate_customization_audit', array( __CLASS__, 'handle_audit_generation' ) );
		add_action( 'wp_ajax_WPSHADOW_export_customization_audit', array( __CLASS__, 'handle_audit_export' ) );
	}

	/**
	 * Register admin menu for customization audit.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_submenu_page(
			null, // Hidden from menu - accessible via QuickLinks
			__( 'Customization Audit', 'wpshadow' ),
			__( 'Customization Audit', 'wpshadow' ),
			'manage_options',
			'wps-customization-audit',
			array( __CLASS__, 'render_audit_page' )
		);
	}

	/**
	 * Generate comprehensive customization audit.
	 *
	 * @return array Audit report data.
	 */
	public static function generate_audit(): array {
		$report = array(
			'id'                    => wp_generate_uuid4(),
			'timestamp'             => time(),
			'site_url'              => get_site_url(),
			'custom_post_types'     => array(),
			'custom_taxonomies'     => array(),
			'custom_shortcodes'     => array(),
			'custom_meta_fields'    => array(),
			'custom_hooks'          => array(),
			'custom_tables'         => array(),
			'wp_config_mods'        => array(),
			'theme_customizations'  => array(),
			'plugin_customizations' => array(),
			'risk_assessment'       => array(),
			'summary'               => array(),
		);

		// Detect custom post types.
		$report['custom_post_types'] = self::detect_custom_post_types();

		// Detect custom taxonomies.
		$report['custom_taxonomies'] = self::detect_custom_taxonomies();

		// Detect custom shortcodes.
		$report['custom_shortcodes'] = self::detect_custom_shortcodes();

		// Detect custom meta fields.
		$report['custom_meta_fields'] = self::detect_custom_meta_fields();

		// Detect custom hooks/filters.
		$report['custom_hooks'] = self::detect_custom_hooks();

		// Detect custom database tables.
		$report['custom_tables'] = self::detect_custom_tables();

		// Detect wp-config customizations.
		$report['wp_config_mods'] = self::detect_wp_config_customizations();

		// Detect theme customizations.
		$report['theme_customizations'] = self::detect_theme_customizations();

		// Detect plugin customizations.
		$report['plugin_customizations'] = self::detect_plugin_customizations();

		// Calculate risk assessment.
		$report['risk_assessment'] = self::calculate_risk_assessment( $report );

		// Generate summary.
		$report['summary'] = self::generate_summary( $report );

		// Store report.
		self::store_report( $report );

		return $report;
	}

	/**
	 * Detect custom post types (excluding WordPress built-ins).
	 *
	 * @return array Custom post types with metadata.
	 */
	private static function detect_custom_post_types(): array {
		$custom_types = array();
		$post_types   = get_post_types( array( '_builtin' => false ), 'objects' );

		foreach ( $post_types as $slug => $post_type ) {
			$location = self::find_registration_location( 'post_type', $slug );
			$count    = wp_count_posts( $slug );
			$total    = 0;

			if ( $count ) {
				foreach ( $count as $status => $num ) {
					$total += (int) $num;
				}
			}

			$custom_types[] = array(
				'name'        => $post_type->label,
				'slug'        => $slug,
				'location'    => $location,
				'public'      => $post_type->public,
				'count'       => $total,
				'risk_level'  => self::assess_post_type_risk( $slug, $total, $location ),
				'description' => $post_type->description ?: __( 'No description', 'wpshadow' ),
			);
		}

		return $custom_types;
	}

	/**
	 * Detect custom taxonomies (excluding WordPress built-ins).
	 *
	 * @return array Custom taxonomies with metadata.
	 */
	private static function detect_custom_taxonomies(): array {
		$custom_taxonomies = array();
		$taxonomies        = get_taxonomies( array( '_builtin' => false ), 'objects' );

		foreach ( $taxonomies as $slug => $taxonomy ) {
			$location   = self::find_registration_location( 'taxonomy', $slug );
			$term_count = wp_count_terms(
				array(
					'taxonomy'   => $slug,
					'hide_empty' => false,
				)
			);

			$custom_taxonomies[] = array(
				'name'        => $taxonomy->label,
				'slug'        => $slug,
				'location'    => $location,
				'public'      => $taxonomy->public,
				'count'       => is_wp_error( $term_count ) ? 0 : (int) $term_count,
				'risk_level'  => self::assess_taxonomy_risk( $slug, $location ),
				'object_type' => implode( ', ', $taxonomy->object_type ),
			);
		}

		return $custom_taxonomies;
	}

	/**
	 * Detect custom shortcodes.
	 *
	 * @return array Custom shortcodes with metadata.
	 */
	private static function detect_custom_shortcodes(): array {
		global $shortcode_tags;

		$custom_shortcodes = array();
		$wp_defaults       = array(
			'caption',
			'gallery',
			'playlist',
			'audio',
			'video',
			'embed',
		);

		if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) {
			return $custom_shortcodes;
		}

		foreach ( $shortcode_tags as $tag => $callback ) {
			if ( in_array( $tag, $wp_defaults, true ) ) {
				continue;
			}

			$location = self::find_callback_location( $callback );
			$usage    = self::count_shortcode_usage( $tag );

			$custom_shortcodes[] = array(
				'tag'        => $tag,
				'location'   => $location,
				'usage'      => $usage,
				'risk_level' => self::assess_shortcode_risk( $tag, $usage, $location ),
			);
		}

		return $custom_shortcodes;
	}

	/**
	 * Detect custom meta fields.
	 *
	 * @return array Custom meta fields with metadata.
	 */
	private static function detect_custom_meta_fields(): array {
		global $wpdb;

		$custom_meta = array();

		// Get unique meta keys from postmeta.
		$meta_keys = $wpdb->get_col(
			"SELECT DISTINCT meta_key 
			FROM {$wpdb->postmeta} 
			WHERE meta_key NOT LIKE '\_%' 
			LIMIT 100"
		);

		foreach ( $meta_keys as $key ) {
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
					$key
				)
			);

			$custom_meta[] = array(
				'key'        => $key,
				'count'      => $count,
				'type'       => 'post',
				'risk_level' => self::RISK_LOW,
			);
		}

		return $custom_meta;
	}

	/**
	 * Detect custom hooks/filters applied.
	 *
	 * @return array Custom hooks with metadata.
	 */
	private static function detect_custom_hooks(): array {
		global $wp_filter;

		$custom_hooks = array();
		$common_hooks = array(
			'init',
			'wp_enqueue_scripts',
			'admin_enqueue_scripts',
			'wp_head',
			'wp_footer',
			'admin_menu',
			'the_content',
			'the_title',
		);

		if ( empty( $wp_filter ) ) {
			return $custom_hooks;
		}

		$count = 0;
		foreach ( $wp_filter as $hook_name => $hook ) {
			if ( ! in_array( $hook_name, $common_hooks, true ) ) {
				continue;
			}

			if ( $count >= 20 ) {
				break;
			}

			$callbacks = $hook->callbacks ?? array();
			$total     = 0;

			foreach ( $callbacks as $priority => $functions ) {
				$total += count( $functions );
			}

			if ( $total > 0 ) {
				$custom_hooks[] = array(
					'hook'       => $hook_name,
					'count'      => $total,
					'risk_level' => self::RISK_LOW,
				);
				++$count;
			}
		}

		return $custom_hooks;
	}

	/**
	 * Detect custom database tables.
	 *
	 * @return array Custom tables with metadata.
	 */
	private static function detect_custom_tables(): array {
		global $wpdb;

		$custom_tables = array();
		$all_tables    = $wpdb->get_col( 'SHOW TABLES' );
		$wp_prefix     = $wpdb->prefix;

		// WordPress core tables.
		$core_tables = array(
			$wp_prefix . 'commentmeta',
			$wp_prefix . 'comments',
			$wp_prefix . 'links',
			$wp_prefix . 'options',
			$wp_prefix . 'postmeta',
			$wp_prefix . 'posts',
			$wp_prefix . 'term_relationships',
			$wp_prefix . 'term_taxonomy',
			$wp_prefix . 'termmeta',
			$wp_prefix . 'terms',
			$wp_prefix . 'usermeta',
			$wp_prefix . 'users',
		);

		foreach ( $all_tables as $table ) {
			if ( ! str_starts_with( $table, $wp_prefix ) ) {
				continue;
			}

			if ( in_array( $table, $core_tables, true ) ) {
				continue;
			}

			$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );

			$custom_tables[] = array(
				'name'       => $table,
				'rows'       => (int) $row_count,
				'risk_level' => self::assess_table_risk( $table, (int) $row_count ),
			);
		}

		return $custom_tables;
	}

	/**
	 * Detect wp-config.php customizations.
	 *
	 * @return array wp-config customizations.
	 */
	private static function detect_wp_config_customizations(): array {
		$customizations = array();

		// Check for custom database prefix.
		global $wpdb;
		if ( $wpdb->prefix !== 'wp_' ) {
			$customizations[] = array(
				'type'        => 'Custom Database Prefix',
				'value'       => $wpdb->prefix,
				'risk_level'  => self::RISK_LOW,
				'description' => __( 'Custom database table prefix', 'wpshadow' ),
			);
		}

		// Check for debug mode.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$customizations[] = array(
				'type'        => 'Debug Mode',
				'value'       => 'Enabled',
				'risk_level'  => self::RISK_MEDIUM,
				'description' => __( 'Debug mode is enabled', 'wpshadow' ),
			);
		}

		// Check for custom content directory.
		if ( defined( 'WP_CONTENT_DIR' ) && WP_CONTENT_DIR !== ABSPATH . 'wp-content' ) {
			$customizations[] = array(
				'type'        => 'Custom Content Directory',
				'value'       => WP_CONTENT_DIR,
				'risk_level'  => self::RISK_MEDIUM,
				'description' => __( 'Custom wp-content directory location', 'wpshadow' ),
			);
		}

		// Check for memory limit customization.
		if ( defined( 'WP_MEMORY_LIMIT' ) ) {
			$customizations[] = array(
				'type'        => 'Custom Memory Limit',
				'value'       => WP_MEMORY_LIMIT,
				'risk_level'  => self::RISK_LOW,
				'description' => __( 'Custom PHP memory limit', 'wpshadow' ),
			);
		}

		return $customizations;
	}

	/**
	 * Detect theme customizations.
	 *
	 * @return array Theme customizations.
	 */
	private static function detect_theme_customizations(): array {
		$customizations = array();
		$theme          = wp_get_theme();

		// Check if using child theme.
		if ( is_child_theme() ) {
			$child_theme_path = get_stylesheet_directory();
			$functions_file   = $child_theme_path . '/functions.php';

			$lines = 0;
			if ( file_exists( $functions_file ) ) {
				$lines = count( file( $functions_file ) );
			}

			$customizations[] = array(
				'type'        => 'Child Theme',
				'name'        => $theme->get( 'Name' ),
				'location'    => $child_theme_path,
				'code_lines'  => $lines,
				'risk_level'  => $lines > 200 ? self::RISK_HIGH : ( $lines > 50 ? self::RISK_MEDIUM : self::RISK_LOW ),
				'description' => sprintf(
					/* translators: %d: number of lines */
					__( 'Child theme with %d lines in functions.php', 'wpshadow' ),
					$lines
				),
			);
		}

		// Check for custom page templates.
		$templates = wp_get_theme()->get_page_templates();
		if ( ! empty( $templates ) ) {
			$customizations[] = array(
				'type'        => 'Custom Page Templates',
				'count'       => count( $templates ),
				'templates'   => array_keys( $templates ),
				'risk_level'  => self::RISK_LOW,
				'description' => sprintf(
					/* translators: %d: number of templates */
					__( '%d custom page templates', 'wpshadow' ),
					count( $templates )
				),
			);
		}

		return $customizations;
	}

	/**
	 * Detect plugin customizations.
	 *
	 * @return array Plugin customizations.
	 */
	private static function detect_plugin_customizations(): array {
		$customizations = array();

		// Check for mu-plugins.
		$mu_plugins = wp_get_mu_plugins();
		if ( ! empty( $mu_plugins ) ) {
			$customizations[] = array(
				'type'        => 'Must-Use Plugins',
				'count'       => count( $mu_plugins ),
				'plugins'     => array_keys( $mu_plugins ),
				'risk_level'  => self::RISK_MEDIUM,
				'description' => sprintf(
					/* translators: %d: number of mu-plugins */
					__( '%d must-use plugins installed', 'wpshadow' ),
					count( $mu_plugins )
				),
			);
		}

		// Check for dropins.
		$dropins        = _get_dropins();
		$active_dropins = array();

		foreach ( $dropins as $file => $dropin ) {
			if ( file_exists( WP_CONTENT_DIR . '/' . $file ) ) {
				$active_dropins[] = $file;
			}
		}

		if ( ! empty( $active_dropins ) ) {
			$customizations[] = array(
				'type'        => 'Drop-ins',
				'count'       => count( $active_dropins ),
				'dropins'     => $active_dropins,
				'risk_level'  => self::RISK_HIGH,
				'description' => sprintf(
					/* translators: %d: number of drop-ins */
					__( '%d drop-in files detected', 'wpshadow' ),
					count( $active_dropins )
				),
			);
		}

		return $customizations;
	}

	/**
	 * Calculate overall risk assessment.
	 *
	 * @param array $report Audit report data.
	 * @return array Risk assessment.
	 */
	private static function calculate_risk_assessment( array $report ): array {
		$total_customizations = 0;
		$high_risk_count      = 0;
		$medium_risk_count    = 0;
		$low_risk_count       = 0;

		// Count customizations by risk level.
		$sections = array(
			'custom_post_types',
			'custom_taxonomies',
			'custom_shortcodes',
			'custom_tables',
			'wp_config_mods',
			'theme_customizations',
			'plugin_customizations',
		);

		foreach ( $sections as $section ) {
			if ( empty( $report[ $section ] ) ) {
				continue;
			}

			foreach ( $report[ $section ] as $item ) {
				++$total_customizations;

				$risk = $item['risk_level'] ?? self::RISK_LOW;
				if ( $risk === self::RISK_HIGH ) {
					++$high_risk_count;
				} elseif ( $risk === self::RISK_MEDIUM ) {
					++$medium_risk_count;
				} else {
					++$low_risk_count;
				}
			}
		}

		// Determine overall complexity.
		$complexity = 'simple';
		if ( $total_customizations >= 6 ) {
			$complexity = 'complex';
		} elseif ( $total_customizations >= 3 ) {
			$complexity = 'moderate';
		}

		return array(
			'total_customizations' => $total_customizations,
			'high_risk_count'      => $high_risk_count,
			'medium_risk_count'    => $medium_risk_count,
			'low_risk_count'       => $low_risk_count,
			'complexity'           => $complexity,
			'average_site'         => '2-3',
			'recommendation'       => self::get_recommendation( $complexity, $high_risk_count ),
		);
	}

	/**
	 * Generate audit summary.
	 *
	 * @param array $report Audit report data.
	 * @return array Summary data.
	 */
	private static function generate_summary( array $report ): array {
		return array(
			'site_url'             => $report['site_url'],
			'audit_date'           => gmdate( 'Y-m-d H:i:s', $report['timestamp'] ),
			'custom_post_types'    => count( $report['custom_post_types'] ),
			'custom_taxonomies'    => count( $report['custom_taxonomies'] ),
			'custom_shortcodes'    => count( $report['custom_shortcodes'] ),
			'custom_tables'        => count( $report['custom_tables'] ),
			'total_customizations' => $report['risk_assessment']['total_customizations'],
			'complexity'           => $report['risk_assessment']['complexity'],
		);
	}

	/**
	 * Store audit report.
	 *
	 * @param array $report Audit report data.
	 * @return void
	 */
	private static function store_report( array $report ): void {
		$reports                  = get_option( self::REPORTS_KEY, array() );
		$reports[ $report['id'] ] = $report;

		// Keep last 5 reports.
		if ( count( $reports ) > 5 ) {
			$reports = array_slice( $reports, -5, 5, true );
		}

		update_option( self::REPORTS_KEY, $reports );
	}

	/**
	 * Get recommendation based on complexity and risk.
	 *
	 * @param string $complexity Complexity level.
	 * @param int    $high_risk_count Number of high-risk items.
	 * @return string Recommendation text.
	 */
	private static function get_recommendation( string $complexity, int $high_risk_count ): string {
		if ( $high_risk_count > 2 ) {
			return __( 'Your site has multiple high-risk customizations. Consider professional maintenance and thorough testing before updates.', 'wpshadow' );
		}

		if ( $complexity === 'complex' ) {
			return __( 'Your site is more customized than average. Recommend careful update testing and professional maintenance.', 'wpshadow' );
		}

		if ( $complexity === 'moderate' ) {
			return __( 'Your site has moderate customizations. Test updates in staging environment before applying to production.', 'wpshadow' );
		}

		return __( 'Your site has minimal customizations. Standard update procedures should be safe.', 'wpshadow' );
	}

	/**
	 * Find registration location for post type or taxonomy.
	 *
	 * @param string $type Type of registration (post_type or taxonomy).
	 * @param string $slug Slug to find.
	 * @return string Location description.
	 */
	private static function find_registration_location( string $type, string $slug ): string {
		// Try to determine from active plugins and theme.
		$active_plugins = get_option( 'active_plugins', array() );
		$theme          = wp_get_theme();

		// This is a simplified approach - full implementation would require more sophisticated detection.
		return sprintf(
			/* translators: %s: type slug */
			__( 'Registered by plugin or theme (slug: %s)', 'wpshadow' ),
			$slug
		);
	}

	/**
	 * Find callback location.
	 *
	 * @param mixed $callback Callback to analyze.
	 * @return string Location description.
	 */
	private static function find_callback_location( $callback ): string {
		if ( is_string( $callback ) ) {
			return sprintf(
				/* translators: %s: function name */
				__( 'Function: %s', 'wpshadow' ),
				$callback
			);
		}

		if ( is_array( $callback ) && count( $callback ) === 2 ) {
			$class = is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0];
			return sprintf(
				/* translators: 1: class name, 2: method name */
				__( 'Method: %1$s::%2$s', 'wpshadow' ),
				$class,
				$callback[1]
			);
		}

		return __( 'Unknown location', 'wpshadow' );
	}

	/**
	 * Count shortcode usage in content.
	 *
	 * @param string $tag Shortcode tag.
	 * @return int Usage count.
	 */
	private static function count_shortcode_usage( string $tag ): int {
		global $wpdb;

		$pattern = '%[' . $tag . '%';
		$count   = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_content LIKE %s 
				AND post_status = 'publish'",
				$pattern
			)
		);

		return $count;
	}

	/**
	 * Assess post type risk level.
	 *
	 * @param string $slug Post type slug.
	 * @param int    $count Number of posts.
	 * @param string $location Registration location.
	 * @return string Risk level.
	 */
	private static function assess_post_type_risk( string $slug, int $count, string $location ): string {
		if ( $count > 100 ) {
			return self::RISK_HIGH;
		}

		if ( $count > 10 ) {
			return self::RISK_MEDIUM;
		}

		return self::RISK_LOW;
	}

	/**
	 * Assess taxonomy risk level.
	 *
	 * @param string $slug Taxonomy slug.
	 * @param string $location Registration location.
	 * @return string Risk level.
	 */
	private static function assess_taxonomy_risk( string $slug, string $location ): string {
		return self::RISK_LOW;
	}

	/**
	 * Assess shortcode risk level.
	 *
	 * @param string $tag Shortcode tag.
	 * @param int    $usage Usage count.
	 * @param string $location Callback location.
	 * @return string Risk level.
	 */
	private static function assess_shortcode_risk( string $tag, int $usage, string $location ): string {
		if ( $usage > 10 ) {
			return self::RISK_HIGH;
		}

		if ( $usage > 0 ) {
			return self::RISK_MEDIUM;
		}

		return self::RISK_LOW;
	}

	/**
	 * Assess table risk level.
	 *
	 * @param string $table Table name.
	 * @param int    $rows Row count.
	 * @return string Risk level.
	 */
	private static function assess_table_risk( string $table, int $rows ): string {
		if ( $rows > 10000 ) {
			return self::RISK_HIGH;
		}

		if ( $rows > 100 ) {
			return self::RISK_MEDIUM;
		}

		return self::RISK_LOW;
	}

	/**
	 * Handle AJAX audit generation.
	 *
	 * @return void
	 */
	public static function handle_audit_generation(): void {
		check_ajax_referer( 'wp_ajax', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You don\'t have permission to do that', 'wpshadow' ) );
		}

		$report = self::generate_audit();

		wp_send_json_success(
			array(
				'message' => __( 'Audit generated', 'wpshadow' ),
				'report'  => $report,
			)
		);
	}

	/**
	 * Handle AJAX audit export.
	 *
	 * @return void
	 */
	public static function handle_audit_export(): void {
		check_ajax_referer( 'wp_ajax', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You don\'t have permission to do that', 'wpshadow' ) );
		}

		$report_id = \WPShadow\WPSHADOW_get_post_text( 'report_id' );
		$format    = \WPShadow\WPSHADOW_get_post_text( 'format', 'json' );

		$reports = get_option( self::REPORTS_KEY, array() );
		$report  = $reports[ $report_id ] ?? null;

		if ( ! $report ) {
			wp_send_json_error( __( 'Report not found', 'wpshadow' ) );
		}

		$export_data = self::export_report( $report, $format );

		wp_send_json_success(
			array(
				'data'     => $export_data,
				'filename' => sprintf( 'customization-audit-%s.%s', gmdate( 'Y-m-d', $report['timestamp'] ), $format ),
			)
		);
	}

	/**
	 * Export report in specified format.
	 *
	 * @param array  $report Audit report data.
	 * @param string $format Export format (json, csv, or txt).
	 * @return string Exported data.
	 */
	private static function export_report( array $report, string $format ): string {
		switch ( $format ) {
			case 'csv':
				return self::export_as_csv( $report );
			case 'txt':
				return self::export_as_text( $report );
			case 'json':
			default:
				return wp_json_encode( $report, JSON_PRETTY_PRINT );
		}
	}

	/**
	 * Export report as CSV.
	 *
	 * @param array $report Audit report data.
	 * @return string CSV data.
	 */
	private static function export_as_csv( array $report ): string {
		$csv = "Type,Name,Risk Level,Details\n";

		// Custom Post Types.
		foreach ( $report['custom_post_types'] as $item ) {
			$csv .= sprintf(
				'"%s","%s","%s","%s"\n',
				'Custom Post Type',
				$item['name'],
				$item['risk_level'],
				sprintf( 'Count: %d, Location: %s', $item['count'], $item['location'] )
			);
		}

		// Custom Taxonomies.
		foreach ( $report['custom_taxonomies'] as $item ) {
			$csv .= sprintf(
				'"%s","%s","%s","%s"\n',
				'Custom Taxonomy',
				$item['name'],
				$item['risk_level'],
				sprintf( 'Count: %d', $item['count'] )
			);
		}

		// Custom Shortcodes.
		foreach ( $report['custom_shortcodes'] as $item ) {
			$csv .= sprintf(
				'"%s","[%s]","%s","%s"\n',
				'Custom Shortcode',
				$item['tag'],
				$item['risk_level'],
				sprintf( 'Usage: %d pages', $item['usage'] )
			);
		}

		return $csv;
	}

	/**
	 * Export report as plain text.
	 *
	 * @param array $report Audit report data.
	 * @return string Plain text data.
	 */
	private static function export_as_text( array $report ): string {
		$text  = sprintf( "Customization Audit - %s\n", $report['site_url'] );
		$text .= str_repeat( '=', 60 ) . "\n\n";
		$text .= sprintf( "Generated: %s\n\n", gmdate( 'Y-m-d H:i:s', $report['timestamp'] ) );

		$text .= sprintf(
			"🎯 Unique to Your Site: %d customizations\n\n",
			$report['risk_assessment']['total_customizations']
		);

		// Custom Post Types.
		if ( ! empty( $report['custom_post_types'] ) ) {
			$text .= "CUSTOM POST TYPES:\n";
			$text .= str_repeat( '-', 60 ) . "\n";
			foreach ( $report['custom_post_types'] as $i => $item ) {
				$text .= sprintf(
					"%d. %s (%s)\n   Count: %d posts\n   Risk Level: %s\n   Location: %s\n\n",
					$i + 1,
					$item['name'],
					$item['slug'],
					$item['count'],
					strtoupper( $item['risk_level'] ),
					$item['location']
				);
			}
			$text .= "\n";
		}

		// Custom Shortcodes.
		if ( ! empty( $report['custom_shortcodes'] ) ) {
			$text .= "CUSTOM SHORTCODES:\n";
			$text .= str_repeat( '-', 60 ) . "\n";
			foreach ( $report['custom_shortcodes'] as $i => $item ) {
				$text .= sprintf(
					"%d. [%s]\n   Used on: %d pages\n   Risk Level: %s\n   Location: %s\n\n",
					$i + 1,
					$item['tag'],
					$item['usage'],
					strtoupper( $item['risk_level'] ),
					$item['location']
				);
			}
			$text .= "\n";
		}

		// Risk Assessment.
		$text .= "📊 RISK ASSESSMENT:\n";
		$text .= str_repeat( '-', 60 ) . "\n";
		$text .= sprintf( "Complexity: %s\n", strtoupper( $report['risk_assessment']['complexity'] ) );
		$text .= sprintf( "High Risk Items: %d\n", $report['risk_assessment']['high_risk_count'] );
		$text .= sprintf( "Medium Risk Items: %d\n", $report['risk_assessment']['medium_risk_count'] );
		$text .= sprintf( "Low Risk Items: %d\n\n", $report['risk_assessment']['low_risk_count'] );

		$text .= sprintf( "Recommendation:\n%s\n", $report['risk_assessment']['recommendation'] );

		return $text;
	}

	/**
	 * Render audit page.
	 *
	 * @return void
	 */
	public static function render_audit_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpshadow' ) );
		}

		$reports = get_option( self::REPORTS_KEY, array() );
		$latest  = ! empty( $reports ) ? end( $reports ) : null;

		require_once WPSHADOW_PATH . 'includes/views/customization-audit.php';
	}
}
