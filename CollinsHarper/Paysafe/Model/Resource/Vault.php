<?php
/**
 * Copyright © 2015 Collinsharper. All rights reserved.
 */

namespace CollinsHarper\Paysafe\Model\Resource;

class Vault extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vault_payment_token_order_payment_link', 'order_payment_id');
    }
}
