<?php

declare(strict_types=1);

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Data\LoginData;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

/**
 * Verifies credentials and issues a Sanctum personal access token for API/mobile
 * use. Abilities are derived from the user's permissions (within their tenant) so
 * a leaked token is bounded by the user's role.
 */
class IssueApiToken
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly PermissionRegistrar $registrar,
    ) {}

    /**
     * @return array{user: User, token: string}
     */
    public function handle(LoginData $data): array
    {
        $user = User::where('email', $data->email)->first();

        if ($user === null || ! Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->tenant !== null) {
            $this->context->set($user->tenant);
            $this->registrar->setPermissionsTeamId($user->tenant_id);
        }

        $abilities = $user->getAllPermissions()->pluck('name')->all();
        $abilities = $abilities === [] ? ['*'] : $abilities;

        $token = $user->createToken($data->deviceName, $abilities)->plainTextToken;

        return [
            'user' => $user->fresh(['tenant', 'roles']),
            'token' => $token,
        ];
    }
}
