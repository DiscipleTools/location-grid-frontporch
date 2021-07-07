<?php
class Location_Grid_Porch_Home
{
    public $token = 'location_grid_home';
    public $magic = false;
    public $parts = false;
    public $page_title = 'Profile';
    public $root = "grid_app";
    public $type = 'home';
    public $post_type = '';
    private $meta_key = '';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_filter( 'dt_non_standard_front_page', [ $this, 'dt_non_standard_front_page' ], 10, 1 );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        $url = dt_get_url_path();
        if ( empty( $url ) && ! dt_is_rest() ) {
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
    public function theme_redirect() {
        $path = get_theme_file_path( 'template-blank.php' );
        include( $path );
        die();
    }

    public function add_api_routes() {
        $namespace = $this->root . '/v1';
        register_rest_route(
            $namespace, '/endpoint', [
                [
                    'methods'  => "POST",
                    'callback' => [ $this, 'endpoint' ],
                ],
            ]
        );
    }

    public function endpoint( WP_REST_Request $request ) {
        // replace in extended class
        $params = $request->get_params();
        return $params;
    }
    public function dt_non_standard_front_page( $url ) {
        if ( dt_is_rest() ) {
            return $url;
        }
        /**
         * This handles a logged in persons urls
         */
//        if ( user_can( get_current_user_id(), 'registered')) {
//            $current_url = dt_get_url_path();
//            // home
//            if ( empty($current_url) ) {
//                $url = home_url( '/' );
//            }
//            else if ( 'examples' === $current_url ) {
//                $url = home_url( '/examples' );
//            }
//            else if ( 'projects' === $current_url ) {
//                $url = home_url( '/projects' );
//            }
//            else if ( 'login' === $current_url ) {
//                $url = home_url( '/login' );
//            }
//            else if ( 'facts' === $current_url ) {
//                $url = home_url( '/facts' );
//            }
//        }
        return $url;
    }

    public function _header(){
        ?>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Location Grid</title>
        <meta name="description" content="">
        <meta name="keywords" content="">
        <?php // @phpcs:disable ?>
        <link rel="stylesheet" href="<?php echo trailingslashit( plugin_dir_url( __FILE__ ) ) ?>css/styles-merged.css">
        <link rel="stylesheet" href="<?php echo trailingslashit( plugin_dir_url( __FILE__ ) ) ?>css/style.min.css">
        <link rel="stylesheet" href="<?php echo trailingslashit( plugin_dir_url( __FILE__ ) ) ?>fonts/icomoon/style.css">

        <!--[if lt IE 9]>
        <script src="<?php echo trailingslashit( plugin_dir_url( __FILE__ ) ) ?>js/vendor/html5shiv.min.js"></script>
        <script src="<?php echo trailingslashit( plugin_dir_url( __FILE__ ) ) ?>js/vendor/respond.min.js"></script>
        <![endif]-->
        <?php // @phpcs:enable ?>
        <?php
        wp_head();
        $this->header_style();
        $this->header_javascript();
    }
    public function header_javascript(){
        ?>
        <script>
            let jsObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'mirror_url' => dt_get_location_grid_mirror( true ),
                'theme_uri' => trailingslashit( get_stylesheet_directory_uri() ),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'trans' => [
                    'add' => __( 'Add Magic', 'disciple_tools' ),
                ],
            ]) ?>][0]

            jQuery(document).ready(function(){
                clearInterval(window.fiveMinuteTimer)
            })
        </script>
        <?php
        return true;
    }
    public function _footer(){
        wp_footer();
    }
    public function scripts() {
    }
    public function _print_scripts(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php

        $allowed_js = apply_filters( 'public_porch_allowed_js', [
            'jquery',
            //            'lodash',
            //            'site-js',
            //            'shared-functions',
            //            'mapbox-gl',
            //            'mapbox-cookie',
            //            'mapbox-search-widget',
            //            'google-search-widget',
            //            'jquery-cookie',
            //            'jquery-touch-punch',
        ] );

        global $wp_scripts;

        if ( isset( $wp_scripts ) ){
            foreach ( $wp_scripts->queue as $key => $item ){
                if ( ! in_array( $item, $allowed_js ) ){
                    unset( $wp_scripts->queue[$key] );
                }
            }
        }
        unset( $wp_scripts->registered['mapbox-search-widget']->extra['group'] );
    }
    public function _print_styles(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php
        $allowed_css = apply_filters( 'public_porch_allowed_css', [
            'foundation-css',
            'jquery-ui-site-css',
            //            'site-css',
            //            'mapbox-gl-css',
        ] );

        global $wp_styles;
        if ( isset( $wp_styles ) ) {
            foreach ($wp_styles->queue as $key => $item) {
                if ( !in_array( $item, $allowed_css )) {
                    unset( $wp_styles->queue[$key] );
                }
            }
        }
    }
    public function header_style(){
    }
    public function body(){
        require_once( plugin_dir_path( __DIR__ ) . 'home/template.php' );
    }

    public function _allowed_js( $allowed_js ) {
        $allowed_js[] = $this->token;
        return $allowed_js;
    }

    public function _allowed_css( $allowed_css ) {
        $allowed_css[] = $this->token;
        return $allowed_css;
    }
}
Location_Grid_Porch_Home::instance();
