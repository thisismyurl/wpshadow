<?php
/**
 * WPS Environment Checker
 *
 * Validates WordPress, PHP, and server capabilities to ensure compatibility
 * and provides graceful degradation when constraints are detected.
 *
 * @package wpshadow_SUPPORT
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPSHADOW_Environment_Checker
 *
 * Checks environment compatibility and provides validation methods.
 */
class WPSHADOW_Environment_Checker {

	/**
	 * Minimum PHP version required.
	 */
	private const MIN_PHP_VERSION = '8.1.29';

	/**
	 * Minimum WordPress version required.
	 */
	private const MIN_WP_VERSION = '6.4.0';

	/**
	 * Recommended memory limit in bytes.
	 */
	private const RECOMMENDED_MEMORY_LIMIT = 256 * 1024 * 1024; // 256MB

	/**
	 * Minimum memory limit in bytes.
	 */
	private const MINIMUM_MEMORY_LIMIT = 128 * 1024 * 1024; // 128MB

	/**
	 * Recommended execution time in seconds.
	 */
	private const RECOMMENDED_EXECUTION_TIME = 60;

	/**
	 * Minimum execution time in seconds.
	 */
	private const MINIMUM_EXECUTION_TIME = 30;

	/**
	 * Required PHP extensions.
	 *
	 * @var array<string>
	 */
	private const REQUIRED_EXTENSIONS = array(
		'json',
		'mbstring',
	);

	/**
	 * Recommended PHP extensions.
	 *
	 * @var array<string>
	 */
	private const RECOMMENDED_EXTENSIONS = array(
		'openssl',
		'zip',
		'curl',
		'gd',
		'imagick',
		'exif',
	);

	/**
	 * Cached environment status.
	 *
	 * @var array<string, mixed>|null
	 */
	private static ?array $cached_status = null;

	/**
	 * Initialize environment checker.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'check_environment' ) );
		add_action( 'admin_notices', array( __CLASS__, 'display_environment_notices' ) );
	}

	/**
	 * Check environment compatibility.
	 *
	 * @return void
	 */
	public static function check_environment(): void {
		// Skip if already checked in this request.
		if ( null !== self::$cached_status ) {
			return;
		}

		// Prevent infinite recursion by marking as checking.
		static $checking = false;
		if ( $checking ) {
			return;
		}
		$checking = true;

		self::$cached_status = self::get_environment_status();

		// Log diagnostics if enabled.
		if ( self::is_diagnostic_logging_enabled() ) {
			self::log_environment_check();
		}

		$checking = false;
	}

