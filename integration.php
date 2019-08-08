<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPAI_WPJM_Field_Editor_Integration
 *
 * @since @@version
 *
 */
class WPAI_WPJM_Field_Editor_Integration {

	/**
	 * @var string Label to describe import type (should be set in extending class)
	 */
	public $label = '';

	/**
	 * @var array Post types to associate with
	 */
	public $post_types = array( 'job_listing', 'resume' );

	/**
	 * @var array Multiple files to skip custom meta field updates on
	 */
	public $multi_files = array();

	/**
	 * @var string RapidAddon slug (should be overridden by extending class)
	 */
	public $slug = 'none';

	/**
	 * @var \WP_Job_Manager_Field_Editor_Fields
	 */
	public $fe;

	/**
	 * @var \RapidAddon
	 */
	public $import;

	/**
	 * @var \WPAI_WPJM_Field_Editor
	 */
	public $core;

	/**
	 * @var array
	 */
	public $import_options;

	/**
	 * @var integer
	 */
	public $post_id;

	/**
	 * @var array
	 */
	public $data;

	/**
	 * WPAI_WPJM_Field_Editor_Integration constructor.
	 *
	 * @param $core \WPAI_WPJM_Field_Editor
	 */
	public function __construct( $core ) {
		$this->core = $core;
		$this->construct();

//		add_action( 'pmxi_after_xml_import', array( $this, 'import_completed' ), 10, 2 );

		// Only seems to be called when setting is set to update only specific meta (not all)
		add_filter( 'pmxi_custom_field_to_update', array( $this, 'custom_field_should_update' ), 10, 4 );
		add_filter( 'pmxi_custom_field', array( $this, 'custom_meta_value_update' ), 10, 5 );

		// I feel like this should only be called when necessary .. but for now, it's OK i guess
		$this->init_fields();
	}

	/**
	 * Force multi file upload fields to empty string
	 *
	 * Because WP All Import Pro sometimes prefills "Custom Fields" to update, if user is updating a multi-file upload
	 * field, we don't want WPAIP to update the meta with old stale values before it calls our magic method to add files
	 * to it.
	 *
	 * @since @@version
	 *
	 * @param $value
	 * @param $pid
	 * @param $m_key
	 * @param $existing_meta_keys
	 * @param $id
	 *
	 * @return string
	 */
	public function custom_meta_value_update( $value, $pid, $m_key, $existing_meta_keys, $id ){

		if ( in_array( $m_key, $this->multi_files ) && in_array( get_post_type( $pid ), $this->post_types ) ) {
			$this->import()->log( "WP Job Manager Field Editor - {$m_key} FORCING empty meta update (is multi file upload) -- only workaround for now until bugs fixed in WP All Import" );

			// Add filter to allow user to override this if they want
			$value = apply_filters( 'field_editor_import_set_multi_file_custom_meta_update_empty', '', $value, $pid, $m_key, $id, $this );
		}

		return $value;
	}

	/**
	 * Prevent Multi File field being updated by custom meta
	 *
	 * When user selects option to "only update specific values" in WPAIP this filter is called, so we can prevent
	 * multi file upload fields from custom meta updating them.  Unfortunately this does not get called when user
	 * selects to update all meta, so that's what custom_meta_value_update handles.
	 *
	 * @see custom_meta_value_update() for details
	 *
	 * @since @@version
	 *
	 * @param $field_to_update
	 * @param $post_type
	 * @param $options
	 * @param $m_key
	 *
	 * @return bool
	 */
	public function custom_field_should_update( $field_to_update, $post_type, $options, $m_key ){

		if( in_array( $post_type, $this->post_types ) && in_array( $field_to_update, $this->multi_files ) ){

			$this->import()->log( "WP Job Manager Field Editor - {$field_to_update} forcing skip custom meta update (is multi file upload)" );

			// Add filter to allow user to override this if they want
			$field_to_update = apply_filters( 'field_editor_import_prevent_multi_file_custom_meta_update', false, $post_type, $options, $m_key, $this );
		}

		return $field_to_update;
	}

	/**
	 * Exist and Not Empty
	 *
	 *
	 * @since @@version
	 *
	 * @param string $key
	 * @param array  $array
	 * @param bool   $return
	 * @param string $default
	 *
	 * @return bool
	 */
	public function ene( $key, $array, $return = false, $default = '' ){

		if( is_array( $array ) && array_key_exists( $key, $array ) && ! empty( $array[$key] ) ){
			return $return ? $array[ $key ] : true;
		}

		return $return ? $default : false;
	}

