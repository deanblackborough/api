<?php

namespace App\Http\Controllers;

use App\Mail\RequestError;
use App\Option\Get;
use App\Option\Post;
use App\Utilities\Response;
use App\Validators\Request\Parameters;
use App\Models\RequestErrorLog;
use App\Models\RequestLog;
use App\Models\Transformers\RequestErrorLog as RequestErrorLogTransformer;
use App\Models\Transformers\RequestLog as RequestLogTransformer;
use App\Utilities\Pagination as UtilityPagination;
use App\Validators\Request\Fields\RequestErrorLog as RequestErrorLogValidator;
use App\Utilities\Response as UtilityResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Manage categories
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2019
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class RequestController extends Controller
{
    protected $collection_parameters = [];

    /**
     * Return the paginated request log
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function errorLog(Request $request): JsonResponse
    {
        $total = (new RequestErrorLog())->totalCount();

        $pagination = UtilityPagination::init($request->path(), $total, 50)
            ->paging();

        $logs = (new RequestErrorLog())->paginatedCollection(
            $pagination['offset'],
            $pagination['limit']
        );

        $headers = [
            'X-Count' => count($logs),
            'X-Total-Count' => $total,
            'X-Offset' => $pagination['offset'],
            'X-Limit' => $pagination['limit'],
            'X-Link-Previous' => $pagination['links']['previous'],
            'X-Link-Next' => $pagination['links']['next'],
        ];

        return response()->json(
            array_map(
                function($log) {
                    return (new RequestErrorLogTransformer($log))->toArray();
                },
                $logs
            ),
            200,
            $headers
        );
    }

    /**
     * Return the paginated access log
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function accessLog(Request $request): JsonResponse
    {
        $total = (new RequestLog())->totalCount();

        $this->collection_parameters = Parameters::fetch(['source']);

        $pagination = UtilityPagination::init($request->path(), $total, 50)
            ->paging();

        $log = (new RequestLog())->paginatedCollection(
            $pagination['offset'],
            $pagination['limit'],
            $this->collection_parameters
        );

        $headers = [
            'X-Total-Count' => $total,
            'X-Offset' => $pagination['offset'],
            'X-Limit' => $pagination['limit'],
            'X-Link-Previous' => $pagination['links']['previous'],
            'X-Link-Next' => $pagination['links']['next'],
        ];

        return response()->json(
            array_map(
                function ($access_log_entry) {
                    return (new RequestLogTransformer($access_log_entry))->toArray();
                },
                $log
            ),
            200,
            $headers
        );
    }

    /**
     * Generate the OPTIONS request for log
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function optionsAccessLog(Request $request)
    {
        $get = Get::init()->
            setDescription('route-descriptions.request_GET_access-log')->
            setParameters('api.request-access-log.parameters.collection')->
            setPagination(true)->
            option();

        return $this->optionsResponse($get, 200);
    }

    /**
     * Generate the OPTIONS request for error log
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function optionsErrorLog(Request $request)
    {
        $get = Get::init()->
            setDescription('route-descriptions.request_GET_error_log')->
            option();

        $post = Post::init()->
            setDescription('route-descriptions.request_POST')->
            setFields('api.request-error-log.fields')->
            option();

        return $this->optionsResponse(
            $get + $post,
            200
        );
    }

    /**
     * Log a request error, these are logged when the web app receives an unexpected
     * http status code response
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createErrorLog(Request $request): JsonResponse
    {
        $validator = (new RequestErrorLogValidator())->create();

        if ($validator->fails() === true) {
            return $this->returnValidationErrors($validator);
        }

        try {
            $request_error_log = new RequestErrorLog([
                'method' => $request->input('method'),
                'source' => $request->input('source'),
                'expected_status_code' => $request->input('expected_status_code'),
                'returned_status_code' => $request->input('returned_status_code'),
                'request_uri' => $request->input('request_uri'),
            ]);
            $request_error_log->save();

            Mail::to(Config::get('api.mail.request-error.to'))->
                send(
                    new RequestError([
                        'method' => $request->input('method'),
                        'source' => $request->input('source'),
                        'expected_status_code' => $request->input('expected_status_code'),
                        'returned_status_code' => $request->input('returned_status_code'),
                        'request_uri' => $request->input('request_uri'),
                        'referer' => $request->server('HTTP_REFERER', 'NOT SET!')
                    ])
                );
        } catch (Exception $e) {
            UtilityResponse::failedToSaveModelForCreate();
        }

        return Response::successNoContent();
    }
}
