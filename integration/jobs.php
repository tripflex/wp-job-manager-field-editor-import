<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPAI_WPJM_Field_Editor_Integration_Jobs
 *
 * @since @@version
 *
 */
class WPAI_WPJM_Field_Editor_Integration_Jobs extends WPAI_WPJM_Field_Editor_Integration {

	/**
	 * @var string
	 */
	public $slug = 'jobs';

	/**
	 * @var array
	 */
	public $post_types = array( 'job_listing' );

	/**
	 * Construct (after extended class)
	 *
	 *
	 * @since @@version
	 *
	 */
	public function construct(){
		$this->label = __( 'Jobs' );
		$this->import()->set_post_type_image( 'job_listing', WPJM_FIELD_EDITOR_IMPORT_PLUGIN_URL . '/assets/wp_job_manager.png' );
	}

	/**
	 * Get Job Fields
	 *
	 *
	 * @since @@version
	 *
	 * @return array
	 */
	public function get_fields(){
		$fields = $this->fe()->get_custom_fields();

		$job = $this->ene( 'job', $fields, true, array() );
		$company = $this->ene( 'company', $fields, true, array() );

		$all = array_merge( $job, $company );

		return $all;
	}
}