<?php
/**
 * WPS Vault handler (shim) for spoke plugins.
 * NOTE: This is a minimal bridge until Vault logic is fully relocated to the vault-support plugin.
 *
 * @package wp_support_SUPPORT
 * @version 1.2601.0819
 */

declare(strict_types=1);

namespace WPS\Core\Spoke;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPS_Vault_v1 {

	public function __construct( private readonly WPS_Spoke_Base $core ) {
		\add_filter( 'WPS_vault_item_meta', array( $this, 'inject_origin_meta' ), 10, 2 );
	}

	public function inject_origin_meta( array $meta, int $attachment_id ): array {
		$meta['plugin']   = $this->core->plugin_slug;
		$meta['vaulted']  = 1;
		$meta['blog_id']  = \is_multisite() ? \get_current_blog_id() : 1;
		$meta['sourceId'] = $attachment_id;
		return $meta;
	}
}