	/**
	 * Get complete environment status.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_environment_status(): array {
		// Prevent infinite recursion.
		static $getting = false;
		if ( $getting ) {
			return array(
				'php_version'      => array(
					'current'           => PHP_VERSION,
					'meets_requirement' => true,
				),
				'wp_version'       => array(
					'current'           => $GLOBALS['wp_version'],
					'meets_requirement' => true,
				),
				'memory_limit'     => array(
					'current'       => ini_get( 'memory_limit' ),
					'current_bytes' => 0,
				),
				'execution_time'   => array( 'current' => ini_get( 'max_execution_time' ) ),
				'upload_limit'     => array( 'current' => ini_get( 'upload_max_filesize' ) ),
				'extensions'       => array(),
				'environment_type' => 'production',
				'is_compatible'    => true,
				'has_constraints'  => false,
				'checked_at'       => current_time( 'mysql' ),
			);
		}
		$getting = true;

		$result = array(
			'php_version'      => self::get_php_version_status(),
			'wp_version'       => self::get_wp_version_status(),
			'memory_limit'     => self::get_memory_limit_status(),
			'execution_time'   => self::get_execution_time_status(),
			'upload_limit'     => self::get_upload_limit_status(),
			'extensions'       => self::get_extensions_status(),
			'environment_type' => wp_get_environment_type(),
			'is_compatible'    => self::is_environment_compatible(),
			'has_constraints'  => self::has_resource_constraints(),
			'checked_at'       => current_time( 'mysql' ),
		);

		$getting = false;
		return $result;
	}

	/**
	 * Check PHP version status.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_php_version_status(): array {
		$current           = PHP_VERSION;
		$meets_requirement = version_compare( $current, self::MIN_PHP_VERSION, '>=' );

		return array(
			'current'           => $current,
			'minimum'           => self::MIN_PHP_VERSION,
			'meets_requirement' => $meets_requirement,
			'message'           => $meets_requirement
				? __( 'PHP version is compatible.', 'plugin-wpshadow' )
				: sprintf(
					/* translators: 1: Current PHP version, 2: Minimum required PHP version */
					__( 'PHP version %1$s is below the minimum required version %2$s.', 'plugin-wpshadow' ),
					$current,
					self::MIN_PHP_VERSION
				),
		);
	}

	/**
	 * Check WordPress version status.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_wp_version_status(): array {
		global $wp_version;
		$current           = $wp_version;
		$meets_requirement = version_compare( $current, self::MIN_WP_VERSION, '>=' );

		return array(
			'current'           => $current,
			'minimum'           => self::MIN_WP_VERSION,
			'meets_requirement' => $meets_requirement,
			'message'           => $meets_requirement
				? __( 'WordPress version is compatible.', 'plugin-wpshadow' )
				: sprintf(
					/* translators: 1: Current WordPress version, 2: Minimum required WordPress version */
					__( 'WordPress version %1$s is below the minimum required version %2$s.', 'plugin-wpshadow' ),
					$current,
					self::MIN_WP_VERSION
				),
		);
	}

	/**
	 * Check memory limit status.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_memory_limit_status(): array {
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = self::convert_to_bytes( $memory_limit );

		$meets_minimum     = $memory_bytes >= self::MINIMUM_MEMORY_LIMIT;
		$meets_recommended = $memory_bytes >= self::RECOMMENDED_MEMORY_LIMIT;

		$level = 'good';
		if ( ! $meets_minimum ) {
			$level = 'critical';
		} elseif ( ! $meets_recommended ) {
			$level = 'warning';
		}

		return array(
			'current'           => $memory_limit,
			'current_bytes'     => $memory_bytes,
			'minimum'           => self::format_bytes( self::MINIMUM_MEMORY_LIMIT ),
			'minimum_bytes'     => self::MINIMUM_MEMORY_LIMIT,
			'recommended'       => self::format_bytes( self::RECOMMENDED_MEMORY_LIMIT ),
			'recommended_bytes' => self::RECOMMENDED_MEMORY_LIMIT,
			'meets_minimum'     => $meets_minimum,
			'meets_recommended' => $meets_recommended,
			'level'             => $level,
			'message'           => self::get_memory_limit_message( $meets_minimum, $meets_recommended ),
		);
	}

	/**
	 * Get memory limit message.
	 *
	 * @param bool $meets_minimum Whether minimum is met.
	 * @param bool $meets_recommended Whether recommended is met.
	 * @return string
	 */
	private static function get_memory_limit_message( bool $meets_minimum, bool $meets_recommended ): string {
		if ( $meets_recommended ) {
			return __( 'Memory limit meets recommended requirements.', 'plugin-wpshadow' );
		}
		if ( $meets_minimum ) {
			return sprintf(
				/* translators: %s: Recommended memory limit */
				__( 'Memory limit meets minimum requirements but %s is recommended for optimal performance.', 'plugin-wpshadow' ),
				self::format_bytes( self::RECOMMENDED_MEMORY_LIMIT )
			);
		}
		return sprintf(
			/* translators: %s: Minimum memory limit */
			__( 'Memory limit is below the minimum required (%s). Heavy operations will be disabled.', 'plugin-wpshadow' ),
			self::format_bytes( self::MINIMUM_MEMORY_LIMIT )
		);
	}

	/**
	 * Check execution time status.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_execution_time_status(): array {
		$max_execution_time = (int) ini_get( 'max_execution_time' );

		// 0 means unlimited.
		if ( 0 === $max_execution_time ) {
			return array(
				'current'           => 0,
				'minimum'           => self::MINIMUM_EXECUTION_TIME,
				'recommended'       => self::RECOMMENDED_EXECUTION_TIME,
				'meets_minimum'     => true,
				'meets_recommended' => true,
				'level'             => 'good',
				'message'           => __( 'Execution time is unlimited.', 'plugin-wpshadow' ),
			);
		}

		$meets_minimum     = $max_execution_time >= self::MINIMUM_EXECUTION_TIME;
		$meets_recommended = $max_execution_time >= self::RECOMMENDED_EXECUTION_TIME;

		$level = 'good';
		if ( ! $meets_minimum ) {
			$level = 'critical';
		} elseif ( ! $meets_recommended ) {
			$level = 'warning';
		}

		return array(
			'current'           => $max_execution_time,
			'minimum'           => self::MINIMUM_EXECUTION_TIME,
			'recommended'       => self::RECOMMENDED_EXECUTION_TIME,
			'meets_minimum'     => $meets_minimum,
			'meets_recommended' => $meets_recommended,
			'level'             => $level,
			'message'           => self::get_execution_time_message( $max_execution_time, $meets_minimum, $meets_recommended ),
		);
	}

	/**
	 * Get execution time message.
	 *
	 * @param int  $current Current execution time.
	 * @param bool $meets_minimum Whether minimum is met.
	 * @param bool $meets_recommended Whether recommended is met.
	 * @return string
	 */
	private static function get_execution_time_message( int $current, bool $meets_minimum, bool $meets_recommended ): string {
		if ( $meets_recommended ) {
			return __( 'Execution time meets recommended requirements.', 'plugin-wpshadow' );
		}
		if ( $meets_minimum ) {
			return sprintf(
				/* translators: %d: Recommended execution time in seconds */
				__( 'Execution time meets minimum requirements but %d seconds is recommended.', 'plugin-wpshadow' ),
				self::RECOMMENDED_EXECUTION_TIME
			);
		}
		return sprintf(
			/* translators: 1: Current execution time, 2: Minimum execution time */
			__( 'Execution time (%1$ds) is below minimum (%2$ds). Long-running tasks will be batched.', 'plugin-wpshadow' ),
			$current,
			self::MINIMUM_EXECUTION_TIME
		);
	}

	/**
	 * Check upload limit status.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_upload_limit_status(): array {
		$upload_max = ini_get( 'upload_max_filesize' );
		$post_max   = ini_get( 'post_max_size' );

		$upload_bytes = self::convert_to_bytes( $upload_max );
		$post_bytes   = self::convert_to_bytes( $post_max );

		return array(
			'upload_max_filesize' => $upload_max,
			'upload_max_bytes'    => $upload_bytes,
			'post_max_size'       => $post_max,
			'post_max_bytes'      => $post_bytes,
			'effective_limit'     => min( $upload_bytes, $post_bytes ),
			'message'             => sprintf(
				/* translators: 1: Upload max filesize, 2: Post max size */
				__( 'Upload limit: %1$s (post limit: %2$s)', 'plugin-wpshadow' ),
				$upload_max,
				$post_max
			),
		);
	}

	/**
	 * Check PHP extensions status.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_extensions_status(): array {
		$required_missing    = array();
		$recommended_missing = array();

		foreach ( self::REQUIRED_EXTENSIONS as $ext ) {
			if ( ! extension_loaded( $ext ) ) {
				$required_missing[] = $ext;
			}
		}

		foreach ( self::RECOMMENDED_EXTENSIONS as $ext ) {
			if ( ! extension_loaded( $ext ) ) {
				$recommended_missing[] = $ext;
			}
		}

		$all_required_loaded = empty( $required_missing );

		return array(
			'required'            => self::REQUIRED_EXTENSIONS,
			'recommended'         => self::RECOMMENDED_EXTENSIONS,
			'required_missing'    => $required_missing,
			'recommended_missing' => $recommended_missing,
			'all_required_loaded' => $all_required_loaded,
			'message'             => self::get_extensions_message( $required_missing, $recommended_missing ),
		);
	}

	/**
	 * Get extensions message.
	 *
	 * @param array<string> $required_missing Required missing extensions.
	 * @param array<string> $recommended_missing Recommended missing extensions.
	 * @return string
	 */
	private static function get_extensions_message( array $required_missing, array $recommended_missing ): string {
		if ( ! empty( $required_missing ) ) {
			return sprintf(
				/* translators: %s: Comma-separated list of missing extensions */
				__( 'Required PHP extensions missing: %s', 'plugin-wpshadow' ),
				implode( ', ', $required_missing )
			);
		}
		if ( ! empty( $recommended_missing ) ) {
			return sprintf(
				/* translators: %s: Comma-separated list of missing extensions */
				__( 'Recommended PHP extensions missing: %s. Some features may be limited.', 'plugin-wpshadow' ),
				implode( ', ', $recommended_missing )
			);
		}
		return __( 'All required and recommended PHP extensions are loaded.', 'plugin-wpshadow' );
	}

	/**
	 * Check if environment is compatible.
	 *
	 * @return bool
	 */
	public static function is_environment_compatible(): bool {
		$status = self::$cached_status ?? self::get_environment_status();

		return $status['php_version']['meets_requirement']
			&& $status['wp_version']['meets_requirement']
			&& $status['memory_limit']['meets_minimum']
			&& $status['execution_time']['meets_minimum']
			&& $status['extensions']['all_required_loaded'];
	}

	/**
	 * Check if environment has resource constraints.
	 *
	 * @return bool
	 */
	public static function has_resource_constraints(): bool {
		$status = self::$cached_status ?? self::get_environment_status();

		return ! $status['memory_limit']['meets_recommended']
			|| ! $status['execution_time']['meets_recommended']
			|| ! empty( $status['extensions']['recommended_missing'] );
	}

	/**
	 * Check if a specific extension is loaded.
	 *
	 * @param string $extension Extension name.
	 * @return bool
	 */
	public static function has_extension( string $extension ): bool {
		return extension_loaded( $extension );
	}

	/**
	 * Check if heavy tasks should be disabled.
	 *
	 * @return bool
	 */
	public static function should_disable_heavy_tasks(): bool {
		$status = self::$cached_status ?? self::get_environment_status();

		// Disable heavy tasks if memory is below minimum or execution time is below minimum.
		return ! $status['memory_limit']['meets_minimum']
			|| ! $status['execution_time']['meets_minimum'];
	}

	/**
	 * Check if tasks should be batched.
	 *
	 * @return bool
	 */
	public static function should_batch_tasks(): bool {
		$status = self::$cached_status ?? self::get_environment_status();

		// Batch tasks if we meet minimum but not recommended limits.
		return ( $status['memory_limit']['meets_minimum']
			&& ! $status['memory_limit']['meets_recommended'] )
			|| ( $status['execution_time']['meets_minimum']
			&& ! $status['execution_time']['meets_recommended'] );
	}

	/**
	 * Display environment notices.
	 *
	 * @return void
	 */
	public static function display_environment_notices(): void {
		// Only show on WPS admin pages.
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'wp-support' ) ) {
			return;
		}

		$status = self::$cached_status ?? self::get_environment_status();

		// Critical issues (incompatible environment).
		if ( ! $status['is_compatible'] ) {
			self::display_incompatibility_notice( $status );
		}

		// Warnings (resource constraints).
		if ( $status['is_compatible'] && $status['has_constraints'] ) {
			self::display_constraint_notice( $status );
		}
	}

	/**
	 * Display incompatibility notice.
	 *
	 * @param array<string, mixed> $status Environment status.
	 * @return void
	 */
	private static function display_incompatibility_notice( array $status ): void {
		$issues = array();

		if ( ! $status['php_version']['meets_requirement'] ) {
			$issues[] = sprintf(
				/* translators: 1: Current PHP version, 2: Minimum required PHP version */
				__( 'PHP version %1$s (minimum: %2$s required)', 'plugin-wpshadow' ),
				$status['php_version']['current'],
				$status['php_version']['minimum']
			);
		}

		if ( ! $status['wp_version']['meets_requirement'] ) {
			$issues[] = sprintf(
				/* translators: 1: Current WordPress version, 2: Minimum required WordPress version */
				__( 'WordPress version %1$s (minimum: %2$s required)', 'plugin-wpshadow' ),
				$status['wp_version']['current'],
				$status['wp_version']['minimum']
			);
		}

		if ( ! $status['memory_limit']['meets_minimum'] ) {
			$issues[] = sprintf(
				/* translators: 1: Current memory limit, 2: Minimum required memory limit */
				__( 'Memory limit %1$s (minimum: %2$s required)', 'plugin-wpshadow' ),
				$status['memory_limit']['current'],
				$status['memory_limit']['minimum']
			);
		}

		if ( ! $status['execution_time']['meets_minimum'] ) {
			$issues[] = sprintf(
				/* translators: 1: Current execution time, 2: Minimum required execution time */
				__( 'Execution time %1$ds (minimum: %2$ds required)', 'plugin-wpshadow' ),
				$status['execution_time']['current'],
				$status['execution_time']['minimum']
			);
		}

		if ( ! empty( $status['extensions']['required_missing'] ) ) {
			$issues[] = sprintf(
				/* translators: %s: Comma-separated list of missing extensions */
				__( 'Missing required PHP extensions: %s', 'plugin-wpshadow' ),
				implode( ', ', $status['extensions']['required_missing'] )
			);
		}

		if ( empty( $issues ) ) {
			return;
		}

		?>
		<div class="notice notice-error">
			<p><strong><?php esc_html_e( 'WPShadow: Environment Incompatibility Detected', 'plugin-wpshadow' ); ?></strong></p>
			<p><?php esc_html_e( 'Your server environment does not meet the minimum requirements. Heavy operations have been disabled to prevent errors.', 'plugin-wpshadow' ); ?></p>
			<ul style="list-style: disc; margin-left: 20px;">
				<?php foreach ( $issues as $issue ) : ?>
					<li><?php echo esc_html( $issue ); ?></li>
				<?php endforeach; ?>
			</ul>
			<p>
				<a href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>" class="button">
					<?php esc_html_e( 'View Site Health', 'plugin-wpshadow' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Display constraint notice.
	 *
	 * @param array<string, mixed> $status Environment status.
	 * @return void
	 */
	private static function display_constraint_notice( array $status ): void {
		$warnings = array();

		if ( ! $status['memory_limit']['meets_recommended'] ) {
			$warnings[] = sprintf(
				/* translators: 1: Current memory limit, 2: Recommended memory limit */
				__( 'Memory limit is %1$s (recommended: %2$s)', 'plugin-wpshadow' ),
				$status['memory_limit']['current'],
				$status['memory_limit']['recommended']
			);
		}

		if ( ! $status['execution_time']['meets_recommended'] && 0 !== $status['execution_time']['current'] ) {
			$warnings[] = sprintf(
				/* translators: 1: Current execution time, 2: Recommended execution time */
				__( 'Execution time is %1$ds (recommended: %2$ds)', 'plugin-wpshadow' ),
				$status['execution_time']['current'],
				$status['execution_time']['recommended']
			);
		}

		if ( ! empty( $status['extensions']['recommended_missing'] ) ) {
			$warnings[] = sprintf(
				/* translators: %s: Comma-separated list of missing extensions */
				__( 'Recommended extensions missing: %s', 'plugin-wpshadow' ),
				implode( ', ', $status['extensions']['recommended_missing'] )
			);
		}

		if ( empty( $warnings ) ) {
			return;
		}

		// Check if notice was dismissed.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Notice_Manager' )
			&& WPSHADOW_Notice_Manager::is_dismissed( 'wpshadow_environment_constraints' ) ) {
			return;
		}

		?>
		<div class="notice notice-warning is-dismissible" data-notice-key="wpshadow_environment_constraints">
			<p><strong><?php esc_html_e( 'WPShadow: Resource Constraints Detected', 'plugin-wpshadow' ); ?></strong></p>
			<p><?php esc_html_e( 'Your server is running below recommended specifications. Operations will be batched for optimal performance.', 'plugin-wpshadow' ); ?></p>
			<ul style="list-style: disc; margin-left: 20px;">
				<?php foreach ( $warnings as $warning ) : ?>
					<li><?php echo esc_html( $warning ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Check if diagnostic logging is enabled.
	 *
	 * @return bool
	 */
	private static function is_diagnostic_logging_enabled(): bool {
		return (bool) get_option( 'wpshadow_diagnostic_logging_enabled', false );
	}

	/**
	 * Log environment check to activity logger.
	 *
	 * @return void
	 */
	private static function log_environment_check(): void {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) ) {
			return;
		}

		$status = self::$cached_status ?? self::get_environment_status();

		WPSHADOW_Activity_Logger::log(
			'environment_check',
			'system',
			array(
				'is_compatible'   => $status['is_compatible'],
				'has_constraints' => $status['has_constraints'],
				'php_version'     => $status['php_version']['current'],
				'wp_version'      => $status['wp_version']['current'],
				'memory_limit'    => $status['memory_limit']['current'],
				'execution_time'  => $status['execution_time']['current'],
			),
			$status['is_compatible'] ? 'info' : 'warning'
		);
	}

	/**
	 * Convert PHP ini value to bytes.
	 *
	 * @param string $value PHP ini value (e.g., "256M", "1G").
	 * @return int Bytes.
	 */
	private static function convert_to_bytes( string $value ): int {
		$value = trim( $value );
		if ( empty( $value ) ) {
			return 0;
		}

		$last_char = substr( $value, -1 );
		if ( ! $last_char ) {
			return 0;
		}

		$last          = strtolower( $last_char );
		$numeric_value = (int) $value;

		switch ( $last ) {
			case 'g':
				$numeric_value *= 1024;
				// Fall through.
			case 'm':
				$numeric_value *= 1024;
				// Fall through.
			case 'k':
				$numeric_value *= 1024;
		}

		return $numeric_value;
	}

	/**
	 * Format bytes to human-readable string.
	 *
	 * @param int $bytes Bytes.
	 * @return string Formatted string (e.g., "256M", "1G").
	 */
	public static function format_bytes( int $bytes ): string {
		if ( $bytes >= 1073741824 ) {
			return round( $bytes / 1073741824, 2 ) . 'G';
		}
		if ( $bytes >= 1048576 ) {
			return round( $bytes / 1048576, 2 ) . 'M';
		}
		if ( $bytes >= 1024 ) {
			return round( $bytes / 1024, 2 ) . 'K';
		}
		return $bytes . 'B';
	}

	/**
	 * Get environment check for a specific module/format.
	 *
	 * @param string $module Module identifier (e.g., 'image', 'vault', 'avif').
	 * @return array<string, mixed>
	 */
	public static function get_module_requirements_status( string $module ): array {
		$requirements = self::get_module_requirements( $module );

		if ( empty( $requirements ) ) {
			return array(
				'supported' => true,
				'missing'   => array(),
				'message'   => __( 'No specific requirements for this module.', 'plugin-wpshadow' ),
			);
		}

		$missing = array();

		foreach ( $requirements as $requirement ) {
			if ( ! extension_loaded( $requirement ) ) {
				$missing[] = $requirement;
			}
		}

		$supported = empty( $missing );

		return array(
			'supported' => $supported,
			'required'  => $requirements,
			'missing'   => $missing,
			'message'   => $supported
				? sprintf(
					/* translators: %s: Module name */
					__( '%s: All requirements met.', 'plugin-wpshadow' ),
					ucfirst( $module )
				)
				: sprintf(
					/* translators: 1: Module name, 2: Comma-separated list of missing extensions */
					__( '%1$s: Missing extensions: %2$s', 'plugin-wpshadow' ),
					ucfirst( $module ),
					implode( ', ', $missing )
				),
		);
	}

	/**
	 * Get requirements for a specific module/format.
	 *
	 * @param string $module Module identifier.
	 * @return array<string>
	 */
	private static function get_module_requirements( string $module ): array {
		$requirements_map = array(
			'image' => array( 'gd' ),
			'vault' => array( 'openssl', 'zip' ),
			'avif'  => array( 'imagick' ),
			'webp'  => array( 'gd' ),
			'heic'  => array( 'imagick' ),
			'raw'   => array( 'imagick' ),
			'svg'   => array( 'xml' ),
		);

		/**
		 * Filter module requirements.
		 *
		 * @param array<string> $requirements Required extensions.
		 * @param string        $module       Module identifier.
		 */
		return (array) apply_filters(
			'wpshadow_module_requirements',
			$requirements_map[ $module ] ?? array(),
			$module
		);
	}
}
