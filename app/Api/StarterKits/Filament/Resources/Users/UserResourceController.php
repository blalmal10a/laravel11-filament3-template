<?php

namespace App\Api\StarterKits\Filament\Resources\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use XtendPackages\RESTPresenter\Resources\ResourceController;

class UserResourceController extends ResourceController
{
    protected static string $model = User::class;

    public static bool $isAuthenticated = false;

    public function index(Request $request): Collection
    {
        $users = $this->getModelQueryInstance()->get();

        return $users->map(
            fn (User $user) => $this->present($request, $user),
        );
    }

    public function show(Request $request, User $user): Data
    {
        return $this->present($request, $user);
    }

    public function filters(): array
    {
        return [
            
        ];
    }

    public function presenters(): array
    {
        return [
            'user' => Presenters\Users\UserPresenter::class,
        ];
    }
}
