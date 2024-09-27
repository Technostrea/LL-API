<?php

namespace App\Http\Controllers\api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    #[
    OA\Post(
        path: '/api/v1/auth/register',
        operationId: 'register',
        description: 'Register a new user.',
        requestBody: new OA\RequestBody(
            request: 'RegisterUserRequest',
            description: 'User data.',
            required: true,
            content: [
                new OA\JsonContent(
                    schema: 'RegisterUserRequest',
                    title: 'RegisterUserRequest',
                    properties: [
                        new OA\Property(property: 'name', description: 'User name.', type: 'string'),
                        new OA\Property(property: 'email', description: 'User email.', type: 'string'),
                        new OA\Property(property: 'password', description: 'User password.', type: 'string'),
                        new OA\Property(property: 'password_confirmation', description: 'User password confirmation.', type: 'string'),
                    ],
                ),
            ],
        ),
        tags: ['Auth'],
        parameters: [
            new OA\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                schema: new OA\Schema(type: 'string', default: 'application/json')
            ),
        ],
        responses: [
            new OA\Response(response: '204', description: 'No content.'),
            new OA\Response(response: '422', description: 'Validation error.'),
        ],
    ),
]
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        try {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        $tokenName = 'token-' . $user->id . '-' . now()->timestamp;
        $jwt = $user->createToken($tokenName)->plainTextToken;
        $user->createToken($tokenName)->plainTextToken;

        return $this->successResponse(
            data: [
                'user' => $user,
                'token' => $jwt,
            ],
            message: 'User registered successfully.',
            status: ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
