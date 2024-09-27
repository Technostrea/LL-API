<?php

namespace App\Http\Controllers\api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    #[
        OA\Post(
            path: '/api/v1/auth/login',
            operationId: 'login',
            description: 'Authenticate a user.',
            requestBody: new OA\RequestBody(
                request: 'LoginRequest',
                description: 'User credentials.',
                required: true,
                content: [
                    new OA\JsonContent(
                        schema: 'LoginRequest',
                        title: 'LoginRequest',
                        properties: [
                            new OA\Property(property: 'email', description: 'User email.', type: 'string'),
                            new OA\Property(property: 'password', description: 'User password.', type: 'string'),
                        ],
                    ),
                ],
            ),
            tags: ['Auth'],
            responses: [
                new OA\Response(response: '204', description: 'No content.'),
                new OA\Response(response: '422', description: 'Validation error.'),
            ],
        ),
    ]
    public function store(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->errorResponse(
                message: 'Invalid credentials.',
                status: ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = Auth::user();
        $tokenName = 'token-' . $user->id . '-' . now()->timestamp;
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User authenticated successfully.',
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * Destroy an authenticated session.
     */
    #[
        OA\Post(
            path: '/api/v1/auth/logout',
            operationId: 'destroy',
            description: 'Destroy an authenticated session.',
            tags: ['Auth'],
            responses: [
                new OA\Response(response: '204', description: 'No content.'),
            ],
        ),
    ]
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully.',
        ], ResponseAlias::HTTP_NO_CONTENT);
    }
}
