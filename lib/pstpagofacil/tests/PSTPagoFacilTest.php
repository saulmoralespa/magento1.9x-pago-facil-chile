<?php 
use PHPUnit\Framework\TestCase;
use PSTPagoFacil\PSTPagoFacil;

/**
*  @author Saul Morales Pacheco <info@saulmoralespa.com>
*/
class PSTPagoFacilTest extends TestCase
{

    protected function tokenUser()
    {
        $token_user = '0e8e9c64-46f4-4d1d-8487-7464d76bfa3c'; //token user
        return $token_user;
    }


    public function testgetAccessToken()
    {

        $pagoFacil = new PSTPagoFacil($this->tokenUser());
        $pagoFacil->sandbox_mode(true);

        $token = $pagoFacil->getAccessToken();

        $this->assertNotEmpty($token);
    }


    public function testCreatePayTransaction()
    {

        $pagoFacil = new PSTPagoFacil($this->tokenUser());
        $pagoFacil->sandbox_mode(true);

        $transaction = $this->getTransactionExample();

        $data = $pagoFacil->createPayTransaction($transaction);

        $this->assertObjectHasAttribute('payUrl', $data);
    }

    public function testGetStatusOrder()
    {
        $pagoFacil = new PSTPagoFacil($this->tokenUser());
        $pagoFacil->sandbox_mode(true);
        $transaction = $this->getTransactionExample();
        $data = $pagoFacil->createPayTransaction($transaction);

        $idOrder = $data->idTrx;

        $statusOrder = $pagoFacil->statusOrder($idOrder);

        $this->assertObjectHasAttribute('status', $statusOrder);
    }

    public function getTransactionExample()
    {
        return array(
            'x_url_callback' => 'http://127.0.0.1/notify',
            'x_url_cancel' => 'http://127.0.0.1/cancel',
            'x_url_complete' => 'http://127.0.0.1/complete',
            'x_customer_email' => 'da545@gmail.com',
            'x_reference' => time(),
            'x_account_id' => '262',
            'x_amount' => 500,
            'x_currency' => 'CLP',
            'x_shop_country' => 'CL'
        );
    }

}
