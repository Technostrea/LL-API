<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Resources\PropertyCollection;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/v1/properties',
        operationId: 'getProperties',
        description: 'Get all properties.',
        security: [['sanctum' => []]],
        tags: ['Properties'],
        responses: [
            new OA\Response(response: '200', description: 'Properties retrieved successfully.'),
            new OA\Response(response: '401', description: 'Unauthorized.')
        ]
    )]
    public function index(): JsonResponse
    {
        $properties = Property::filterByStatus(request('status'))
            ->filterByPriceRange(request('min_price'), request('max_price'))
            ->filterByCity(request('city'))
            ->filterByType(request('property_type'))
            ->filterByAreaRange(request('min_area'), request('max_area'))
            ->nearLocation(request('latitude'), request('longitude'), request('radius'))
            ->with('images')
            ->paginate(request('limit', 10));

        return $this->successResponseWithPagination(
            data: $properties,
            message: 'Properties retrieved successfully',
            resourceData: new PropertyCollection($properties)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    #[
        OA\Post(
            path: '/api/v1/properties',
            operationId: 'storeProperty',
            description: 'Create a new property for the authenticated user.',
            security: [['sanctum' => []]],
            tags: ['Properties'],
            requestBody: new OA\RequestBody(
                description: 'Details of the property to be created.',
                required: true,
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: 'Property created successfully.',
                ),
                new OA\Response(response: 401, description: 'Unauthorized'),
                new OA\Response(response: 422, description: 'Validation error')
            ]
        )
    ]
    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = Property::create(array_merge($request->all(), [
            'user_id' => Auth::id(),
        ]));
        return $this->successResponse(
            data: new PropertyResource($property),
            message: 'Property created successfully',
            status: 201
        );
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/v1/properties/{id}',
        operationId: 'getPropertyById',
        description: 'Get a property by ID.',
        security: [['sanctum' => []]],
        tags: ['Properties'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the property to get.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: '200', description: 'Property retrieved successfully.'),
            new OA\Response(response: '404', description: 'Property not found.')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $property = Property::query()
            ->with('images')
            ->find($id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404
            );
        }
        return $this->successResponse(
            data: new PropertyResource($property),
            message: 'Property retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/v1/properties/{id}',
        operationId: 'updateProperty',
        description: 'Update a property by ID.',
        security: [['sanctum' => []]],
        tags: ['Properties'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'price', type: 'number'),
                    new OA\Property(property: 'area', type: 'number'),
                    new OA\Property(property: 'status', type: 'string'),
                    new OA\Property(property: 'property_type', type: 'string'),
                    new OA\Property(property: 'address', type: 'string'),
                    new OA\Property(property: 'city', type: 'string'),
                    new OA\Property(property: 'state', type: 'string'),
                    new OA\Property(property: 'zip', type: 'string'),
                    new OA\Property(property: 'latitude', type: 'number', nullable: true),
                    new OA\Property(property: 'longitude', type: 'number', nullable: true)
                ]
            )
        ),
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the property to update.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: '200', description: 'Property updated successfully.'),
            new OA\Response(response: '422', description: 'Validation error.'),
            new OA\Response(response: '404', description: 'Property not found.'),
            new OA\Response(response: '401', description: 'Unauthorized.')
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $property = Property::find($id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404
            );
        }
        if ($property->user_id !== Auth::id()) {
            return $this->errorResponse(
                message: 'Unauthorized',
                status: 401
            );
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
                status: 422
            );
        }
        $property->update($request->all());
        return $this->successResponse(
            data: new PropertyResource($property),
            message: 'Property updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/v1/properties/{id}',
        operationId: 'deleteProperty',
        description: 'Delete a property by ID.',
        security: [['sanctum' => []]],
        tags: ['Properties'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the property to delete.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: '200', description: 'Property  delete successfully.'),
            new OA\Response(response: '401', description: 'Unauthorized.'),
            new OA\Response(response: '404', description: 'Not found.'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $property = Property::find($id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404
            );
        }
        if ($property->user_id !== Auth::id()) {
            return $this->errorResponse(
                message: 'Unauthorized',
                status: 401
            );
        }
        $property->delete();
        return $this->successResponse(
            data: null,
            message: 'Property deleted successfully'
        );
    }

    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: 'api/v1/properties/me',
        operationId: 'getMyProperties',
        description: 'Get a property to auth user.',
        security: [['sanctum' => []]],
        tags: ['Properties'],
        responses: [
            new OA\Response(response: '200', description: 'Properties retrieved successfully.'),
        ]
    )]
    public function myProperties(): JsonResponse
    {
        $properties = Property::where('user_id', (int)Auth::id())->get();
        return $this->successResponse(
            data: new PropertyCollection($properties),
            message: 'Properties retrieved successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    #[
        OA\Post(
            path: '/api/v1/properties/{id}/images',
            operationId: 'storeImageProperty',
            description: 'Store an image for a property.',
            security: [['sanctum' => []]],
            tags: ['Properties'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    description: 'ID of the property to add the image to.',
                    in: 'path',
                    required: true,
                    schema: new OA\Schema(type: 'string')
                )
            ],
            requestBody: new OA\RequestBody(
                required: true,
                description: 'Image file to upload',
                content: [
                    new OA\MediaType(
                        mediaType: 'multipart/form-data',
                        schema: new OA\Schema(
                            type: 'object',
                            required: ['image'],
                            properties: [
                                new OA\Property(
                                    property: 'image',
                                    description: 'Image of the property',
                                    type: 'string',
                                    format: 'binary'
                                )
                            ]
                        )
                    )
                ]
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: 'Image uploaded successfully.',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'image_url', type: 'string', description: 'URL of the uploaded image')
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'Unauthorized'),
                new OA\Response(response: 404, description: 'Property not found'),
                new OA\Response(
                    response: 422,
                    description: 'Validation error',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: 'errorMessages',
                                type: 'array',
                                items: new OA\Items(type: 'string')
                            )
                        ]
                    )
                )
            ]
        )
    ]

    public function storeImage(Request $request, string $id): JsonResponse
    {
        $property = Property::find($id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404
            );
        }
        if ($property->user_id !== Auth::id()) {
            return $this->errorResponse(
                message: 'Unauthorized',
                status: 401
            );
        }
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error',
                errorMessages: $validator->errors()->all(),
                status: 422
            );
        }
        $imageName = time() . '_' . trim(str_replace(" ", "_", $request->file('image')->getClientOriginalName()));
        $imageUrl = $request->file('image')->storeAs('images', $imageName, 's3');
        $property->images()->create([
            'image_url' => $imageUrl,
        ]);
        return $this->successResponse(
            data: new PropertyResource($property),
            message: 'Image uploaded successfully',
            status: Response::HTTP_CREATED
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    #[
        OA\Delete(
            path: '/api/v1/properties/{id}/images/{imageId}',
            operationId: 'destroyImageProperty',
            description: 'Delete an image of a property.',
            security: [['sanctum' => []]],
            tags: ['Properties'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    description: 'ID of the property.',
                    in: 'path',
                    required: true,
                    schema: new OA\Schema(type: 'string')
                ),
                new OA\Parameter(
                    name: 'imageId',
                    description: 'ID of the image to delete.',
                    in: 'path',
                    required: true,
                    schema: new OA\Schema(type: 'string')
                )
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Image deleted successfully.',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: 'Image deleted successfully.')
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'Unauthorized'),
                new OA\Response(response: 404, description: 'Property or image not found')
            ]
        )
    ]
    public function destroyImage(string $id, string $imageId): JsonResponse
    {
        $property = Property::find($id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: Response::HTTP_NOT_FOUND
            );
        }
        if ($property->user_id !== Auth::id()) {
            return $this->errorResponse(
                message: 'Unauthorized',
                status: Response::HTTP_UNAUTHORIZED
            );
        }
        $image = $property->images()->find($imageId);
        if (!$image) {
            return $this->errorResponse(
                message: 'Image not found',
                status: 404
            );
        }
        $image->delete();
        return $this->successResponse(
            data: null,
            message: 'Image deleted successfully'
        );
    }

    /**
     * Display a listing of the resource.
     */
    #[
        OA\Get(
            path: '/api/v1/properties/favorites',
            operationId: 'getFavoriteProperties',
            description: 'Retrieve a list of user\'s favorite properties.',
            security: [['sanctum' => []]],
            tags: ['Properties'],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Favorites retrieved successfully.'
                ),
                new OA\Response(response: 401, description: 'Unauthorized')
            ]
        )
    ]
    public function favorites(): JsonResponse
    {
        $properties = Auth::user()->favorites;
        return $this->successResponse(
            data: $properties,
            message: 'Favorites retrieved successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    #[
        OA\Post(
            path: '/api/v1/properties/{property_id}/favorite',
            operationId: 'addPropertyToFavorites',
            description: 'Add a property to the user\'s favorites.',
            security: [['sanctum' => []]],
            tags: ['Properties'],
            parameters: [
                new OA\Parameter(
                    name: 'property_id',
                    description: 'ID of the property to add to favorites.',
                    in: 'path',
                    required: true,
                    schema: new OA\Schema(type: 'string')
                )
            ],
            responses: [
                new OA\Response(
                    response: 201,
                    description: 'Property added to favorites successfully.',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: 'Property added to favorites successfully.')
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'Unauthorized'),
                new OA\Response(response: 404, description: 'Property not found'),
                new OA\Response(response: 409, description: 'Property is already in favorites')
            ]
        )
    ]
    public function addFavorite(string $property_id, Request $request): JsonResponse
    {
        $user = $request->user();
        $property = Property::find($property_id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404
            );
        }
        if ($user->favorites()->where('property_id', $property_id)->exists()) {
            return $this->errorResponse(
                message: 'Property is already in favorites.',
                status: Response::HTTP_CONFLICT
            );
        }
        $user->favorites()->attach($property_id);
        return $this->successResponse(
            data: null,
            message: 'Property added to favorites successfully.',
            status: 201
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    #[
        OA\Delete(
            path: '/api/v1/properties/{property_id}/favorite',
            operationId: 'removePropertyFromFavorites',
            description: 'Remove a property from the user\'s favorites.',
            security: [['sanctum' => []]],
            tags: ['Properties'],
            parameters: [
                new OA\Parameter(
                    name: 'property_id',
                    description: 'ID of the property to remove from favorites.',
                    in: 'path',
                    required: true,
                    schema: new OA\Schema(type: 'string')
                )
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Property removed from favorites successfully.',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: 'Property removed from favorites successfully.')
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'Unauthorized'),
                new OA\Response(response: 404, description: 'Property not found in favorites')
            ]
        )
    ]
    public function removeFavorite(string $property_id, Request $request): JsonResponse
    {
        $user = $request->user();

        $property = Property::find($property_id);
        if (!$property) {
            return $this->errorResponse(
                message: 'Property not found',
                status: 404
            );
        }

        if (!$user->favorites()->where('property_id', $property_id)->exists()) {
            return $this->errorResponse(
                message: 'Property not found in favorites',
                status: Response::HTTP_NOT_FOUND
            );
        }

        $user->favorites()->detach($property_id);

        return $this->successResponse(
            data: null,
            message: 'Property removed from favorites successfully'
        );
    }


    #[
        OA\Get(
            path: '/api/v1/properties/search',
            operationId: 'searchProperties',
            description: 'Search for properties based on city, state, zip code, or geolocation (latitude, longitude, and radius).',
            security: [['sanctum' => []]],
            tags: ['Properties'],
            parameters: [
                new OA\Parameter(
                    name: 'city',
                    description: 'Filter properties by city.',
                    in: 'query',
                    required: false,
                    schema: new OA\Schema(type: 'string', maxLength: 100)
                ),
                new OA\Parameter(
                    name: 'state',
                    description: 'Filter properties by state.',
                    in: 'query',
                    required: false,
                    schema: new OA\Schema(type: 'string', maxLength: 100)
                ),
                new OA\Parameter(
                    name: 'zip',
                    description: 'Filter properties by zip code.',
                    in: 'query',
                    required: false,
                    schema: new OA\Schema(type: 'string', maxLength: 20)
                ),
                new OA\Parameter(
                    name: 'latitude',
                    description: 'Latitude for geolocation search.',
                    in: 'query',
                    required: false,
                    schema: new OA\Schema(type: 'number', format: 'float')
                ),
                new OA\Parameter(
                    name: 'longitude',
                    description: 'Longitude for geolocation search.',
                    in: 'query',
                    required: false,
                    schema: new OA\Schema(type: 'number', format: 'float')
                ),
                new OA\Parameter(
                    name: 'radius',
                    description: 'Search radius (in kilometers) for geolocation search.',
                    in: 'query',
                    required: false,
                    schema: new OA\Schema(type: 'number', format: 'float', minimum: 1)
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Properties retrieved successfully.',

                ),
                new OA\Response(response: 422, description: 'Validation error'),
                new OA\Response(response: 401, description: 'Unauthorized')
            ]
        )
    ]
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'city' => 'string|max:100|nullable',
            'state' => 'string|max:100|nullable',
            'zip' => 'string|max:20|nullable',
            'latitude' => 'numeric|nullable',
            'longitude' => 'numeric|nullable',
            'radius' => 'numeric|nullable|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: 'Validation error',
                errorMessages: $validator->errors()->all(),
                status: 422
            );
        }

        $query = Property::query();

        // Filter by city, state, or zip code if provided
        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }

        if ($request->filled('state')) {
            $query->where('state', $request->input('state'));
        }

        if ($request->filled('zip')) {
            $query->where('zip', $request->input('zip'));
        }

        // Geolocation search: Filter by latitude/longitude within a radius
        if ($request->filled('latitude') && $request->filled('longitude') && $request->filled('radius')) {
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $radius = $request->input('radius');

            // Calculate the boundaries for the search radius
            $haversine = "(6371 * acos(cos(radians($latitude)) 
                       * cos(radians(latitude)) 
                       * cos(radians(longitude) - radians($longitude)) 
                       + sin(radians($latitude)) 
                       * sin(radians(latitude))))";

            $query->whereRaw("$haversine < ?", [$radius]);
        }

        $properties = $query->get();

        return $this->successResponse(
            data: $properties,
            message: 'Properties retrieved successfully'
        );
    }

}
