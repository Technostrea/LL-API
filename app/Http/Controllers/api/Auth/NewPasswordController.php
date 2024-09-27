<?php

namespace App\Http\Controllers\api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    #[
        OA\Post(
            path: '/api/v1/auth/reset-password',
            operationId: 'resetPassword',
            description: 'Reset a user password.',
            requestBody: new OA\RequestBody(
                request: 'NewPasswordRequest',
                description: 'User password.',
                required: true,
                content: [
                    new OA\JsonContent(
                        schema: 'NewPasswordRequest',
                        title: 'NewPasswordRequest',
                        properties: [
                            new OA\Property(property: 'token', description: 'Password reset token.', type: 'string'),
                            new OA\Property(property: 'email', description: 'User email.', type: 'string'),
                            new OA\Property(property: 'password', description: 'User password.', type: 'string'),
                            new OA\Property(property: 'password_confirmation', description: 'User password confirmation.', type: 'string'),
                        ],
                    ),
                ],
            ),
            tags: ['Auth'],
            responses: [
                new OA\Response(response: '200', description: 'Password reset.'),
                new OA\Response(response: '422', description: 'Validation error.'),
            ],
        ),
    ]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => __($status)]);
    }
}
