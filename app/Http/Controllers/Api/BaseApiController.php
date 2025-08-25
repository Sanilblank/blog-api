<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BaseApiController
 *
 * @package App\Http\Controllers\Api
 */
class BaseApiController extends Controller
{
    /**
     * @param  string  $message
     * @param $data
     * @param  int  $code
     * @param $pagination
     *
     * @return JsonResponse
     */
    public function success(
        string $message = 'Success',
        $data = null,
        $pagination = null,
        int $code = ResponseAlias::HTTP_OK
    ): JsonResponse {
        $responseData = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $responseData['data'] = $data;
        }

        if ($pagination !== null) {
            $responseData['meta'] = $pagination;
        }

        return response()->json($responseData, $code);
    }

    /**
     * @param  string  $message
     * @param $data
     * @param  int  $code
     *
     * @return JsonResponse
     */
    public function failure(
        string $message = 'Fail',
        $data = null,
        int $code = ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        $responseData = [
            'success' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            $responseData['data'] = $data;
        }

        return response()->json($responseData, $code);
    }
}
