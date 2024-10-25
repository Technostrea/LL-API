<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

#[
    OA\Info(
        version: "0.1",
        description: "api a destination des clients mobile et web",
        title: "Louka-Loca API",
        termsOfService: "https://louka-loca.com/tos",
        contact: new OA\Contact(email: "contact@example.com"),
        license: new OA\License(name: "MIT", url: "https://louka-loca.com"),
    ),
    OA\SecurityScheme(
        securityScheme: "basic",
        type: "http",
        scheme: "basic",
    ),
    OA\SecurityScheme(
        securityScheme: "sanctum",
        type: "http",
        description: "Sanctum Token",
        scheme: "bearer",
        flows: [
            new OA\Flow(
                authorizationUrl: "https://louka-loca.com/oauth/authorize",
                tokenUrl: "https://louka-loca.com/oauth/token",
                refreshUrl: "https://louka-loca.com/oauth/token/refresh",
                flow: "password",
                scopes: [
                    "read:users" => "read users",
                    "write:users" => "write users",
                ],
            ),
        ],
    ),
    OA\Server(
        url: "http://localhost:8000",
        description: "API Server Louka-Loca",
    ),
    OA\Server(
        url: "https://louka-loca.com",
        description: "API Server Louka-Loca",
    ),
    OA\Tag(name: "User", description: "Operations about user"),
    OA\Tag(name: "Properties", description: "Operations about properties"),
    OA\Tag(name: "Agencies", description: "Operations about agencies"),
]
abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Send a success response.
     *
     * @param array|object|null $data
     * @param string|null $message
     * @param int $status
     *
     * @return JsonResponse
     */
    public function successResponse(array|object|null $data, string|null $message = null, int $status = ResponseAlias::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $response['data'] = $data->items();
            $response['current_page'] = $data->currentPage();
            $response['first_page_url'] = $data->url(1);
            $response['from'] = $data->firstItem();
            $response['last_page'] = $data->lastPage();
            $response['last_page_url'] = $data->url($data->lastPage());
            $response['next_page_url'] = $data->nextPageUrl();
            $response['path'] = $data->path();
            $response['per_page'] = $data->perPage();
            $response['prev_page_url'] = $data->previousPageUrl();
            $response['to'] = $data->lastItem();
            $response['total'] = $data->total();
            $response['total_pages'] = ceil($data->total() / $data->perPage());
        }

        return response()->json($response, $status);
    }

    /**
     * Send a success response with or without pagination.
     *
     * @param array|object|null $data
     * @param string|null $message
     * @param int $status
     * @param array|object|null $resourceData
     *
     * @return JsonResponse
     */
    public function successResponseWithPagination(
        array|object|null $data,
        string|null $message = null,
        int $status = ResponseAlias::HTTP_OK,
        array|object|null $resourceData = null
    ): JsonResponse {

        $response = [
            'success' => true,
            'message' => $message,
            'data' => filled($resourceData) ?
                $resourceData : ($data instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data->items() : $data),
        ];

        if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            // Ajout des détails de la pagination
            $response['pagination'] = [
                'current_page' => $data->currentPage(), // Numéro de la page actuelle
                'first_page_url' => $data->url(1), // URL de la première page
                'from' => $data->firstItem(), // Premier élément de la page actuelle
                'last_page' => $data->lastPage(), // Numéro de la dernière page
                'last_page_url' => $data->url($data->lastPage()), // URL de la dernière page
                'next_page_url' => $data->nextPageUrl(), // URL de la page suivante, ou null si c'est la dernière page
                'path' => $data->path(), // Chemin de base utilisé pour la pagination (sans le paramètre de la page)
                'per_page' => $data->perPage(), // Nombre d'éléments par page
                'prev_page_url' => $data->previousPageUrl(), // URL de la page précédente, ou null si c'est la première page
                'to' => $data->lastItem(), // Dernier élément de la page actuelle
                'total' => $data->total(), // Nombre total d'éléments disponibles
                'total_pages' => ceil($data->total() / $data->perPage()), // Nombre total de pages, calculé en divisant le total des éléments par le nombre d'éléments par page
            ];
        }
        return response()->json($response, $status);
    }

    /**
     * Send an error response.
     *
     * @param string $message
     * @param array $errorMessages
     * @param int $status
     *
     * @return JsonResponse
     */
    public function errorResponse(string $message, array $errorMessages = [], int $status = ResponseAlias::HTTP_BAD_REQUEST): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $status);
    }

    /**
     * Send an error response.
     *
     * @param string $error
     * @param array $errorMessages
     * @param int $code
     *
     * @return JsonResponse
     */
    public function sendErrorWithCode(string $error, array $errorMessages = [], int $code = ResponseAlias::HTTP_NOT_FOUND): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * Send response with pagination.
     *
     * @param $items
     * @param $data
     *
     * @return JsonResponse
     */
    public function respondWithPagination($items, $data): JsonResponse
    {
        $data = array_merge($data, [
            'paginator' => [
                'total_count' => $items->total(),
                'total_pages' => ceil($items->total() / $items->perPage()),
                'current_page' => $items->currentPage(),
                'limit' => $items->perPage()
            ]
        ]);

        return $this->successResponse($data, 'Data retrieved successfully.');
    }
}
