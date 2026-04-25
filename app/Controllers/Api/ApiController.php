<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    /**
     * Success response
     */
    protected function success($data = null, string $message = '', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Error response
     */
    protected function error(string $message = '', $errors = null, int $status = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Resource not found
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, null, 404);
    }

    /**
     * Validation error response
     */
        protected function validationError($errors, string $message = 'Validation failed'): JsonResponse
        {
            return $this->error($message, $errors, 422);
        }

    /**
     * Server error response
     */
    protected function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return $this->error($message, null, 500);
    }

    /**
     * Created response (for POST requests)
     */
    protected function created($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, null, 401);
    }

    /**
     * Forbidden response
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, null, 403);
    }

    /**
     * No content response (for DELETE requests)
     */
    protected function noContent(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return $this->success(null, $message, 204);
    }

        protected function paginated($paginatedData, string $message = '')
    {
        $data = [
            'data' => $paginatedData->items(),
            'meta' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total(),
                'from' => $paginatedData->firstItem(),
                'to' => $paginatedData->lastItem(),
            ],
            'links' => [
                'first' => $paginatedData->url(1),
                'last' => $paginatedData->url($paginatedData->lastPage()),
                'prev' => $paginatedData->previousPageUrl(),
                'next' => $paginatedData->nextPageUrl(),
            ]
        ];

        return $this->success($data, $message);
    }
}
