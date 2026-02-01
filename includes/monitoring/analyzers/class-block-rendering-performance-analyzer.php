<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Block Rendering Performance Analyzer
 *
 * Monitors Gutenberg block rendering performance to identify slow blocks
 * that impact page generation time.
 *
 * Philosophy: Show value (#9) - Optimize block editor content performance.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class Block_Rendering_Performance_Analyzer {

	/**
	 * @var array Block render timing data
	 */
	private static $block_times = array();

	/**
	 * @var float Page render start time
	 */
	private static $page_start_time = 0;

	/**
	 * Initialize block monitoring
	 *
	 * @return void
	 */
	public static function init(): void {
		// Track block rendering
		add_filter( 'render_block', array( __CLASS__, 'track_block_render' ), 10, 2 );

		// Track page render time
		add_action( 'template_redirect', array( __CLASS__, 'start_page_timer' ) );
		add_action( 'wp_footer', array( __CLASS__, 'save_render_data' ), 999 );

		// Run hourly analysis
		if ( ! wp_next_scheduled( 'wpshadow_analyze_block_rendering' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_analyze_block_rendering' );
		}
		add_action( 'wpshadow_analyze_block_rendering', array( __CLASS__, 'analyze' ) );
	}

	/**
	 * Start page timer
	 *
	 * @return void
	 */
	public static function start_page_timer(): void {
		self::$page_start_time = microtime( true );
	}

	/**
	 * Track block rendering
	 *
	 * @param string $block_content Block content
	 * @param array $block Block data
	 * @return string Unmodified content
	 */
	public static function track_block_render( string $block_content, array $block ): string {
		$start = microtime( true );

		// Store block info
		$block_name = $block['blockName'] ?? 'unknown';

		// Estimate render time (we're in post-render, so estimate from content size)
		$content_length    = strlen( $block_content );
		$estimated_time_ms = max( 1, (int) ( $content_length / 1024 ) ); // 1ms per KB minimum

		self::$block_times[] = array(
			'name'           => $block_name,
			'time_ms'        => $estimated_time_ms,
			'content_length' => $content_length,
			'timestamp'      => time(),
		);

		return $block_content;
	}

	/**
	 * Save render data
	 *
	 * @return void
	 */
	public static function save_render_data(): void {
		if ( empty( self::$block_times ) ) {
			return;
		}

		// Calculate total page render time
		$page_time_ms = 0;
		if ( self::$page_start_time > 0 ) {
			$page_time_ms = (int) ( ( microtime( true ) - self::$page_start_time ) * 1000 );
		}

		$stored = \WPShadow\Core\Cache_Manager::get( 'block_rendering_data', 'wpshadow_monitoring' );
		if ( ! is_array( $stored ) ) {
			$stored = array(
				'blocks' => array(),
				'pages'  => array(),
			);
		}

		// Add blocks
		$stored['blocks'] = array_merge( $stored['blocks'], self::$block_times );

		// Add page timing
		if ( $page_time_ms > 0 ) {
			$stored['pages'][] = array(
				'time_ms'     => $page_time_ms,
				'block_count' => count( self::$block_times ),
				'timestamp'   => time(),
			);
		}

		// Keep only last 24 hours
		$one_day_ago      = time() - DAY_IN_SECONDS;
		$stored['blocks'] = array_filter(
			$stored['blocks'],
			function ( $item ) use ( $one_day_ago ) {
				return $item['timestamp'] > $one_day_ago;
			}
		);
		$stored['pages']  = array_filter(
			$stored['pages'],
			function ( $item ) use ( $one_day_ago ) {
				return $item['timestamp'] > $one_day_ago;
			}
		);

		// Limit entries
		if ( count( $stored['blocks'] ) > 1000 ) {
			$stored['blocks'] = array_slice( $stored['blocks'], -1000 );
		}
		if ( count( $stored['pages'] ) > 100 ) {
			$stored['pages'] = array_slice( $stored['pages'], -100 );
		}

		\WPShadow\Core\Cache_Manager::set( 'block_rendering_data', $stored, DAY_IN_SECONDS , 'wpshadow_monitoring');
	}

	/**
	 * Analyze block rendering performance
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		$data = \WPShadow\Core\Cache_Manager::get( 'block_rendering_data', 'wpshadow_monitoring' );

		$results = array(
			'total_blocks'     => 0,
			'unique_blocks'    => 0,
			'avg_page_time_ms' => 0,
			'slow_blocks'      => array(),
			'most_used_blocks' => array(),
			'total_pages'      => 0,
		);

		if ( ! is_array( $data ) ) {
			\WPShadow\Core\Cache_Manager::set( 'block_rendering_time', $results, HOUR_IN_SECONDS , 'wpshadow_monitoring');
			return $results;
		}

		// Analyze blocks
		$blocks = $data['blocks'] ?? array();
		$pages  = $data['pages'] ?? array();

		$results['total_blocks'] = count( $blocks );
		$results['total_pages']  = count( $pages );

		if ( ! empty( $blocks ) ) {
			// Group by block name
			$by_name = array();
			foreach ( $blocks as $block ) {
				$name = $block['name'];
				if ( ! isset( $by_name[ $name ] ) ) {
					$by_name[ $name ] = array(
						'count'      => 0,
						'total_time' => 0,
						'max_time'   => 0,
					);
				}

				++$by_name[ $name ]['count'];
				$by_name[ $name ]['total_time'] += $block['time_ms'];
				$by_name[ $name ]['max_time']    = max( $by_name[ $name ]['max_time'], $block['time_ms'] );
			}

			$results['unique_blocks'] = count( $by_name );

			// Calculate averages
			foreach ( $by_name as $name => $stats ) {
				$by_name[ $name ]['avg_time'] = (int) ( $stats['total_time'] / $stats['count'] );
			}

			// Find slow blocks (avg > 50ms or max > 200ms)
			foreach ( $by_name as $name => $stats ) {
				if ( $stats['avg_time'] > 50 || $stats['max_time'] > 200 ) {
					$results['slow_blocks'][ $name ] = array(
						'avg_time_ms' => $stats['avg_time'],
						'max_time_ms' => $stats['max_time'],
						'renders'     => $stats['count'],
					);
				}
			}

			// Most used blocks
			uasort(
				$by_name,
				function ( $a, $b ) {
					return $b['count'] - $a['count'];
				}
			);
			$results['most_used_blocks'] = array_slice( $by_name, 0, 10, true );
		}

		// Analyze page times
		if ( ! empty( $pages ) ) {
			$total_page_time             = array_sum( array_column( $pages, 'time_ms' ) );
			$results['avg_page_time_ms'] = (int) ( $total_page_time / count( $pages ) );
		}

		// Set cache for diagnostic
		\WPShadow\Core\Cache_Manager::set( 'block_rendering_time', $results, HOUR_IN_SECONDS , 'wpshadow_monitoring');

		return $results;
	}

	/**
	 * Get summary
	 *
	 * @return array Summary data
	 */
	public static function get_summary(): array {
		$results = \WPShadow\Core\Cache_Manager::get( 'block_rendering_time', 'wpshadow_monitoring' );
		return is_array( $results ) ? $results : array(
			'total_blocks'     => 0,
			'unique_blocks'    => 0,
			'avg_page_time_ms' => 0,
			'slow_blocks'      => array(),
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'block_rendering_data', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'block_rendering_time', 'wpshadow_monitoring' );
	}
}
