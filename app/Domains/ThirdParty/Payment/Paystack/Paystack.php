<?php

namespace App\Domains\ThirdParty\Payment\Paystack;

use App\Domains\ThirdParty\Payment\PaymentProvider;
use App\Domains\Utils\Exceptions\CustomException;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Paystack extends PaymentProvider
{
    public function __construct(public readonly Config $config = new Config())
    {
    }

    /**
     * @throws ConnectionException
     */
    public function getBanks()
    {
        return $this->http()->get('/bank?country=nigeria')->json();
    }

    public function http(): PendingRequest
    {
        return Http::timeout(180)->withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->config->secretKey(),
        ])->baseUrl($this->config->baseUrl());
    }

    /**
     * @throws CustomException
     */
    public function createCustomer(object $data): object
    {
        try {
            return $this->http()->post('/customer', [
                'email' => $data->email,
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'phone' => $data->phone,
            ])->json();
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * @throws CustomException
     */
    public function createVirtualBankAccount(object $data): object
    {
        try {
            return $this->http()->post('/dedicated_account', [
                'customer' => $data->customer,
                'preferred_bank' => $data->preferred_bank,
            ])->json();
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * @throws CustomException
     */
    public function createTransferRecipient(object $data): object
    {
        try {
            return $this->http()->post('/transferrecipient', [
                'type' => 'nuban',
                'name' => $data->account_name,
                'account_number' => $data->account_number,
                'bank_code' => $data->bank_name,
                'currency' => $data->currency,
            ])->json();
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * @throws CustomException
     */
    public function initiateTransfer(object $data): object
    {
        try {
            return $this->http()->post('/transfer', [
                'source' => $data->source,
                'reason' => $data->reason,
                'amount' => $data->amount,
                'recipient' => $data->recipient_code,
            ])->json();
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * @throws CustomException
     */
    public function finalizeTransfer(object $data): object
    {
        try {
            return $this->http()->post('/transfer/finalize_transfer', [
                'transfer_code' => $data->transfer_code,
                'otp' => $data->otp,
            ])->json();
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

}
