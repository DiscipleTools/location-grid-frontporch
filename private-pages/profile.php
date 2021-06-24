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

        // require lo
        if ( ! is_user_logged_in() ) {
            wp_safe_redirect( dt_custom_login_url('login') );
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
                'google_logo' => plugin_dir_url( __FILE__ ) . 'google.png',
                'wikipedia_logo' => plugin_dir_url( __FILE__ ) .'wikipedia.png',
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
                    <h2>Explore</h2>
                    <div class="grid-x grid-padding-x">
                        <div class="cell medium-4 view-card" data-id="summary">
                            <div class="card">
                                <div class="card-divider">
                                    Summary of Levels
                                </div>
                                <div style="background-color: lightblue; width:100%; height: 75px;"></div>
                                <div class="card-section">
                                    <p>Summary of the location grid database by country and level.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h2>Projects</h2>
                    <div class="grid-x grid-padding-x">
                        <div class="cell medium-4 view-card" data-id="population_difference">
                            <div class="card">
                                <div class="card-divider">
                                    Population Difference Project
                                </div>
                                <div style="background-color: lightgreen; width:100%; height: 75px;"></div>
                                <div class="card-section">
                                    <p>Shows the population difference from the country record to the flat grid calculation.</p>
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

            window.get_data_page = (action, data ) => {
                return jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify({ action: action, parts: jsObject.parts, data: data }),
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

            function load_panel( action, data, title = '' ) {
                switch (action) {
                    case 'summary':
                        load_summary(action, data)
                        break;
                    case 'flat_grid_project':
                        load_flat_grid_project(action, data)
                        break;
                    case 'flat_grid_by_country':
                        load_flat_grid_by_country(action, data, title)
                        break;
                    case 'population_difference':
                        load_population_difference(action, data)
                        break;
                }
            }

            function load_summary( action, data ) {
                let content = jQuery('#reveal-content')
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
            }
            function load_flat_grid_project(action, data) {
                let content = jQuery('#reveal-content')
                content.empty().html(`
                    <h1>Flat Grid</h1>
                    <table class="hover display" id="summary-table">
                        <thead>
                            <tr>
                                <th>Grid ID</th>
                                <th>Name</th>
                                <th>Country Code</th>
                                <th>Level</th>
                                <th>Population</th>
                            </tr>
                        </thead>
                        <tbody id="table-list"></tbody>
                    </table>`)
                let table_list = jQuery('#table-list')
                jQuery.each( data, function(i,v){
                    table_list.append(`<tr><td>${v.grid_id}</td><td>${v.name}</td><td>${v.country_code}</td><td>${v.level}</td><td>${v.population}</td></tr>`)
                })

                jQuery('#summary-table').dataTable({
                    "paging": false
                });
            }

            function load_population_difference(action, data) {
                let content = jQuery('#reveal-content')
                content.empty().html(`
                    <style>#summary-table tr { cursor: pointer;}</style>
                    <h1>Population Difference</h1>
                    <table class="hover display" id="summary-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Country Code</th>
                                <th>Population</th>
                                <th>Flat Population</th>
                                <th>Difference</th>
                            </tr>
                        </thead>
                        <tbody id="table-list"></tbody>
                    </table>`)
                let table_list = jQuery('#table-list')
                jQuery.each( data, function(i,v){
                    table_list.append(`<tr class="country_selection" data-id="${v.country_code}" data-name="${v.name}"><td>${v.name}</td><td>${v.country_code}</td><td>${v.population}</td><td>${v.sum_population}</td><td>${v.difference}</td></tr>`)
                })

                jQuery('#summary-table').dataTable({
                    "paging": false,
                    "order": [ 4, 'desc' ]
                });

                jQuery('.country_selection').on('click', function(e){

                    $('#reveal-content').html(`<span class="loading-spinner active"></span>`)
                    $('#modal').foundation('open')
                    let cc = jQuery(this).data('id')
                    let name = jQuery(this).data('name')
                    window.get_data_page( 'flat_grid_by_country', cc )
                        .done(function( data ) {
                            load_panel( 'flat_grid_by_country', data, name )
                        })
                })
            }
            function load_flat_grid_by_country(action, data, title) {
                let content = jQuery('#reveal-content')
                content.empty().html(`
                    <style>.social-icon { height: 20px; padding: 2px; cursor:pointer;} .verified{display:none;}</style>
                    <h1>Flat Grid - <span id="country_code">${title}</span> <button class="button tiny hollow" style="position:absolute; top:10px; right:150px;" onclick="jQuery('.verified').toggle()">show verified</button></h1>
                    <table class="hover display" id="summary-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Links</th>
                                <th>Level</th>
                                <th>Population</th>
                                <th>Update</th>
                                <th>Verified</th>
                            </tr>
                        </thead>
                        <tbody id="table-list"></tbody>
                    </table>`)
                let table_list = jQuery('#table-list')
                jQuery.each( data, function(i,v){
                    let check = ''
                    if ( v.verified !== '' ){
                        check = '&#9989;'
                    }
                    table_list.append(
                        `<tr class="${v.grid_id} ${v.verified}" id="${v.grid_id}">
                            <td>${v.full_name}</td>
                            <td><img class="social-icon" src="${jsObject.google_logo}" data-url="https://www.google.com/search?q=${encodeURIComponent(v.full_name)}+population" />
                                <img class="social-icon" src="${jsObject.wikipedia_logo}" data-url="https://en.wikipedia.org/wiki/Special:Search?search=${encodeURIComponent(v.full_name)}"/></td>
                            <td>${v.level}</td>
                            <td>${v.formatted_population}</td>
                            <td><input type="text" class="input"  data-id="${v.grid_id}" data-old="${v.population}" /></td>
                            <td id="verified_${v.grid_id}">${check}</td>
                        </tr>`
                    )
                })

                jQuery('#summary-table').dataTable({
                    "paging": false
                });

                jQuery('.social-icon').on('click', function(){
                    let url = jQuery(this).data('url')
                    window.open( url, "_blank");
                })

                jQuery('.input').blur(function() {
                    let value = jQuery(this).val()
                    let id = jQuery(this).data('id')
                    let old = jQuery(this).data('old')

                    if ( value === '' || value === ' ' || value < 100 ) {
                        return
                    }

                    jQuery('#verified_'+id).html('saving...')

                    let data = {'grid_id': id, 'old_value': old, 'new_value': value }
                    window.get_data_page('update_population', data )
                        .done(function(result) {
                            if ( result.status === 'OK' ){
                                jQuery('#'+id).addClass('verified')
                                jQuery('#verified_'+id).html('&#9989;')
                            }
                            console.log(result)
                        })
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
                return Location_Grid_Queries::summary();
            case 'flat_grid_project':
                return Location_Grid_Queries::flat_grid_fname_fpop();
            case 'flat_grid_by_country':
                return Location_Grid_Queries::flat_grid_by_country( $params['data'] );
            case 'population_difference':
                return Location_Grid_Queries::population_difference();
            case 'update_population':
                return $this->update_population( $params['data'] );

            default:
                return new WP_Error( __METHOD__, "Missing valid action", [ 'status' => 400 ] );
        }
    }

    public function update_population( $data ) {
        global $wpdb;
        if ( ! isset( $data['grid_id'], $data['new_value'], $data['old_value'] ) ) {
            return new WP_Error( __METHOD__, "Missing parameters", [ 'status' => 400 ] );
        }

        $user_id = get_current_user_id();
        if ( empty( $user_id ) ) {
            return new WP_Error( __METHOD__, "Missing user id", [ 'status' => 400 ] );
        }

        $new_value = intval( str_replace( ',', '', trim( $data['new_value'] ) ) );
        $old_value = intval( str_replace( ',', '', trim( $data['old_value'] ) ) );
        $timestamp = time();

        $result = $wpdb->query( $wpdb->prepare("
            INSERT INTO location_grid_edit_log (grid_id, user_id, type, subtype, old_value, new_value, timestamp )
            VALUES (%d, %d, 'population', 'flat_grid_project', %s, %s, %d );
        ", $data['grid_id'], $user_id, $old_value, $new_value, $timestamp ) );

        if ( $result ) {
            return [
                'status' => 'OK',
                'result' => $result,
                'data' => $data
            ];
        } else {
            return [
                'status' => false,
                'result' => $result,
                'data' => $data
            ];
        }

    }

}

