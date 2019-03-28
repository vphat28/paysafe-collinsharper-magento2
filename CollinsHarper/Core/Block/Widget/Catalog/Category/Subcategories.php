<?php

/**
 * Widget to display category sub categories
 */
namespace CollinsHarper\Core\Block\Widget\Catalog\Category;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Subcategories extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{


    const CATEGORY_ENTITY = 'category';
    /**
     * Prepared href attribute
     *
     * @var string
     */
    protected $_limit;

    /**
     * Prepared anchor text
     *
     * @var string
     */
    protected $_shelfName;

    /**
     * Url finder for category
     *
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;


    /**
     * @var string
     */
    protected $_template = 'widget/catalog/category/subcategory.phtml';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param UrlFinderInterface $urlFinder
     * @param \Magento\Catalog\Model\ResourceModel\AbstractResource $entityResource
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        UrlFinderInterface $urlFinder,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlFinder = $urlFinder;
        $this->_coreRegistry = $coreRegistry;

    }

    public function getCategoryUrl($category)
    {

            $href = false;
            $store = $this->hasStoreId() ? $this->_storeManager->getStore($this->getStoreId())
                : $this->_storeManager->getStore();
            $filterData = [
                UrlRewrite::ENTITY_ID => $category->getId(),
                UrlRewrite::ENTITY_TYPE => SELF::CATEGORY_ENTITY,
                UrlRewrite::STORE_ID => $store->getId(),
            ];

            $rewrite = $this->urlFinder->findOneByData($filterData);

            if ($rewrite) {
                $href = $store->getUrl('', ['_direct' => $rewrite->getRequestPath()]);

                if (strpos($href, '___store') === false) {
                    $href .= (strpos($href, '?') === false ? '?' : '&') . '___store=' . $store->getCode();
                }
            }
            return $href;

    }




    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->_coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }

    public function getCurrentChildCategories()
    {
        $collection = $this->getCurrentCategory()->getChildrenCategories();
        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('is_anchor')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', 1)
            ->setOrder('position', 'ASC')
            //->joinUrlRewrite()
            ;

        if(0 && (int)$this->getData('limit')) {
            $collection->setPageSize((int)$this->getData('limit'))
                ->setCurPage(1);
        }
        $collection->load();

        file_put_contents('/tmp/shane.log', " sel " .  $collection->getSelect()->__toString()."\n");

        return $collection;
    }

    /**
     * Render block HTML
     * or return empty string if url can't be prepared
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getCurrentCategory()) {
            return parent::_toHtml();
        }
        return '';
    }
}