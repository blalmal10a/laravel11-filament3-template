<?php

namespace App\RestApi\Resources\Images;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use XtendPackages\RESTPresenter\Resources\ResourceController;

class ImageResourceController extends ResourceController
{
    protected static string $model = Image::class;

    public static bool $isAuthenticated = false;

    public function index(Request $request): Collection
    {
        $images = $this->getModelQueryInstance()->get();

        return $images->map(
            fn (Image $image) => $this->present($request, $image),
        );
    }

    public function show(Request $request, Image $image): Data
    {
        return $this->present($request, $image);
    }

    public function filters(): array
    {
        return [
            
        ];
    }

    public function presenters(): array
    {
        return [
            
        ];
    }
}
