<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Revenue_Per_Visitor extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-rpv', 'title' => __('Revenue Per Visitor Tracking', 'wpshadow'), 'description' => __('Monitors average revenue per visit. Drop indicates undermonetization, pricing issues, or product-market fit loss.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/revenue-optimization/', 'training_link' => 'https://wpshadow.com/training/pricing-strategy/', 'auto_fixable' => false, 'threat_level' => 7]; } }
