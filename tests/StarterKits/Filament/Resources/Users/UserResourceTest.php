<?php

use App\Api\StarterKits\Filament\Resources\Users\Presenters\Users\Data\UserData as DataResponse;
use App\Models\User as User;

use function Pest\Laravel\getJson;

beforeEach(function (): void {
    $this->users = User::factory(10)->create();
});

describe('User', function (): void {
    test('can show a user', function (): void {

        $user = $this->users->random();

        $response = getJson(
            uri: route('api.v1.filament.users.show', $user),
            headers: [
                'x-rest-presenter-api-key' => config('rest-presenter.auth.key'),
                'x-rest-presenter' => 'User'
            ],
        )->assertOk()->json();

        expect($response)
            ->toMatchArray(
                array: DataResponse::from($user)->toArray(),
                message: 'Response data is in the expected format',
            );
    });

    test('can list all users', function (): void {
        $response = getJson(
            uri: route('api.v1.filament.users.index'),
            headers: [
                'x-rest-presenter-api-key' => config('rest-presenter.auth.key'),
                'x-rest-presenter' => 'User'
            ],
        )->assertOk()->json();

        expect($response)
            ->toMatchArray(
                array: DataResponse::collect($this->users)->toArray(),
                message: 'Response data is in the expected format',
            )
            ->toHaveCount($this->users->count());
    });
});
