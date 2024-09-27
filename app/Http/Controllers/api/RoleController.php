<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $roles = Role::all();
        return $this->successResponse(
            data: $roles,
            message: 'Roles retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error.',
                errorMessages: $validator->errors()->all(),
                status: ResponseAlias::HTTP_BAD_REQUEST);
        }

        $role = Role::create(['name' => $request->name]);

        return $this->successResponse(
            data: $role,
            message: 'Role created successfully.',
            status: ResponseAlias::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse(
                message: 'Role not found.',
                status: 404);
        }

        return $this->successResponse(
            data: $role,
            message: 'Role retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse(
                message: 'Role not found.',
                status: 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error.',
                errorMessages: $validator->errors()->all(),
                status: 400);
        }

        $role->update(['name' => $request->name]);

        return $this->successResponse(
            data: $role,
            message: 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse(
                message: 'Role not found.',
                status: 404);
        }

        $role->delete();

        return $this->successResponse(
            data: null,
            message: 'Role deleted successfully.');
    }

    /**
     * Get the permissions of the role.
     */
    public function permissions(string $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse(
                message: 'Role not found.',
                status: 404);
        }

        return $this->successResponse(
            data: $role->permissions,
            message: 'Permissions retrieved successfully.');
    }

    /**
     * Add permission to the role.
     */
    public function addPermission(Request $request, string $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse(
                message: 'Role not found.',
                status: 404);
        }

        $validator = Validator::make($request->all(), [
            'permission_name' => 'required|string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error.',
                errorMessages: $validator->errors()->all(),
                status: 400);
        }

        $permission = Permission::where('name', $request->permission_name)->first();
        $role->givePermissionTo($permission);

        return $this->successResponse(
            data: $role->permissions,
            message: 'Permission added to role successfully.');

    }
}
