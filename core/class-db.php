<?php

namespace Nova_Poshta\Core;

class DB {

	private $cities_table;
	private $warehouses_table;

	public function __construct() {
		global $wpdb;
		$this->cities_table     = $wpdb->prefix . 'np_cities';
		$this->warehouses_table = $wpdb->prefix . 'np_warehouses';
	}

	public function create() {

		global $wpdb;
		$sql = 'CREATE TABLE ' . $this->cities_table . '
			(
		        city_id            VARCHAR(36) NOT NULL UNIQUE,
		        description        VARCHAR(100) NOT NULL
	        ) ' . $wpdb->get_charset_collate();

		maybe_create_table( $this->cities_table, $sql );

		global $wpdb;
		$sql = 'CREATE TABLE ' . $this->warehouses_table . '
			(
		        warehouse_id       VARCHAR(36) NOT NULL UNIQUE,
		        city_id            VARCHAR(36) NOT NULL,
		        description        VARCHAR(100) NOT NULL
	        ) ' . $wpdb->get_charset_collate();


		maybe_create_table( $this->cities_table, $sql );
	}

	public function cities( string $search, int $limit ): array {
		global $wpdb;
		$sql = 'SELECT * FROM ' . $this->cities_table;
		if ( $search ) {
			$sql .= $wpdb->remove_placeholder_escape(
				$wpdb->prepare( ' WHERE description LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' )
			);
		}
		$sql .= ' ORDER BY LENGTH(`description`), `description`';
		if ( $limit ) {
			$sql .= $wpdb->prepare( ' LIMIT %d', $limit );
		}

		$cities = $wpdb->get_results( $sql );

		return wp_list_pluck( $cities, 'description', 'city_id' );
	}

	public function update_cities( array $cities ): void {
		global $wpdb;
		$sql = 'INSERT INTO ' . $this->cities_table . ' (`city_id`, `description`, `area`) VALUES ';
		foreach ( $cities as $city ) {
			$sql .= $wpdb->prepare(
				'(%s, %s, %s),',
				$city['Ref'],
				$city['DescriptionRu'],
				$city['Area']
			);
		}
		$sql = rtrim( $sql, ',' );

		$sql .= ' ON DUPLICATE KEY UPDATE `description`=VALUES(`description`), `area`=VALUES(`area`)';
		$wpdb->query( $sql );
	}

	public function city( string $city_id ): string {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare( 'SELECT `description` FROM ' . $this->cities_table . ' WHERE city_id = %s', $city_id )
		);
	}

	public function area( string $city_id ): string {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare( 'SELECT `area` FROM ' . $this->cities_table . ' WHERE city_id = %s', $city_id )
		);
	}

	public function warehouses( string $city_id ): array {
		global $wpdb;

		$sql = $wpdb->prepare(
			'SELECT warehouse_id, description FROM ' . $this->warehouses_table .
			' WHERE city_id = %s  ORDER BY LENGTH(`description`), `description`',
			$city_id
		);

		$warehouses = $wpdb->get_results( $sql );

		return wp_list_pluck( $warehouses, 'description', 'warehouse_id' );
	}

	public function update_warehouses( array $warehouses ): void {
		global $wpdb;
		$sql = 'INSERT INTO ' . $this->warehouses_table . ' (`warehouse_id`,`city_id`, `description`) VALUES ';
		foreach ( $warehouses as $warehouse ) {
			if ( 'Postomat' === $warehouse['CategoryOfWarehouse'] ) {
				continue;
			}
			$sql .= $wpdb->prepare(
				'(%s, %s, %s),',
				$warehouse['Ref'],
				$warehouse['CityRef'],
				$warehouse['DescriptionRu']
			);
		}
		$sql = rtrim( $sql, ',' );

		$sql .= ' ON DUPLICATE KEY UPDATE `city_id`=VALUES(`city_id`), `description`=VALUES(`description`)';

		$wpdb->query( $sql );
	}

	public function warehouse( string $warehouse_id ): string {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				'SELECT `description` FROM ' . $this->warehouses_table . ' WHERE warehouse_id = %s',
				$warehouse_id
			)
		);
	}

}
