<?php
/**
 * Form class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models\Core_Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use awsmug\Torro_Forms\DB_Objects\Containers\Container_Collection;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Collection;
use awsmug\Torro_Forms\DB_Objects\Participants\Participant_Collection;
use WP_Post;
use stdClass;

/**
 * Class representing a form.
 *
 * @since 1.0.0
 *
 * @property string $title
 * @property string $slug
 * @property int    $author
 * @property string $status
 * @property int    $timestamp
 * @property int    $timestamp_modified
 *
 * @property-read int $id
 */
class Form extends Core_Model {
	use Sitewide_Model_Trait;

	/**
	 * Constructor.
	 *
	 * Sets the ID and fetches relevant data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form_Manager $manager The manager instance for the model.
	 * @param WP_Post|null $db_obj  Optional. The database object or null for a new instance.
	 */
	public function __construct( $manager, $db_obj = null ) {
		parent::__construct( $manager, $db_obj );

		$this->redundant_prefix = 'post_';
	}

	/**
	 * Magic isset-er.
	 *
	 * Checks whether a property is set.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to check for.
	 * @return bool True if the property is set, false otherwise.
	 */
	public function __isset( $property ) {
		switch ( $property ) {
			case 'id':
			case 'slug':
			case 'author':
			case 'timestamp':
			case 'timestamp_modified':
				return true;
		}

		return parent::__isset( $property );
	}

	/**
	 * Magic getter.
	 *
	 * Returns a property value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to get.
	 * @return mixed Property value, or null if property is not set.
	 */
	public function __get( $property ) {
		switch ( $property ) {
			case 'id':
				return $this->original->ID;
			case 'slug':
				return $this->original->post_name;
			case 'author':
				return (int) $this->original->post_author;
			case 'timestamp':
				return (int) strtotime( $this->original->post_date_gmt );
			case 'timestamp_modified':
				return (int) strtotime( $this->original->post_modified_gmt );
		}

		return parent::__get( $property );
	}

	/**
	 * Magic setter.
	 *
	 * Sets a property value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $property Property to set.
	 * @param mixed  $value    Property value.
	 */
	public function __set( $property, $value ) {
		$found = false;
		$changed = false;

		switch ( $property ) {
			case 'id':
				return;
			case 'slug':
				$found = true;
				if ( $this->original->post_name !== $value ) {
					$this->original->post_name = $value;
					$changed = true;
				}
				break;
			case 'author':
				$found = true;
				if ( (int) $this->original->post_author !== (int) $value ) {
					$this->original->post_author = (int) $value;
					$changed = true;
				}
				break;
			case 'timestamp':
				$found = true;
				if ( (int) strtotime( $this->original->post_date_gmt ) !== (int) $value ) {
					$this->original->post_date = '0000-00-00 00:00:00';
					$this->original->post_date_gmt = date( 'Y-m-d H:i:s', $value );
					$changed = true;
				}
				break;
			case 'timestamp_modified':
				$found = true;
				if ( (int) strtotime( $this->original->post_modified_gmt ) !== (int) $value ) {
					$this->original->post_modified = '0000-00-00 00:00:00';
					$this->original->post_modified_gmt = date( 'Y-m-d H:i:s', $value );
					$changed = true;
				}
				break;
		}

		if ( $found ) {
			if ( $changed && ! in_array( $property, $this->pending_properties, true ) ) {
				$this->pending_properties[] = $property;
			}

			return;
		}

		parent::__set( $property, $value );
	}

	/**
	 * Returns all containers that belong to the form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Optional. Additional query arguments. Default empty array.
	 * @return Container_Collection List of containers.
	 */
	public function get_containers( $args = array() ) {
		if ( empty( $this->original->ID ) ) {
			return $this->manager->get_child_manager( 'containers' )->get_collection( array(), 0, 'objects' );
		}

		$args = wp_parse_args( $args, array(
			'number'  => -1,
			'form_id' => $this->original->ID,
		) );

		return $this->manager->get_child_manager( 'containers' )->query( $args );
	}

	/**
	 * Returns all submissions that belong to the form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Optional. Additional query arguments. Default empty array.
	 * @return Submission_Collection List of submissions.
	 */
	public function get_submissions( $args = array() ) {
		if ( empty( $this->original->ID ) ) {
			return $this->manager->get_child_manager( 'submissions' )->get_collection( array(), 0, 'objects' );
		}

		$args = wp_parse_args( $args, array(
			'number'  => -1,
			'form_id' => $this->original->ID,
		) );

		return $this->manager->get_child_manager( 'submissions' )->query( $args );
	}

	/**
	 * Returns all participants that belong to the form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Optional. Additional query arguments. Default empty array.
	 * @return Participant_Collection List of participants.
	 */
	public function get_participants( $args = array() ) {
		if ( empty( $this->original->ID ) ) {
			return $this->manager->get_child_manager( 'participants' )->get_collection( array(), 0, 'objects' );
		}

		$args = wp_parse_args( $args, array(
			'number'  => -1,
			'form_id' => $this->original->ID,
		) );

		return $this->manager->get_child_manager( 'participants' )->query( $args );
	}

	/**
	 * Returns an array representation of the model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $include_meta Optional. Whether to include metadata for each model in the collection.
	 *                           Default true.
	 * @return array Array including all information for the model.
	 */
	public function to_json( $include_meta = true ) {
		$data = parent::to_json( $include_meta );

		/**
		 * Filters the form tag classes.
		 *
		 * @since 1.0.0
		 *
		 * @param array $form_classes Array of form classes.
		 * @param Form  $form         Form object.
		 */
		$form_classes = apply_filters( "{$this->manager->get_prefix()}form_classes", array( 'torro-form' ), $this );

		/**
		 * Filters the form action URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $form_action_url Form action URL.
		 * @param int    $form_id         Form ID.
		 */
		$form_action_url = apply_filters( 'torro_form_action_url', home_url( $_SERVER['REQUEST_URI'] ), $this->id );

		$data['form_attrs'] = array(
			'id'         => 'torro-form-' . $this->id,
			'class'      => implode( ' ', $form_classes ),
			'action'     => $form_action_url,
			'method'     => 'post',
			'enctype'    => 'multipart/form-data',
			'novalidate' => true,
		);

		return $data;
	}

	/**
	 * Returns all current values as $property => $value pairs.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param bool $pending_only Whether to only return pending properties. Default false.
	 * @return array Array of $property => $value pairs.
	 */
	protected function get_property_values( $pending_only = false ) {
		$properties = array( 'id', 'title', 'slug', 'author', 'status', 'timestamp', 'timestamp_modified' );
		if ( $pending_only ) {
			$properties = $this->pending_properties;
		}

		$values = array();
		foreach ( $properties as $property ) {
			$values[ $property ] = $this->__get( $property );
		}

		return $values;
	}

	/**
	 * Fills the $original property with a default object.
	 *
	 * This method is called if a new object has been instantiated.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function set_default_object() {
		$this->original = new WP_Post( new stdClass() );
	}

	/**
	 * Returns the names of all properties that should be accessible on the Core object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of property names.
	 */
	protected function get_db_fields() {
		return array(
			'post_title',
			'post_author',
			'post_status',
		);
	}
}
