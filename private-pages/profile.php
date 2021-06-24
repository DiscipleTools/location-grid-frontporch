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
                                <img src="https://via.placeholder.com/150x50">
                                <div class="card-section">
                                    <p>Summary of the location grid database by country and level.</p>
                                </div>
                            </div>
                        </div>

                        <div class="cell medium-4 view-card" data-id="population_difference">
                            <div class="card">
                                <div class="card-divider">
                                    Population Difference
                                </div>
                                <img src="https://via.placeholder.com/150x50">
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

            function load_panel( action, data ) {
                switch (action) {
                    case 'summary':
                        load_summary(action, data)
                        break;
                    case 'flat_grid_project':
                        load_flat_grid_project(action, data)
                        break;
                    case 'flat_grid_by_country':
                        load_flat_grid_by_country(action, data)
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
                    table_list.append(`<tr class="country_selection" data-id="${v.country_code}"><td>${v.name}</td><td>${v.country_code}</td><td>${v.population}</td><td>${v.sum_population}</td><td>${v.difference}</td></tr>`)
                })

                jQuery('#summary-table').dataTable({
                    "paging": false,
                    "order": [ 4, 'desc' ]
                });

                jQuery('.country_selection').on('click', function(e){

                    $('#reveal-content').html(`<span class="loading-spinner active"></span>`)
                    $('#modal').foundation('open')
                    let cc = jQuery(this).data('id')
                    window.get_data_page( 'flat_grid_by_country', cc )
                        .done(function( data ) {
                            load_panel( 'flat_grid_by_country', data )
                        })
                })
            }
            function load_flat_grid_by_country(action, data) {
                let content = jQuery('#reveal-content')
                content.empty().html(`
                    <h1>Flat Grid - <span id="country_code"></span></h1>
                    <table class="hover display" id="summary-table">
                        <thead>
                            <tr>
                                <th>Grid ID</th>
                                <th>Name</th>
                                <th>Level</th>
                                <th>Population</th>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody id="table-list"></tbody>
                    </table>`)
                let country_code = jQuery('#country_code')
                let table_list = jQuery('#table-list')
                jQuery.each( data, function(i,v){
                    table_list.append(`<tr><td>${v.grid_id}</td><td>${v.name}</td><td>${v.level}</td><td>${v.population}</td><td><input type="text"  /></td></tr>`)
                    country_code.html(`${v.country_code}`)
                })

                jQuery('#summary-table').dataTable({
                    "paging": false
                });
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
            case 'flat_grid_project':
                return $this->flat_grid_project();
            case 'flat_grid_by_country':
                return $this->flat_grid_by_country( $params['data'] );
            case 'population_difference':
                return $this->population_difference();

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

    public function flat_grid_project() {
        global $wpdb;
        $data = $wpdb->get_results("
            SELECT
            lg1.grid_id, lg1.population, lg1.name, lg1.country_code, lg1.level
            FROM $wpdb->location_grid lg1
            WHERE lg1.level = 0
			AND lg1.grid_id NOT IN ( SELECT lg11.admin0_grid_id FROM $wpdb->location_grid lg11 WHERE lg11.level = 1 AND lg11.admin0_grid_id = lg1.grid_id )
 			#'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg1.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg1.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)
			# above admin 0 (22)

			UNION ALL
            --
            # admin 1 for countries that have no level 2 (768)
            --
            SELECT
            lg2.grid_id, lg2.population, lg2.name, lg2.country_code, lg2.level
            FROM $wpdb->location_grid lg2
            WHERE lg2.level = 1
			AND lg2.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM $wpdb->location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg2.grid_id )
             #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg2.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg2.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

            UNION ALL
			--
            # admin 2 all countries (37100)
            --
			SELECT
            lg3.grid_id, lg3.population, lg3.name, lg3.country_code, lg3.level
            FROM $wpdb->location_grid lg3
            WHERE lg3.level = 2
            #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg3.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg3.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

			UNION ALL
            --
            # admin 1 for little highly divided countries (352)
            --
            SELECT
            lg4.grid_id, lg4.population, lg4.name, lg4.country_code, lg4.level
            FROM $wpdb->location_grid lg4
            WHERE lg4.level = 1
            #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg4.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg4.admin0_grid_id IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

			UNION ALL

 			--
            # admin 3 for big countries (6153)
            --
            SELECT
            lg5.grid_id, lg5.population, lg5.name, lg5.country_code, lg5.level
            FROM $wpdb->location_grid as lg5
            WHERE
            lg5.level = 3
            #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg5.admin0_grid_id IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg5.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

			# Total Records (44395)
        ", ARRAY_A );

        return $data;
    }

    public function population_difference() {
        global $wpdb;
        $data = $wpdb->get_results("
            SELECT tb.name, tb.country_code, FORMAT( tb.population, 0) as population, FORMAT( tb.sum_population, 0) as sum_population, FORMAT(sum_population - population, 0) as difference
            FROM (
            SELECT
            l.name,
            l.country_code,
            l.population,
            (SELECT SUM(p.population) as population FROM $wpdb->location_grid p WHERE p.country_code = l.country_code GROUP BY p.country_code) as sum_population
            FROM $wpdb->location_grid l
            WHERE l.level = 0
            ) as tb
        ", ARRAY_A );

        return $data;
    }

    public function flat_grid_by_country( $country_code ) {
        global $wpdb;
        $data = $wpdb->get_results($wpdb->prepare( "
            SELECT
            lg1.grid_id, FORMAT( lg1.population, 0) as population, lg1.name, lg1.country_code, lg1.level
            FROM $wpdb->location_grid lg1
            WHERE lg1.level = 0
			AND lg1.grid_id NOT IN ( SELECT lg11.admin0_grid_id FROM $wpdb->location_grid lg11 WHERE lg11.level = 1 AND lg11.admin0_grid_id = lg1.grid_id )
 			#'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg1.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg1.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)
			# above admin 0 (22)
            AND lg1.country_code = %s

			UNION ALL
            --
            # admin 1 for countries that have no level 2 (768)
            --
            SELECT
            lg2.grid_id, FORMAT( lg2.population, 0) as population, lg2.name, lg2.country_code, lg2.level
            FROM $wpdb->location_grid lg2
            WHERE lg2.level = 1
			AND lg2.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM $wpdb->location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg2.grid_id )
             #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg2.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg2.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)
            AND lg2.country_code = %s


            UNION ALL
			--
            # admin 2 all countries (37100)
            --
			SELECT
            lg3.grid_id, FORMAT( lg3.population, 0) as population, lg3.name, lg3.country_code, lg3.level
            FROM $wpdb->location_grid lg3
            WHERE lg3.level = 2
            #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg3.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg3.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)
            AND lg3.country_code = %s

			UNION ALL
            --
            # admin 1 for little highly divided countries (352)
            --
            SELECT
            lg4.grid_id, FORMAT( lg4.population, 0) as population, lg4.name, lg4.country_code, lg4.level
            FROM $wpdb->location_grid lg4
            WHERE lg4.level = 1
            #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg4.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg4.admin0_grid_id IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)
            AND lg4.country_code = %s


			UNION ALL

 			--
            # admin 3 for big countries (6153)
            --
            SELECT
            lg5.grid_id, FORMAT( lg5.population, 0) as population, lg5.name, lg5.country_code, lg5.level
            FROM $wpdb->location_grid as lg5
            WHERE
            lg5.level = 3
            #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg5.admin0_grid_id IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg5.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)
            AND lg5.country_code = %s
			# Total Records (44395)
        ", $country_code, $country_code, $country_code, $country_code, $country_code ), ARRAY_A );

        return $data;
    }

}

