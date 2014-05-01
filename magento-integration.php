<?php

final class MI
{
    private static $isInit = false;
    private static $layout;
    public static function init($pathToMage)
    {
        if (!self::$isInit) {
            include $pathToMage;
            umask(0);
            Mage::app();
            Mage::getSingleton('core/translate')
                ->setLocale(Mage::app()->getLocale()->getLocaleCode())
                ->init('frontend', true);
            Mage::getSingleton('core/session', array('name' => 'frontend'));
            Mage::getSingleton("checkout/session");
            Mage::getDesign()
                ->setPackageName('enterprise')
                ->setTheme('default');
            self::$isInit = true;
            $layout = Mage::app()->getLayout();            
            $module = Mage::app()->getRequest()->getModuleName();        
            if(!$module) {                
                $customerSession = Mage::getSingleton('customer/session');  
                $logged = ($customerSession->isLoggedIn()) ? 'customer_logged_in' : 'customer_logged_out';                
                $layout->getUpdate()
                    ->addHandle('default')
                    ->addHandle($logged)
                    ->load();                
                $layout->generateXml()
                    ->generateBlocks();                   
            }
            self::$layout = $layout;
        }
    }
    public static function getBlock($name, $render = true) {
        $block = self::$layout->getBlock($name);
        if ($block && $render) { 
            return $block->toHtml(); 
        } else if ($block && $render === false) {
            return $block;
        }
        return false;
    }
    public static function getStaticBlock($identifier)
    {   
        $layout = Mage::getSingleton('core/layout');
        $block = $layout->createBlock('cms/block')->setBlockId($identifier)->toHtml();        
        if ($block) {
            return $block;
        }
        return false;        
    }
}