<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Cart_Abandonment_Rate extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-cart-abandon', 'title' => __('Cart Abandonment Rate Monitoring', 'wpshadow'), 'description' => __('Tracks % of carts abandoned. High abandonment = checkout friction, payment issues, or security concerns.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/cart-recovery/', 'training_link' => 'https://wpshadow.com/training/checkout-ux/', 'auto_fixable' => false, 'threat_level' => 8]; } 
}