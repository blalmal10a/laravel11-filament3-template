<?php

namespace App\Api\StarterKits\Filament\Resources\Shield\Roles\Presenters\Roles;

use App\Api\StarterKits\Filament\Resources\Shield\Roles\Presenters\Roles\Data\RoleData;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use Spatie\Permission\Models\Role as RoleModel;
use XtendPackages\RESTPresenter\Concerns\InteractsWithPresenter;
use XtendPackages\RESTPresenter\Contracts\Presentable;

class RolePresenter implements Presentable
{
    use InteractsWithPresenter;

    public function __construct(
        protected Request $request,
        protected ?RoleModel $model,
    ) {}

    public function transform(): RoleData | Data
    {
        return RoleData::from($this->model);
    }
}
