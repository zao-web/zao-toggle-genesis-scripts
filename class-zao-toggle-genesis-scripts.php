<?php
/**
 * CMB2 Genesis Toggle Scripts Metabox Settings Metabox
 *
 * @version 1.3.2
 */
class Zao_Toggle_Genesis_Scripts {

	/**
	 * Option key. Could maybe be 'genesis-seo-settings', or other section?
	 *
	 * @var string
	 */
	protected $key = 'genesis-settings';

	/**
	 * The admin page slug.
	 *
	 * @var string
	 */
	protected $admin_page = 'genesis';

	/**
	 * Options page metabox id
	 *
	 * @var string
	 */
	protected $metabox_id = 'qp_genesis_script_metabox_settings';

	/**
	 * Admin page hook
	 *
	 * @var string
	 */
	protected $admin_hook = 'toplevel_page_genesis';

	/**
	 * Holds an instance of CMB2
	 *
	 * @var CMB2
	 */
	protected $cmb = null;

	/**
	 * Holds an instance of the object
	 *
	 * @var Zao_Toggle_Genesis_Scripts
	 */
	protected static $instance = null;

	/**
	 * Returns the running object
	 *
	 * @return Zao_Toggle_Genesis_Scripts
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.3.2
	 */
	protected function __construct() {}

	/**
	 * Initiate our hooks
	 *
	 * @since 1.3.2
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'toggle_script_metabox_supports' ), 9 );
		add_action( 'admin_menu', array( $this, 'admin_hooks' ) );
	}

	/**
	 * Add menu options page
	 *
	 * @since 1.3.2
	 */
	public function admin_hooks() {
		// Include CMB CSS in the head to avoid FOUC.
		add_action( "admin_print_styles-{$this->admin_hook}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );

		// Hook into the genesis cpt setttings save and add in the CMB2 sanitized values.
		add_filter( "sanitize_option_{$this->key}", array( $this, 'add_sanitized_values' ), 999 );

		// Hook up our Genesis metabox.
		add_action( 'genesis_theme_settings_metaboxes', array( $this, 'add_meta_box' ) );
	}


	/**
	 * Hook up our Genesis metabox.
	 *
	 * @since 1.3.2
	 */
	public function add_meta_box() {
		$cmb = $this->init_metabox();
		add_meta_box(
			$cmb->cmb_id,
			$cmb->prop( 'title' ),
			array( $this, 'output_metabox' ),
			$this->admin_hook,
			$cmb->prop( 'context' ),
			$cmb->prop( 'priority' )
		);
	}

	/**
	 * Output our Genesis metabox.
	 *
	 * @since 1.3.2
	 */
	public function output_metabox() {
		$cmb = $this->init_metabox();
		$cmb->show_form( $cmb->object_id(), $cmb->object_type() );
	}

	/**
	 * If saving the cpt settings option, add the CMB2 sanitized values.
	 *
	 * @since 1.3.2
	 *
	 * @param array $new_value Array of values for the setting.
	 *
	 * @return array Updated array of values for the setting.
	 */
	public function add_sanitized_values( $new_value ) {
		if ( ! empty( $_POST ) ) {
			$cmb = $this->init_metabox();

			$new_value = array_merge(
				$new_value,
				$cmb->get_sanitized_values( $_POST )
			);
		}

		return $new_value;
	}

	/**
	 * Register our Genesis metabox and return the CMB2 instance.
	 *
	 * @since  1.3.2
	 *
	 * @return CMB2 instance.
	 */
	public function init_metabox() {
		if ( null !== $this->cmb ) {
			return $this->cmb;
		}

		$this->cmb = cmb2_get_metabox( array(
			'id'           => $this->metabox_id,
			'title'        => __( 'Scripts Metabox Toggle', 'qp' ),
			'hookup'       => false, // We'll handle ourselves. (add_sanitized_values())
			'cmb_styles'   => false, // We'll handle ourselves. (admin_hooks())
			'context'      => 'main', // Important for Genesis.
			// 'priority'     => 'low', // Defaults to 'high'.
			'object_types' => array( $this->admin_hook ),
			'show_on'      => array(
				// These are important, don't remove.
				'key'   => 'options-page',
				'value' => array( $this->key ),
			),
		), $this->key, 'options-page' );

		// Set our CMB2 multicheck field.
		$this->cmb->add_field( array(
			'name'       => __( 'Toggle the Scripts metabox for the following post-types', 'qp' ),
			'desc'       => __( 'Deselecting all and saving will reset to the default post-types registered for the Genesis Scripts Metabox.', 'qp' ),
			'id'         => 'scripts_mb_types',
			'type'       => 'multicheck_inline',
			'options_cb' => array( __CLASS__, 'get_post_types' ),
			'default_cb' => array( __CLASS__, 'get_supports_default' )
		) );

		return $this->cmb;
	}

	/**
	 * Get all public post-types which have an admin UI.
	 *
	 * @since  1.3.2
	 *
	 * @return array
	 */
	public static function get_post_types() {
		return wp_list_pluck( (array) get_post_types( array(
			'public'  => true,
			'show_ui' => true,
		), 'objects' ), 'label' );
	}

	/**
	 * Get all the post-types that have the 'genesis-scripts' support by default.
	 *
	 * @since  1.3.2
	 *
	 * @return array
	 */
	public static function get_supports_default() {
		$value = array();
		foreach ( self::get_post_types() as $post_type => $label ) {
			if ( post_type_supports( $post_type, 'genesis-scripts' ) ) {
				$value[] = $post_type;
			}
		}

		return $value;
	}

	/**
	 * Handles registering post-type support for 'genesis-scripts', the Genesis script metabox.
	 *
	 * @since  1.3.2
	 */
	public function toggle_script_metabox_supports() {
		$cmb = $this->init_metabox();
		$field = $cmb->get_field( 'scripts_mb_types' );

		// If no value, we'll leave well-enough alone.
		if ( empty( $field->value ) ) {
			return;
		}

		// Ok, we have our "allowed" value, so flip it (for more efficient reference).
		$allowed = array_flip( $field->value );

		// Loop through
		foreach ( self::get_post_types() as $post_type => $label ) {
			if ( isset( $allowed[ $post_type ] ) ) {
				add_post_type_support( $post_type, 'genesis-scripts' );
			} else {
				// Remove it if it is not checked.
				remove_post_type_support( $post_type, 'genesis-scripts' );
			}
		}
	}

	/**
	 * Public getter method for retrieving protected/private variables.
	 *
	 * @since 1.3.2
	 *
	 * @param string $field Field to retrieve.
	 *
	 * @throws Exception Throws an exception if the field is invalid.
	 *
	 * @return mixed Field value or exception is thrown
	 */
	public function __get( $field ) {
		if ( 'cmb' === $field ) {
			return $this->init_metabox();
		}

		// Allowed fields to retrieve.
		if ( in_array( $field, array( 'key', 'admin_page', 'metabox_id', 'admin_hook' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

}
