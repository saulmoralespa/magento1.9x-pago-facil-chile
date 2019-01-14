<?php

class Saulmoralespa_PagoFacilChile_Block_Pay
    extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('pagofacilchile/pay.phtml');
    }
}