<?php

namespace CollinsHarper\Paysafe\Model;

use Magento\Framework\Model\AbstractModel;
 
class Vault extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('CollinsHarper\Paysafe\Model\Resource\Vault');
    }
}