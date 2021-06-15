<?php
class Location_Grid_Public_Porch_Projects extends Location_Grid_Public_Porch_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        parent::__construct();

        $url = dt_get_url_path();
        if ( ( 'projects' === $url ) && ! dt_is_rest() ) {

            add_action( "template_redirect", [ $this, 'my_theme_redirect' ] );

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
        }
    }

    public function _browser_tab_title( $title ){
        return 'Location Grid - Projects';
    }
    public function body(){
        require_once('parts/navigation.html')
        ?>
        Projects
        <?php
    }

}
Location_Grid_Public_Porch_Projects::instance();
