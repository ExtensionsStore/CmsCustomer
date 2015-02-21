<?php

/**
 * Source model for cms pages
 *
 * @category   Aydus
 * @package    Aydus_CmsCustomer
 * @author     Aydus Consulting <davidt@aydus.com>
 */

class Aydus_CmsCustomer_Model_Page_Source extends Mage_Core_Model_Abstract
{
    /**
     * Return option array with page_id value
     * Page collection toOptionIdArray returns identifier=>title
     * 
     * @return array
     */
    public function getAllOptions()
    {
        $collection = Mage::getModel('cms/page')->getCollection();
        $collection->addFieldToFilter('is_active',1);
        $options = array();
        
        $defaultCmsPages = array('no-route','home','about-magento-demo-store',
                'customer-service','enable-cookies','privacy-policy-cookie-restriction-mode',
                'service-unavailable','private-sales','reward-points','company',
                'private-sales-home','private_sales_home','unauthorized',
        );
        
        $collection->addFieldToFilter('identifier', array( 'nin' => $defaultCmsPages ));
                
        foreach ($collection as $item) {

            if (!in_array($item->getData('identifier'),$defaultCmsPages)){
                
                $option = array();
                $option['value'] = $item->getData('page_id');;
                $option['label'] = $item->getData('title');
                
                $options[] = $option;                
            }

        }
        
        return $options;
    }
    
}
