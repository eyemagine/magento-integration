<?php

/**
 * Main Magento Integration class
 */
final class MI
{
    /**
     * Determines if Magento has been loaded or not
     * 
     * @var boolean
     */
    private static $isInit = false;
    /**
     * Magento layout object
     * 
     * @var Mage_Core_Model_Layout
     */
    private static $layout;
    /**
     * Loads Magento and constructs initial layout
     * 
     * @param  string $pathToMage Path to app/Mage.php
     * 
     * @return void
     */
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
        }
        /**
         * @var Mage_Core_Model_Layout $layout
         */
        $layout = Mage::app()->getLayout();
        /**
         * @var string $module
         */
        $module = Mage::app()->getRequest()->getModuleName();
        if (!$module) {
            /**
             * @var Mage_Customer_Model_Session $customerSession
             */
            $customerSession = Mage::getSingleton('customer/session');
            $layout->getUpdate()
                ->addHandle('default')
                ->addHandle(
                    $customerSession->isLoggedIn()
                        ? 'customer_logged_in'
                        : 'customer_logged_out'
                )
                ->load();
            $layout->generateXml()
                ->generateBlocks();
        }
        self::$layout = $layout;
    }
    /**
     * Loads a Magento layout block
     * 
     * @param  string  $name   Reference name of block
     * @param  boolean $render true: As HTML string; false: As block object
     * 
     * @return string|Mage_Core_Block_Abstract|boolean
     */
    public static function getBlock($name, $render = true)
    {
        /**
         * @var Mage_Core_Block_Abstract $block
         */
        $block = self::$layout->getBlock($name);
        if ($block && $render) { 
            return $block->toHtml(); 
        } else if ($block && $render === false) {
            return $block;
        }
        return false;
    }
    /**
     * Loads a Magento CMS static block
     * 
     * @param  string $identifier Key of CMS block
     * 
     * @return string|boolean
     */
    public static function getStaticBlock($identifier)
    {
        $block = self::$layout->createBlock('cms/block')
            ->setBlockId($identifier)
            ->toHtml();
        if ($block) {
            return $block;
        }
        return false;
    }
}