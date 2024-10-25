<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Filters\EmailFilter;
use App\Http\Filters\NameFilter;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/v1/users',
        operationId: 'index',
        description: 'Get all users.',
        security: [['sanctum' => []]],
        tags: ['User'],
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
            new OA\Response(response: '200', description: 'Users retrieved successfully.'),
            new OA\Response(response: '401', description: 'Unauthorized.'),
            new OA\Response(response: '404', description: 'Not found.'),
        ],
    )]
    public function index(Request $request): JsonResponse
    {
        $pipelines = [
            NameFilter::class,
            EmailFilter::class,
        ];

        $users = Pipeline::send(User::query())
            ->through($pipelines)
            ->thenReturn()
            ->with(['properties','favorites','agencies'])
            ->paginate($request->limit ?? 10);

        return $this->successResponse(
            data: new UserCollection($users),
            message: 'Users retrieved successfully.'
        );
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/v1/users/{id}',
        operationId: 'show',
        description: 'Get a user by ID.',
        security: [['sanctum' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the user to get.',
                in: "path",
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: '200', description: 'User retrieved successfully.'),
            new OA\Response(response: '401', description: 'Unauthorized.'),
            new OA\Response(response: '404', description: 'Not found.'),
        ],
    )]
    public function show(string $id): JsonResponse
    {
        $user = User::query()->find($id);
        if (!$user) {
            return $this->errorResponse(
                message: 'User not found.',
                status: 404
            );
        }
        return $this->successResponse(
            data: new UserResource($user),
            message: 'User retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/v1/users/{id}',
        operationId: 'update',
        description: 'Update a user by ID.',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            request: "UserRequest",
            description: 'User data to update.',
            required: true
        ),
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the user to update.',
                in: "path",
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: '200', description: 'User updated successfully.'),
            new OA\Response(response: '401', description: 'Unauthorized.'),
            new OA\Response(response: '404', description: 'Not found.'),
        ],
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'number_phone' => 'nullable|string|max:20',
        ]);

        $user = User::query()->find($id);
        if (!$user) {
            return $this->errorResponse(
                message: 'User not found.',
                status: 404
            );
        }
        $user->update($validatedData);
        return $this->successResponse(
            data: new UserResource($user),
            message: 'User updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::query()->find($id);
        if (!$user) {
            return $this->errorResponse(
                message: 'User not found.',
                status: 404
            );
        }
        $user->delete();
        return $this->successResponse(
            data: null,
            message: 'User deleted successfully.'
        );
    }

    /**
     * Logout the user.
     */
    #[
        OA\Post(
            path: '/api/v1/logout',
            operationId: 'logoutUser',
            description: 'Log the user out and delete their tokens.',
            security: [['sanctum' => []]],
            tags: ['User'],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'User logged out successfully.'
                )
            ]
        )
    ]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return $this->successResponse(
            data: null,
            message: 'User logged out successfully.'
        );
    }

    /**
     * Get the authenticated user.
     */
    #[
        OA\Get(
            path: '/api/v1/me',
            operationId: 'getAuthenticatedUser',
            description: 'Get the authenticated user.',
            security: [['sanctum' => []]],
            tags: ['User'],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'User retrieved successfully.',
                )
            ]
        )
    ]
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            data: new UserResource($request->user()),
            message: 'User retrieved successfully.'
        );
    }

    /**
     * Assign role to user.
     */
    #[
        OA\Post(
            path: '/api/v1/users/{id}/assign-role',
            operationId: 'assignRoleToUser',
            description: 'Assign a role to the specified user.',
            security: [['sanctum' => []]],
            tags: ['User'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    in: 'path',
                    required: true,
                    description: 'ID of the user to assign a role.',
                )
            ],
            requestBody: new OA\RequestBody(
                required: true,
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Role assigned successfully.',
                ),
                new OA\Response(
                    response: 404,
                    description: 'User not found.',
                ),
                new OA\Response(
                    response: 400,
                    description: 'Validation error.',
                )
            ]
        )
    ]
    public function assignRole(string $id, Request $request): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse(
                message: 'User not found.',
                status: 404
            );
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user->assignRole($request->role);

        return $this->successResponse(
            data: new UserResource($user),
            message: 'Role assigned successfully.'
        );
    }

    /**
     * Revoke role from user.
     */
    #[
        OA\Post(
            path: '/api/v1/users/{id}/revoke-role',
            operationId: 'revokeRoleFromUser',
            description: 'Revoke a role from the specified user.',
            security: [['sanctum' => []]],
            tags: ['User'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    in: 'path',
                    required: true,
                    description: 'ID of the user to revoke a role.',
                )
            ],
            requestBody: new OA\RequestBody(
                required: true,
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Role revoked successfully.',
                ),
                new OA\Response(
                    response: 404,
                    description: 'User not found.',
                ),
                new OA\Response(
                    response: 400,
                    description: 'Validation error.',
                )
            ]
        )
    ]
    public function revokeRole(string $id, Request $request): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse(
                message: 'User not found.',
                status: 404
            );
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user->removeRole($request->role);

        return $this->successResponse(
            data: new UserResource($user),
            message: 'Role revoked successfully.'
        );
    }
}
