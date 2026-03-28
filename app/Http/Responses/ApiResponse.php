<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ApiResponse — AUDIT FIX: Standardized API response envelope.
 *
 * Usage in controllers (add `use ApiResponse` to the class):
 *   return $this->success($data, 'Done.');
 *   return $this->created($resource);
 *   return $this->error('Not found.', 404);
 *   return $this->paginated($paginator);
 */
trait ApiResponse
{
    protected function success(mixed $data = null, string $message = '', int $status = 200, array $meta = []): JsonResponse
    {
        $body = ['success' => true];
        if ($message) $body['message'] = $message;
        $body['data'] = $data;
        if ($meta) $body['meta'] = $meta;
        return response()->json($body, $status);
    }

    protected function created(mixed $data = null, string $message = 'Created.'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function error(string $message, int $status = 400, ?string $code = null, array $errors = []): JsonResponse
    {
        $body = ['success' => false, 'message' => $message];
        if ($code) $body['code'] = $code;
        if ($errors) $body['errors'] = $errors;
        return response()->json($body, $status);
    }

    protected function paginated(LengthAwarePaginator $paginator, ?string $resourceClass = null): JsonResponse
    {
        $items = $resourceClass ? $resourceClass::collection($paginator->items()) : $paginator->items();
        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'pagination' => [
                    'current_page'  => $paginator->currentPage(),
                    'last_page'     => $paginator->lastPage(),
                    'per_page'      => $paginator->perPage(),
                    'total'         => $paginator->total(),
                    'has_more'      => $paginator->hasMorePages(),
                    'next_page_url' => $paginator->nextPageUrl(),
                    'prev_page_url' => $paginator->previousPageUrl(),
                ],
            ],
        ]);
    }
}
