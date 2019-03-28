<?php
namespace CollinsHarper\Paysafe\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * Init
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * Installs DB schema for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $customerSetup->getEntityTypeId(\Magento\Customer\Model\Customer::ENTITY);

        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "chpaysafe_profile_id");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "chpaysafe_profile_id",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "",
            "input"    => "text",
            "source"   => "",
            "visible"  => false,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => "",
            "is_system" => 0
        ));

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'chpaysafe_profile_id')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                "is_system" => 0
            ]);

        $attribute->save();

        $installer->endSetup();
    }
}