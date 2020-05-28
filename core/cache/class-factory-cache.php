<?php
/**
 * Cache factory
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core\Cache;

/**
 * Class Factory_Cache
 *
 * @package Nova_Poshta\Core\Cache
 */
class Factory_Cache {

	/**
	 * Object Cache
	 *
	 * @var Object_Cache
	 */
	private $object_cache;
	/**
	 * Transient Cache
	 *
	 * @var Transient_Cache
	 */
	private $transient_cache;

	/**
	 * Factory_Cache constructor.
	 *
	 * @param Transient_Cache $transient_cache Transient cache.
	 * @param Object_Cache    $object_cache    Object cache.
	 */
	public function __construct( Transient_Cache $transient_cache, Object_Cache $object_cache ) {
		$this->object_cache    = $object_cache;
		$this->transient_cache = $transient_cache;
	}

	/**
	 * Transient Cache
	 *
	 * @return Abstract_Cache
	 */
	public function transient(): Abstract_Cache {
		return $this->transient_cache;
	}

	/**
	 * Object Cache
	 *
	 * @return Abstract_Cache
	 */
	public function object(): Abstract_Cache {
		return $this->object_cache;
	}

}
