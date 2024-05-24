<?php

namespace App\Domains\ThirdParty\Payment\Paystack;

use App\Domains\ThirdParty\Payment\PaymentProvider;
use App\Domains\Utils\Exceptions\CustomException;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Paystack extends PaymentProvider
{
    public function __construct(public readonly Config $config = new Config())
    {
    }

    /**
     * @throws CustomException
     */
    public function createVirtualBankAccount(object $data): array
    {
        try {
            $customer = $this->createCustomer($data);

            return $this->http()->post('/dedicated_account', [
                'customer' => $customer['data']['customer_code'],
                'preferred_bank' => $this->config->preferredBank(),
            ])->json();
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * @throws CustomException
     */
    public function createCustomer(object $data): array
    {
        try {
            return $this->http()->post('/customer', [
                'email' => $data->email,
                'first_name' => $data->firstname,
                'last_name' => $data->lastname,
                'phone' => $data->phone,
            ])->json();
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
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
    public function verifyAccountNumber(string $accountNumber, string $bankCode): array
    {
        try {
            $url = '/bank/resolve?account_number='.$accountNumber.'&bank_code='.$bankCode;
            
            return $this->http()->get($url)->json();
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * @throws CustomException
     */
    public function listBanks(string $country = 'nigeria'): array
    {
        try {
            return $this->http()->get('/bank?country='.$country)->json();
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
                'bank_code' => $data->bank_code,
                'currency' => $data->currency,
            ])->json();
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * @throws CustomException
     */
    public function verifyTransferRecipient(string $recipient): object
    {
        try {
            return $this->http()->get('/transferrecipient/'.$recipient)->json();
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
    public function charge(object $data): object
    {
        try {
            return $this->http()->post('/charge', [
                'email' => $data->email,
                'amount' => $data->amount,
                'bank_transfer' => [
                    'account_expires_at' => now()->addDays(2),
                ],
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

    /**
     * @throws CustomException
     */
    public function verifyTransaction(string $reference): array
    {
        try {
            return $this->http()->get('/transaction/verify/'.$reference)->json();
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

}
