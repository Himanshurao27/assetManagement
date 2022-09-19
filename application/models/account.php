<?php

namespace Models;
use Shared\Services\Db;
use Framework\{Security};

class Account extends \Shared\Model {

	/**
	 * @readwrite
	 * @var string
	 */
	protected $_table = "account";

	/**
	 * @column
	 * @readwrite
	 * @type text
	 *
	 * @label Name
	 */
	protected $_name;

	/**
	 * @column
	 * @readwrite
	 * @type text
	 *
	 * @label type
	 */
	protected $_type;

    /**
	 * @column
	 * @readwrite
	 * @type text
	 * @length 100
	 * @index
	 * 
	 * @validate required, min(8), max(100)
	 * @label Password
	 */
	protected $_password;

    /**
	 * @column
	 * @readwrite
	 * @type text
	 * @length 255
	 * @index
	 * 
	 * @validate required, min(8), max(255)
	 * @label vendor Email Address
	 */
	protected $_email;

    /**
	 * @column
	 * @readwrite
	 * @type text
	 * @length 200
	 * 
	 * @validate max(200)
	 * @label phone number
	 */
	protected $_phone = null;
}