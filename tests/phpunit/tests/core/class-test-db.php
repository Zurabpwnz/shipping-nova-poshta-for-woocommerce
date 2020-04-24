<?php
/**
 * DB tests
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Core;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use WP_Mock;
use WP_Mock\Functions;

/**
 * Class Test_Notice
 *
 * @package Nova_Poshta\Core
 */
class Test_DB extends Test_Case {

	/**
	 * Test including hooks
	 */
	public function test_hooks() {
		WP_Mock::userFunction( 'plugin_dir_path' )->
		once();
		WP_Mock::userFunction( 'plugin_basename' )->
		once()->
		andReturn( 'path/to/main-file' );
		WP_Mock::userFunction( 'register_activation_hook' )->
		once();
		WP_Mock::userFunction( 'get_locale' )->
		once();
		global $wpdb;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'prefix_';

		$db = new DB();

		$db->hooks();
	}

	/**
	 * Test search cities
	 */
	public function test_create() {
		global $wpdb;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'prefix_';
		$wpdb
			->shouldReceive( 'get_charset_collate' )
			->twice();
		WP_Mock::userFunction( 'maybe_create_table' )->
		withArgs( [ $wpdb->prefix . 'np_cities', Functions::type( 'string' ) ] )->
		once();
		WP_Mock::userFunction( 'maybe_create_table' )->
		withArgs( [ $wpdb->prefix . 'np_warehouses', Functions::type( 'string' ) ] )->
		once();
		$db = new DB();

		$db->create();
	}

	/**
	 * Test cities query
	 */
	public function test_cities() {
		global $wpdb;
		$search     = 'search';
		$esc_search = 'esc-search';
		$limit      = 11;
		$cities     = [ 'City 1', 'City 2' ];
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'prefix_';
		$wpdb
			->shouldReceive( 'esc_like' )
			->withArgs( [ $search ] )
			->andReturn( $esc_search );
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs( [ ' WHERE description_ru LIKE %s', '%' . $esc_search . '%' ] )
			->once()
			->andReturn( ' WHERE description_ru LIKE "%' . $esc_search . '%"' );
		$wpdb
			->shouldReceive( 'remove_placeholder_escape' )
			->withArgs( [ ' WHERE description_ru LIKE "%' . $esc_search . '%"' ] )
			->once()
			->andReturn( ' WHERE description_ru LIKE "%' . $esc_search . '%"' );
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs( [ ' LIMIT %d', $limit ] )
			->once()
			->andReturn( ' LIMIT ' . $limit );
		$wpdb
			->shouldReceive( 'get_results' )
			->withArgs(
				[
					'SELECT * FROM ' . $wpdb->prefix . 'np_cities' .
					' WHERE description_ru LIKE "%' . $esc_search . '%"' .
					' ORDER BY LENGTH(`description_ru`), `description_ru`' .
					' LIMIT ' . $limit,
				]
			)
			->once()
			->andReturn( $cities );
		WP_Mock::userFunction( 'wp_list_pluck' )->
		withArgs( [ $cities, 'description_ru', 'city_id' ] )->
		once()->
		andReturn( $cities );
		$db = new DB();

		$this->assertSame( $cities, $db->cities( $search, $limit ) );
	}

