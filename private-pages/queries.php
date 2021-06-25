<?php

class Location_Grid_Queries {

    public function flat_grid_raw() {
        global $wpdb;
        $data = $wpdb->get_results("
            SELECT
            lg1.grid_id, lg1.name, lg1.population,  lg1.country_code, lg1.level
            FROM location_grid lg1
            WHERE lg1.level = 0
			AND lg1.grid_id NOT IN ( SELECT lg11.admin0_grid_id FROM location_grid lg11 WHERE lg11.level = 1 AND lg11.admin0_grid_id = lg1.grid_id )
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
            lg2.grid_id, lg2.name, lg2.population, lg2.country_code, lg2.level
            FROM location_grid lg2
            WHERE lg2.level = 1
			AND lg2.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg2.grid_id )
             #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg2.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg2.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

            UNION ALL
			--
            # admin 2 all countries (37100)
            --
			SELECT
            lg3.grid_id, lg3.name, lg3.population,  lg3.country_code, lg3.level
            FROM location_grid lg3
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
            lg4.grid_id, lg4.name, lg4.population, lg4.country_code, lg4.level
            FROM location_grid lg4
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
            lg5.grid_id, lg5.name, lg5.population, lg5.country_code, lg5.level
            FROM location_grid as lg5
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

    public static function flat_grid_full() {
        global $wpdb;
        $data = $wpdb->get_results("
            SELECT
            lg1.grid_id, lg1.name, lg1.population,  lg1.country_code, lg1.level, lg1.name as full_name, FORMAT(lg1.population, 0) as formatted_population
            FROM location_grid lg1
            WHERE lg1.level = 0
			AND lg1.grid_id NOT IN ( SELECT lg11.admin0_grid_id FROM location_grid lg11 WHERE lg11.level = 1 AND lg11.admin0_grid_id = lg1.grid_id )
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
            lg2.grid_id, lg2.name, lg2.population,  lg2.country_code, lg2.level, CONCAT( lg2.name, ', ', lg2a0.name) as full_name, FORMAT(lg2.population, 0) as formatted_population
            FROM location_grid lg2
            LEFT JOIN location_grid lg2a0 ON lg2.admin0_grid_id=lg2a0.grid_id
            WHERE lg2.level = 1
			AND lg2.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg2.grid_id )
             #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg2.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg2.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

            UNION ALL
			--
            # admin 2 all countries (37100)
            --
			SELECT
            lg3.grid_id, lg3.name, lg3.population, lg3.country_code, lg3.level, CONCAT( lg3.name, ', ', lg3a1.name, ', ', lg3a0.name) as full_name, FORMAT(lg3.population, 0) as formatted_population
            FROM location_grid lg3
            LEFT JOIN location_grid lg3a0 ON lg3.admin0_grid_id=lg3a0.grid_id
            LEFT JOIN location_grid lg3a1 ON lg3.admin1_grid_id=lg3a1.grid_id
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
            lg4.grid_id, lg4.name, lg4.population, lg4.country_code, lg4.level, CONCAT( lg4.name, ', ', lg4a0.name) as full_name, FORMAT(lg4.population, 0) as formatted_population
            FROM location_grid lg4
            LEFT JOIN location_grid lg4a0 ON lg4.admin0_grid_id=lg4a0.grid_id
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
            lg5.grid_id, lg5.name, lg5.population, lg5.country_code, lg5.level, CONCAT( lg5.name, ', ', lg5a2.name, ', ', lg5a1.name, ', ', lg5a0.name) as full_name, FORMAT(lg5.population, 0) as formatted_population
            FROM location_grid lg5
            LEFT JOIN location_grid lg5a0 ON lg5.admin0_grid_id=lg5a0.grid_id
            LEFT JOIN location_grid lg5a1 ON lg5.admin1_grid_id=lg5a1.grid_id
            LEFT JOIN location_grid lg5a2 ON lg5.admin2_grid_id=lg5a2.grid_id
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

    public static function flat_grid_fname_fpop() {
        global $wpdb;
        $data = $wpdb->get_results("
            SELECT
            lg1.grid_id, lg1.name as full_name, FORMAT(lg1.population, 0) as formatted_population
            FROM location_grid lg1
            WHERE lg1.level = 0
			AND lg1.grid_id NOT IN ( SELECT lg11.admin0_grid_id FROM location_grid lg11 WHERE lg11.level = 1 AND lg11.admin0_grid_id = lg1.grid_id )
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
            lg2.grid_id, CONCAT( lg2.name, ', ', lg2a0.name) as full_name, FORMAT(lg2.population, 0) as formatted_population
            FROM location_grid lg2
            LEFT JOIN location_grid lg2a0 ON lg2.admin0_grid_id=lg2a0.grid_id
            WHERE lg2.level = 1
			AND lg2.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg2.grid_id )
             #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg2.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg2.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

            UNION ALL
			--
            # admin 2 all countries (37100)
            --
			SELECT
            lg3.grid_id, CONCAT( lg3.name, ', ', lg3a1.name, ', ', lg3a0.name) as full_name, FORMAT(lg3.population, 0) as formatted_population
            FROM location_grid lg3
            LEFT JOIN location_grid lg3a0 ON lg3.admin0_grid_id=lg3a0.grid_id
            LEFT JOIN location_grid lg3a1 ON lg3.admin1_grid_id=lg3a1.grid_id
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
            lg4.grid_id, CONCAT( lg4.name, ', ', lg4a0.name) as full_name, FORMAT(lg4.population, 0) as formatted_population
            FROM location_grid lg4
            LEFT JOIN location_grid lg4a0 ON lg4.admin0_grid_id=lg4a0.grid_id
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
            lg5.grid_id, CONCAT( lg5.name, ', ', lg5a2.name, ', ', lg5a1.name, ', ', lg5a0.name) as full_name, FORMAT(lg5.population, 0) as formatted_population
            FROM location_grid lg5
            LEFT JOIN location_grid lg5a0 ON lg5.admin0_grid_id=lg5a0.grid_id
            LEFT JOIN location_grid lg5a1 ON lg5.admin1_grid_id=lg5a1.grid_id
            LEFT JOIN location_grid lg5a2 ON lg5.admin2_grid_id=lg5a2.grid_id
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

    public static function flat_grid_by_country( $country_code ) {
        global $wpdb;
        $data = $wpdb->get_results($wpdb->prepare( "
        SELECT tb.grid_id,
            tb.name,
            tb.country_code,
            tb.level,
            tb.full_name,
            IF ( (SELECT lge.new_value
                  FROM location_grid_edit_log lge
                  WHERE lge.grid_id = tb.grid_id
                  ORDER BY lge.id DESC
                  LIMIT 0,1) , (SELECT lge.new_value
                                FROM location_grid_edit_log lge
                                WHERE lge.grid_id = tb.grid_id
                                ORDER BY lge.id DESC
                                LIMIT 0,1), tb.population ) as population,
            FORMAT( IF ( (SELECT lge.new_value
                 FROM location_grid_edit_log lge
                 WHERE lge.grid_id = tb.grid_id
                 ORDER BY lge.id DESC
                 LIMIT 0,1) , (SELECT lge.new_value
                               FROM location_grid_edit_log lge
                               WHERE lge.grid_id = tb.grid_id
                               ORDER BY lge.id DESC
                               LIMIT 0,1), tb.population ), 0 ) as formatted_population,
            IF ( (SELECT lge.new_value
                  FROM location_grid_edit_log lge
                  WHERE lge.grid_id = tb.grid_id
                  ORDER BY lge.id DESC
                  LIMIT 0,1), 'verified', '' ) as verified
            FROM (

                SELECT
                lg1.grid_id, lg1.name, lg1.population,  lg1.country_code, lg1.level, lg1.name as full_name, FORMAT(lg1.population, 0) as formatted_population
                FROM location_grid lg1
                WHERE lg1.level = 0
                AND lg1.grid_id NOT IN ( SELECT lg11.admin0_grid_id FROM location_grid lg11 WHERE lg11.level = 1 AND lg11.admin0_grid_id = lg1.grid_id )
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
                lg2.grid_id, lg2.name, lg2.population,  lg2.country_code, lg2.level, CONCAT( lg2.name, ', ', lg2a0.name) as full_name, FORMAT(lg2.population, 0) as formatted_population
                FROM location_grid lg2
                LEFT JOIN location_grid lg2a0 ON lg2.admin0_grid_id=lg2a0.grid_id
                WHERE lg2.level = 1
                AND lg2.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg2.grid_id )
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
                lg3.grid_id, lg3.name, lg3.population, lg3.country_code, lg3.level, CONCAT( lg3.name, ', ', lg3a1.name, ', ', lg3a0.name) as full_name, FORMAT(lg3.population, 0) as formatted_population
                FROM location_grid lg3
                LEFT JOIN location_grid lg3a0 ON lg3.admin0_grid_id=lg3a0.grid_id
                LEFT JOIN location_grid lg3a1 ON lg3.admin1_grid_id=lg3a1.grid_id
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
                lg4.grid_id, lg4.name, lg4.population, lg4.country_code, lg4.level, CONCAT( lg4.name, ', ', lg4a0.name) as full_name, FORMAT(lg4.population, 0) as formatted_population
                FROM location_grid lg4
                LEFT JOIN location_grid lg4a0 ON lg4.admin0_grid_id=lg4a0.grid_id
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
                lg5.grid_id, lg5.name, lg5.population, lg5.country_code, lg5.level, CONCAT( lg5.name, ', ', lg5a2.name, ', ', lg5a1.name, ', ', lg5a0.name) as full_name, FORMAT(lg5.population, 0) as formatted_population
                FROM location_grid lg5
                LEFT JOIN location_grid lg5a0 ON lg5.admin0_grid_id=lg5a0.grid_id
                LEFT JOIN location_grid lg5a1 ON lg5.admin1_grid_id=lg5a1.grid_id
                LEFT JOIN location_grid lg5a2 ON lg5.admin2_grid_id=lg5a2.grid_id
                WHERE
                lg5.level = 3
                #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
                AND lg5.admin0_grid_id IN (100050711,100219347, 100089589,100074576,100259978,100018514)
                #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
                AND lg5.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)
                AND lg5.country_code = %s
            ) as tb
			# Total Records (44395)
        ", $country_code, $country_code, $country_code, $country_code, $country_code ), ARRAY_A );

        return $data;
    }

    public static function population_difference() {
        global $wpdb;
        $data = $wpdb->get_results("
            SELECT
                tb.name,
                tb.country_code,
                FORMAT( tb.population, 0) as population,
                FORMAT( tb.sum_population, 0) as sum_population,
                FORMAT(sum_population - population, 0) as difference
            FROM (
                     SELECT
                         l.name,
                         l.country_code,
                         l.population,
                         (
                             SELECT SUM(p.population) as population FROM (
                                 SELECT
                                     lg1.grid_id,
                                        IF ( (SELECT lge.new_value
                                                        FROM location_grid_edit_log lge
                                                        WHERE lge.grid_id = lg1.grid_id
                                                        ORDER BY lge.id DESC
                                                        LIMIT 0,1), (SELECT lge.new_value
                                                                      FROM location_grid_edit_log lge
                                                                      WHERE lge.grid_id = lg1.grid_id
                                                                      ORDER BY lge.id DESC
                                                                      LIMIT 0,1), lg1.population ) as population,
                                        lg1.name,
                                        lg1.country_code,
                                        lg1.level
                                 FROM location_grid lg1
                                 WHERE lg1.level = 0
                                   AND lg1.grid_id NOT IN ( SELECT lg11.admin0_grid_id FROM location_grid lg11 WHERE lg11.level = 1 AND lg11.admin0_grid_id = lg1.grid_id )
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
                                     lg2.grid_id, IF ( (SELECT lge.new_value
                                                        FROM location_grid_edit_log lge
                                                        WHERE lge.grid_id = lg2.grid_id
                                                        ORDER BY lge.id DESC
                                                        LIMIT 0,1), (SELECT lge.new_value
                                                                     FROM location_grid_edit_log lge
                                                                     WHERE lge.grid_id = lg2.grid_id
                                                                     ORDER BY lge.id DESC
                                                                     LIMIT 0,1), lg2.population ) as population, lg2.name, lg2.country_code, lg2.level
                                 FROM location_grid lg2
                                 WHERE lg2.level = 1
                                   AND lg2.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg2.grid_id )
                                   #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
                                   AND lg2.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
                                   #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
                                   AND lg2.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

                                 UNION ALL
                                 --
                                 # admin 2 all countries (37100)
                                 --
                                 SELECT
                                     lg3.grid_id, IF ( (SELECT lge.new_value
                                                        FROM location_grid_edit_log lge
                                                        WHERE lge.grid_id = lg3.grid_id
                                                        ORDER BY lge.id DESC
                                                        LIMIT 0,1), (SELECT lge.new_value
                                                                     FROM location_grid_edit_log lge
                                                                     WHERE lge.grid_id = lg3.grid_id
                                                                     ORDER BY lge.id DESC
                                                                     LIMIT 0,1), lg3.population ) as population, lg3.name, lg3.country_code, lg3.level
                                 FROM location_grid lg3
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
                                     lg4.grid_id, IF ( (SELECT lge.new_value
                                                        FROM location_grid_edit_log lge
                                                        WHERE lge.grid_id = lg4.grid_id
                                                        ORDER BY lge.id DESC
                                                        LIMIT 0,1), (SELECT lge.new_value
                                                                     FROM location_grid_edit_log lge
                                                                     WHERE lge.grid_id = lg4.grid_id
                                                                     ORDER BY lge.id DESC
                                                                     LIMIT 0,1), lg4.population ) as population, lg4.name, lg4.country_code, lg4.level
                                 FROM location_grid lg4
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
                                     lg5.grid_id, IF ( (SELECT lge.new_value
                                                        FROM location_grid_edit_log lge
                                                        WHERE lge.grid_id = lg5.grid_id
                                                        ORDER BY lge.id DESC
                                                        LIMIT 0,1), (SELECT lge.new_value
                                                                     FROM location_grid_edit_log lge
                                                                     WHERE lge.grid_id = lg5.grid_id
                                                                     ORDER BY lge.id DESC
                                                                     LIMIT 0,1), lg5.population ) as population, lg5.name, lg5.country_code, lg5.level
                                 FROM location_grid as lg5
                                 WHERE
                                         lg5.level = 3
                                   #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
                                   AND lg5.admin0_grid_id IN (100050711,100219347, 100089589,100074576,100259978,100018514)
                                   #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
                                   AND lg5.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

                                 # Total Records (44395)
                             ) p
                             WHERE p.country_code = l.country_code GROUP BY p.country_code) as sum_population
                     FROM location_grid l
                     WHERE l.level = 0
                 ) as tb;
                 ", ARRAY_A );

                    return $data;
                }

    public static function population_by_admin_layer() {
        global $wpdb;
        $data = $wpdb->get_results("
            SELECT
                tb.name,
                max_depth,
                tb.admin0_population,
                IF ( tb.admin1_population, tb.admin1_population, '') as admin1_population,
                IF ( tb.admin2_population, tb.admin2_population, '') as admin2_population,
                IF ( tb.admin3_population, tb.admin3_population, '') as admin3_population,
                IF ( tb.admin0_population - tb.admin1_population, tb.admin0_population - tb.admin1_population , '') as admin1_variance,
                IF ( tb.admin0_population - tb.admin2_population, tb.admin0_population - tb.admin2_population , '') as admin2_variance,
                IF ( tb.admin0_population - tb.admin3_population, tb.admin0_population - tb.admin3_population , '') as admin3_variance
            FROM (
            SELECT
                l.name,
                (SELECT MAX(mx.level) FROM location_grid mx WHERE mx.admin0_grid_id = l.grid_id) as max_depth,
                l.population as admin0_population,
                (
                SELECT SUM(a1.population) FROM location_grid a1 WHERE a1.level = 1 AND a1.country_code = l.country_code
                ) as admin1_population,
                (
                SELECT SUM(a2.population) FROM location_grid a2 WHERE a2.level = 2 AND a2.country_code = l.country_code
                ) as admin2_population,
                (
                SELECT SUM(a3.population) FROM location_grid a3 WHERE a3.level = 3 AND a3.country_code = l.country_code
            ) as admin3_population
            FROM location_grid l
            WHERE level = 0
            ) as tb;
        ", ARRAY_A );

        return $data;
    }

    public static function summary() {
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
