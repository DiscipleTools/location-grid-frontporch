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
    public $allowed_scripts = [ 'datatables', 'mapbox-gl', 'lodash', 'lodash-core', 'site-js', 'shared-functions' ];
    public $allowed_styles = [ 'datatables', 'mapbox-gl-css', 'site-css', 'foundation-css' ];

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
            wp_safe_redirect( dt_custom_login_url( 'login' ) );
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
        wp_enqueue_style( 'datatables', '//cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css', [], '1.10.25' );
        wp_enqueue_script( 'datatables', '//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js', [ 'jquery' ], '1.10.25' );
    }

    public function header_style(){
        ?>
        <style>
            body {
                background-color: white;
                padding: 1em;
            }
            .view-card {
                cursor: pointer;
            }
            .lightblue {
                background-color: lightblue;
                width:100%;
                height: 25px;
            }
            .lightgreen {
                background-color: lightgreen;
                width:100%;
                height: 25px;
            }
            .lightcoral {
                background-color: lightcoral;
                width:100%;
                height: 25px;
            }
            .row-of-tiles {
                border-bottom: 1px solid lightgrey;
                padding-bottom:.5em;
            }
            #summary-table tr {
                cursor: pointer;
            }
        </style>
        <?php
    }
    public function header_javascript(){

        ?>
        <script>
            let jsObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'mirror_url' => 'https://storage.googleapis.com/location-grid-mirror-v2/',
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
        global $wpdb;
        DT_Mapbox_API::geocoder_scripts();
        require_once( 'part-navigation.php' );
        $tiles = [
            'population_project' => [
                'title' => 'Population Project',
                'permissions' => [],
                'tiles' => [
                    'population_difference' => [
                        'key' => 'population_difference',
                        'title' => 'Population Difference Project',
                        'description' => 'Shows the population difference from the country record to the flat grid calculation.',
                        'auto_load' => 1,
                        'image' => '',
                        'class' => 'lightgreen',
                        'permissions' => []
                    ],
                    'review_and_accept_population' => [
                        'key' => 'review_and_accept_population',
                        'title' => 'Review and Accept Population Changes',
                        'description' => 'Review and accept outstanding population changes',
                        'auto_load' => 1,
                        'image' => '',
                        'class' => 'lightgreen',
                        'permissions' => [ 'manage_options' ]
                    ],
                ],
            ],
            'name_project' => [
                'title' => 'Name Project',
                'permissions' => [ 'manage_options' ],
                'tiles' => [
                    'name_verification' => [
                        'key' => 'name_verification',
                        'title' => 'Name Verification',
                        'description' => 'Verifies or updates location name for the flat grid.',
                        'auto_load' => 1,
                        'image' => '',
                        'class' => 'lightcoral',
                        'permissions' => [ 'manage_options' ]
                    ],
                ],
            ],
            'maps' => [
                'title' => 'Maps',
                'permissions' => [],
                'tiles' => [
                    'flat_grid_map' => [
                        'key' => 'flat_grid_map',
                        'title' => 'Flat Grid Map',
                        'description' => 'Map of the flat grid with population values.',
                        'auto_load' => 0,
                        'image' => '',
                        'class' => 'lightblue',
                        'permissions' => []
                    ],
                    'search_map' => [
                        'key' => 'search_map',
                        'title' => 'Search Map',
                        'description' => 'Search Map for a specific location',
                        'auto_load' => 0,
                        'image' => '',
                        'class' => 'lightblue',
                        'permissions' => []
                    ],
                ],
            ],
            'explore' => [
                'title' => 'Database',
                'permissions' => [],
                'tiles' => [
                    'modification_activity' => [
                        'key' => 'modification_activity',
                        'title' => 'Modification History',
                        'description' => 'History of changes to the database.',
                        'auto_load' => 0,
                        'image' => '',
                        'class' => 'lightblue',
                        'permissions' => []
                    ],
                    'summary' => [
                        'key' => 'summary',
                        'title' => 'Summary of Levels',
                        'description' => 'Summary of the location grid database by country and level.',
                        'auto_load' => 1,
                        'image' => '',
                        'class' => 'lightblue',
                        'permissions' => []
                    ],
                    'population_by_admin_layer' => [
                        'key' => 'population_by_admin_layer',
                        'title' => 'Population by Layers',
                        'description' => 'Population by admin layers showing current total population calculated by the layer and then the difference.',
                        'auto_load' => 1,
                        'image' => '',
                        'class' => 'lightblue',
                        'permissions' => []
                    ],
                    'flat_grid' => [
                        'key' => 'flat_grid',
                        'title' => 'Flat Grid',
                        'description' => 'Full list of the flat grid names and population.',
                        'auto_load' => 1,
                        'image' => '',
                        'class' => 'lightblue',
                        'permissions' => []
                    ],
                ],
            ],

        ]
        ?>
        <style id="custom-style"></style>
        <div class="wrapper">
            <div class="grid-x grid-padding-x">
                <div class="cell">
                    <?php
                    foreach ( $tiles as $section ) {
                        if ( ! empty( $section['permissions'] ) ) {
                            $has_permissions = $this->_has_permissions( $section['permissions'] );
                            if ( ! $has_permissions ) {
                                continue;
                            }
                        }
                        ?>
                        <h2 style="padding-top:1em;"><?php echo esc_html( $section['title'] ) ?></h2>
                        <div class="grid-x grid-padding-x row-of-tiles" data-equalizer data-equalize-on="medium">
                        <?php
                        foreach ( $section['tiles'] as $key => $value ) {
                            if ( ! empty( $value['permissions'] ) ) {
                                $has_permissions = $this->_has_permissions( $value['permissions'] );
                                if ( ! $has_permissions ) {
                                    continue;
                                }
                            }
                            ?>
                            <div class="cell medium-4 large-3 view-card"  data-id="<?php echo esc_attr( $value['key'] ) ?>" data-auto-load="<?php echo esc_attr( $value['auto_load'] ) ?>">
                                <div class="card" data-equalizer-watch>
                                    <div class="card-divider">
                                        <strong><?php echo esc_html( $value['title'] ) ?></strong>
                                    </div>
                                    <div class="<?php echo esc_html( $value['class'] ) ?>"></div>
                                    <div class="card-section">
                                        <p><?php echo esc_html( $value['description'] ) ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        </div>
                        <?php
                    }
                    ?>
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
                    let auto = $(this).data('auto-load')
                    console.log(action)

                    $('#reveal-content').html(`<span class="loading-spinner active"></span>`)
                    $('#modal').foundation('open')

                    if ( auto ) {
                        window.get_page( action )
                            .done(function( data ) {
                                load_panel( action, data )
                            })
                    } else {
                        load_panel( action, 0 )
                    }

                })
            })

            function load_panel( action, data, title = '' ) {
                switch (action) {
                    case 'summary':
                        load_summary(action, data)
                        break;
                    case 'flat_grid':
                        load_flat_grid(action, data)
                        break;
                    case 'flat_grid_by_country':
                        load_flat_grid_by_country(action, data, title)
                        break;
                    case 'population_by_admin_layer':
                        load_population_by_admin_layer(action, data)
                        break;
                    case 'population_difference':
                        load_population_difference(action, data)
                        break;
                    case 'modification_activity':
                        load_modification_activity(action, data)
                        break;
                    case 'review_and_accept_population':
                        load_review_and_accept_population(action, data)
                        break;
                    case 'name_verification':
                        load_name_verification(action, data)
                        break;
                    case 'name_verification_by_country':
                        load_name_verification_by_country(action, data, title)
                        break;
                    case 'flat_grid_map':
                        load_flat_grid_map()
                        break;
                    case 'search_map':
                        load_search_map()
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
                                <th>Divisions</th>
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

            function load_search_map() {
                let countries = [<?php
                    $countries = $wpdb->get_results("SELECT name, country_code FROM location_grid WHERE level = 0 ORDER BY name;", ARRAY_A);
                    echo json_encode($countries) ?>][0]

                let option_list = ''
                option_list += '<option value=""></option>'
                jQuery.each(countries, function(i,v){
                    option_list += '<option value="'+v.country_code+'">'+v.name+'</option>'
                })

                let content = jQuery('#reveal-content')
                content.empty().html(`
                    <h1>Search Map</h1>
                    <div class="grid-x grid-padding-x">
                        <div class="cell medium-3">
                            <div class="grid-x grid-padding-x">
                                <div class="cell">
                                     <div class="input-group">
                                          <input class="input-group-field" id="search-name-value" type="text" placeholder="Name">
                                          <div class="input-group-button">
                                            <input type="submit" class="button search-button" data-type="name" id="search-name-button" value="Search">
                                          </div>
                                     </div>
                                </div>
                                <div class="cell">
                                     <div class="input-group">
                                          <input class="input-group-field" id="search-grid_id-value" type="text" placeholder="Grid ID">
                                          <div class="input-group-button">
                                            <input type="submit" class="button search-button" id="search-grid_id-button" data-type="grid_id" value="Search">
                                          </div>
                                     </div>
                                </div>
                                <div class="cell">
                                     <div class="input-group">
                                            <select class="input" id="search-country_code-value">
                                                ${option_list}
                                            </select>
                                            <select class="input" id="search-country_code-admin">
                                                <option value="admin0">Admin0</option>
                                                <option value="admin1">Admin1</option>
                                                <option value="admin2">Admin2</option>
                                                <option value="admin3">Admin3</option>
                                                <option value="admin4">Admin4</option>
                                                <option value="admin5">Admin5</option>
                                            </select>
                                          <div class="input-group-button">
                                            <input type="submit" style="height:38px;" class="button search-button" id="search-country_code-button" data-type="country_code" value="Search">
                                          </div>
                                     </div>
                                </div>
                            </div>
                        </div>
                        <div class="cell medium-9">
                            <div class="table-scroll">
                                <table class="hover" id="summary-table">
                                    <tbody id="table-list"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `)

                let list = jQuery('#table-list')
                let input_name = jQuery('#search-name-value')
                let input_name_button = jQuery('#search-name-button')
                let input_grid_id = jQuery('#search-grid_id-value')
                let input_grid_id_button = jQuery('#search-grid_id-button')
                let input_country_code = jQuery('#search-country_code-value')
                let input_country_code_admin = jQuery('#search-country_code-admin')
                let input_country_code_button = jQuery('#search-country_code-button')

                jQuery('.input-group-field').on('focus', function(){
                    if ( input_name.attr('id') !== jQuery(this).attr('id') ) {
                        input_name.val('')
                    }
                    if ( input_grid_id.attr('id') !== jQuery(this).attr('id') ) {
                        input_grid_id.val('')
                    }
                    if ( input_country_code.attr('id') !== jQuery(this).attr('id') ) {
                        input_country_code.val('')
                    }
                })

                input_name_button.on('click', function(){
                    jQuery('.search-button').prop('disabled', true)
                    let term = input_name.val()
                    let type = input_name_button.data('type')
                    search( type, term )
                })
                input_grid_id_button.on('click', function(){
                    jQuery('.search-button').prop('disabled', true)
                    let term = input_grid_id.val()
                    let type = input_grid_id_button.data('type')
                    search( type, term )
                })
                input_country_code_button.on('click', function(){
                    jQuery('.search-button').prop('disabled', true)
                    let term = input_country_code.val()
                    let term2 = input_country_code_admin.val()
                    let type = input_country_code_button.data('type')
                    search( type, term, term2 )
                })
                $('.input-group-field').keypress(function (e) {
                    var key = e.which;
                    if( key === 13 )  // the enter key code
                    {
                        if( input_name.val() )  // the enter key code
                        {
                            jQuery('.search-button').prop('disable', true)
                            let term = input_name.val()
                            let type = input_name_button.data('type')
                            search( type, term )
                            return false;
                        }
                        else if( input_grid_id.val() )  // the enter key code
                        {
                            jQuery('.search-button').prop('disable', true)
                            let term = input_grid_id.val()
                            let type = input_grid_id_button.data('type')
                            search( type, term )
                            return false;
                        }
                        else if( input_country_code.val() )  // the enter key code
                        {
                            jQuery('.search-button').prop('disabled', true)
                            let term = input_country_code.val()
                            let term2 = input_country_code_admin.val()
                            let type = input_country_code_button.data('type')
                            search( type, term, term2 )
                            return false;
                        }
                    }
                });

                function search( type, term, term2 = null ) {

                    list.empty().html(`<span class="loading-spinner active"></span>`)

                    window.get_data_page( 'search_map_query', { type: type, term: term, term2: term2 } )
                        .done(function(data){
                            console.log(data)

                            if ( data ) {
                                let row = ''
                                list.empty()
                                jQuery.each(data, function(i,v){
                                    row = '<tr data-id="'+v.grid_id+'">'
                                    jQuery.each(v, function(ii,vv){
                                        row += '<td>'+vv+'</td>'
                                    })
                                    row += '</tr>'
                                    list.append(row)
                                })
                            }
                            else {
                                list.empty().append(`<tr><td>No matches found.</td></tr>`)
                            }

                            jQuery('.search-button').prop('disabled', false)

                            jQuery('#table-list tr').on('click', function(){
                                let grid_id = jQuery(this).data('id')
                                load_single_map( grid_id )
                            })
                        })
                }

            }

            function load_single_map( grid_id ) {
                let content = jQuery('#reveal-content-map')
                content.html(`<span class="loading-spinner active"></span>`)
                jQuery('#modal-map').foundation('open')

                content.empty().html(`
                    <style id="map-style"></style>
                    <h1 id="map-title"><span class="loading-spinner active"></span></h1>
                    <div class="grid-x grid-padding-x">
                        <div class="cell" id="map-container">
                           <div id="map-wrapper" >
                                <div id='single-map'></div>
                            </div>
                        </div>
                    </div>
                `)

                jQuery('#map-style').empty().append(`
                        #wrapper {
                            height: ${window.innerHeight / 2}px !important;
                        }
                        #map-wrapper {
                            height: ${window.innerHeight / 2}px !important;
                        }
                        #single-map {
                            height: ${window.innerHeight / 2}px !important;
                        }
                    `)

                let center = [-98, 38.88]
                mapboxgl.accessToken = jsObject.map_key;
                let map = new mapboxgl.Map({
                    container: 'single-map',
                    style: 'mapbox://styles/mapbox/light-v10',
                    center: center,
                    minZoom: 2,
                    maxZoom: 8,
                    zoom: 3
                });
                map.dragRotate.disable();
                // disable drag
                // disable zoom higher than parent
                // disable zoom closer than selected
                map.touchZoomRotate.disableRotation();

                map.on('load', function() {
                    jQuery.ajax({
                        url: jsObject.mirror_url + 'low/'+grid_id+'.geojson',
                        dataType: 'json',
                        data: null,
                        cache: true,
                        beforeSend: function (xhr) {
                            if (xhr.overrideMimeType) {
                                xhr.overrideMimeType("application/json");
                            }
                        }
                    })
                        .done(function (geojson) {
                            map.addSource('selected', {
                                'type': 'geojson',
                                'data': geojson
                            });
                            map.addLayer({
                                'id': 'selected_fill',
                                'type': 'fill',
                                'source': 'selected',
                                'paint': {
                                    'fill-color': '#0080ff',
                                    'fill-opacity': 0.75
                                }
                            });
                            map.addLayer({
                                'id': 'selected_line',
                                'type': 'line',
                                'source': 'selected',
                                'paint': {
                                    'line-color': 'black',
                                    'line-width': 1
                                }
                            });
                        })
                    window.get_data_page( 'grid_row', grid_id )
                        .done(function(grid_row) {
                            if ( grid_row ) {

                                jQuery('#map-title').html(grid_row.full_name)

                                map.fitBounds([
                                    [parseFloat( grid_row.west_longitude), parseFloat(grid_row.south_latitude)], // southwestern corner of the bounds
                                    [parseFloat(grid_row.east_longitude), parseFloat(grid_row.north_latitude)] // northeastern corner of the bounds
                                ]);

                                // zoom to parent bbox

                                // highlight selected polygon

                                // fly zoom to selected polygon

                                // load all children into parent


                                jQuery('.loading-spinner').removeClass('active')
                            }
                        })
                })
            }

            function load_flat_grid_map( action, data ) {
                let content = jQuery('#reveal-content')
                content.empty().html(`
                    <div id="initialize-screen">
                        <div id="initialize-spinner-wrapper" class="center">
                            <progress class="success initialize-progress" max="46" value="0"></progress><br>
                            Loading the planet ...<br>
                            <span id="initialize-people" style="display:none;">Locating world population...</span><br>
                            <span id="initialize-activity" style="display:none;">Calculating movement activity...</span><br>
                            <span id="initialize-coffee" style="display:none;">Shamelessly brewing coffee...</span><br>
                            <span id="initialize-dothis" style="display:none;">Let's do this...</span><br>
                        </div>
                    </div>
                    <div class="grid-x">
                        <div class="cell medium-9" id="map-container">
                            <div id="map-wrapper">
                                <span class="loading-spinner active"></span>
                                <div id='map'></div>
                            </div>
                        </div>
                        <div class="cell medium-3" id="map-sidebar-wrapper">
                            <div id="details-panel">
                                <div class="grid-x grid-padding-x" >
                                    <div class="cell">
                                        <br><br>
                                        <h1 id="title"></h1><br>
                                        <h3>Population: <span id="population">0</span></h3><br>
                                        <hr>
                                        <span style="color:lightgrey;" id="grid_id">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    `)

                $('#custom-style').empty().append(`
                    #wrapper {
                        height: ${window.innerHeight}px !important;
                    }
                    #map-wrapper {
                        height: ${window.innerHeight}px !important;
                    }
                    #map {
                        height: ${window.innerHeight}px !important;
                    }
                    #initialize-screen {
                        height: ${window.innerHeight}px !important;
                    }
                    #welcome-modal {
                        height: ${window.innerHeight - 30}px !important;
                    }
                    #map-sidebar-wrapper {
                        height: ${window.innerHeight}px !important;
                    }
                `)

                let initialize_screen = jQuery('.initialize-progress')

                // preload all geojson
                let asset_list = []
                var i = 1;
                while( i <= 45 ){
                    asset_list.push(i+'.geojson')
                    i++
                }

                let loop = 0
                let list = 0
                window.load_map_triggered = 0
                window.get_page( 'flat_grid_populations')
                    .done(function(x){
                        list = 1
                        jsObject.grid_data = x
                        if ( loop > 44 && list > 0 && window.load_map_triggered !== 1 ){
                            window.load_map_triggered = 1
                            load_map()
                        }
                    })
                    .fail(function(){
                        console.log('Error getting grid data')
                        jsObject.grid_data = {'data': {}, 'highest_value': 1 }
                    })
                jQuery.each(asset_list, function(i,v) {
                    jQuery.ajax({
                        url: jsObject.mirror_url + 'tiles/world/saturation/' + v,
                        dataType: 'json',
                        data: null,
                        cache: true,
                        beforeSend: function (xhr) {
                            if (xhr.overrideMimeType) {
                                xhr.overrideMimeType("application/json");
                            }
                        }
                    })
                        .done(function(x){
                            loop++
                            initialize_screen.val(loop)

                            if ( 5 === loop ) {
                                jQuery('#initialize-people').show()
                            }

                            if ( 15 === loop ) {
                                jQuery('#initialize-activity').show()
                            }

                            if ( 22 === loop ) {
                                jQuery('#initialize-coffee').show()
                            }

                            if ( 40 === loop ) {
                                jQuery('#initialize-dothis').show()
                            }

                            if ( loop > 44 && list > 0 && window.load_map_triggered !== 1 ){
                                window.load_map_triggered = 1
                                load_map()
                            }
                        })
                        .fail(function(){
                            loop++
                        })
                })

                function load_map() {
                    jQuery('#initialize-screen').hide()

                    // set title
                    let ptt = 'Population'
                    $('#panel-type-title').html(ptt)

                    $('.loading-spinner').removeClass('active')

                    let center = [-98, 38.88]
                    mapboxgl.accessToken = jsObject.map_key;
                    let map = new mapboxgl.Map({
                        container: 'map',
                        style: 'mapbox://styles/mapbox/light-v10',
                        center: center,
                        minZoom: 2,
                        maxZoom: 8,
                        zoom: 3
                    });
                    map.dragRotate.disable();
                    map.touchZoomRotate.disableRotation();

                    window.previous_hover = false

                    let asset_list = []
                    var i = 1;
                    while( i <= 45 ){
                        asset_list.push(i+'.geojson')
                        i++
                    }

                    jQuery.each(asset_list, function(i,v){

                        jQuery.ajax({
                            url: jsObject.mirror_url + 'tiles/world/saturation/' + v,
                            dataType: 'json',
                            data: null,
                            cache: true,
                            beforeSend: function (xhr) {
                                if (xhr.overrideMimeType) {
                                    xhr.overrideMimeType("application/json");
                                }
                            }
                        })
                            .done(function (geojson) {

                                map.on('load', function() {

                                    jQuery.each(geojson.features, function (i, v) {
                                        if (typeof jsObject.grid_data.data[v.id] !== 'undefined' ) {
                                            geojson.features[i].properties.value = parseInt(jsObject.grid_data.data[v.id])
                                        } else {
                                            geojson.features[i].properties.value = 0
                                        }
                                    })

                                    map.addSource(i.toString(), {
                                        'type': 'geojson',
                                        'data': geojson
                                    });
                                    map.addLayer({
                                        'id': i.toString()+'line',
                                        'type': 'line',
                                        'source': i.toString(),
                                        'paint': {
                                            'line-color': 'grey',
                                            'line-width': .5
                                        }
                                    });

                                    /**************/
                                    /* hover map*/
                                    /**************/
                                    map.addLayer({
                                        'id': i.toString() + 'fills',
                                        'type': 'fill',
                                        'source': i.toString(),
                                        'paint': {
                                            'fill-color': 'black',
                                            'fill-opacity': [
                                                'case',
                                                ['boolean', ['feature-state', 'hover'], false],
                                                .8,
                                                0
                                            ]
                                        }
                                    })
                                    /* end hover map*/

                                    /**********/
                                    /* heat map brown */
                                    /**********/
                                    map.addLayer({
                                        'id': i.toString() + 'fills_heat',
                                        'type': 'fill',
                                        'source': i.toString(),
                                        'paint': {
                                            'fill-color': {
                                                property: 'value',
                                                stops: [[0, 'rgba(0, 0, 0, 0)'], [1, 'rgb(155, 200, 254)'], [jsObject.grid_data.highest_value, 'rgb(37, 82, 154)']]
                                            },
                                            'fill-opacity': 0.75,
                                            'fill-outline-color': '#707070'
                                        }
                                    })
                                    /**********/
                                    /* end fill map */
                                    /**********/

                                    map.on('mousemove', i.toString()+'fills', function (e) {
                                        if ( window.previous_hover ) {
                                            map.setFeatureState(
                                                window.previous_hover,
                                                { hover: false }
                                            )
                                        }
                                        window.previous_hover = { source: i.toString(), id: e.features[0].id }
                                        if (e.features.length > 0) {
                                            map.setFeatureState(
                                                window.previous_hover,
                                                {hover: true}
                                            );
                                            $('#title').html(e.features[0].properties.full_name)
                                            $('#population').html(numberWithCommas(jsObject.grid_data.data[e.features[0].properties.grid_id]))
                                            $('#grid_id').html(e.features[0].properties.grid_id)
                                        }
                                    });
                                })

                            }) /* ajax call */
                    }) /* for each loop */

                } /* .preCache */

            }

            function load_population_by_admin_layer( action, data ) {
                let content = jQuery('#reveal-content')
                content.empty().html(`
                    <h1>Population by Admin Layer</h1>
                    <table class="hover display" id="summary-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Max Depth</th>
                                <th>Admin0 Population</th>
                                <th>Admin1 Population</th>
                                <th>Admin2 Population</th>
                                <th>Admin3 Population</th>
                                <th>Admin1 Variance</th>
                                <th>Admin2 Variance</th>
                                <th>Admin3 Variance</th>
                            </tr>
                        </thead>
                        <tbody id="table-list"></tbody>
                    </table>`)
                let table_list = jQuery('#table-list')
                jQuery.each( data, function(i,v){
                    table_list.append(`<tr>
                        <td>${v.name}</td><td>${v.max_depth}</td>
                        <td>${numberWithCommas(v.admin0_population)}</td>
                        <td>${numberWithCommas(v.admin1_population)}</td>
                        <td>${numberWithCommas(v.admin2_population)}</td>
                        <td>${numberWithCommas(v.admin3_population)}</td>
                        <td>${numberWithCommas(v.admin1_variance)}</td>
                        <td>${numberWithCommas(v.admin2_variance)}</td>
                        <td>${numberWithCommas(v.admin3_variance)}</td>
                        </tr>`)
                })

                jQuery('#summary-table').dataTable({
                    "paging": false
                });

            }

            function load_flat_grid(action, data) {
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
                let html = ''
                jQuery.each( data, function(i,v){
                    html +=  `<tr><td>${v.grid_id}</td><td>${v.full_name}</td><td>${v.country_code}</td><td>${v.level}</td><td>${v.formatted_population}</td></tr>`
                })

                table_list.append(html)

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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="table-list"></tbody>
                    </table>`)
                let table_list = jQuery('#table-list')
                jQuery.each( data, function(i,v){
                    table_list.append(`<tr class="country_selection" data-id="${v.country_code}" data-name="${v.name}">
                                        <td>${v.name}</td>
                                        <td>${v.country_code}</td>
                                        <td>${numberWithCommas(v.population)}</td>
                                        <td>${numberWithCommas(v.sum_population)}</td>
                                        <td>${numberWithCommas(v.difference)}</td>
                                        <td>${v.percent}%</td>
                                        </tr>`)
                })

                jQuery('#summary-table').dataTable({
                    "paging": false,
                    "order": [ 4, 'desc' ]
                });

                jQuery('.country_selection').on('click', function(e){

                    $('#reveal-content-2').html(`<span class="loading-spinner active"></span>`)
                    $('#modal-2').foundation('open')
                    let cc = jQuery(this).data('id')
                    let name = jQuery(this).data('name')
                    window.get_data_page( 'flat_grid_by_country', cc )
                        .done(function( data ) {
                            load_panel( 'flat_grid_by_country', data, name )
                        })
                })
            }

            function load_flat_grid_by_country(action, data, title) {
                let content = jQuery('#reveal-content-2')
                content.empty().html(`
                    <style>.social-icon { height: 20px; padding: 2px; cursor:pointer;}</style>
                    <style id="local-style-2">.verified {display:none;}</style>
                    <h1>Flat Grid - <span id="country_code-2">${title}</span> <button class="button tiny hollow" style="position:absolute; top:10px; right:150px;" id="show_verified-2">show verified</button></h1>
                    <table class="hover display" id="summary-table-2">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Population</th>
                                <th>Update</th>
                                <th>Verified</th>
                                <th>Links</th>
                                <th>Level</th>
                            </tr>
                        </thead>
                        <tbody id="table-list-2"></tbody>
                    </table>`)
                let table_list = jQuery('#table-list-2')
                jQuery.each( data, function(i,v){
                    let check = ''
                    if ( v.verified !== '' ){
                        check = '&#9989;'
                    }
                    table_list.append(
                        `<tr class="${v.grid_id} ${v.verified}" id="${v.grid_id}">
                            <td>${v.full_name}</td>
                            <td id="population_${v.grid_id}-2">${v.formatted_population}</td>
                            <td><input type="text" class="input"  data-id="${v.grid_id}" data-old="${v.population}" /></td>
                            <td id="verified_${v.grid_id}-2">${check}</td>
                            <td><img class="social-icon" src="${jsObject.google_logo}" data-url="https://www.google.com/search?q=${encodeURIComponent(v.full_name)}+population" />
                                <img class="social-icon" src="${jsObject.wikipedia_logo}" data-url="https://en.wikipedia.org/wiki/Special:Search?search=${encodeURIComponent(v.full_name)}"/></td>
                            <td>${v.level}</td>
                        </tr>`
                    )
                })

                jQuery('#summary-table-2').dataTable({
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

                    if ( value === '' || value === ' ' ) {
                        return
                    }


                    jQuery('#verified_'+id+'-2').html('saving...')

                    let data = {'grid_id': id, 'old_value': old, 'new_value': value }
                    window.get_data_page('update_population', data )
                        .done(function(result) {
                            if ( result.status === 'OK' ){
                                jQuery('#'+id+'-2').addClass('verified')
                                jQuery('#verified_'+id+'-2').html('&#9989;')
                                jQuery('#population_'+id+'-2').html(value)
                            }
                            console.log(result)
                        })
                })

                jQuery('#show_verified-2').on('click', function(){
                    if ( typeof window.show_verified === 'undefined' || window.show_verified === false ) {
                        window.show_verified = true
                        jQuery('#local-style-2').html(`.verified {display:none;}`)
                    } else {
                        window.show_verified = false
                        jQuery('#local-style-2').html(` `)
                    }
                })
            }

            function load_name_verification(action, data) {
                let content = jQuery('#reveal-content')
                content.empty().html(`
                    <style>#summary-table tr { cursor: pointer;}</style>
                    <h1>Name Verification</h1>
                    <table class="hover display" id="summary-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody id="table-list"></tbody>
                    </table>`)
                let table_list = jQuery('#table-list')
                jQuery.each( data, function(i,v){
                    table_list.append(`<tr class="country_selection" data-id="${v.country_code}" data-name="${v.name}">
                                        <td>${v.name}</td>
                                        </tr>`)
                })

                jQuery('#summary-table').dataTable({
                    "paging": false,
                });

                jQuery('.country_selection').on('click', function(e){

                    $('#reveal-content-2').html(`<span class="loading-spinner active"></span>`)
                    $('#modal-2').foundation('open')
                    let cc = jQuery(this).data('id')
                    let name = jQuery(this).data('name')
                    window.get_data_page( 'name_verification_by_country', cc )
                        .done(function( data ) {
                            load_panel( 'name_verification_by_country', data, name )
                        })
                })
            }

            function load_name_verification_by_country(action, data, title) {
                let content = jQuery('#reveal-content-2')
                content.empty().html(`
                    <style>.social-icon { height: 20px; padding: 2px; cursor:pointer;}</style>
                    <style id="local-style">.verified {display:none;}</style>
                    <h1>Flat Grid - <span id="country_code-2">${title}</span> <button class="button tiny hollow" style="position:absolute; top:10px; right:150px;" id="show_verified-2">show verified</button></h1>
                    <table class="hover display" id="summary-table-2">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Name</th>
                                <th>Update</th>
                                <th>Verified</th>
                                <th>Level</th>
                                <th>Links</th>
                            </tr>
                        </thead>
                        <tbody id="table-list-2"></tbody>
                    </table>`)
                let table_list = jQuery('#table-list-2')
                jQuery.each( data, function(i,v){
                    let check = ''
                    if ( v.verified !== '' ){
                        check = '&#9989;'
                    }
                    table_list.append(
                        `<tr class="${v.grid_id} ${v.verified}" id="${v.grid_id}-2">
                            <td>${v.full_name}</td>
                            <td id="name_${v.grid_id}-2">${v.name}</td>
                            <td><input type="text" class="input" data-id="${v.grid_id}" data-old="${v.name}" /></td>
                            <td id="verified_${v.grid_id}-2">${check}</td>
                            <td>${v.level}</td>
                            <td>
                                <img class="social-icon" src="${jsObject.google_logo}" data-url="https://www.google.com/search?q=${encodeURIComponent(v.full_name)}+population" />
                                <img class="social-icon" src="${jsObject.wikipedia_logo}" data-url="https://en.wikipedia.org/wiki/Special:Search?search=${encodeURIComponent(v.full_name)}"/>
                            </td>
                        </tr>`
                    )
                })

                jQuery('#summary-table-2').dataTable({
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

                    if ( value === '' || value === ' '  ) {
                        return
                    }

                    jQuery('#verified_'+id+'-2').html('saving...')

                    let data = {'grid_id': id, 'old_value': old, 'new_value': value }
                    window.get_data_page('update_name', data )
                        .done(function(result) {
                            if ( result.status === 'OK' ){
                                jQuery('#'+id+'-2').addClass('verified')
                                jQuery('#verified_'+id+'-2').html('&#9989;')
                                jQuery('#name_'+id+'-2').html(value)
                            }
                            console.log(result)
                        })
                })

                jQuery('#show_verified').on('click', function(){
                    if ( typeof window.show_verified === 'undefined' || window.show_verified === false ) {
                        window.show_verified = true
                        jQuery('#local-style').html(`.verified {display:none;}`)
                    } else {
                        window.show_verified = false
                        jQuery('#local-style').html(` `)
                    }
                })
            }

            function load_modification_activity( action, data ) {
                let content = jQuery('#reveal-content')
                content.empty().html(`
                            <h1>Modification History</h1>
                            <div class="table-scroll">
                                <table class="hover striped" id="summary-table">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Time</th>
                                            <th>Grid ID</th>
                                            <th>Name</th>
                                            <th>Old Value</th>
                                            <th>New Value</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-list"></tbody>
                                </table>
                            </div>
                            <hr>
                            <div class="grid-x">
                                <div class="cell center">
                                    <button class="button" id="load-more" disabled>Load More</button> <span class="loading-spinner active"></span>
                                </div>
                            </div>
                        `)

                let table_list = jQuery('#table-list')
                load_block( 0 )

                function load_block( block ) {
                    window.get_data_page ('modification_activity_block', block )
                        .done(function(data) {
                            jQuery.each( data, function(i,v){
                                table_list.append(`<tr>
                                <td>${v.id}</td>
                                <td>${converTimeStamp(v.timestamp )}</td>
                                <td>${v.grid_id}</td>
                                <td>${v.full_name}</td>
                                <td>${numberWithCommas(v.old_value)}</td>
                                <td>${numberWithCommas(v.new_value)}</td>
                                <td>${v.user_email}</td>
                                </tr>`)
                            })
                            jQuery('#load-more').prop('disabled', false )
                            jQuery('.loading-spinner').removeClass('active')
                        })
                }

                jQuery('#load-more').on('click', function(){
                    jQuery('#load-more').prop('disabled', true )
                    jQuery('.loading-spinner').addClass('active')
                    let list = jQuery('#table-list tr').length
                    load_block( list + 1 )
                })
            }

            function load_review_and_accept_population( action, data ) {
                let content = jQuery('#reveal-content')
                content.empty().html(`
                    <h1>Review and Accept Population</h1>
                        <table class="hover display" id="summary-table">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Grid ID</th>
                                    <th>Name</th>
                                    <th>Old Value</th>
                                    <th>New Value</th>
                                    <th class="center">
                                        <button id="accept-all" class="button small hollow"  value="accept">Accept All Listed Below</button>
                                    </th>
                                    <th>Email</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody id="table-list"></tbody>
                        </table>
                `)
                let table_list = jQuery('#table-list')
                jQuery.each( data, function(i,v){
                    table_list.append(`<tr id="row-${v.id}" data-id="${v.id}">
                            <td>${v.timestamp }</td>
                            <td>${v.grid_id}</td>
                            <td>${v.full_name}</td>
                            <td>${numberWithCommas(v.old_value)}</td>
                            <td>${numberWithCommas(v.new_value)}</td>
                            <td class="center" id="buttons-${v.grid_id}">
                                <button class="button accept" data-id="${v.id}" value="accept">Accept</button>
                                <button class="button reject" data-id="${v.id}" value="reject">Reject</button>
                            </td>
                            <td>${v.user_email}</td>
                            <td>${converTimeStamp(v.timestamp )}</td>
                            </tr>`)
                })

                jQuery('#summary-table').dataTable({
                    "paging": false,
                    "order": [[ 0, "desc" ]],
                    "columnDefs": [
                        {
                            "targets": [0],
                            "visible": false,
                            "searchable": false,
                        }
                    ]
                });

                jQuery('#accept-all').on('click', function(){

                    let list = []
                    jQuery.each( jQuery('#table-list tr'), function(i,v){
                        list.push( jQuery(this).data('id')  )
                    })

                    console.log( list )
                    window.get_data_page ( 'commit_populations_to_master', list )
                        .done(function(response) {
                            console.log(response)
                            if ( response.status === 'OK' ){
                                jQuery.each(response.result, function(i,v){
                                    jQuery('#buttons-'+i).html('accepted')
                                })
                            }
                        })

                })

                jQuery('.accept').on('click', function(){
                    let button = jQuery(this)
                    button.prop('disabled', true)
                    let list = []
                    list.push( button.data('id')  )

                    console.log( list )
                    window.get_data_page ( 'commit_populations_to_master', list )
                        .done(function(response) {
                            console.log(response)
                            if ( response.status === 'OK' ){
                                jQuery.each(response.result, function(i,v){
                                    jQuery('#buttons-'+i).html('accepted')
                                })
                            }
                        })

                })

                jQuery('.reject').on('click', function(){
                    let button = jQuery(this)
                    button.prop('disabled', true)
                    let list = []
                    let id = button.data('id')
                    list.push(  id )

                    console.log( list )
                    window.get_data_page ( 'reject_population', list )
                        .done(function(response) {
                            if ( response ){
                                jQuery('#row-'+id).hide()
                            }
                        })

                })

            }

            window.show_verified = () => {
                if ( typeof window.show_verified === 'undefined' || window.show_verified === false ) {
                    window.show_verified = true
                    jQuery('#table-list tr').addClass('show')
                } else {
                    window.show_verified = false
                    jQuery('#table-list tr').removeClass('show')
                }
            }

            function numberWithCommas(x) {
                return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            function converTimeStamp( t ){
                let milliseconds = t * 1000
                let dateObject = new Date(milliseconds)
                return dateObject.toLocaleString('en-US', {timeZoneName: "short"})
            }

        </script>
        <div class="reveal full" id="modal" data-v-offset="0" data-multiple-opened="true" data-reveal>
            <div id="reveal-content"></div>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">Close &times;</span>
            </button>
        </div>
        <div class="reveal full" id="modal-2" data-v-offset="0" data-multiple-opened="true" data-reveal>
            <div id="reveal-content-2"></div>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">Close &times;</span>
            </button>
        </div>
        <div class="reveal full" id="modal-map" data-v-offset="0" data-multiple-opened="true" data-reveal>
            <div id="reveal-content-map"></div>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">Close &times;</span>
            </button>
        </div>
        <?php
    }

    public function _has_permissions( array $permissions ) : bool {
        if ( count( $permissions ) > 0 ) {
            foreach ( $permissions as $permission ){
                if ( current_user_can( $permission ) ){
                    return true;
                }
            }
        }
        return false;
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
            case 'flat_grid':
                return Location_Grid_Queries::flat_grid_full();
            case 'flat_grid_populations':
                return Location_Grid_Queries::flat_grid_populations();
            case 'flat_grid_by_country':
                return Location_Grid_Queries::flat_grid_by_country( $params['data'] );
            case 'population_difference':
                return Location_Grid_Queries::population_difference();
            case 'population_by_admin_layer':
                return Location_Grid_Queries::population_by_admin_layer();
            case 'update_population':
                return $this->update_population( $params['data'] );
            case 'update_name':
                return $this->update_name( $params['data'] );
            case 'name_verification':
                return Location_Grid_Queries::country_list();
            case 'name_verification_by_country':
                return Location_Grid_Queries::name_verification_by_country( $params['data'] );
            case 'modification_activity':
                return true; // trigger secondary
            case 'modification_activity_block':
                return Location_Grid_Queries::modification_activity( $params['data'] );
            case 'review_and_accept_population':
                return Location_Grid_Queries::review_population_change_activity();
            case 'commit_populations_to_master':
                return $this->commit_populations_to_master( $params['data'] );
            case 'reject_population':
                return $this->reject_population( $params['data'] );
            case 'search_map_query':
                return Location_Grid_Queries::search_map_query( $params['data'] );
            case 'grid_row':
                return Location_Grid_Queries::grid_row( $params['data'] );

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
    public function update_name( $data ) {
        global $wpdb;
        if ( ! isset( $data['grid_id'], $data['new_value'], $data['old_value'] ) ) {
            return new WP_Error( __METHOD__, "Missing parameters", [ 'status' => 400 ] );
        }

        $user_id = get_current_user_id();
        if ( empty( $user_id ) ) {
            return new WP_Error( __METHOD__, "Missing user id", [ 'status' => 400 ] );
        }

        $new_value = utf8_encode( trim( $data['new_value'] ) );
        $old_value = utf8_encode( trim( $data['old_value'] ) );
        $timestamp = time();

        $result = $wpdb->query( $wpdb->prepare("
            INSERT INTO location_grid_edit_log (grid_id, user_id, type, subtype, old_value, new_value, timestamp )
            VALUES (%d, %d, 'name', 'name_verification_project', %s, %s, %d );
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
    public function commit_populations_to_master( $data ) {
        global $wpdb;
        if ( ! is_array( $data ) ) {
            return new WP_Error( __METHOD__, "Not an array", [ 'status' => 400 ] );
        }

        $current_raw = Location_Grid_Queries::review_population_change_activity();
        $current = [];
        foreach ( $current_raw as $row ){
            $current[$row['id']] = $row;
        }

        $result = [];
        foreach ( $data as $id ) {

            if ( isset( $current[$id] ) ) {
                $population = $current[$id]['new_value'];
                $grid_id = $current[$id]['grid_id'];
            } else {
                $urow = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM location_grid_edit_log WHERE id = %d", $id ), ARRAY_A );
                if ( empty( $urow ) ) {
                    continue;
                } else {
                    $population = $urow['new_value'];
                    $grid_id = $urow['grid_id'];
                }
            }

            $result[$grid_id] = [];

            // update
            $updated_lg = $wpdb->query( $wpdb->prepare(
                "UPDATE location_grid SET population = %d, modification_date = %s WHERE grid_id = %d;",
            $population, gmdate( "Y-m-d" ), $grid_id ) );

            $result[$grid_id][] = $updated_lg;

            // check off all matching records as accepted
            if ( false === $updated_lg ) {
                dt_write_log( 'FAIL Commit'. $grid_id );
                continue;
            }

            $updated_accepted = $wpdb->query( $wpdb->prepare("UPDATE location_grid_edit_log SET accepted = 1 WHERE grid_id = %d AND type = 'population' AND subtype = 'flat_grid_project';",
            $grid_id ) );

            $result[$grid_id][] = $updated_accepted;


            if ( false === $updated_accepted ) {
                dt_write_log( 'FAIL Accepted '. $grid_id );
                continue;
            }
        }

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
    public function reject_population( $data ) {
        global $wpdb;
        if ( ! is_array( $data ) ) {
            return new WP_Error( __METHOD__, "Not an array", [ 'status' => 400 ] );
        }

        $current_raw = Location_Grid_Queries::review_population_change_activity();
        $current = [];
        foreach ( $current_raw as $row ){
            $current[$row['id']] = $row;
        }

        $deleted = false;
        if ( isset( $data[0] ) && isset( $current[$data[0]] ) ) {
            $deleted = $wpdb->query( $wpdb->prepare(
                "DELETE FROM location_grid_edit_log WHERE id = %d;",
            $data[0] ) );
        }

        if ( $deleted ) {
            return $data[0];
        } else {
            return $deleted;
        }

    }

}

