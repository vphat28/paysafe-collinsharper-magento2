<?php

namespace CollinsHarper\Paysafe\Model\Adminhtml\Source;

/**
 * Class CcType
 * @codeCoverageIgnore
 */
class CcType extends \Magento\Payment\Model\Source\Cctype
{

    protected $_allowedTypes = ['VI', 'MC', 'AE', 'DI', 'JCB', 'MI', 'DN'];

    public function getValueByCardType($type)
    {
        $availableCards = $this->toOptionArray();

        foreach ($availableCards as $card)
        {
            if ($card["value"] === $type)
            {
                return $card["label"];
            }
        }
    }
}
