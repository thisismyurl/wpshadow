<?php
/**
 * TIMU Processor - core runtime helper for spoke plugins.
 *
 * @package TIMU_CORE_SUPPORT
 * @version 1.2601.0819
 */

declare(strict_types=1);

namespace TIMU\Core\Spoke;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TIMU_Processor_v1 {

	public function __construct( private readonly TIMU_Spoke_Base $core ) {
		\add_action( 'init', array( $this, 'maybe_run_cron' ) );
	}

	public function maybe_run_cron(): void {
		$next = (int) \get_option( $this->core->plugin_slug . '_next_cron', 0 );
		if ( $next > time() ) {
			return;
		}
		\update_option( $this->core->plugin_slug . '_next_cron', time() + 6 * HOUR_IN_SECONDS, false );
		$this->core->clear_option_cache();
	}
}
