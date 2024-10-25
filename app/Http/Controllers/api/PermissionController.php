<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use OpenApi\Attributes as OA;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[
        OA\Get(
            path: '/api/v1/permissions',
            operationId: 'getPermissions',
            description: 'Retrieve all permissions.',
            security: [['sanctum' => []]],
            tags: ['Permissions'],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Permissions retrieved successfully.',
                )
            ]
        )
    ]
    public function index(): JsonResponse
    {
        $permissions = Permission::all();
        return $this->successResponse(
            data: $permissions,
            message: 'Permissions retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    #[
        OA\Post(
            path: '/api/v1/permissions',
            operationId: 'createPermission',
            description: 'Store a newly created permission in storage.',
            security: [['sanctum' => []]],
            tags: ['Permissions'],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['name'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', description: 'Name of the permission')
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: 'Permission created successfully.',
                ),
                new OA\Response(
                    response: 400,
                    description: 'Validation error.',
                )
            ]
        )
    ]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error.',
                errorMessages: $validator->errors()->all(),
                status: 400
            );
        }

        $permission = Permission::create(['name' => $request->name]);

        return $this->successResponse(
            data: $permission,
            message: 'Permission created successfully.',
            status: 201
        );
    }

    /**
     * Display the specified resource.
     */
    #[
        OA\Get(
            path: '/api/v1/permissions/{id}',
            operationId: 'getPermission',
            description: 'Display the specified permission.',
            security: [['sanctum' => []]], // Dépend de votre système d'authentification
            tags: ['Permissions'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    in: 'path',
                    required: true,
                    description: 'ID of the permission to retrieve.',
                )
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Permission retrieved successfully.',
                ),
                new OA\Response(
                    response: 404,
                    description: 'Permission not found.',
                )
            ]
        )
    ]
    public function show(string $id): JsonResponse
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->errorResponse(
                message: 'Permission not found.',
                status: 404
            );
        }

        return $this->successResponse(
            data: $permission,
            message: 'Permission retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    #[
        OA\Put(
            path: '/api/v1/permissions/{id}',
            operationId: 'updatePermission',
            description: 'Update the specified permission in storage.',
            security: [['sanctum' => []]],
            tags: ['Permissions'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    in: 'path',
                    required: true,
                    description: 'ID of the permission to update.',
                )
            ],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['name'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', description: 'Name of the permission')
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Permission updated successfully.'
                ),
                new OA\Response(
                    response: 404,
                    description: 'Permission not found.',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', description: 'Error message')
                        ]
                    )
                ),
                new OA\Response(
                    response: 400,
                    description: 'Validation error.',
                )
            ]
        )
    ]
    public function update(Request $request, string $id): JsonResponse
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->errorResponse(
                message: 'Permission not found.',
                status: 404
            );
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error.',
                errorMessages: $validator->errors()->all(),
                status: 400
            );
        }

        $permission->update(['name' => $request->name]);

        return $this->successResponse(
            data: $permission,
            message: 'Permission updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    #[
        OA\Delete(
            path: '/api/v1/permissions/{id}',
            operationId: 'deletePermission',
            description: 'Remove the specified permission from storage.',
            security: [['sanctum' => []]],
            tags: ['Permissions'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    in: 'path',
                    required: true,
                    description: 'ID of the permission to delete.',
                )
            ],
            responses: [
                new OA\Response(
                    response: 204,
                    description: 'Permission deleted successfully.'
                ),
                new OA\Response(
                    response: 404,
                    description: 'Permission not found.',
                )
            ]
        )
    ]
    public function destroy(string $id): JsonResponse
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->errorResponse(
                message: 'Permission not found.',
                status: 404
            );
        }

        $permission->delete();

        return $this->successResponse(
            data: null,
            message: 'Permission deleted successfully.'
        );
    }
}