	/**
	 * Add a custom field
	 *
	 *
	 * @since @@version
	 *
	 * @param        $title
	 * @param        $content
	 * @param string $tooltip
	 */
	public function custom_field( $title, $content, $tooltip = '' ){
		$this->import()->add_title( $title, $tooltip );
		$this->import()->add_text( $content );
	}

	/**
	 * Initialize Fields
	 *
	 *
	 * @since @@version
	 *
	 */
	public function init_fields(){

		$fields = $this->get_fields();

		foreach ( (array) $fields as $key => $field ) {

			// Set defaults
			$type     = $this->ene( 'type', $field, true, 'text' );
			$tooltip  = $this->ene( 'description', $field, true );
			$meta_key = $this->ene( 'meta_key', $field, true, $key );
			$label    = wp_strip_all_tags( $field['label'] );
			$options  = null; $is_html = true; $default = ''; $skip = false;
			$_meta_key = "_{$meta_key}";

			// text, textarea, wp_editor, image or file, radio, accordion, acf, plain_text
			switch ( $type ) {
				case 'text':
				case 'file':
					if( $this->ene( 'multiple', $field ) ){
						$this->add_multi_file_import( $_meta_key, $label );
						$skip = true;
					}
				case 'textarea':
					// The above case don't need to be modified at all
					break;
				case 'select':
				case 'radio':
				case 'checklist':
				case 'multiselect':
					$type = 'radio';
					$options = $field['options'];
					break;
				case 'term-checklist':
				case 'term-select':
				case 'term-multiselect':
					// We don't want to add taxonomy field types
					$skip = true;
					break;
				case 'wp-editor':
					$type = 'wp_editor';
					break;
				case 'html':
					$type = 'textarea';
					break;
				default:
					// If field type did not match any above, we set to 'text' to have standard text input value
					$type = 'text';
					break;
			}

			if ( ! $skip ) {

				$maybe_populate_html = empty( $options ) ? "<span data-metakey='{$_meta_key}' class='fetrypopulate dashicons dashicons-arrow-down-alt2' title='" . __( 'Guess template and try to populate' ) . "'></span>" : '';
				if( ! empty( $options ) ){
					$encoded_options = array();
					foreach( (array) $options as $opt_key => $opt_val ){
						$encoded_key = htmlentities( $opt_key, ENT_QUOTES );
						$encoded_options[ $encoded_key ] = htmlentities( $opt_val, ENT_QUOTES );
					}
					$options = $encoded_options;
				}

				$label = "{$label} (<small>{$meta_key}</small>):  {$maybe_populate_html}";
				$this->import()->add_field( $_meta_key, $label, $type, $options, $tooltip, $is_html, $default );
			}
		}

		// This adds a notice for the multiple file uploads to convert from a serialized array
		$multi_notice = '<div style="max-width: 90%;">' . sprintf( __('If your values are serialized array (value starts with %1$s), you MUST wrap it in a WP All Import function to convert them to a CSV of the file URLs, using the %2$s function. If you use this, <strong>Do NOT change the separator, it must be a comma!</strong>  Here is an example:'), '<code>a:</code>', '<code>field_editor_import_multi_files</code>' )  . '</div>';
		$multi_notice .= '<p><code style="background-color: #c7c7c7;color: #404040;">[field_editor_import_multi_files({_some_meta_key[1]})]</code></p>';

		ob_start();
		?>
		<script>
			jQuery( function ( $ ) {

				$( '.fe_multi_file' ).each(function(){

					var inner = $(this).closest( '.wpallimport-collapsed' ).find('.wpallimport-collapsed-content-inner' );
					if( inner ){
						inner.prepend('<?php echo $multi_notice; ?>')
					}
				});

				$( ".fetrypopulate" ).click( function () {
					var _metakey = $( this ).data( "metakey" );
					if ( _metakey ) {
						var maybe_template = _metakey + "[1]";

						var found_node = $( "div[title='/node/" + maybe_template + "']" );
						console.log( found_node );

						if( found_node ){
							var input = $( 'div[id$="wpjm_fe_addon_<?php echo $this->slug; ?>' + _metakey + '"]' );
							if( input ){
								input.val( "{" + maybe_template + "}" );
							}
						}
					}
				} );

			} );
		</script>
		<?php

		// This adds a down arrow to try and "guess" and populate fields with template variables
		$jquery_populate = ob_get_clean();

		if( ! empty( $fields ) ){
			// We add the jQuery using the add_text method
			$this->import()->add_text( $jquery_populate, true );
		}

		$this->import()->set_import_function( array( $this, 'do_import' ) );

		$this->import()->admin_notice(
			'The WP Job Manager Field Editor Add-On requires WP All Import <a href="http://www.wpallimport.com/order-now/?utm_source=free-plugin&utm_medium=dot-org&utm_campaign=wpjm_fe" target="_blank">Pro</a> or <a href="http://wordpress.org/plugins/wp-all-import" target="_blank">Free</a>, and the <a href="https://plugins.smyl.es/wp-job-manager-field-editor/">WP Job Manager Field Editor</a> plugin.',
			array(
				'plugins' => array( 'wp-job-manager-field-editor/wp-job-manager-field-editor.php' ),
			) );

		$this->import()->run( array(
			                  'plugins'    => array( 'wp-job-manager-field-editor/wp-job-manager-field-editor.php' ),
			                  'post_types' => $this->post_types
		                  ) );

	}

