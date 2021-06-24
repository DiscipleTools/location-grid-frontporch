<?php
declare(strict_types=1);

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


require_once( 'abstract.php' );

/**
 * Class LG_Migration_0000
 */
class LG_Migration_0000 extends LG_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $table) {
            $rv = $wpdb->query( $table ); // WPCS: unprepared SQL OK
            if ( $rv == false ) {
                throw new Exception( "Got error when creating table $name: $wpdb->last_error" );
            }
        }
    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {
        global $wpdb;
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $table) {
            $rv = $wpdb->query( "DROP TABLE `{$name}`" ); // WPCS: unprepared SQL OK
            if ( $rv == false ) {
                throw new Exception( "Got error when dropping table $name: $wpdb->last_error" );
            }
        }
    }

    /**
     * @return array
     */
    public function get_expected_tables(): array {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        return array(
            "location_grid" =>
                "CREATE TABLE IF NOT EXISTS `location_grid` (
                  `grid_id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `name` varchar(200) NOT NULL DEFAULT '',
                  `level` float DEFAULT NULL,
                  `level_name` varchar(7) DEFAULT NULL,
                  `country_code` varchar(10) DEFAULT NULL,
                  `admin0_code` varchar(10) DEFAULT NULL,
                  `admin1_code` varchar(20) DEFAULT NULL,
                  `admin2_code` varchar(20) DEFAULT NULL,
                  `admin3_code` varchar(20) DEFAULT NULL,
                  `admin4_code` varchar(20) DEFAULT NULL,
                  `admin5_code` varchar(20) DEFAULT NULL,
                  `parent_id` bigint(20) DEFAULT NULL,
                  `admin0_grid_id` bigint(20) DEFAULT NULL,
                  `admin1_grid_id` bigint(20) DEFAULT NULL,
                  `admin2_grid_id` bigint(20) DEFAULT NULL,
                  `admin3_grid_id` bigint(20) DEFAULT NULL,
                  `admin4_grid_id` bigint(20) DEFAULT NULL,
                  `admin5_grid_id` bigint(20) DEFAULT NULL,
                  `longitude` float DEFAULT NULL,
                  `latitude` float DEFAULT NULL,
                  `north_latitude` float DEFAULT NULL,
                  `south_latitude` float DEFAULT NULL,
                  `east_longitude` float DEFAULT NULL,
                  `west_longitude` float DEFAULT NULL,
                  `population` bigint(20) NOT NULL DEFAULT '0',
                  `population_date` date DEFAULT NULL,
                  `modification_date` date DEFAULT NULL,
                  `geonames_ref` bigint(20) DEFAULT NULL,
                  `wikidata_ref` varchar(20) DEFAULT NULL,
                  PRIMARY KEY (`grid_id`),
                  KEY `level` (`level`),
                  KEY `latitude` (`latitude`),
                  KEY `longitude` (`longitude`),
                  KEY `admin0_code` (`admin0_code`),
                  KEY `admin1_code` (`admin1_code`),
                  KEY `admin2_code` (`admin2_code`),
                  KEY `admin3_code` (`admin3_code`),
                  KEY `admin4_code` (`admin4_code`),
                  KEY `country_code` (`country_code`),
                  KEY `parent_id` (`parent_id`),
                  KEY `north_latitude` (`north_latitude`),
                  KEY `south_latitude` (`south_latitude`),
                  KEY `east_longitude` (`west_longitude`),
                  KEY `west_longitude` (`east_longitude`),
                  KEY `admin5_code` (`admin5_code`),
                  KEY `admin0_grid_id` (`admin0_grid_id`),
                  KEY `admin1_grid_id` (`admin1_grid_id`),
                  KEY `admin2_grid_id` (`admin2_grid_id`),
                  KEY `admin3_grid_id` (`admin3_grid_id`),
                  KEY `admin4_grid_id` (`admin4_grid_id`),
                  KEY `admin5_grid_id` (`admin5_grid_id`),
                  KEY `level_name` (`level_name`),
                  KEY `population` (`population`),
                  FULLTEXT KEY `name` (`name`)
                 ) $charset_collate;",
            "location_grid_edit_log" =>
                "CREATE TABLE IF NOT EXISTS `location_grid_edit_log` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `grid_id` bigint(22) DEFAULT NULL,
                  `user_id` bigint(22) DEFAULT NULL,
                  `type` varchar(100) DEFAULT NULL,
                  `subtype` varchar(100) DEFAULT NULL,
                  `old_value` longtext,
                  `new_value` longtext,
                  PRIMARY KEY (`id`),
                  KEY `grid_id` (`grid_id`),
                  KEY `user_id` (`user_id`),
                  KEY `type` (`type`),
                  KEY `subtype` (`subtype`)
                ) $charset_collate;",
        );
    }

    /**
     * Test function
     */
    public function test() {
        $this->test_expected_tables();
    }

}
