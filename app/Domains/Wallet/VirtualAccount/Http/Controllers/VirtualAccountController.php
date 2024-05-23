<?php

namespace App\Domains\Wallet\VirtualAccount\Http\Controllers;

use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Wallet\VirtualAccount\Actions\GenerateVirtualAccount;
use App\Domains\Wallet\VirtualAccount\Actions\GetVirtualAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VirtualAccountController
{
    public function __construct(
        public GenerateVirtualAccount $generateVirtualAccount,
        public GetVirtualAccount $getVirtualAccount,
    ) {

    }

    /**
     * @throws CustomException
     */
    public function generateAccount(Request $request): JsonResponse
    {
        $response = $this->generateVirtualAccount->execute()->toArray();

        return successResponse('Virtual account details successfully generated', Response::HTTP_CREATED, $response);
    }

    public function getAccount(Request $request): JsonResponse
    {
        $response = $this->getVirtualAccount->execute($request);

        return successResponse('Virtual account details successfully retrieved', Response::HTTP_OK, $response);
    }
}
