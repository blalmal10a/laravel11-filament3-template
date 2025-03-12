<?php

namespace App\Api\StarterKits\Filament\Resources\Users\Presenters\Users;

use App\Api\StarterKits\Filament\Resources\Users\Presenters\Users\Data\UserData;
use App\Models\User as UserModel;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;
use XtendPackages\RESTPresenter\Concerns\InteractsWithPresenter;
use XtendPackages\RESTPresenter\Contracts\Presentable;

class UserPresenter implements Presentable
{
    use InteractsWithPresenter;

    public function __construct(
        protected Request $request,
        protected ?UserModel $model,
    ) {}

    public function transform(): UserData | Data
    {
        return UserData::from($this->model);
    }
}
