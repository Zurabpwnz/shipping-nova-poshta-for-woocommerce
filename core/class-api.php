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
use Nova_Poshta\Core\Cache\Factory_Cache;

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
	 * Cache
	 *
	 * @var Factory_Cache
	 */
	private $factory_cache;
	/**
	 * API Errors
	 *
	 * @var array
	 */
	private $errors = [];

	/**
	 * API constructor.
	 *
	 * @param DB            $db            Database.
	 * @param Factory_Cache $factory_cache Factory Cache.
	 * @param Settings      $settings      Plugin settings.
	 */
	public function __construct( DB $db, Factory_Cache $factory_cache, Settings $settings ) {
		$this->settings      = $settings;
		$this->factory_cache = $factory_cache;
		$this->db            = $db;
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
		$cache = $this->factory_cache->transient();
		if ( ! $cache->get( 'cities' ) ) {
			$response = $this->request( 'Address', 'getCities' );
			if ( $response['success'] ) {
				$this->db->update_cities( $response['data'] );
				$cache->set( 'cities', 1, constant( 'DAY_IN_SECONDS' ) );
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
		$cache = $this->factory_cache->object();
		if ( ! $cache->get( 'warehouse-' . $city_id ) ) {
			$response = $this->request(
				'AddressGeneral',
				'getWarehouses',
				[
					'CityRef' => $city_id,
				]
			);
			if ( ! empty( $response['success'] ) ) {
				$this->db->update_warehouses( $response['data'] );
				$cache->set( 'warehouse-' . $city_id, 1, constant( 'DAY_IN_SECONDS' ) );
			}
			unset( $response );
		}

		return $this->db->warehouses( $city_id );
	}

	/**
	 * Shipping cost
	 *
	 * @param string $recipient_city_id Recipient City ID.
	 * @param float  $weight            Weight.
	 * @param float  $volume            Volume weight.
	 *
	 * @return int
	 * @throws Exception Invalid DateTime.
	 */
	public function shipping_cost( string $recipient_city_id, float $weight, float $volume ): int {
		$city_id = $this->settings->city_id();
		$key     = 'shipping-from-' . $city_id . '-to-' . $recipient_city_id . '-' . $weight . '-' . $volume;
		$cache   = $this->factory_cache->object();
		$cost    = (int) $cache->get( $key );
		if ( ! $cost ) {
			$request = $this->request(
				'InternetDocument',
				'getDocumentPrice',
				[
					'CitySender'    => $city_id,
					'CityRecipient' => $recipient_city_id,
					'CargoType'     => 'Parcel',
					'DateTime'      => $this->get_current_date(),
					'VolumeGeneral' => max( 0.0004, $volume ),
					'Weight'        => max( 0.1, $weight ),
				]
			);
			$cost    = $request['success'] ? $request['data'][0]['CostWarehouseWarehouse'] : 0;
			if ( $cost ) {
				$cache->set( $key, $cost, 300 );
			}
		}

		return $cost;
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
	 * @param float  $weight       Weight of all products in order.
	 * @param float  $volume       Volume of all products in order.
	 * @param float  $redelivery   Cash on delivery price.
	 *
	 * @return string
	 * @throws Exception Invalid DateTime.
	 */
	public function internet_document(
		string $first_name, string $last_name, string $phone,
		string $city_id, string $warehouse_id, float $price,
		float $weight = 0, float $volume = 0, float $redelivery = 0
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
			'Description'   => apply_filters( 'shipping_nova_poshta_for_woocommerce_document_description', $this->settings->description() ),
			'VolumeGeneral' => max( 0.0004, $volume ),
			'Weight'        => max( 0.1, $weight ),
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
	 * Get last API errors
	 *
	 * @return array
	 */
	public function errors(): array {
		return $this->errors;
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
			$this->errors[] = __( 'You must enter the API key', 'shipping-nova-poshta-for-woocommerce' );

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
				'timeout'     => 5,
			]
		);

		if ( is_wp_error( $response ) ) {
			$this->errors = array_merge( $this->errors, $response->get_error_messages() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! empty( $body['errors'] ) ) {
			$this->errors = array_merge( $this->errors, $body['errors'] );
		}

		return $body ? $body : [];
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
