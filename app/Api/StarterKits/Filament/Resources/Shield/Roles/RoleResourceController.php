<?php

namespace App\Api\StarterKits\Filament\Resources\Shield\Roles;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\Permission\Models\Role;
use XtendPackages\RESTPresenter\Resources\ResourceController;

class RoleResourceController extends ResourceController
{
    protected static string $model = Role::class;

    public static bool $isAuthenticated = false;

    public function index(Request $request): Collection
    {
        $roles = $this->getModelQueryInstance()->get();

        return $roles->map(
            fn (Role $role) => $this->present($request, $role),
        );
    }

    public function show(Request $request, Role $role): Data
    {
        return $this->present($request, $role);
    }

    public function filters(): array
    {
        return [
            
        ];
    }

    public function presenters(): array
    {
        return [
            'role' => Presenters\Roles\RolePresenter::class,
        ];
    }
}
