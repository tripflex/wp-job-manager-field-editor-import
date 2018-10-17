<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPAI_WPJM_Field_Editor_Integration_Resumes
 *
 * @since @@version
 *
 */
class WPAI_WPJM_Field_Editor_Integration_Resumes extends WPAI_WPJM_Field_Editor_Integration {

	/**
	 * @var string
	 */
	public $slug = 'resumes';

	/**
	 * @var array
	 */
	public $post_types = array( 'resume' );

	/**
	 * Construct (after extended class)
	 *
	 *
	 * @since @@version
	 *
	 */
	public function construct(){
		$this->label = __( 'Resumes' );
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
		return $this->fe()->get_custom_fields( 'resume_fields' );
	}
}