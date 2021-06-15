<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Location_Grid_Public_Porch_Charts
{
    private static $_instance = null;
    public static function instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct(){

        require_once( 'one-page-chart-template.php' );
        new Location_Grid_Public_Porch_Chart_Template();

        /**
         * @todo add other charts like the pattern above here
         */

    } // End __construct
}
Location_Grid_Public_Porch_Charts::instance();
