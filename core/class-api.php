<?php
/**
 * API for Nova Poshta
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

use DateTime;
use DateTimeZone;
use Exception;
use Nova_Poshta\Core\Cache\Cache;
use Nova_Poshta\Core\Cache\Object_Cache;
use Nova_Poshta\Core\Cache\Transient_Cache;

/**
 * Class API
 *
 * @package Nova_Poshta\Core
 */
class API {

	/**
	 * Nova Poshta API endpoint
	 */
	const ENDPOINT = 'https://api.novaposhta.ua/v2.0/json/';
	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	private $settings;
	/**
	 * Database
	 *
	 * @var DB
	 */
	private $db;
	/**
	 * Object cache
	 *
	 * @var Object_Cache
	 */
	private $object_cache;
	/**
	 * Transient cache
	 *
	 * @var Transient_Cache
	 */
	private $transient_cache;

	/**
	 * API constructor.
	 *
	 * @param DB              $db              Database.
	 * @param Object_Cache    $object_cache    Object cache.
	 * @param Transient_Cache $transient_cache Transient cache.
	 * @param Settings        $settings        Plugin settings.
	 */
	public function __construct( DB $db, Object_Cache $object_cache, Transient_Cache $transient_cache, Settings $settings ) {
		$this->settings        = $settings;
		$this->object_cache    = $object_cache;
		$this->transient_cache = $transient_cache;
		$this->db              = $db;
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		register_activation_hook(
			plugin_dir_path( __DIR__ ) . dirname( plugin_basename( __DIR__ ) ) . '.php',
			[ $this, 'activate' ]
		);
	}

	/**
	 * On activate plugin
	 */
	public function activate() {
		if ( $this->settings->api_key() ) {
			$this->cities();
		}
	}

	/**
	 * List of the cities
	 *
	 * @param string $search Search string.
	 * @param int    $limit  Limit cities in result.
	 *
	 * @return array
	 */
	public function cities( string $search = '', int $limit = 10 ): array {
		if ( ! $this->transient_cache->get( 'cities' ) ) {
			$response = $this->request( 'Address', 'getCities' );
			if ( $response['success'] ) {
				$this->db->update_cities( $response['data'] );
				$this->transient_cache->set( 'cities', 1 );
			}
			unset( $response );
		}

		return $this->db->cities( $search, $limit );
	}

	/**
	 * City name
	 *
	 * @param string $city_id City ID.
	 *
	 * @return string
	 */
	public function city( string $city_id ): string {
		return $this->db->city( $city_id );
	}

	/**
	 * City area
	 *
	 * @param string $city_id City ID.
	 *
	 * @return string
	 */
	public function area( string $city_id ): string {
		return $this->db->area( $city_id );
	}

	/**
	 * Warehouse full description
	 *
	 * @param string $warehouse_id Warehouse ID.
	 *
	 * @return string
	 */
	public function warehouse( string $warehouse_id ): string {
		return $this->db->warehouse( $warehouse_id );
	}

	/**
	 * List of warehouses
	 *
	 * @param string $city_id Warehouse ID.
	 *
	 * @return array
	 */
	public function warehouses( string $city_id ): array {
		if ( ! $this->object_cache->get( 'warehouse-' . $city_id ) ) {
			$response = $this->request(
				'AddressGeneral',
				'getWarehouses',
				[
					'CityRef' => $city_id,
				]
			);
			if ( ! empty( $response['success'] ) ) {
				$this->db->update_warehouses( $response['data'] );
				$this->object_cache->set( 'warehouse-' . $city_id, 1 );
			}
			unset( $response );
		}

		return $this->db->warehouses( $city_id );
	}

	/**
	 * Shipping cost
	 *
	 * @param string $city_id Recipient City ID.
	 *
	 * @return int
	 * @throws Exception Invalid DateTime.
	 */
	public function shipping_cost( string $city_id, float $weight, float $volume ): int {
		$cost = $this->request(
			'InternetDocument',
			'getDocumentPrice',
			[
				'CitySender'    => $this->settings->city_id(),
				'CityRecipient' => $city_id,
				'CargoType'     => 'Parcel',
				'DateTime'      => $this->get_current_date(),
				'VolumeGeneral' => max( 0.0004, $volume ),
				'Weight'        => max( 0.1, $weight ),
			]
		);

		return $cost['success'] ? $cost['data'][0]['CostWarehouseWarehouse'] : '';
	}

	/**
	 * Get current date
	 *
	 * @return string
	 * @throws Exception Invalid DateTime.
	 */
	private function get_current_date(): string {
		$date = new DateTime( '', new DateTimeZone( 'Europe/Kiev' ) );

		return $date->format( 'd.m.Y' );
	}

