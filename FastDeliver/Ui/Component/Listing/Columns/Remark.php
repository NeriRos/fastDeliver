<?php
namespace LightX\FastDeliver\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Remark extends Column
{
	/**
	 * @var UrlInterface
	 */
	protected $urlBuilder;

	/**
	 * @var PriceCurrencyInterface
	 */
	protected $priceFormatter;

	/**
	 * @var manufacturerOptions
	 */
	protected $manufacturerOptions = null;

	/**
	 * @var manufacturerOptions
	 */
	protected $_options = '';

	/**
	 * @var connection
	 */
	protected $_connection = null;

	/**
	 * Constructor
	 *
	 * @param ContextInterface $context
	 * @param UiComponentFactory $uiComponentFactory
	 * @param UrlInterface $urlBuilder
	 * @param PriceCurrencyInterface $priceFormatter
	 * @param \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository
	 * @param array $components
	 * @param array $data
	 */
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		UrlInterface $urlBuilder,
		PriceCurrencyInterface $priceFormatter,
		\Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
		\LightX\FastDeliver\Helper\Data $helperData,
		array $components = [],
		array $data = []
	) {
		$this->urlBuilder = $urlBuilder;
		$this->priceFormatter = $priceFormatter;
		$this->_productAttributeRepository = $productAttributeRepository;
		$this->helperData = $helperData;
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}

	/**
	 * Prepare Data Source
	 *
	 * @param array $dataSource
	 * @return array
	 */
	public function prepareDataSource(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			$fieldName = $this->getData('name');
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$select = "select * from hk_baldar_order limit 1";
			$rs = $connection->fetchRow($select);

			foreach ($dataSource['data']['items'] as &$item) {
				//$currencyCode = isset($item['order_currency_code']) ? $item['order_currency_code'] : null;
				$selectBaldarShipment = "select count(id)as shipmentTotal from hk_baldar_order_shipment where order_id='" . $item['entity_id'] . "'";
				$rsBaldarShipment = $connection->fetchRow($selectBaldarShipment);

				$billingAddress = $this->getAddressByType("billing", $item['entity_id']);
				$shippingAddress = $this->getAddressByType("shipping", $item['entity_id']);
				$currencyCode = null;
				$item[$fieldName . '_html'] = "<button class='button'><span>שלח לבלדר</span></button>";

				if (isset($rsBaldarShipment['shipmentTotal']) && $rsBaldarShipment['shipmentTotal'] > 0) {
					$item[$fieldName . '_html'] = "<span>ההזמנה נשלחה.</span><button class='button'><span>שלח שוב לבלדר</span></button>";
				}

				preg_match_all('!\d+!', $shippingAddress['street'], $matches);

				$item[$fieldName . '_title'] = __('מלא פרטי הזמנה');
				$item[$fieldName . '_submitlabel'] = __('שלח');
				$item[$fieldName . '_cancellabel'] = __('אפס');
				$item[$fieldName . '_orderid'] = $item['entity_id'];

				$item[$fieldName . '_origin_street'] = isset($rs['origin_street']) ? $rs['origin_street'] : '';
				$item[$fieldName . '_origin_city'] = isset($rs['origin_town']) ? $rs['origin_town'] : '';
				$item[$fieldName . '_origin_house_number'] = isset($rs['origin_house_number']) ? $rs['origin_house_number'] : '';
				$item[$fieldName . '_origin_company'] = isset($rs['origin_company']) ? $rs['origin_company'] : '';
				$item[$fieldName . '_origin_postcode'] = isset($rs['origin_postcode']) ? $rs['origin_postcode'] : '';				

				$item[$fieldName . '_destination_street'] = isset($matches[0][0]) ? str_replace($matches[0][0], "", $shippingAddress['street']) : $shippingAddress['street'];
				$item[$fieldName . '_destination_city'] = $shippingAddress['city'];
				$item[$fieldName . '_destination_house_number'] = isset($matches[0][0]) ? $matches[0][0] : '';
				$item[$fieldName . '_destination_company'] = $shippingAddress['company'];
				$item[$fieldName . '_destination_postcode'] = $shippingAddress['postcode'];
				
				$item[$fieldName . '_contact_name'] = $item['shipping_name'];
				$item[$fieldName . '_contact_telephone'] = $shippingAddress['telephone'];
				$item[$fieldName . '_contact_email'] = $shippingAddress['email'];
				
				$item[$fieldName . '_shipping_instruction'] = $this->getShippingInstruction($item['entity_id']);
				$item[$fieldName . '_execution_date'] = date("Y-m-d", strtotime($item['created_at']));
				$item[$fieldName . '_baldar_client_code'] = isset($rs['baldar_client_code']) ? $rs['baldar_client_code'] : '';
				$item[$fieldName . '_site_internal_order_id'] = $item['increment_id'];
				$item[$fieldName . '_formaction'] = $this->urlBuilder->getUrl('fastDeliver/order/saveremark');
			}
		}

		return $dataSource;
	}

	protected function getShippingInstruction($order_id = 0)
	{
		if (is_null($this->_connection)) {
			$this->_connection = $this->helperData->getDbConnection();
		}

		$select = "select comment from sales_shipment_comment where parent_id='" . $order_id . "'";
		$rsShipment = $this->_connection->fetchAll($select);
		$shipmentComments = [];
		$shipmentCommentsString = '';

		if (count($rsShipment) > 0) {
			foreach ($rsShipment as $_shipment) {
				$shipmentComments[] = $_shipment['comment'];
			}

			$shipmentCommentsString = implode(",", $shipmentComments);
		}

		return $shipmentCommentsString;
	}

	protected function getAddressByType($type = 'billing', $order_id = 0)
	{
		if (is_null($this->_connection)) {
			$this->_connection = $this->helperData->getDbConnection();
		}

		$select = "select * from sales_order_address where parent_id='" . $order_id . "' and address_type='" . $type . "' limit 1";
		$rsRemark = $this->_connection->fetchRow($select);
		
		return (isset($rsRemark['entity_id'])) ? $rsRemark : [];
	}
}
