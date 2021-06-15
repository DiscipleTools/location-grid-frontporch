<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Configures the site link system for the network reporting
 */

class Location_Grid_Public_Porch_Site_Links {
    public $type = 'location_grid_public_porch';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        // Add the specific capabilities needed for the site to site linking.
        add_filter( 'site_link_type_capabilities', [ $this, 'site_link_capabilities' ], 10, 1 );

        // Adds the type to the site link system
        add_filter( 'site_link_type', [ $this, 'site_link_type' ], 10, 1 );
    }

    public function site_link_capabilities( $args ) {
        if ( $this->type === $args['connection_type'] ) {
            $args['capabilities'][] = 'create_' . $this->type;
            $args['capabilities'][] = 'update_any_' . $this->type;
            // @todo add other capabilities here
        }
        return $args;
    }

    public function site_link_type( $type ) {
        $type[$this->type] = __( 'Location Grid Public Porch' );
        return $type;
    }
}
Location_Grid_Public_Porch_Site_Links::instance();

