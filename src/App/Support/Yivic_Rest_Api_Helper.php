<?php
declare( strict_types = 1 );

namespace Yivic_Rest_Api\App\Support;

class Yivic_Rest_Api_Helper {
	public static function check_mandatory_prerequisites(): bool {
		return version_compare( phpversion(), '7.3.0', '>=' );
	}

	public static function check_yivic_base_plugin(): bool {
		return (bool) class_exists( \Yivic_Base\App\WP\WP_Application::class );
	}


	public static function get_yivic_plugins_installer_url(): string {
		return admin_url( 'admin.php?page=yivic-plugins-installer' );
	}

	public static function check_wp_upload_and_upgrade_dirs_existence(): bool {
		$upload_dir     = wp_upload_dir();
		$upgrade_dir    = WP_CONTENT_DIR . '/upgrade';

		$uploads_exists = ! empty( $upload_dir['basedir'] ) && is_dir( $upload_dir['basedir'] );
		$upgrade_exists = is_dir( $upgrade_dir );

		return (bool) ( $uploads_exists && $upgrade_exists );
	}

	/**
	 * Checks if the upload and upgrade directories exist.
	 * Returns an error message if any of them are missing.
	 */
	public static function check_missing_directories(): string {

		if ( static::check_wp_upload_and_upgrade_dirs_existence() ) {
			return '';
		}

		$upload_dir     = wp_upload_dir();
		$upload_path    = $upload_dir['basedir'] ?? '';
		$upgrade_path   = WP_CONTENT_DIR . '/upgrade';

		$missing_dirs   = [];

		if ( empty( $upload_path ) || ! is_dir( $upload_path ) ) {
			$missing_dirs[] = __( '<p>- <strong>wp-content/uploads</strong> directory is missing. Please create it with permission 0777.</p>', 'yivic-rest-api' );
		}

		if ( ! is_dir( $upgrade_path ) ) {
			$missing_dirs[] = __( '<p>- <strong>wp-content/upgrade</strong> directory is missing. Please create it with permission 0777.</p>', 'yivic-rest-api' );
		}

		return implode( '', $missing_dirs );
	}

	/**
	 * Returns the error message for missing plugin.
	 */
	public static function get_missing_plugin_message(): string {
		return sprintf(
			__( '<p>- Plugin <strong>%1$s</strong> is required. Please <a href="%2$s">click here</a> to install and activate it first.</p>', 'yivic-rest-api' ),
			'Yivic Base',
			static::get_yivic_plugins_installer_url()
		);
	}

	/**
	 * Displays an admin notice for missing plugin.
	 */
	public static function display_admin_notice( string $error_message ) {
		// Check if the notice should be hidden (transient exists)
		if ( get_transient( 'yivic_rest_api_dismiss_notice' ) ) {
			return;
		}

		$error_message = sprintf(
				__( '<h3><strong>%s</strong> is not functioning!</h3>', 'yivic-rest-api' ),
				'Yivic Rest Api Plugin'
			) . $error_message;
		?>
		<div class="notice notice-warning is-dismissible yivic-rest-api-notice">
			<span class="warning-icon">
				<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>../../../public-assets/images/warning-sign-icon.webp" alt="Yivic REST API" />
			</span>
			<div class="notice-content"><?php echo $error_message; ?></div>
		</div>
		<?php
	}
}