	/**
	 * Create internet document
	 *
	 * @param string $first_name   Customer first name.
	 * @param string $last_name    Customer last name.
	 * @param string $phone        Customer phone.
	 * @param string $city_id      Customer city ID.
	 * @param string $warehouse_id Customer warehouse ID.
	 * @param float  $price        Order price.
	 * @param int    $count        Order items count.
	 * @param float  $redelivery   Cash on delivery price.
	 *
	 * @return string
	 * @throws Exception Invalid DateTime.
	 */
	public function internet_document(
		string $first_name, string $last_name, string $phone,
		string $city_id, string $warehouse_id, float $price,
		int $count, float $redelivery = 0
	): string {
		$sender = $this->sender();
		if ( empty( $sender ) ) {
			return '';
		}
		$recipient = $this->recipient( $first_name, $last_name, $phone, $city_id, $warehouse_id );
		if ( empty( $recipient ) ) {
			return '';
		}
		$info = [
			'ServiceType'   => 'WarehouseWarehouse',
			'PaymentMethod' => 'Cash',
			'PayerType'     => 'Recipient',
			'Cost'          => $price,
			'SeatsAmount'   => 1,
			'OptionsSeat'   => [
				[
					'volumetricVolume' => 1,
					'volumetricWidth'  => $count * 26, // TODO: Calculate width.
					'volumetricLength' => $count * 14.5, // TODO: Calculate length.
					'volumetricHeight' => $count * 10, // TODO: Calculate height.
					'weight'           => ( $count * .5 ) - .01, // TODO: Calculate weight.
				],
			],
			'Description'   => apply_filters( 'shipping_nova_poshta_for_woocommerce_document_description', $this->settings->description() ),
			'Weight'        => ( $count * .5 ) - .01, // TODO: Calculate weight.
			'CargoType'     => 'Parcel',
			'DateTime'      => $this->get_current_date(),
		];
		if ( $redelivery ) {
			$info['BackwardDeliveryData'] = [
				[
					'PayerType'        => 'Recipient',
					'CargoType'        => 'Money',
					'RedeliveryString' => $redelivery,
				],
			];
		}
		$internet_document = $this->request(
			'InternetDocument',
			'save',
			array_merge( $sender, $recipient, $info )
		);

		return $internet_document['success'] ? $internet_document['data'][0]['IntDocNumber'] : '';
	}

	/**
	 * Validate phone number
	 *
	 * @param string $phone Phone number.
	 *
	 * @return string
	 */
	private function validate_phone( string $phone ): string {
		return '380' . substr( preg_replace( '/[^0-9]/', '', $phone ), - 9 );
	}

	/**
	 * Create sender
	 *
	 * @return array
	 */
	private function sender(): array {
		$phone        = $this->validate_phone( $this->settings->phone() ) ?? '';
		$city_id      = $this->settings->city_id() ?? '';
		$warehouse_id = $this->settings->warehouse_id() ?? '';
		if ( ! $phone || ! $city_id || ! $warehouse_id ) {
			return [];
		}
		$sender   = [
			'ContactSender' => $phone,
			'CitySender'    => $city_id,
			'SenderAddress' => $warehouse_id,
			'SendersPhone'  => $phone,
		];
		$response = $this->request(
			'Counterparty',
			'getCounterparties',
			[
				'City'                 => $sender['CitySender'],
				'CounterpartyProperty' => 'Sender',
				'Page'                 => 1,
			]
		);
		if ( ! isset( $response['success'] ) || ! $response['success'] ) {
			return [];
		}
		$sender['Sender'] = $response['data'][0]['Ref'];

		$response = $this->request(
			'Counterparty',
			'getCounterpartyContactPersons',
			[
				'Ref' => $sender['Sender'],
			]
		);
		if ( ! isset( $response['success'] ) || ! $response['success'] ) {
			return [];
		}
		$sender['ContactSender'] = $response['data'][0]['Ref'];

		return $sender;
	}

	/**
	 * Create recipient
	 *
	 * @param string $first_name   First name.
	 * @param string $last_name    Last name.
	 * @param string $phone        Phone number.
	 * @param string $city_id      City ID.
	 * @param string $warehouse_id Warehouse ID.
	 *
	 * @return array
	 */
	private function recipient(
		string $first_name, string $last_name, string $phone, string $city_id, string $warehouse_id
	): array {
		$phone     = $this->validate_phone( $phone );
		$recipient = [
			'FirstName'        => $first_name,
			'LastName'         => $last_name,
			'Phone'            => $phone,
			'RecipientsPhone'  => $phone,
			'Region'           => $this->area( $city_id ),
			'City'             => $city_id,
			'CityRecipient'    => $city_id,
			'RecipientAddress' => $warehouse_id,
		];
		$response  = $this->request(
			'Counterparty',
			'save',
			array_merge(
				[
					'CounterpartyProperty' => 'Recipient',
					'CounterpartyType'     => 'PrivatePerson',
				],
				$recipient
			)
		);
		if ( ! isset( $response['success'] ) || ! $response['success'] ) {
			return [];
		}
		$recipient['Recipient']        = $response['data'][0]['Ref'];
		$recipient['ContactRecipient'] = $response['data'][0]['ContactPerson']['data'][0]['Ref'];

		return $recipient;
	}

	/**
	 * Request to Nova Poshta API.
	 *
	 * @param string $model  Model name.
	 * @param string $method Method name.
	 * @param array  $args   Arguments for methods.
	 *
	 * @return array
	 */
	private function request( string $model, string $method, array $args = [] ): array {
		if ( ! $this->settings->api_key() ) {
			return [];
		}

		$response = wp_remote_post(
			self::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => wp_json_encode(
					apply_filters(
						'shipping_nova_poshta_for_woocommerce_request_body',
						[
							'modelName'        => $model,
							'calledMethod'     => $method,
							'methodProperties' => (object) $args,
							'apiKey'           => $this->settings->api_key(),
						],
						$model,
						$method
					)
				),
				'data_format' => 'body',
				'timeout'     => 30,
			]
		);

		return ! is_wp_error( $response ) ? json_decode( $response['body'], true ) : [];
	}

	/**
	 * Validate api key
	 *
	 * @param string $api_key API key.
	 *
	 * @return bool
	 */
	public function validate( string $api_key ): bool {
		$response = wp_remote_post(
			self::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => wp_json_encode(
					[
						'modelName'        => 'Address',
						'calledMethod'     => 'getCities',
						'apiKey'           => $api_key,
						'methodProperties' => (object) [
							'FindByString' => 'Киев',
						],
					]
				),
				'data_format' => 'body',
			]
		);
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$response = json_decode( $response['body'], true );

		return $response['success'];
	}

}
