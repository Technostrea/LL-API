<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $properties = Property::all();
        return $this->successResponse(
            data: $properties,
            message: 'Properties retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = Property::create(array_merge($request->all(), [
            'user_id' => Auth::id(),
        ]));
        return $this->successResponse(
            data: $property,
            message: 'Property created successfully',
            status: 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $property = Property::find($id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404);
        }
        return $this->successResponse(
            data: $property,
            message: 'Property retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $property = Property::find($id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404);
        }
        if ($property->user_id !== Auth::id()) {
            return $this->errorResponse(
                message: 'Unauthorized',
                status: 401);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric|min:0',
            'area' => 'numeric|min:0',
            'status' => 'in:available,rented,sold',
            'property_type' => 'in:house,apartment,commercial,land',
            'address' => 'string|max:255',
            'city' => 'string|max:100',
            'state' => 'string|max:100',
            'zip' => 'string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error',
                errorMessages: $validator->errors()->all(),
                status: 422);
        }
        $property->update($request->all());
        return $this->successResponse(
            data: $property,
            message: 'Property updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $property = Property::find($id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404);
        }
        if ($property->user_id !== Auth::id()) {
            return $this->errorResponse(
                message: 'Unauthorized',
                status: 401);
        }
        $property->delete();
        return $this->successResponse(
            data: null,
            message: 'Property deleted successfully');
    }

    /**
     * Display a listing of the resource.
     */
    public function myProperties(): JsonResponse
    {
        $properties = Property::where('user_id', (int)Auth::id())->get();
        return $this->successResponse(
            data: $properties,
            message: 'Properties retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeImage(Request $request, string $id): JsonResponse
    {
        $property = Property::find($id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404);
        }
        if ($property->user_id !== Auth::id()) {
            return $this->errorResponse(
                message: 'Unauthorized',
                status: 401);
        }
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error',
                errorMessages: $validator->errors()->all(),
                status: 422);
        }
        $imageName = time() . '_' . trim(str_replace(" ", "_", $request->file('image')->getClientOriginalName()));
        $imageUrl = $request->file('image')->storeAs('images', $imageName, 's3');
        $property->images()->create([
            'image_url' => $imageUrl,
        ]);
        return $this->successResponse(
            data: $property,
            message: 'Image uploaded successfully',
            status: Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyImage(string $id, string $imageId): JsonResponse
    {
        $property = Property::find($id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: Response::HTTP_NOT_FOUND);
        }
        if ($property->user_id !== Auth::id()) {
            return $this->errorResponse(
                message: 'Unauthorized',
                status: Response::HTTP_UNAUTHORIZED);
        }
        $image = $property->images()->find($imageId);
        if (!$image) {
            return $this->errorResponse(
                message: 'Image not found',
                status: 404);
        }
        $image->delete();
        return $this->successResponse(
            data: null,
            message: 'Image deleted successfully');
    }

    /**
     * Display a listing of the resource.
     */
    public function favorites(): JsonResponse
    {
        $properties = Auth::user()->favorites;
        return $this->successResponse(
            data: $properties,
            message: 'Favorites retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function addFavorite(string $property_id, Request $request): JsonResponse
    {
        $user = $request->user();
        $property = Property::find($property_id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404);
        }
        if ($user->favorites()->where('property_id', $property_id)->exists()) {
            return $this->errorResponse(
                message: 'Property is already in favorites.',
                status: Response::HTTP_CONFLICT);
        }
        $user->favorites()->attach($property_id);
        return $this->successResponse(
            data: null,
            message: 'Property added to favorites successfully.',
            status: 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function removeFavorite(string $property_id, Request $request): JsonResponse
    {
        $user = $request->user();

        $property = Property::find($property_id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404);
        }

        if (!$user->favorites()->where('property_id', $property_id)->exists()) {
            return $this->errorResponse(
                message: 'Property not found in favorites',
                status: Response::HTTP_NOT_FOUND);
        }

        $user->favorites()->detach($property_id);

        return $this->successResponse(
            data: null,
            message: 'Property removed from favorites successfully');
    }

}
