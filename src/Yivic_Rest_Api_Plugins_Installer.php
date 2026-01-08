<?php
declare( strict_types = 1 );

namespace Yivic_Rest_Api;

use Yivic_Rest_Api\App\Support\Yivic_Rest_Api_Helper;
use Plugin_Upgrader;

class Yivic_Rest_Api_Plugins_Installer {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		add_action( 'wp_ajax_yivic_install_plugin', [ $this, 'install_plugin' ] );
		add_action( 'wp_ajax_yivic_activate_plugin', [ $this, 'activate_plugin' ] );
		add_action( 'wp_ajax_yivic_deactivate_plugin', [ $this, 'deactivate_plugin' ] );
		add_action( 'wp_ajax_yivic_rest_api_dismiss_notice', [ $this,'dismiss_admin_notice' ] );
		add_action( 'wp_ajax_nopriv_yivic_rest_api_dismiss_notice', [ $this,'dismiss_admin_notice' ] );
	}

	public function enqueue_admin_scripts( $hook ) {
		wp_enqueue_style(
			'yivic-rest-api-admin-style',
			plugin_dir_url( __FILE__ ) . '../public-assets/dist/css/admin.css',
			[],
			YIVIC_REST_API_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'yivic-rest-api-admin-script',
			plugin_dir_url( __FILE__ ) . '../public-assets/dist/js/admin.js',
			[ 'jquery' ],
			YIVIC_REST_API_PLUGIN_VERSION,
			false
		);

		$yivicDismissNotice = [
			'nonce' => wp_create_nonce( 'yivic_dismiss_notice' ),
		];

		wp_localize_script( 'yivic-rest-api-admin-script', 'yivicDismissNotice', $yivicDismissNotice );
	}

	/**
	 * Handles the AJAX request to dismiss the notice.
	 */
	public function dismiss_admin_notice() {
		set_transient( 'yivic_rest_api_dismiss_notice', true, 3600 ); // Hide for an hour
		wp_die();
	}


	// Define required plugins
	protected function get_required_plugins() {
		return [
			'yivic-base'    => [
				'name'      => 'Yivic Base',
				'zip_url'   => 'https://yivic-com-demo.dev-srv.org/wp-content/uploads/2026/01/yivic-base-0.0.1.zip',
				'type'      => 'mu-plugins',
				'folder'    => 'yivic-base',
				'main_file' => 'yivic-base',
			],
			'yivic-html-components' => [
				'name'      => 'Yivic HTML Components',
				'zip_url'   => 'https://yivic-com-demo.dev-srv.net/wp-content/uploads/2026/01/yivic-html-components-0.0.1.zip',
				'type'      => 'plugins',
				'folder'    => 'yivic-html-components',
				'main_file' => 'yivic-html-components',
			],
		];
	}

	// Add admin menu page
	public function add_admin_menu() {
		add_menu_page( 'Yivic Plugins Installer', 'Yivic Plugins Installer', 'manage_options', 'yivic-plugins-installer', [ $this, 'admin_page' ] );
	}

	// Admin page content
	public function admin_page() {
		$required_plugins = $this->get_required_plugins();

		$counts = [
			'all' => 0,
			'active' => 0,
			'inactive' => 0,
			'Must-Use' => 0,
			'not-installed' => 0,
		];

		foreach ( $required_plugins as $plugin ) {
			$plugin_path = WP_CONTENT_DIR . '/' . esc_attr( $plugin['type'] ) . '/' . esc_attr( $plugin['folder'] );
			$plugin_file = esc_attr( $plugin['folder'] ) . '/' . esc_attr( $plugin['main_file'] ) . '.php';

			$is_mu_plugin = $plugin['type'] === 'mu-plugins';
			$is_installed = is_dir( $plugin_path );
			$is_active = is_plugin_active( $plugin_file );
			$is_registered = array_key_exists( $plugin_file, get_plugins() );

			$status = $is_active || ( $is_installed && $is_mu_plugin ) ? 'active'
				: ( $is_registered && ! $is_mu_plugin ? 'inactive' : 'not-installed' );

			if ( $is_mu_plugin && $is_installed ) {
				$status = 'Must-Use';
			}

			++$counts[ $status ];
			++$counts['all'];
		}
		$error_msgs = '';
		if ( ! empty( Yivic_Rest_Api_Helper::check_missing_directories() ) ) {
			$error_msgs .= Yivic_Rest_Api_Helper::check_missing_directories();
			if ( ! empty( $error_msgs ) ) {
				echo '
				<div class="wrap yivic-plugins-installer">
					<h2 class="yivic-plugins-installer__title">Yivic Required Plugins Installer</h2>
					<div class="card">
						<div class="card-body">
						<h2>Required Directories Missing</h2>
						<p>' . $error_msgs . ' </p>
						</div>
					</div>
				</div>';
			}
		} else {
			?>
			<div class="wrap yivic-plugins-installer">
				<h2 class="yivic-plugins-installer__title">Yivic Required Plugins Installer</h2>
				<ul class="yivic-plugins-installer__tabs">
					<li class="all"><a href="#" class="current" aria-current="page">All <span class="count">(<?php echo esc_html( $counts['all'] ); ?>)</span></a> |</li>
					<li class="active"><a href="#">Active <span class="count">(<?php echo esc_html( $counts['active'] ); ?>)</span></a> |</li>
					<li class="inactive"><a href="#">Inactive <span class="count">(<?php echo esc_html( $counts['inactive'] ); ?>)</span></a> |</li>
					<li class="not-installed"><a href="#">Not Installed <span class="count">(<?php echo esc_html( $counts['not-installed'] ); ?>)</span></a> |</li>
					<li class="Must-Use"><a href="#">Must-Use <span class="count">(<?php echo esc_html( $counts['Must-Use'] ); ?>)</span></a></li>
				</ul>
				<table class="yivic-plugins-installer__table">
					<thead>
					<tr>
						<th>Plugin Name</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach ( $required_plugins as $slug => $plugin ) :
						$plugin_path = WP_CONTENT_DIR . '/' . esc_attr( $plugin['type'] ) . '/' . esc_attr( $plugin['folder'] );
						$plugin_file = esc_attr( $plugin['folder'] ) . '/' . esc_attr( $plugin['main_file'] ) . '.php';

						$is_mu_plugin = $plugin['type'] === 'mu-plugins';
						$is_installed = is_dir( $plugin_path );
						$is_active = is_plugin_active( $plugin_file );
						$is_registered = array_key_exists( $plugin_file, get_plugins() );
						$status = ( $is_active || ( $is_installed && $is_mu_plugin ) ) ? 'active'
							: ( $is_registered && ! $is_mu_plugin ? 'inactive' : 'not-installed' );

						if ( $is_mu_plugin && $is_installed ) {
							$status = 'Must-Use';
						}
						?>
						<tr>
							<td><?php echo esc_html( $plugin['name'] ); ?></td>
							<td class="yivic-plugins-installer__status yivic-plugins-installer__status--<?php echo esc_attr( $status ); ?>">
								<?php echo esc_html( ucfirst( $status ) ); ?>
							</td>
							<td>
								<?php if ( ! $is_installed ) : ?>
									<button class="yivic-plugins-installer__button yivic-plugins-installer__button--install" data-slug="<?php echo esc_attr( $slug ); ?>">Install</button>
								<?php elseif ( ! $is_active && $is_mu_plugin ) : ?>
									<span class="yivic-plugins-installer__status yivic-plugins-installer__status--active">Must-Use Activated</span>
								<?php elseif ( ! $is_active && ! $is_mu_plugin ) : ?>
									<button class="yivic-plugins-installer__button yivic-plugins-installer__button--activate" data-path="<?php echo esc_attr( $plugin['folder'] ); ?>" data-file="<?php echo esc_attr( $plugin['main_file'] ); ?>">Activate</button>
								<?php else : ?>
									<button class="yivic-plugins-installer__button yivic-plugins-installer__button--deactivate" data-path="<?php echo esc_attr( $plugin['folder'] ); ?>" data-file="<?php echo esc_attr( $plugin['main_file'] ); ?>">Deactivate</button>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div id="plugin-action-spinner">
				<div class="lds-roller">
					<div></div><div></div><div></div><div></div>
					<div></div><div></div><div></div><div></div>
				</div>
				<p>Processing</p>
			</div>
			<?php
		}
	}

	// Handle plugin installation using WordPress Core methods
	public function install_plugin() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$slug = sanitize_text_field( $_POST['plugin_slug'] );
		$plugins = $this->get_required_plugins();

		if ( ! isset( $plugins[ $slug ] ) ) {
			wp_send_json_error( 'Plugin not found.' );
		}

		$plugin = $plugins[ $slug ];
		$zip_url = $plugin['zip_url'];
		$destination_folder = WP_CONTENT_DIR . '/' . $plugin['type']; // wp-content/plugins or wp-content/mu-plugins

		// Load required WordPress core files
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		WP_Filesystem(); // Initialize WP_Filesystem

		global $wp_filesystem;

		// Ensure the upgrade directory exists
		$upgrade_dir = WP_CONTENT_DIR . '/upgrade/';
		if ( ! $wp_filesystem->is_dir( $upgrade_dir ) ) {
			$wp_filesystem->mkdir( $upgrade_dir, 0777 );
		}

		// 🔹 Download the ZIP file using WordPress' built-in function
		$zip_path = download_url( $zip_url );

		if ( is_wp_error( $zip_path ) ) {
			wp_send_json_error( 'Download failed: ' . $zip_path->get_error_message() );
		}

		// 🔹 Extract the ZIP file into the wp-content/upgrade/ folder
		$extract_folder = $upgrade_dir . $slug;
		$unzip_result = unzip_file( $zip_path, $extract_folder );

		if ( is_wp_error( $unzip_result ) ) {
			unlink( $zip_path ); // Remove the failed ZIP file
			wp_send_json_error( 'Extraction failed: ' . $unzip_result->get_error_message() );
		}

		// Identify extracted plugin folder
		$extracted_plugin_folders = array_diff( scandir( $extract_folder ), [ '.', '..' ] );

		if ( count( $extracted_plugin_folders ) !== 1 ) {
			unlink( $zip_path );
			$wp_filesystem->delete( $extract_folder, true );
			wp_send_json_error( 'Invalid plugin structure. No valid plugin folder found.' );
		}

		$extracted_plugin_path = $extract_folder . '/' . reset( $extracted_plugin_folders );
		$final_plugin_path = $destination_folder . '/' . $plugin['folder'];

		// 🔹 Move extracted folder to final plugin directory using WordPress' install_package() method
		$upgrader = new Plugin_Upgrader();
		$install_result = $upgrader->install_package(
			[
				'source'            => $extracted_plugin_path,
				'destination'       => $final_plugin_path,
				'clear_destination' => true,
				'clear_working'     => true,
				'hook_extra'        => [
					'type'   => 'plugin',
					'action' => 'install',
				],
			]
		);

		if ( is_wp_error( $install_result ) ) {
			$wp_filesystem->delete( $extract_folder, true );
			wp_send_json_error( 'Failed to install plugin: ' . $install_result->get_error_message() );
		}

		// 🔹 Cleanup - Delete ZIP and extracted folder
		unlink( $zip_path );
		$wp_filesystem->delete( $extract_folder, true );

		wp_send_json_success(
			[
				'message' => 'Installed successfully.',
				'plugin_path' => $plugin['folder'],
				'plugin_file' => basename( $plugin['main_file'], '.php' ),
			]
		);
	}

	// Handle plugin activation
	public function activate_plugin() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$plugin_path = sanitize_text_field( $_POST['plugin_path'] );
		$plugin_file = sanitize_text_field( $_POST['plugin_file'] ) . '.php';

		$full_plugin_path = WP_PLUGIN_DIR . '/' . $plugin_path . '/' . $plugin_file;

		// Validate that the file exists
		if ( ! file_exists( $full_plugin_path ) ) {
			wp_send_json_error( 'Plugin file not found: ' . $full_plugin_path );
		}

		// Activate the plugin
		$result = activate_plugin( $plugin_path . '/' . $plugin_file );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( 'Activation failed: ' . $result->get_error_message() );
		}

		wp_send_json_success( 'Activated successfully.' );
	}

	public function deactivate_plugin() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$plugin_path = sanitize_text_field( $_POST['plugin_path'] );
		$plugin_file = sanitize_text_field( $_POST['plugin_file'] ) . '.php';

		$full_plugin_path = WP_PLUGIN_DIR . '/' . $plugin_path . '/' . $plugin_file;

		// Validate that the plugin exists before deactivating
		if ( ! file_exists( $full_plugin_path ) ) {
			wp_send_json_error( 'Plugin file not found: ' . $full_plugin_path );
		}

		// Deactivate the plugin
		deactivate_plugins( $plugin_path . '/' . $plugin_file );

		// Check if successfully deactivated
		if ( is_plugin_active( $plugin_path . '/' . $plugin_file ) ) {
			wp_send_json_error( 'Deactivation failed.' );
		}

		wp_send_json_success( 'Plugin deactivated successfully.' );
	}
}