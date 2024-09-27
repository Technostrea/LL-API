<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::all();
        return $this->successResponse(
            data: $permissions,
            message: 'Permissions retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error.',
                errorMessages: $validator->errors()->all(),
                status: 400);
        }

        $permission = Permission::create(['name' => $request->name]);

        return $this->successResponse(
            data: $permission,
            message: 'Permission created successfully.',
            status: 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->errorResponse(
                message: 'Permission not found.',
                status: 404);
        }

        return $this->successResponse(
            data: $permission,
            message: 'Permission retrieved successfully.');

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->errorResponse(
                message: 'Permission not found.',
                status: 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error.',
                errorMessages: $validator->errors()->all(),
                status: 400);
        }

        $permission->update(['name' => $request->name]);

        return $this->successResponse(
            data: $permission,
            message: 'Permission updated successfully.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->errorResponse(
                message: 'Permission not found.',
                status: 404);
        }

        $permission->delete();

        return $this->successResponse(
            data: null,
            message: 'Permission deleted successfully.');

    }
}