	/**
	 * Same as RapidAddon import_files()
	 *
	 *
	 * @since @@version
	 *
	 * @param $slug
	 * @param $title
	 */
	public function add_multi_file_import( $slug, $title ){

		if( ! in_array( $slug, $this->multi_files ) ){
			$this->multi_files[] = $slug;
		}

		$section_slug = 'pmxi_' . $slug;

		$title = "{$title} (<small>{$slug}</small>)<span class='fe_multi_file'></span>";

		$this->import()->image_sections[] = array(
			'title' => $title,
			'slug'  => $section_slug,
			'type'  => 'files'
		);

		$options = $this->import()->image_options;

		// Try to set search by filename as default
		$options['search_existing_images_logic'] = 'by_filename';

		foreach ( $options as $option_slug => $value ) {
			$this->import()->add_option( $section_slug . $option_slug, $value );
		}

		if ( count( $this->import()->image_sections ) > 1 ) {
			add_filter( 'wp_all_import_is_show_add_new_images', array( $this->import(), 'filter_is_show_add_new_images' ), 10, 2 );
		}

		add_filter( 'wp_all_import_is_allow_import_images', array( $this->import(), 'is_allow_import_images' ), 10, 2 );

		/**
		 * Add action for magic method to update post meta
		 *
		 * This is why we have to use our own method to do this, since WPAI tries to add action
		 * for a standard function, and doesn't work with class methods
		 */
		add_action( $section_slug, array( $this, "multiple_files_{$slug}" ), 10, 4 );
	}

	/**
	 * Process Import
	 *
	 *
	 * @since @@version
	 *
	 * @param $post_id
	 * @param $data
	 * @param $import_options
	 * @param $article
	 * @param $logger
	 */
	public function do_import( $post_id, $data, $import_options, $article, $logger ){

		$this->post_id = $post_id;
		$this->data = $data;
		$this->import_options = $import_options;

		foreach( (array) $data as $meta_key => $value ){
			$this->update_meta( $meta_key, $value );
		}
	}

	/**
	 * Update Post Meta
	 *
	 *
	 * @since @@version
	 *
	 * @param $meta_key
	 * @param $value
	 */
	public function update_meta( $meta_key, $value ){

		$with_value_of = __( 'with value of' );
		$updating_creating = __( 'Updating/Creating' );
		$wpjmfe = '<strong>WP Job Manager Field Editor:</strong>';

		$import_options = $this->import_options['options'];
		$is_new_listing = isset( $import_options['wizard_type'] ) && $import_options['wizard_type'] === 'new';

		if ( $is_new_listing || $this->import()->can_update_meta( $meta_key, $this->import_options ) ) {

			/**
			 * Handle single file field uploads, by first checking for value in `image_url_or_path` key in array,
			 * or if only attachment ID is specified use that to return the full URL to file.  If for some reason
			 * none of those methods return a URL, set the meta value to empty string.
			 */
			if( is_array( $value ) && isset( $value['attachment_id'] ) ){

				$file_url = false;
				if( isset( $value['image_url_or_path'] ) && ! empty( $value['image_url_or_path'] ) ){
					$file_url = $value['image_url_or_path'];
				} elseif( isset( $value['attachment_id'] ) && ! empty( $value['image_url_or_path'] ) ){
					$file_url = wp_get_attachment_url( $value['image_url_or_path'] );
				}

				$value = ! empty( $file_url ) ? $file_url : '';
			}

			$this->log( "{$wpjmfe} {$updating_creating} {$meta_key} {$with_value_of} {$value}" );

			// Maybe unserialize before updating to make sure we don't serialize already serialized data
			$value = maybe_unserialize( $value );
			$result = update_post_meta( $this->post_id, $meta_key, $value );

			// Numeric means meta was created, true means it was updated
			$result_type = is_numeric( $result ) ? __( 'SUCCESSFULLY CREATED' ) : __( 'SUCCESSFULLY UPDATED' );
			// False means error creating/updating
			if( empty( $result_type ) ){
				$result_type = '<strong>' . __( 'ERROR CREATING/UPDATING' ) . '</strong>';
			}

			$this->log( "{$wpjmfe} {$result_type} {$meta_key} {$with_value_of} {$value}" );

		} else {

			$skip_msg = sprintf( __('%1$s Skipping %2$s update (user disabled updated)'), $wpjmfe, $meta_key );
			$this->log( $skip_msg );
		}

	}

