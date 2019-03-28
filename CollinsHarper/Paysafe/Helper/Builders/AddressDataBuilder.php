<?php

namespace CollinsHarper\Paysafe\Helper\Builders;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Class AddressDataBuilder
 */
class AddressDataBuilder extends AbstractDataBuilder implements BuilderInterface
{
    /**
     * BillingAddress index name
     */
    const BILLING_ADDRESS = 'billingDetails';

    /**
     * ShippingAddress index name
     */
    const SHIPPING_ADDRESS = 'shippingDetails';

    const CITY = 'city';
    const COUNTRY = 'country';
    const STATE = 'state';
    const STREET = 'street';
    const STREET2 = 'street2';
    const ZIP = 'zip';
    const PHONE = 'phone';

    private $useRegionCodeForCountries = [
        'CA',
        'US'
    ];

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $result = [];

        $billingAddress = $this->quote->getBillingAddress();
        $shippingAddress = $this->quote->getShippingAddress();

        $useAsShippingAddress = ($this->quote->getShippingAddress()->getSameAsBilling()) ? true : false;

        $result[self::BILLING_ADDRESS] = $this->buildAddress($billingAddress);
        $result[self::BILLING_ADDRESS]['useAsShippingAddress'] = $useAsShippingAddress;
        $result[self::SHIPPING_ADDRESS] = $this->buildAddress($shippingAddress);

        return $result;
    }

    public function getStreetDetails($address, $position)
    {
        if(isset($address[$position]))
        {
            return $address[$position];
        } else {
            return '';
        }
    }

    private function buildAddress(Address $address)
    {
        $data = array(
            self::CITY => $address->getCity(),
            self::COUNTRY => $address->getCountryId(),
            self::STREET => $this->getStreetDetails($address->getStreet(), 0),
            self::STREET2 => $this->getStreetDetails($address->getStreet(), 1),
            self::ZIP => $address->getPostcode(),
            self::PHONE => $address->getTelephone()
        );

        if ($data[self::STREET2] === '') {
            unset($data[self::STREET2]);
        }

        if (in_array($address->getCountryId(), $this->useRegionCodeForCountries)) {
            $data[self::STATE] = $address->getRegionCode();
        } else {
            if ($address->getRegion() != '') {
                $data[self::STATE] = $address->getRegion();
            }
        }

        return $data;
    }
}
