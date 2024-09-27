<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Filters\EmailFilter;
use App\Http\Filters\NameFilter;
use App\Http\Requests\AgencyStoreRequest;
use App\Http\Requests\AgencyUpdateRequest;
use App\Models\Agency;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Pipeline;
use Symfony\Component\HttpFoundation\Response;

class AgencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $pipelines = [
            NameFilter::class,
            EmailFilter::class,
            // TODO Add NumberPhoneFilter::class
        ];
        $agencies = Pipeline::of(Agency::query())
            ->through($pipelines)
            ->thenReturn()
            ->paginate(per_page: request('limit', 10));

        return $this->successResponse(
            data: $agencies,
            message: 'Agencies retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
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
