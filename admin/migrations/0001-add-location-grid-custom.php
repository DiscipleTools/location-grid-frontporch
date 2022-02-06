<?php
declare(strict_types=1);

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


/**
 * Class LG_Migration_0000
 */
class LG_Migration_0001 extends LG_Migration {

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
            "location_grid_groupings" =>
                "CREATE TABLE IF NOT EXISTS `location_grid_groupings` (
                  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `grid_id` bigint(20) DEFAULT NULL,
                  `grouping` varchar(100) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `grouping` (`grouping`)
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
