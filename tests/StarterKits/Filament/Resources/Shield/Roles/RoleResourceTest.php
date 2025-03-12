<?php

use App\Api\StarterKits\Filament\Resources\Shield\Roles\Presenters\Roles\Data\RoleData as DataResponse;
use Spatie\Permission\Models\Role as Role;

use function Pest\Laravel\getJson;

beforeEach(function (): void {
    $this->roles = Role::factory(10)->create();
});

describe('Role', function (): void {
    test('can show a role', function (): void {

        $role = $this->roles->random();

        $response = getJson(
            uri: route('api.v1.filament.roles.show', $role),
            headers: [
                'x-rest-presenter-api-key' => config('rest-presenter.auth.key'),
                'x-rest-presenter' => 'Role'
            ],
        )->assertOk()->json();

        expect($response)
            ->toMatchArray(
                array: DataResponse::from($role)->toArray(),
                message: 'Response data is in the expected format',
            );
    });

    test('can list all roles', function (): void {
        $response = getJson(
            uri: route('api.v1.filament.roles.index'),
            headers: [
                'x-rest-presenter-api-key' => config('rest-presenter.auth.key'),
                'x-rest-presenter' => 'Role'
            ],
        )->assertOk()->json();

        expect($response)
            ->toMatchArray(
                array: DataResponse::collect($this->roles)->toArray(),
                message: 'Response data is in the expected format',
            )
            ->toHaveCount($this->roles->count());
    });
});
