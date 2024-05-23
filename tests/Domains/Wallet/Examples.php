<?php

namespace Tests\Domains\Wallet;

class Examples
{
    public static function depositWebhookEvent(
        ?string $status = 'success',
        ?string $email = null,
        ?string $reference = null,
    ): array {
        return [
            "event" => "charge.success",
            "data" => [
                "id" => 3818622067,
                "domain" => "test",
                "status" => $status,
                "reference" => $reference ?? "171648306444710x23b27jlwjhp4zj",
                "amount" => 4000000,
                "message" => null,
                "gateway_response" => "Approved",
                "paid_at" => "2024-05-23T16:51:05.000Z",
                "created_at" => "2024-05-23T16:51:05.000Z",
                "channel" => "dedicated_nuban",
                "currency" => "NGN",
                "ip_address" => null,
                "metadata" => [
                    "receiver_account_number" => "1238158287",
                    "receiver_bank" => "Test Bank",
                    "custom_fields" => [
                        [
                            "display_name" => "Receiver Account",
                            "variable_name" => "receiver_account_number",
                            "value" => "1238158287",
                        ],
                        [
                            "display_name" => "Receiver Bank",
                            "variable_name" => "receiver_bank",
                            "value" => "Test Bank",
                        ],
                    ],
                ],
                "fees_breakdown" => null,
                "log" => null,
                "fees" => 30000,
                "fees_split" => null,
                "authorization" => [
                    "authorization_code" => "AUTH_nubwwylzko",
                    "bin" => "008XXX",
                    "last4" => "X553",
                    "exp_month" => "04",
                    "exp_year" => "2024",
                    "channel" => "dedicated_nuban",
                    "card_type" => "transfer",
                    "bank" => null,
                    "country_code" => "NG",
                    "brand" => "Managed Account",
                    "reusable" => false,
                    "signature" => null,
                    "account_name" => null,
                    "sender_country" => "NG",
                    "sender_bank" => null,
                    "sender_bank_account_number" => "XXXXXX4553",
                    "receiver_bank_account_number" => "1238158287",
                    "receiver_bank" => "Test Bank",
                ],
                "customer" => [
                    "id" => 169254362,
                    "first_name" => "Ayodele",
                    "last_name" => "Oniosun",
                    "email" => $email ?? 'invalidEmail@example.com',
                    "customer_code" => "CUS_0novdxjt7cs9u60",
                    "phone" => "+2348132016744",
                    "metadata" => [
                    ],
                    "risk_action" => "default",
                    "international_format_phone" => "+2348132016744",
                ],
                "plan" => [],
                "subaccount" => [],
                "split" => [],
                "order_id" => null,
                "paidAt" => "2024-05-23T16:51:05.000Z",
                "requested_amount" => 4000000,
                "pos_transaction_data" => null,
                "source" => null,
            ],
        ];
    }

    public static function invalidTransactionReference(): array
    {
        return [
            "status" => "error",
            "message" => "Transaction reference not found",
            "meta" => [
                "nextStep" => "Ensure that you're passing the reference of a transaction that exists on this integration",
            ],
            "type" => "validation_error",
            "code" => "transaction_not_found",
        ];
    }
}
