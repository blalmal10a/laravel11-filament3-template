<?php

namespace App\v1\Resources\Users\Presenters\Users;

use App\Models\User as UserModel;
use App\v1\Resources\Users\Presenters\Users\Data\UserData;
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
