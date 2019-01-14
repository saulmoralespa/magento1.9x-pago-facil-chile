<?php

class Saulmoralespa_PagoFacilChile_Block_Pending
    extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('pagofacilchile/pending.phtml');
    }

    public function getContinueShoppingUrl()
    {
        return Mage::getUrl('checkout/cart');
    }
}