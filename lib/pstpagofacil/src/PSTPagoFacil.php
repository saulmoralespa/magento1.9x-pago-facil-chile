<?php

/**
 *
 * PHP Version 5
 *
 * @author Saul Morales Pacheco <info@saulmoralespa.com>
 * @version GIT 1.0
 *
 */

namespace PSTPagoFacil;

class PSTPagoFacil
{

    const API_LOGIN_TOKEN = 'https://api.pgf.cl/loginToken';

    const API_DEV_LOGIN_TOKEN = 'https://api-dev.pgf.cl/loginToken';

    const API_CREATE_PAY_TRANSACTION = 'https://api.pgf.cl/trxs/create';

    const API_DEV_CREATE_PAY_TRANSACTION = 'https://api-dev.pgf.cl/trxs/create';

    const API_STATUS_TRANSACTION = 'https://api.pgf.cl/trxs/status';

    const API_DEV_STATUS_TRANSACTION = 'https://api-dev.pgf.cl/trxs/status';

    /**
     * @var
     */
    private $token_secret;

    /**
     * @var bool
     */
    private $sandbox = false;

    /**
     * PSTPagoFacil constructor.
     * @param $token_user
     */
    public function __construct($token_user)
    {
        $this->setToken($token_user);
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function sandbox_mode($status = false)
    {
        if($status){
            $this->sandbox = true;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token_secret;
    }

    /**
     * @param $token_user
     * @return $this
     */
    public function setToken($token_user)
    {
        $this->token_secret = $token_user;
        return $this;
    }

    /**
     * @return mixed
     * @throws PSTPagoFacilException
     */
    public function getAccessToken()
    {

        $token = $this->exec();

        if (isset($token->error)){
            throw new  PSTPagoFacilException($token->error);
        }

        return $token->access_token_jwt;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws PSTPagoFacilException
     */
    public function createPayTransaction($data = array())
    {

        return $this->exec($data);
    }

    /**
     * @param $id
     * @return mixed
     * @throws PSTPagoFacilException
     */
    public function statusOrder($id)
    {
        $order = array('orderId' => $id);
        return $this->exec($order);
    }

    /**
     * @param array $request
     * @return mixed
     * @throws PSTPagoFacilException
     */
    private function exec($request = array())
    {
        try{
            $connect = $this->build_request($request);
        }catch (PSTPagoFacilException $exception){
            throw new PSTPagoFacilException($exception->getMessage());
        }

        $result = curl_exec($connect);

        if (curl_errno($connect)) {
            throw new  PSTPagoFacilException(curl_error ($connect));
        }
        curl_close ($connect);

        return json_decode($result);
    }

    /**
     * @param array $request
     * @return false|resource
     * @throws PSTPagoFacilException
     */
    private function build_request($request = array())
    {
        $ch = curl_init();

        $headers = array();

        if (empty($request)){
            curl_setopt($ch, CURLOPT_URL, $this->sandbox ? self::API_DEV_LOGIN_TOKEN :  self::API_LOGIN_TOKEN);
            $headers[] = "Authorization: Bearer $this->token_secret";
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        }elseif (!empty($request)){
            try{
                $token = $this->getAccessToken();
            }catch (PSTPagoFacilException $exception){
               throw new PSTPagoFacilException($exception->getMessage());
            }

            if (count($request) > 1){
                curl_setopt($ch, CURLOPT_URL, $this->sandbox ? self::API_DEV_CREATE_PAY_TRANSACTION :  self::API_CREATE_PAY_TRANSACTION);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                $headers[] = "Content-Type: application/x-www-form-urlencoded";
            }else{
                $urlEnviroment = $this->sandbox ? self::API_DEV_STATUS_TRANSACTION :  self::API_STATUS_TRANSACTION;
                $urlEnviroment .= DIRECTORY_SEPARATOR . $request['orderId'];
                curl_setopt($ch, CURLOPT_URL, $urlEnviroment);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            }

            $headers[] = "Authorization: Bearer $token";
        }

        $headers[] = "Cache-Control: no-cache";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $ch;
    }

}