	/**
	 * Test update cities
	 */
	public function test_update_cities() {
		global $wpdb;
		$city1  = [
			'Ref'           => 'Ref 1',
			'DescriptionRu' => 'DescriptionRu 1',
			'Description'   => 'Description 1',
			'Area'          => 'Area 1',
		];
		$city2  = [
			'Ref'           => 'Ref 2',
			'DescriptionRu' => 'DescriptionRu 2',
			'Description'   => 'Description 2',
			'Area'          => 'Area 2',
		];
		$cities = [
			[
				'Ref'  => 'Ref fail',
				'Area' => 'Area fail',
			],
			$city1,
			$city2,
		];
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'prefix_';
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs( [
				            '(%s, %s, %s, %s),',
				            $city1['Ref'],
				            $city1['DescriptionRu'],
				            $city1['Description'],
				            $city1['Area'],
			            ] )
			->once()
			->andReturn( '("' . implode( '", "', $city1 ) . '"),' );
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs( [
				            '(%s, %s, %s, %s),',
				            $city2['Ref'],
				            $city2['DescriptionRu'],
				            $city2['Description'],
				            $city2['Area'],
			            ] )
			->once()
			->andReturn( '("' . implode( '", "', $city2 ) . '"),' );
		$wpdb
			->shouldReceive( 'query' )
			->withArgs(
				[
					'INSERT INTO ' . $wpdb->prefix . 'np_cities (`city_id`, `description_ru`, `description_ua`, `area`) VALUES ' .
					'("' . implode( '", "', $city1 ) . '"),' .
					'("' . implode( '", "', $city2 ) . '")' .
					' ON DUPLICATE KEY UPDATE `description_ru`=VALUES(`description_ru`), `description_ua`=VALUES(`description_ua`), `area`=VALUES(`area`)',
				]
			)
			->once();
		$db = new DB();

		$db->update_cities( $cities );
	}

	/**
	 * Test get city name by id
	 */
	public function test_city() {
		global $wpdb;
		$city_id   = 'city-id';
		$city_name = 'City Name';
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'prefix_';
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs( [ 'SELECT `description_ru` FROM ' . $wpdb->prefix . 'np_cities WHERE city_id = %s', $city_id ] )
			->once()
			->andReturn( 'SELECT `description_ru` FROM ' . $wpdb->prefix . 'np_cities WHERE city_id = "' . $city_id . '"' );
		$wpdb
			->shouldReceive( 'get_var' )
			->withArgs( [ 'SELECT `description_ru` FROM ' . $wpdb->prefix . 'np_cities WHERE city_id = "' . $city_id . '"' ] )
			->once()
			->andReturn( $city_name );
		$db = new DB();

		$this->assertSame( $city_name, $db->city( $city_id ) );
	}

	/**
	 * Test get city area by id
	 */
	public function test_area() {
		global $wpdb;
		$city_id = 'city-id';
		$area    = 'Area';
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'prefix_';
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs( [ 'SELECT `area` FROM ' . $wpdb->prefix . 'np_cities WHERE city_id = %s', $city_id ] )
			->once()
			->andReturn( 'SELECT `area` FROM ' . $wpdb->prefix . 'np_cities WHERE city_id = "' . $city_id . '"' );
		$wpdb
			->shouldReceive( 'get_var' )
			->withArgs( [ 'SELECT `area` FROM ' . $wpdb->prefix . 'np_cities WHERE city_id = "' . $city_id . '"' ] )
			->once()
			->andReturn( $area );
		$db = new DB();

		$this->assertSame( $area, $db->area( $city_id ) );
	}

	/**
	 * Test city warehouses
	 */
	public function test_warehouses() {
		global $wpdb;
		$city_id    = 'city-id';
		$warehouses = [ 'Warehouse 1', 'Warehouse 2' ];
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'prefix_';
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs(
				[
					'SELECT warehouse_id, description_ru FROM ' . $wpdb->prefix . 'np_warehouses' .
					' WHERE city_id = %s  ORDER BY LENGTH(`order`), `order`',
					$city_id,
				]
			)
			->once()
			->andReturn(
				'SELECT warehouse_id, description_ru FROM ' . $wpdb->prefix .
				'np_warehouses  WHERE city_id = "' . $city_id . '"  ORDER BY LENGTH(`order`), `order`'
			);
		$wpdb
			->shouldReceive( 'get_results' )
			->withArgs(
				[
					'SELECT warehouse_id, description_ru FROM ' . $wpdb->prefix .
					'np_warehouses  WHERE city_id = "' . $city_id . '"  ORDER BY LENGTH(`order`), `order`',
				]
			)
			->once()
			->andReturn( $warehouses );
		WP_Mock::userFunction( 'wp_list_pluck' )->
		withArgs( [ $warehouses, 'description_ru', 'warehouse_id' ] )->
		once()->
		andReturn( $warehouses );
		$db = new DB();

		$this->assertSame( $warehouses, $db->warehouses( $city_id ) );
	}

