<?php

namespace App\Api\StarterKits\Sanctum\Resources\Auth;

use XtendPackages\RESTPresenter\StarterKits\Sanctum\Actions;
use XtendPackages\RESTPresenter\StarterKits\Sanctum\Resources\Auth\AuthResourceController as XtendAuthResourceController;

class AuthResourceController extends XtendAuthResourceController
{
    public static bool $onlyRegisterActionRoutes = true;

    /**
     * @return array<string, string>
     */
    public function routeActions(): array
    {
        return [
            'register' => Actions\Register::class,
            'login' => Actions\Login::class,
            'logout' => Actions\Logout::class,
            'reset-password' => Actions\ResetPassword::class,
        ];
    }
}
