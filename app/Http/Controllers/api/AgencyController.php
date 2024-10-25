<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgencyStoreRequest;
use App\Http\Requests\AgencyUpdateRequest;
use App\Http\Resources\AgencyCollection;
use App\Models\Agency;
use Illuminate\Http\JsonResponse;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

class AgencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/v1/agencies',
        operationId: 'getAgencies',
        description: 'Get all agencies.',
        security: [['sanctum' => []]],
        tags: ['Agencies'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number.',
                in: "query",
                required: false,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Items per page.',
                in: "query",
                required: false,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'name',
                description: 'Name of the user.',
                in: "query",
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'email',
                description: 'Email of the user.',
                in: "query",
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: '200', description: 'Agencies retrieved successfully.'),
            new OA\Response(response: '401', description: 'Unauthorized.'),
            new OA\Response(response: '404', description: 'Not found.'),
        ]
    )]
    public function index(): JsonResponse
    {
        $pipelines = [
            //     NameFilter::class,
            //     EmailFilter::class,
        ];
        $agencies = app(Pipeline::class)
            ->send(Agency::query())
            ->through($pipelines)
            ->thenReturn()
            ->filterByName(request('name'))
            ->filterByEmail(request('email'))
            ->filterByPhone(request('phone'))
            ->orderBy('created_at', 'desc')
            ->paginate(request('limit', 10));

        return $this->successResponseWithPagination(
            data: $agencies,
            resourceData: new AgencyCollection($agencies),
            message: 'Agencies retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    #[
        OA\Post(
            path: '/api/v1/agencies',
            operationId: 'createAgency',
            description: 'Create a new agency.',
            security: [['sanctum' => []]],
            tags: ['Agencies'],
            requestBody: new OA\RequestBody(
                description: 'Details of the agency to be created.',
                required: true,
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: 'Agency created successfully.',
                ),
                new OA\Response(response: 401, description: 'Unauthorized'),
                new OA\Response(response: 422, description: 'Validation error'),
            ]
        )
    ]

    public function store(AgencyStoreRequest $request): JsonResponse
    {
        $agency = Agency::create($request->validated());

        return $this->successResponse(
            data: $agency,
            message: 'Agency created successfully.',
            status: Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/v1/agencies/{id}',
        operationId: 'getAgencyById',
        description: 'Get a agency by ID.',
        security: [['sanctum' => []]],
        tags: ['Agencies'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the agency to get.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: '200', description: 'Agency retrieved successfully.'),
            new OA\Response(response: '404', description: 'Agency not found.')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $agency = Agency::find($id);
        if (!$agency) {
            return $this->errorResponse(
                message: 'Agency not found.',
                status: Response::HTTP_NOT_FOUND
            );
        }
        return $this->successResponse(
            data: $agency,
            message: 'Agency retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    #[
        OA\Put(
            path: '/api/v1/agencies/{id}',
            operationId: 'updateAgency',
            description: 'Update the details of an existing agency.',
            security: [['sanctum' => []]],
            tags: ['Agencies'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    description: 'ID of the agency to update.',
                    in: 'path',
                    required: true,
                    schema: new OA\Schema(type: 'string')
                )
            ],
            requestBody: new OA\RequestBody(
                description: 'Updated details of the agency.',
                required: true,
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Agency updated successfully.',
                ),
                new OA\Response(response: 404, description: 'Agency not found.'),
                new OA\Response(response: 401, description: 'Unauthorized'),
                new OA\Response(response: 422, description: 'Validation error'),
            ]
        )
    ]
    public function update(AgencyUpdateRequest $request, string $id): JsonResponse
    {
        $agency = Agency::find($id);
        if (!$agency) {
            return $this->errorResponse(
                message: 'Agency not found.',
                status: Response::HTTP_NOT_FOUND
            );
        }
        $agency->update($request->validated());

        return $this->successResponse(
            data: $agency,
            message: 'Agency updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/v1/agencies/{id}',
        operationId: 'deleteAgency',
        description: 'Delete a agency by ID.',
        security: [['sanctum' => []]],
        tags: ['Agencies'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the agency to delete.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: '200', description: 'Agency  delete successfully.'),
            new OA\Response(response: '404', description: 'Not found.'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $agency = Agency::find($id);
        if (!$agency) {
            return $this->errorResponse(
                message: 'Agency not found.',
                status: Response::HTTP_NOT_FOUND
            );
        }
        $agency->delete();

        return $this->successResponse(
            data: null,
            message: 'Agency deleted successfully.'
        );
    }
}