	/**
	 * Test update warehouses
	 */
	public function test_update_warehouses() {
		global $wpdb;
		$warehouse1 = [
			'Ref'           => 'Ref 1',
			'CityRef'       => 'CityRef 1',
			'DescriptionRu' => 'DescriptionRu 1',
			'Description'   => 'Description 1',
		];
		$warehouse2 = [
			'Ref'           => 'Ref 2',
			'CityRef'       => 'CityRef 2',
			'DescriptionRu' => 'DescriptionRu 2',
			'Description'   => 'Description 2',
		];
		$warehouses = [
			$warehouse1,
			$warehouse2,
		];
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'prefix_';
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs(
				[
					'(%s, %s, %s, %s, %d),',
					$warehouse1['Ref'],
					$warehouse1['CityRef'],
					$warehouse1['DescriptionRu'],
					$warehouse1['Description'],
					0,
				]
			)
			->once()
			->andReturn(
				'("' . $warehouse1['Ref'] . '", "' . $warehouse1['CityRef'] . '", "' . $warehouse1['DescriptionRu'] . '", "' . $warehouse1['Description'] . '", 0),'
			);
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs(
				[
					'(%s, %s, %s, %s, %d),',
					$warehouse2['Ref'],
					$warehouse2['CityRef'],
					$warehouse2['DescriptionRu'],
					$warehouse2['Description'],
					1,
				]
			)
			->once()
			->andReturn(
				'("' . $warehouse2['Ref'] . '", "' . $warehouse2['CityRef'] . '", "' . $warehouse2['DescriptionRu'] . '", "' . $warehouse2['Description'] . '", 1),'
			);
		$wpdb
			->shouldReceive( 'query' )
			->withArgs(
				[
					'INSERT INTO ' . $wpdb->prefix . 'np_warehouses (`warehouse_id`,`city_id`, `description_ru`, `description_ua`, `order`) VALUES ' .
					'("' . $warehouse1['Ref'] . '", "' . $warehouse1['CityRef'] . '", "' . $warehouse1['DescriptionRu'] . '", "' . $warehouse1['Description'] . '", 0),' .
					'("' . $warehouse2['Ref'] . '", "' . $warehouse2['CityRef'] . '", "' . $warehouse2['DescriptionRu'] . '", "' . $warehouse2['Description'] . '", 1)' .
					' ON DUPLICATE KEY UPDATE `city_id`=VALUES(`city_id`), `description_ru`=VALUES(`description_ru`), `description_ua`=VALUES(`description_ua`), `order`=VALUES(`order`)',
				]
			)
			->once();
		$db = new DB();

		$db->update_warehouses( $warehouses );
	}

	/**
	 * Test get warehouse name by id
	 */
	public function test_warehouse() {
		global $wpdb;
		$warehouse_id   = 'warehouse-id';
		$warehouse_name = 'Warehouse Name';
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'prefix_';
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs(
				[
					'SELECT `description_ru` FROM ' . $wpdb->prefix . 'np_warehouses WHERE warehouse_id = %s',
					$warehouse_id,
				]
			)
			->once()
			->andReturn( 'SELECT `description_ru` FROM ' . $wpdb->prefix . 'np_warehouses WHERE warehouse_id = "' . $warehouse_id . '"' );
		$wpdb
			->shouldReceive( 'get_var' )
			->withArgs( [ 'SELECT `description_ru` FROM ' . $wpdb->prefix . 'np_warehouses WHERE warehouse_id = "' . $warehouse_id . '"' ] )
			->once()
			->andReturn( $warehouse_name );
		$db = new DB();

		$this->assertSame( $warehouse_name, $db->warehouse( $warehouse_id ) );
	}

}
