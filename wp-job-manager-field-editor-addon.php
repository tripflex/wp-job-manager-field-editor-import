<?php
/**
 * Plugin Name: WP All Import - WP Job Manager Field Editor Add-On
 * Plugin URI: https://github.com/tripflex/wp-job-manager-field-editor-import
 * Description: Support importing listings in WP Job Manager, with support for WP Job Manager Field Editor custom fields
 * Version:     1.0.3
 * Author:      Myles McNamara
 * Author URI:  http://plugins.smyl.es
 * Requires at least: 4.7
 * Tested up to: 5.2.2
 * Domain Path: /languages
 * Text Domain: smyles-wp-job-manager-field-editor-import
 * Last Updated: @@timestamp
 */

class WPAI_WPJM_Field_Editor {

	/**
	 * @var \WPAI_WPJM_Field_Editor
	 */
	protected static $instance;
	/**
	 * @var \WPAI_WPJM_Field_Editor_Integration_Jobs
	 */
	public $jobs;
	/**
	 * @var \WPAI_WPJM_Field_Editor_Integration_Resumes
	 */
	public $resumes;

	/**
	 * WPAI_WPJM_Field_Editor constructor.
	 */
	function __construct() {

		if ( ! $this->has_fe() ) {
			add_action( 'admin_notices', array( $this, 'fe_missing' ) );
			return;
		}

		if ( ! defined( 'WPJM_FIELD_EDITOR_IMPORT_PLUGIN_DIR' ) ) {
			define( 'WPJM_FIELD_EDITOR_IMPORT_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		}
		if ( ! defined( 'WPJM_FIELD_EDITOR_IMPORT_PLUGIN_URL' ) ) {
			define( 'WPJM_FIELD_EDITOR_IMPORT_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		}

		include( 'rapid-addon.php' );

		require_once( 'integration.php' );
		require_once( 'integration/jobs.php' );
		require_once( 'integration/resumes.php' );

		$this->jobs = new WPAI_WPJM_Field_Editor_Integration_Jobs( $this );
		$this->resumes = new WPAI_WPJM_Field_Editor_Integration_Resumes( $this );
	}

	/**
	 * Field Editor missing Admin Notice
	 *
	 *
	 * @since @@version
	 *
	 */
	function fe_missing(){
		?>
			<div class="notice notice-error is-dismissible">
				<p><?php printf( __( '%s must be installed and activated for WP All Import integration support.' ),'<a href="https://plugins.smyl.es/wp-job-manager-field-editor/" target="_blank">WP Job Manager Field Editor</a>' ); ?></p>
			</div>
		<?php
	}

	/**
	 * Get Default Options
	 *
	 *
	 * @since @@version
	 *
	 * @return array
	 */
	function get_default_import_options() {
		return PMXI_Plugin::get_default_import_options();
	}

	/**
	 * Get Singleton Instance
	 *
	 *
	 * @since @@version
	 *
	 * @return \WPAI_WPJM_Field_Editor
	 */
	static public function get_instance() {
		if (self::$instance == NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Check if Field Editor exists and has been loaded
	 *
	 *
	 * @since @@version
	 *
	 * @return bool
	 */
	public function has_fe() {
		// Should already be defined on instance load (ran when loading plugins)
		return defined( 'WPJM_FIELD_EDITOR_VERSION' );
	}
}

if( ! function_exists( 'field_editor_import_multi_files' ) ){
	/**
	 * WP All Import helper Function for Serialized Multi File Fields
	 *
	 *
	 * @since @@version
	 *
	 * @param $files
	 *
	 * @return mixed|string
	 */
	function field_editor_import_multi_files( $files ){
		$files = maybe_unserialize( $files );
		$files = implode(',', $files );
		return $files;
	}
}

if( ! function_exists( 'field_editor_import_multi_field' ) ){
	/**
	 * WP All Import helper Function for Converting Data to Serialized Format
	 *
	 *
	 * @param string $data
	 * @param string $separator
	 *
	 * @return string
	 * @since 1.0.3
	 *
	 */
	function field_editor_import_multi_field( $data = '', $separator = ',' ){
		$a_data = explode( $separator, $data );
		return ! empty( $a_data ) ? maybe_serialize( $a_data ) : '';
	}
}

add_action( 'plugins_loaded', 'WPAI_WPJM_Field_Editor::get_instance' );
