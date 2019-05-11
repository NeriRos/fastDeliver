<?php

namespace LightX\FastDeliver\Helper;

use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
	public $connection = null;

	const XML_PATH_HELLOWORLD = 'fastDeliver/';

	public function __construct(Context $context, ResourceConnection $resource)
	{
		$this->_resource = $resource;
		parent::__construct($context);
	}

	public function getDbConnection()
	{
		if (!$this->connection) {
			$this->connection = $this->_resource->getConnection();
		}

		return $this->connection;
	}

	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
	}

	public function getGeneralConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_HELLOWORLD . 'general/' . $code, $storeId);
	}
}
