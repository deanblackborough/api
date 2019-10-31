<?php
declare(strict_types=1);

namespace App\Utilities;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Utility class to return default responses, we want some consistency
 * through out the API so all non expected responses should be returned via this
 * class
 *
 * As with all utility classes, eventually they may be moved into libraries if
 * they gain more than a few functions and the creation of a library makes
 * sense.
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright G3D Development Limited 2018-2019
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class Response
{
    /**
     * Return not found, 404
     *
     * @param string|null $type Entity type that cannot be found
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function notFound(?string $type = null, ?Exception $e = null): JsonResponse
    {
        $response = [
            'message' => ($type !== null) ? trans('responses.not-found-entity', ['type'=>$type]) :
                trans('responses.not-found')
        ];

        response()->json(
            $response,
            404
        )->send();
        exit;
    }

    /**
     * Return not found, 404
     *
     * @param string|null $type Entity type that cannot be found
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function notFoundOrNotAccessible(?string $type = null, ?Exception $e = null): JsonResponse
    {
        $response = [
            'message' => ($type !== null) ? trans('responses.not-found-or-not-accessible-entity', ['type'=>$type]) :
                trans('responses.not-found')
        ];

        response()->json(
            $response,
            404
        )->send();
        exit;
    }

    /**
     * Return a foreign key constraint error, 500
     *
     * @param string $message Custom message for error
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function foreignKeyConstraintError($message = '', ?Exception $e = null): JsonResponse
    {
        $response = [
            'message' => (strlen($message) > 0) ? $message : trans('responses.constraint')
        ];

        response()->json(
            $response,
            500
        )->send();
        exit;
    }

    /**
     * 500 error, unable to select the data ready to update
     *
     * Until we add logging this is an unknown server error, later we will
     * add MySQL error logging
     *
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function failedToSelectModelForUpdate(?Exception $e = null): JsonResponse
    {
        $response = [
            'message' => trans('responses.model-select-failure'),
        ];

        response()->json(
            $response,
            500
        )->send();
        exit();
    }

    /**
     * 500 error, failed to save the model.
     *
     * Until we add logging this is an unknown server error, later we will
     * add MySQL error logging
     *
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function failedToSaveModelForUpdate(?Exception $e = null): JsonResponse
    {
        $response = [
            'message' => trans('responses.model-save-failure-update'),
        ];

        response()->json(
            $response,
            500
        )->send();
        exit();
    }

    /**
     * 403 error, authentication required
     *
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function authenticationRequired(?Exception $e = null): JsonResponse
    {
        $response = [
            'message' => trans('responses.authentication-required')
        ];

        response()->json(
            $response,
            403
        )->send();
        exit();
    }

    /**
     * 500 error, failed to save the model.
     *
     * Until we add logging this is an unknown server error, later we will
     * add MySQL error logging
     *
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function failedToSaveModelForCreate(?Exception $e = null): JsonResponse
    {
        $response = [
            'message' => trans('responses.model-save-failure-create'),
        ];

        response()->json(
            $response,
            500
        )->send();
        exit();
    }

    /**
     * 404 error, unable to decode the selected value, hasher missing or value
     * invalid
     *
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function unableToDecode(?Exception $e = null): JsonResponse
    {
        $response = [
            'message' => trans('responses.decode-error')
        ];

        response()->json(
            $response,
            500
        )->send();
        exit;
    }

    /**
     * 204, successful request, no content to return, typically a PATCH
     *
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function successNoContent(?Exception $e = null): JsonResponse
    {
        $response = [];

        response()->json($response,204)->send();
        exit;
    }

    /**
     * 200, successful request, no content to return
     *
     * @param boolean $array Return empty array, if false empty object
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function successEmptyContent(bool $array = false, ?Exception $e = null): JsonResponse
    {
        $response = ($array === true ? [] : null);

        response()->json($response,200)->send();
        exit;
    }

    /**
     * 400 error, nothing to PATCH, bad request
     *
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function nothingToPatch(?Exception $e = null)
    {
        $response = [
            'message' => trans('responses.patch-empty')
        ];

        response()->json(
            $response,
            400
        )->send();
        exit();
    }

    /**
     * 400 error, invalid fields in the request, therefore bad request
     *
     * @param array $invalid_fields An array of invalid fields
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function invalidFieldsInRequest(array $invalid_fields, ?Exception $e = null): JsonResponse
    {
        $response = [
            'message' => trans('responses.patch-invalid'),
            'fields' => $invalid_fields
        ];

        response()->json(
            $response,
            400
        )->send();
        exit();
    }

    /**
     * 503, maintenance
     *
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function maintenance(?Exception $e = null): JsonResponse
    {
        $response = [
            'message' => trans('responses.maintenance')
        ];

        response()->json(
            $response,
            503
        )->send();
        exit();
    }

    /**
     * 422 error, validation error
     *
     * @param array $validation_errors
     * @param Exception|null $e
     *
     * @return JsonResponse
     */
    static public function validationErrors(array $validation_errors, ?Exception $e = null): JsonResponse
    {
        $response = [
            'message' => trans('responses.validation'),
            'fields' => $validation_errors
        ];

        response()->json(
            $response,
            422
        )->send();
        exit();
    }
}
