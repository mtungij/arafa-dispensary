<?php

namespace App\Service;

use SonicPesa\SonicPesa;
// use SonicPesa\SonicPesa;

class SonicPesaService
{
    /**
     * SonicPesa Getaway payement simple to use low free.
     * visit https://sonicpesa.com/ for more details.
     */
    private $sonicPesa;

    public function __construct()
    {
        // use SonicPesa\SonicPesa;
        $this->sonicPesa = new SonicPesa("", "");
    }

    public function createPayment()
    {
        try {
            $payment = $this->sonicPesa->payment()->create_order([]);
            return $payment;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function paymentStatus($paymentId)
    {
        try {
            $status = $this->sonicPesa->payment()->order_status($paymentId);
            return $status;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
