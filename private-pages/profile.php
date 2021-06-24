<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

LG_Public_Porch_Profile::instance();

class LG_Public_Porch_Profile extends DT_Magic_Url_Base {

    public $magic = false;
    public $parts = false;
    public $page_title = 'Profile';
    public $root = "grid_app";
    public $type = 'profile';
    public $post_type = 'contacts';
    private $meta_key = '';
    public $allowed_scripts = ['datatables'];
    public $allowed_styles = ['datatables'];

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        $this->meta_key = $this->root . '_' . $this->type . '_magic_key';
        parent::__construct();

        /**
         * post type and module section
         */
        add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );
        /**
         * Magic Url Section
         */
        //don't load the magic link page for other urls
        // fail if not valid url
        $this->magic = new DT_Magic_URL( $this->root );
        $this->parts = $this->magic->parse_url_parts();
        if ( !$this->parts ){
            return;
        }

        // fail if does not match type
        if ( $this->type !== $this->parts['type'] ){
            return;
        }

        // fail if not valid url
        $url = dt_get_url_path();
        if ( strpos( $url, $this->root . '/' . $this->type ) === false ) {
            return;
        }

        // load if valid url
        add_action( 'dt_blank_head', [ $this, '_header' ] );
        add_action( 'dt_blank_footer', [ $this, '_footer' ] );
        add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key

        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

    }

    public function _header(){
        wp_head();
        $this->header_style();
        $this->header_javascript();
    }
    public function _footer(){
        wp_footer();
    }

    public function scripts() {
        wp_enqueue_style( 'datatables', '//cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css' );
        wp_enqueue_script( 'datatables', '//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js', ['jquery'] );
    }

    public function header_style(){
        ?>
        <style>
            body {
                background-color: white;
                padding: 1em;
            }
        </style>
        <?php
    }
    public function header_javascript(){
        ?>
        <script>
            let jsObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'translations' => [
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

    public function body(){
        require_once('part-navigation.php')
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
                        <div class="cell medium-4 view-card" data-id="summary">
                            <div class="card">
                                <div class="card-divider">
                                    Summary
                                </div>
                                <img src="https://via.placeholder.com/150">
                                <div class="card-section">
                                    <p>Summary of the location grid database by country and level.</p>
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
                    data: JSON.stringify({ action: action, parts: jsObject.parts }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
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

                switch( action ) {
                    case 'summary':
                        content.empty().html(`
                        <h1>Summary of the Location Grid Database</h1>
                        <table class="hover display" id="summary-table">
                            <thead>
                                <tr>
                                    <th>Grid ID</th>
                                    <th>Name</th>
                                    <th>Country Code</th>
                                    <th>Level</th>
                                    <th>Records</th>
                                </tr>
                            </thead>
                            <tbody id="table-list"></tbody>
                        </table>`)
                        let table_list = jQuery('#table-list')
                        jQuery.each( data, function(i,v){
                            table_list.append(`<tr><td>${v.grid_id}</td><td>${v.name}</td><td>${v.country_code}</td><td>${v.level_name}</td><td>${v.count}</td></tr>`)
                        })

                        jQuery('#summary-table').dataTable({
                            "paging": false
                        });

                        break;
                }


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

    /**
     * Register REST Endpoints
     * @link https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
     */
    public function add_endpoints() {
        $namespace = $this->root . '/v1';
        register_rest_route(
            $namespace, '/'.$this->type, [
                [
                    'methods'  => "POST",
                    'callback' => [ $this, 'endpoint' ],
                    'permission_callback' => function( WP_REST_Request $request ){
                        return true;
                    },
                ],
            ]
        );
    }

    public function endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();

        if ( ! isset( $params['parts'], $params['action'] ) ) {
            return new WP_Error( __METHOD__, "Missing parameters", [ 'status' => 400 ] );
        }

        $params = dt_recursive_sanitize_array( $params );
        $action = sanitize_text_field( wp_unslash( $params['action'] ) );

        switch ( $action ) {
            case 'summary':
                return $this->summary();

            default:
                return new WP_Error( __METHOD__, "Missing valid action", [ 'status' => 400 ] );
        }
    }

    public function summary() {
        global $wpdb;
        $data = $wpdb->get_results("
            SELECT
            (SELECT g.grid_id FROM location_grid as g WHERE g.country_code = l.country_code AND level = 0 LIMIT 1) as grid_id,
            (SELECT n.name FROM location_grid as n WHERE n.country_code = l.country_code AND level = 0 LIMIT 1) as name,
            l.country_code,
            l.level_name,
            count(*) as count
            FROM location_grid as l
            GROUP BY l.country_code, l.level_name;
        ", ARRAY_A );

        return $data;
    }
}

