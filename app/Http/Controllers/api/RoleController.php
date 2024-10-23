<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use OpenApi\Attributes as OA;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[
        OA\Get(
            path: '/api/v1/roles',
            operationId: 'getRoles',
            description: 'Retrieve all roles.',
            security: [['sanctum' => []]],
            tags: ['Roles'],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Roles retrieved successfully.',
                ),
                new OA\Response(response: 401, description: 'Unauthorized'),
            ]
        )
    ]
    public function index(): JsonResponse
    {
        $roles = Role::all();
        return $this->successResponse(
            data: $roles,
            message: 'Roles retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    #[
        OA\Post(
            path: '/api/v1/roles',
            operationId: 'createRole',
            description: 'Create a new role.',
            security: [['sanctum' => []]],
            tags: ['Roles'],
            requestBody: new OA\RequestBody(
                description: 'Details of the role to be created.',
                required: true,
                content: new OA\JsonContent(
                    type: 'object',
                    required: ['name'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', description: 'Name of the role', example: 'Admin'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: 'Role created successfully.',

                ),
                new OA\Response(response: 400, description: 'Validation error'),
                new OA\Response(response: 401, description: 'Unauthorized'),
            ]
        )
    ]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error.',
                errorMessages: $validator->errors()->all(),
                status: ResponseAlias::HTTP_BAD_REQUEST
            );
        }

        $role = Role::create(['name' => $request->name]);

        return $this->successResponse(
            data: $role,
            message: 'Role created successfully.',
            status: ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    #[
        OA\Get(
            path: '/api/v1/roles/{id}',
            operationId: 'getRole',
            description: 'Retrieve a specific role by ID.',
            security: [['sanctum' => []]],
            tags: ['Roles'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    description: 'ID of the role to retrieve.',
                    in: 'path',
                    required: true,
                    schema: new OA\Schema(type: 'string')
                )
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Role retrieved successfully.',
                ),
                new OA\Response(response: 404, description: 'Role not found.'),
                new OA\Response(response: 401, description: 'Unauthorized'),
            ]
        )
    ]
    public function show(string $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse(
                message: 'Role not found.',
                status: 404
            );
        }

        return $this->successResponse(
            data: $role,
            message: 'Role retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    #[
        OA\Put(
            path: '/api/v1/roles/{id}',
            operationId: 'updateRole',
            description: 'Update a specific role by ID.',
            security: [['sanctum' => []]],
            tags: ['Roles'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    description: 'ID of the role to update.',
                    in: 'path',
                    required: true,
                    schema: new OA\Schema(type: 'string')
                )
            ],
            requestBody: new OA\RequestBody(
                description: 'Updated details of the role.',
                required: true,
                content: new OA\JsonContent(
                    type: 'object',
                    required: ['name'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', description: 'Updated name of the role', example: 'User'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Role updated successfully.',
                ),
                new OA\Response(response: 404, description: 'Role not found.'),
                new OA\Response(response: 400, description: 'Validation error'),
                new OA\Response(response: 401, description: 'Unauthorized'),
            ]
        )
    ]
    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse(
                message: 'Role not found.',
                status: 404
            );
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error.',
                errorMessages: $validator->errors()->all(),
                status: 400
            );
        }

        $role->update(['name' => $request->name]);

        return $this->successResponse(
            data: $role,
            message: 'Role updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    #[
        OA\Delete(
            path: '/api/v1/roles/{id}',
            operationId: 'deleteRole',
            description: 'Remove a specific role by ID.',
            security: [['sanctum' => []]],
            tags: ['Roles'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    description: 'ID of the role to delete.',
                    in: 'path',
                    required: true,
                    schema: new OA\Schema(type: 'string')
                )
            ],
            responses: [
                new OA\Response(
                    response: 204,
                    description: 'Role deleted successfully.'
                ),
                new OA\Response(response: 404, description: 'Role not found.'),
                new OA\Response(response: 401, description: 'Unauthorized'),
            ]
        )
    ]
    public function destroy(string $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse(
                message: 'Role not found.',
                status: 404
            );
        }

        $role->delete();

        return $this->successResponse(
            data: null,
            message: 'Role deleted successfully.'
        );
    }

    /**
     * Get the permissions of the role.
     */
    #[
        OA\Get(
            path: '/api/v1/roles/{id}/permissions',
            operationId: 'getRolePermissions',
            description: 'Get the permissions of a specific role.',
            security: [['sanctum' => []]],
            tags: ['Roles'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    description: 'ID of the role to retrieve permissions for.',
                    in: 'path',
                    required: true,
                    schema: new OA\Schema(type: 'string')
                )
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Permissions retrieved successfully.',
                ),
                new OA\Response(response: 404, description: 'Role not found.'),
                new OA\Response(response: 401, description: 'Unauthorized'),
            ]
        )
    ]
    public function permissions(string $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse(
                message: 'Role not found.',
                status: 404
            );
        }

        return $this->successResponse(
            data: $role->permissions,
            message: 'Permissions retrieved successfully.'
        );
    }

    /**
     * Add permission to the role.
     */
    #[
        OA\Post(
            path: '/api/v1/roles/{id}/permissions',
            operationId: 'addRolePermission',
            description: 'Add a permission to a specific role.',
            security: [['sanctum' => []]],
            tags: ['Roles'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    description: 'ID of the role to which the permission will be added.',
                    in: 'path',
                    required: true,
                    schema: new OA\Schema(type: 'string')
                )
            ],
            requestBody: new OA\RequestBody(
                description: 'Details of the permission to be added to the role.',
                required: true,
                content: new OA\JsonContent(
                    type: 'object',
                    required: ['permission_name'],
                    properties: [
                        new OA\Property(property: 'permission_name', type: 'string', description: 'Name of the permission to add', example: 'edit-properties'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Permission added to role successfully.',
                ),
                new OA\Response(response: 404, description: 'Role not found.'),
                new OA\Response(response: 400, description: 'Validation error'),
                new OA\Response(response: 401, description: 'Unauthorized'),
            ]
        )
    ]
    public function addPermission(Request $request, string $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse(
                message: 'Role not found.',
                status: 404
            );
        }

        $validator = Validator::make($request->all(), [
            'permission_name' => 'required|string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error.',
                errorMessages: $validator->errors()->all(),
                status: 400
            );
        }

        $permission = Permission::where('name', $request->permission_name)->first();
        $role->givePermissionTo($permission);

        return $this->successResponse(
            data: $role->permissions,
            message: 'Permission added to role successfully.'
        );
    }
}
