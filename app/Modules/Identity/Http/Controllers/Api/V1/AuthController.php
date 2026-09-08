<?php

declare(strict_types=1);

namespace App\Modules\Identity\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Actions\IssueApiToken;
use App\Modules\Identity\Actions\RegisterTenant;
use App\Modules\Identity\Http\Requests\LoginRequest;
use App\Modules\Identity\Http\Requests\RegisterTenantRequest;
use App\Modules\Identity\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Register a new tenant and its owner user, returning an API token.
     */
    public function register(RegisterTenantRequest $request, RegisterTenant $action): JsonResponse
    {
        $user = $action->handle($request->toData());
        $token = $user->createToken('api')->plainTextToken;

        return UserResource::make($user)
            ->additional(['token' => $token])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Exchange credentials for a Sanctum API token.
     */
    public function login(LoginRequest $request, IssueApiToken $action): JsonResponse
    {
        ['user' => $user, 'token' => $token] = $action->handle($request->toData());

        return UserResource::make($user)
            ->additional(['token' => $token])
            ->response();
    }

    /**
     * The authenticated user.
     */
    public function me(Request $request): UserResource
    {
        $user = $request->user();
        $user->loadMissing(['tenant', 'roles']);

        return UserResource::make($user);
    }

    /**
     * Revoke the token used for the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
