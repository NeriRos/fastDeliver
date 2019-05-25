<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace LightX\FastDeliver\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Sales\Model\OrderFactory;
use Magento\Framework\Registry;
use \LightX\FastDeliver\Helper\Data as DataHelper;
use LightX\FastDeliver\Model\Remark;

class Saveremark extends \Magento\Cms\Controller\Adminhtml\Block
{
	/**
	 * @var DataPersistorInterface
	 */
	protected $dataPersistor;

	/**
	 * @var BlockFactory
	 */
	private $fastDeliverFactory;

	/**
	 * @param Context $context
	 * @param Registry $coreRegistry
	 * @param DataPersistorInterface $dataPersistor
	 */
	public function __construct(
		Context $context,
		Registry $coreRegistry,
		StoreManagerInterface $storeManager,
		DataPersistorInterface $dataPersistor,
		DataHelper $helperData,
		OrderFactory $orderFactory

	) {
		$this->_orderFactory = $orderFactory;
		$this->_storeManager = $storeManager;
		$this->dataPersistor = $dataPersistor;
		$this->helperData = $helperData;
		parent::__construct($context, $coreRegistry);
	}

	/**
	 * Save action
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute()
	{
		$resultRedirect = $this->resultRedirectFactory->create();
		$data = $this->getRequest()->getParams();
		$orderId = $data['order_id'];

		if (array_key_exists("key", $data)) {
			unset($data['key']);
			unset($data['order_id']);
		}

		if ($data) {
			try {
				$result = $this->submitOrderToBaldar($data);

				$this->saveShipmentNumber($orderId, $result);
				$this->saveBaldarOrders($data);
				$this->saveBaldarOrderShipment($orderId, $result);
				$this->messageManager->addSuccessMessage(__('Delivery successful. Shipment number : ' . $result));

				return $resultRedirect->setPath('sales/order/index');
			} catch (LocalizedException $e) {
				$this->messageManager->addErrorMessage($e->getMessage());
			} catch (\Exception $e) {
				echo $e->getMessage();
				die;
				$this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the remark.'));
			}

			return $resultRedirect->setPath('sales/order/index');
		}

		return $resultRedirect->setPath('sales/order/index');
	}

	protected function saveBaldarOrderShipment($orderId, $shipmentNumber)
	{
		if ($orderId && $shipmentNumber) {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$insertData = [];
			$insertData['order_id'] = $orderId;
			$insertData['shipment_number'] = $shipmentNumber;

			$connection->insert("hk_baldar_order_shipment", $insertData);
		}
	}

	protected function saveBaldarOrders($data = [])
	{
		if (!empty($data)) {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$select = "select id from hk_baldar_order limit 1";
			$rs = $connection->fetchRow($select);
			$updateData = [];
			$updateData['baldar_client_code'] = $data['baldar_client_code'];
			$updateData['origin_company'] = $data['origin_company'];
			$updateData['origin_postcode'] = $data['origin_postcode'];
			$updateData['origin_street'] = $data['origin_street'];
			$updateData['origin_house_number'] = $data['origin_house_number'];
			$updateData['origin_town'] = $data['origin_town'];

			if (isset($rs['id'])) {
				$connection->update("hk_baldar_order", $updateData, ["id=?" => (int)$rs['id']]);
			} else {
				$connection->insert("hk_baldar_order", $updateData);
			}
		}
	}

	protected function saveShipmentNumber($orderId = 0, $shipmentNumber = 0)
	{
		if ($shipmentNumber && $orderId) {
			$order = $this->_orderFactory->create()->load($orderId);
			$history = $order->addStatusHistoryComment(":בלדר קיבל את ההזמנה, מספר משלוח" . $shipmentNumber);

			$history->save();
			$order->save();
		}
	}

	public function submitOrderToBaldar($orderData = [])
	{
		$paramsArr = [];

		if (empty($orderData)) {
			throw new ValidatorException('Invalid orders data.');
		}

		// print_r("ORDER data");
		// print_r($orderData);

		$destination_entrence = $orderData["destination_entrance"];
		$destination_floor = $orderData["destination_floor"];
		$destination_apartment = $orderData["destination_apartment"];

		unset($orderData["destination_entrance"]);
		unset($orderData["destination_floor"]);
		unset($orderData["destination_apartment"]);

		foreach ($orderData as $key => $value) {
			if ($key == "destination_house_number") {
				$paramsArr[] = $value .
				(!!$destination_entrence ? " " . $destination_entrence : "") .
				(!!$destination_floor ? ", " . $destination_floor : "") .
				(!!$destination_apartment ? ", " . $destination_apartment : "");
			}
			else {
				$paramsArr[] = $value;
			}
		}

		$soapUrl = $this->helperData->getGeneralConfig('api_url');
		$pParamString = implode(";", $paramsArr);

		$soapUser = $this->helperData->getGeneralConfig('username');
		$soapPassword = $this->helperData->getGeneralConfig('password');

		$requestXml = '<?xml version="1.0" encoding="utf-8"?>
						<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
						  <soap:Body>
							<SaveData xmlns="http://tempuri.org/">
							  <pParam>' . $pParamString . '</pParam>
							</SaveData>
						  </soap:Body>
						</soap:Envelope>';
		$headers = array(
			"Content-type: text/xml;charset=\"utf-8\"",
			"Accept: text/xml",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"SOAPAction: http://tempuri.org/SaveData",
			"Content-length: " . strlen($requestXml),
		);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, $soapUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $soapUser . ":" . $soapPassword); // username and password - declared at the top of the doc
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXml); // the SOAP request
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);

		curl_close($ch);

		$result = $this->getResponseCode($response);

		if ($result == -999) {
			throw new \Magento\Framework\Exception\LocalizedException(__('תקלה כללית, המידע אינו תקין.'));
		} elseif ($result == -100) {
			throw new \Magento\Framework\Exception\LocalizedException(__('קוד לקוח אינו קיים.'));
		} elseif ($result < -200) {
			throw new \Magento\Framework\Exception\LocalizedException(__('תשובה: ' . $result . 'השדה: ' . $this->getBadFieldName($result) . " אינו תקין"));
		} else {
			return $result;
		}
	}

	private function getBadFieldName($badFieldNumber)
	{
		$fieldLabels = ["סוג משלוח", "רחוב מוצא", "מספר בית מוצא", "עיר מוצא", "רחוב יעד", "מספר בית יעד", "עיר יעד", "שם חברת מוצא", "שם חברת יעד", "הודאות משלוח", "דחיפות", "מהיום למחר", "סוג כלי רכב", "מספר חבילות", "משלוח כפול", "מזהה הזמנה פנימי", "קוד לקוח", "ברקוד", "הערות", "מספר פלטות", "מיקוד מוצא", "מיקוד יעד", "שם איש קשר", "טלפון איש קשר", "אימייל איש קשר", "תאריך ביצוע", "משלוח גובינא"];

		if($badFieldNumber + 200 < count($fieldLabels)) {
			return $fieldLabels[$badFieldNumber + 200] . " - code: " . $badFieldNumber;
		} else {
			return "לא ידוע (" . $badFieldNumber . ")";
		}
	}

	protected function getResponseCode($responseXML = '')
	{
		$xml = preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$2$3', $responseXML);
		$xml = simplexml_load_string($xml);
		$json = json_encode($xml);
		$responseArray = json_decode($json, true);

		return $responseArray['soapBody']['SaveDataResponse']['SaveDataResult'];
	}
}
