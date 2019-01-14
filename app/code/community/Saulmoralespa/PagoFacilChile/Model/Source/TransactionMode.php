<?php
class Saulmoralespa_PagoFacilChile_Model_Source_TransactionMode
{
    public function toOptionArray()
    {
        $options =  array();
        $options[] = array(
            'value' => '1',
            'label' => 'Development'
        );
        $options[] = array(
            'value' => '0',
            'label' => 'Production'
        );
        return $options;
    }
}