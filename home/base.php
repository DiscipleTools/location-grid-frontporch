<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Location_Grid_Public_Porch_Base
{
    public $root = 'lg_porch';

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
    }

    public function theme_redirect() {
        $path = get_theme_file_path('template-blank.php');
        include( $path );
        die();
    }

    public function add_endpoints() {
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

        <link rel="stylesheet" href="<?php echo trailingslashit( plugin_dir_url(__FILE__) ) ?>css/styles-merged.css">
        <link rel="stylesheet" href="<?php echo trailingslashit( plugin_dir_url(__FILE__) ) ?>css/style.min.css">
        <link rel="stylesheet" href="<?php echo trailingslashit( plugin_dir_url(__FILE__) ) ?>fonts/icomoon/style.css">

        <!--[if lt IE 9]>
        <script src="<?php echo trailingslashit( plugin_dir_url(__FILE__) ) ?>js/vendor/html5shiv.min.js"></script>
        <script src="<?php echo trailingslashit( plugin_dir_url(__FILE__) ) ?>js/vendor/respond.min.js"></script>
        <![endif]-->
        <?php
        wp_head();
        $this->header_style();
        $this->header_javascript();
    }
    public function header_style(){
        ?>

        <?php
    }
    public function _browser_tab_title( $title ){
        return 'Location Grid';
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
}
Location_Grid_Public_Porch_Base::instance();
