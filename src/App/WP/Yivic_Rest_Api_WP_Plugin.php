<?php
declare( strict_types = 1 );

namespace Yivic_Rest_Api\App\WP;

use Yivic_Base\Foundation\WP\WP_Plugin;

class Yivic_Rest_Api_WP_Plugin extends WP_Plugin {
	/**
	 * All hooks should be registered here, inside this method
	 * @return void
	 * @throws BindingResolutionException
	 */
	public function manipulate_hooks(): void {}

	public function get_name(): string {
		return 'Yivic REST API';
	}

	public function get_version(): string {
		return YIVIC_REST_API_PLUGIN_VERSION;
	}
}