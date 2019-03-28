<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Core\Model\Currency\Import;

/**
 * Source model for Ccurrency Converter overload
 */
class Webservicex extends \Magento\Directory\Model\Currency\Import\Webservicex
{

    /**
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param int $retry
     * @return float|null
     */

    public function convert($currencyFrom, $currencyTo, $retry = 0)
    {
        return $this->_convert($currencyFrom, $currencyTo, $retry);
    }


}
