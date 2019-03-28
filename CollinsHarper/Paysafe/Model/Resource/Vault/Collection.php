<?php
/**
 * Copyright © 2015 Collinsharper. All rights reserved.
 */

namespace CollinsHarper\Paysafe\Model\Resource\Vault;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CollinsHarper\Paysafe\Model\Vault', 'CollinsHarper\Paysafe\Model\Resource\Vault');
    }
}
