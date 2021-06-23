<?php
class Location_Grid_Public_Porch_Profile
{
    public $token = 'location_grid_profile';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {


        $url = dt_get_url_path();
        if ( ( 'profile' === $url ) && ! dt_is_rest() ) {


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
        }
    }

    public function _browser_tab_title( $title ){
        return 'Location Grid - Profile';
    }
    public function body(){
        require_once('parts/navigation.php')
        ?>
        <style>
            .view-card {
                cursor: pointer;
            }
        </style>
        <div class="wrapper" style="max-width:1200px;margin: 0 auto;">
            <div class="grid-x grid-padding-x">
                <div class="cell">
                    <div class="grid-x grid-padding-x">
                        <div class="cell medium-4 view-card" data-id="hierarchy">
                            <div class="card">
                                <div class="card-divider">
                                    Hierarchy
                                </div>
                                <img src="https://via.placeholder.com/150">
                                <div class="card-section">
                                    <p>It has an easy to override visual style, and is appropriately subdued.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.get_page = (action) => {
                return jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify({ action: action }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: jsObject.root + 'lg_porch/v1/profile',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce )
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                        jQuery('#error').html(e)
                    })
            }

            jQuery(document).ready(function($){
                $('.view-card').on('click', function(e){
                    // console.log(e)
                    let action = $(this).data('id')
                    console.log(action)

                    $('#reveal-content').html(`<span class="loading-spinner active"></span>`)
                    $('#modal').foundation('open')

                    window.get_page( action )
                    .done(function( data ) {
                        load_panel( action, data )
                    })
                })
            })

            function load_panel( action, data ) {
                let content = jQuery('#reveal-content')
                jQuery.each( data, function(i,v){
                    content.append(`<div>${v.name} ${v.count}</div>`)
                })
            }
        </script>
        <div class="reveal full" id="modal" data-reveal>
            <div id="reveal-content"></div>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">Close &times;</span>
            </button>
        </div>

        <?php
    }
    public function _allowed_js( $allowed_js ) {
        $allowed_js[] = $this->token;
        return $allowed_js;
    }

    public function scripts() {
//        wp_enqueue_script( $this->token, plugin_dir_url( __FILE__ ) . 'js/home.js', ['jquery', 'jquery-ui', 'jquery-touch-punch'], filemtime( plugin_dir_path( __FILE__ ) . 'js/home.js' ) );
    }

    public function endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();

        return true;

        if ( ! isset( $params['parts'], $params['action'] ) ) {
            return new WP_Error( __METHOD__, "Missing parameters", [ 'status' => 400 ] );
        }

        $params = dt_recursive_sanitize_array( $params );
        $action = sanitize_text_field( wp_unslash( $params['action'] ) );

        switch ( $action ) {
            case 'hierarchy':
                return $this->hierarchy( );
            default:
                return new WP_Error( __METHOD__, "Missing valid action", [ 'status' => 400 ] );
        }
    }

    public function hierarchy( ) {
        global $wpdb;
        return $wpdb->get_results("
            SELECT name, COUNT(admin0_grid_id) as count
            FROM $wpdb->location_grid
            GROUP BY admin0_grid_id, name
            ", ARRAY_A );

    }

}
Location_Grid_Public_Porch_Profile::instance();
