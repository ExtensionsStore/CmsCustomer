<?php

/**
 * CmsCustomer observer
 *
 * @category   Aydus
 * @package    Aydus_CmsCustomer
 * @author     Aydus Consulting <davidt@aydus.com>
 */

class Aydus_CmsCustomer_Model_Observer  
{
    const UNAUTHORIZED_URL = 'unauthorized';
    
    /**
     * Authenticate access to cms page
     *
     * @see controller_action_predispatch_cms_page_view
     * @param Varien_Event_Observer $observer
     */    
    public function authenticate($observer)
    {
        if (Mage::helper('core')->isModuleEnabled('Aydus_AdminLoggedIn') && 
                Mage::helper('aydus_adminloggedin')->adminIsLoggedin()){
            
            return $this;
        }
        
        $cmsPageController = $observer->getControllerAction();
        $request = $cmsPageController->getRequest();
        $pageId = $request->getParam('page_id');
        
        $cache = Mage::app()->getCache();
        $cacheKey = md5(get_class($this).'_PAGE_IDS');
        $productPageIds = unserialize($cache->load($cacheKey));
        
        if (!is_array($productPageIds)){
            
            $productsCollection = Mage::getModel('catalog/product')->getCollection();
            $productsCollection->addAttributeToFilter('page_ids', array('neq' => ''));    

            $cmsPageTableName = $productsCollection->getTable('cms/page');
            
            $productsCollection->getSelect()->join(array('pages'=> $cmsPageTableName), 
                'FIND_IN_SET(`pages`.`page_id`, IF(at_page_ids.value_id > 0, at_page_ids.value, at_page_ids_default.value))', 
                array());
            
            $select = $productsCollection->getSelect();
            $select->reset(Zend_Db_Select::COLUMNS)
                ->columns('pages.page_id');
            $sql = (string)$select;

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $pageIds = $read->fetchCol($sql);
                        
            if (count($productPageIds) > 0){
                
                $productPageIds =  $pageIds;
                
                $cache->save(serialize($productPageIds), $cacheKey, array(Mage_Catalog_Model_Product::CACHE_TAG), 86400);
            }
            
        }
                
        if (!in_array($pageId, $productPageIds)){
            return;
        }
        
        if (Mage::getSingleton('customer/session')->isLoggedIn()){
            
            $customer = Mage::helper('customer')->getCustomer();

            $collection = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('customer_id', $customer->getId());
            
            if ($collection->getSize()){

                $productsCollection = Mage::getModel('catalog/product')->getCollection();
                $productsCollection->addAttributeToFilter('page_ids', array('finset' => $pageId));
                $select = (string)$productsCollection->getSelect();
                
                if ($productsCollection->getSize()>0){
                    
                    $productIds = $productsCollection->getAllIds();
                    
                    $itemsTableName = $collection->getTable('sales/order_item');
                    
                    $collection = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('customer_id', $customer->getId());
                    
                    $collection->getSelect()->join(
                            array('items'=> $itemsTableName), 
                            "items.order_id = main_table.entity_id", 
                            array('product_id'));       
                    $collection->addFieldToFilter('items.product_id', array('in'=>$productIds));
                    
                                        
                    $select = (string)$collection->getSelect();
                    $size = $collection->getSize();
                    
                    if ($size == 0){
                        
                        $this->_block();
                    }
                    
                }
                
                return $this;
                    
                
            } else {
                
                $this->_block();
            }
            
        } else {
            
            $this->_block();
        }
        
        return $this;
    }
    
    /**
     * Redirect to unauthorized page
     */
    protected function _block()
    {
        $url = Mage::getUrl(self::UNAUTHORIZED_URL);
        Mage::app()->getFrontController()->getResponse()->setRedirect($url, 401);
        Mage::app()->getResponse()->sendResponse();
        exit();        
    }
    
}