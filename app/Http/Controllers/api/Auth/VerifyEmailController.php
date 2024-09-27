<?php

namespace App\Http\Controllers\api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use OpenApi\Attributes as OA;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    #[
        OA\Get(
            path: '/api/v1/auth/verify-email/{id}/{hash}',
            operationId: 'verifyEmail',
            description: 'Verify a user email.',
            tags: ['Auth'],
            parameters: [
                new OA\Parameter(
                    name: 'id',
                    description: 'User ID.',
                    in: 'path',
                    required: true,
                ),
                new OA\Parameter(
                    name: 'hash',
                    description: 'User hash.',
                    in: 'path',
                    required: true,
                ),
            ],
            responses: [
                new OA\Response(response: '302', description: 'Redirect.'),
            ],
        ),
    ]
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(
                config('app.frontend_url') . '/dashboard?verified=1'
            );
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(
            config('app.frontend_url') . '/dashboard?verified=1'
        );
    }
}