	/**
	 * Log to Importer
	 *
	 *
	 * @since @@version
	 *
	 * @param $message
	 */
	public function log( $message ){
		$this->import->log( $message );
	}

	/**
	 * Get Field Editor Instance
	 *
	 *
	 * @since @@version
	 *
	 * @return \WP_Job_Manager_Field_Editor_Fields
	 */
	public function fe(){

		if( ! $this->fe ){
			$this->fe = WP_Job_Manager_Field_Editor_Fields::get_instance();
		}

		return $this->fe;
	}

	/**
	 * Get RapidAddon Instance
	 *
	 *
	 * @since @@version
	 *
	 * @return \RapidAddon
	 */
	public function import(){

		if( ! $this->import ){
			$this->import = new RapidAddon( "WP Job Manager Field Editor {$this->label} Add-On", "wpjm_fe_addon_{$this->slug}" );
		}

		return $this->import;
	}

	/**
	 * Get fields placeholder
	 *
	 *
	 * @since @@version
	 *
	 * @return array
	 */
	public function get_fields(){
		return array();
	}

	/**
	 * Construct placeholder
	 *
	 *
	 * @since @@version
	 *
	 */
	public function construct(){}

	/**
	 * Magic Method to handle multiple file upload callbacks
	 *
	 *
	 * @since @@since
	 *
	 * @param $method_name
	 * @param $args
	 *
	 */
	public function __call( $method_name, $args ) {
		//  $args[0] = pid -- 1 = attachid
		if( stripos( $method_name, 'multiple_files_') === 0 && is_array( $args ) && ! empty( $args[0] ) && ! empty( $args[1] ) ){
			$meta_key = str_replace( 'multiple_files_', '', $method_name );
			$post_id = $args[0];
			$attach_id = $args[1];

			$fe_text = '<strong>WP Job Manager Field Editor:</strong>';

//			$file_path = $args[2];
//			$file_name = basename( $file_path );
			$this->import()->log( "{$fe_text} - {$meta_key} multi file attempting to update meta values" );

			$attach_url = wp_get_attachment_url( $attach_id );

			if( empty( $attach_url ) ){
				$this->import()->log( "{$fe_text} - {$meta_key} unable to get attachmemnt URL" );
				return;
			}

			$existing = get_post_meta( $post_id, $meta_key, true );

			// Might be string value with single entry, convert to array
			if( ! empty( $existing ) && ! is_array( $existing ) ){
				$existing = (array) $existing;
			} elseif( empty( $existing ) ){
				$existing = array();
			}

			if( ! empty( $existing ) ){

				$this->import()->log( "{$fe_text} - {$meta_key} Checking existing meta values for {$attach_url}" );

				foreach ( (array) $existing as $exist ) {
					// If we found this URL already set in meta, no need to keep searching
					if ( strtolower( $exist ) === strtolower( $attach_url ) ) {
						$this->import()->log( "{$fe_text} - {$meta_key} {$attach_url} already found in meta value, skipping update" );
						return;
					}

				}

			}

			$this->import()->log( "{$fe_text} - {$meta_key} Adding {$attach_url} to meta values" );
			// Otherwise add to array, then update meta
			$existing[] = $attach_url;
			update_post_meta( $post_id, $meta_key, $existing );
		}

	}
}