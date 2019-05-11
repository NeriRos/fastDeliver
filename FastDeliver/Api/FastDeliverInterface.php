<?php
/**
 * LightX_FastDeliver - Version 1.0.0
 * Author: LightX <hiren.kava84@gmail.com>
 */
namespace LightX\FastDeliver\Api;
 
interface FastDeliverInterface
{
    /**
     * Returns order success or fail message.
     *
     * @api
     * @param LightX\FastDeliver\Api\FastDeliverInterface $orderData
     * @return \LightX\FastDeliver\Api\FastDeliverInterface
     */
    public function submitOrder($orderData=[]);
}