<?php

class Saulmoralespa_PagoFacilChile_Helper_Data
extends Mage_Payment_Helper_Data
{
    const XML_PATH_ENVIROMENT = 'payment/pagofacilchile/environment';
    const XML_PATH_USER_TOKEN = 'payment/pagofacilchile/user_token_production';
    const XML_PATH_ACCOUNT_ID = 'payment/pagofacilchile/account_id_production';
    const XML_PATH_USER_TOKEN_DEVELOPMENT = 'payment/pagofacilchile/user_token_development';
    const XML_PATH_ACCOUNT_ID_DEVELOPMENT = 'payment/pagofacilchile/account_id_development';


    public function getEnviroment()
    {
        $status =  (bool)(int)Mage::getStoreConfig(self::XML_PATH_ENVIROMENT);
        return $status;
    }

    public function getUserToken()
    {
        if ($this->getEnviroment())
            return Mage::getStoreConfig(self::XML_PATH_USER_TOKEN_DEVELOPMENT);
        return Mage::getStoreConfig(self::XML_PATH_USER_TOKEN);
    }

    public function accountId()
    {
        if ($this->getEnviroment())
            return Mage::getStoreConfig(self::XML_PATH_ACCOUNT_ID_DEVELOPMENT);
        return Mage::getStoreConfig(self::XML_PATH_ACCOUNT_ID);
    }

    public function log($message, $array = null, $file = "pagofacilchile.log")
    {
        if (!is_null($array)) {
            $message .= " - " . json_encode($array);
        }

        Mage::log($message, null, $file, true);
    }

}