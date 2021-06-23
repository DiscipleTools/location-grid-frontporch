<?php
class Location_Grid_Public_Porch_Home extends Location_Grid_Public_Porch_Base
{
    public $token = 'location_grid_home';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        parent::__construct();

        $url = dt_get_url_path();
        if ( empty($url) && ! dt_is_rest() ) {
            add_action( "template_redirect", [ $this, 'theme_redirect' ] );

            add_filter( 'dt_blank_access', function(){ return true;
            } );
            add_filter( 'dt_allow_non_login_access', function(){ return true;
            }, 100, 1 );

            add_filter( "dt_blank_title", [ $this, "_browser_tab_title" ] );
            add_action( 'dt_blank_head', [ $this, '_header' ] );
            add_action( 'dt_blank_footer', [ $this, '_footer' ] );
            add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key

            // load page elements
            add_action( 'wp_print_scripts', [ $this, '_print_scripts' ], 1500 );
            add_action( 'wp_print_styles', [ $this, '_print_styles' ], 1500 );

            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

            add_filter( 'public_porch_allowed_js', [ $this, '_allowed_js' ], 10, 1 );
            add_filter( 'public_porch_allowed_css', [ $this, '_allowed_css' ], 10, 1 );
        }
    }

    public function _browser_tab_title( $title ){
        return 'Location Grid - Home';
    }
    public function body(){
        require_once( plugin_dir_path(__DIR__) . 'home/template.php');
    }

    public function _allowed_js( $allowed_js ) {
        $allowed_js[] = $this->token;
        return $allowed_js;
    }

    public function _allowed_css( $allowed_css ) {
        $allowed_css[] = $this->token;
        return $allowed_css;
    }

    public function scripts() {
    }
}
Location_Grid_Public_Porch_Home::instance();
