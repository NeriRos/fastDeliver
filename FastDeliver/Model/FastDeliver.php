<?php
/**
 * LightX_FastDeliver - Version 1.0.0
 * Author: LightX <neriya@lightx.co.il>
 */
namespace LightX\FastDeliver\Model;

use LightX\FastDeliver\Api\FastDeliverInterface;

class FastDeliver implements FastDeliverInterface
{
	/**
	 * Returns order success or fail message.
	 *
	 * @api
	 * @param array $orderData.
	 * @return string message success or fail.
	 */
	public function submitOrder($orderData = [])
	{
		return json_encode($orderData);

		$paramsArr = [];
		$username = 'ner';
		$password = 'pass';

		if (empty($orderData)) {
			return json_encode(['Invalid orders data.']);
		}

		foreach ($orderData as $key => $value) {
			$paramsArr[] = $value;
		}

		$pParamString = "pParam=" . implode(";", $paramsArr);
		$ch = curl_init();
		$url = "http://141.226.21.44:8050/BaldarP/Service.asmx/SaveData";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_POST, 1);                //0 for a get request
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $pParamString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($ch);

		if (curl_error($ch)) {
			return $error_msg = curl_error($ch);
		}

		curl_close($ch);

		return $response;
	}
}
