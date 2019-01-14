<?php
class Saulmoralespa_PagoFacilChile_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Varien constructor
     */
    protected function _construct()
    {
        $this->setTemplate('pagofacilchile/form.phtml');
        parent::_construct();
    }
}