<?php

namespace App\Api\StarterKits\Filament\Resources\Shield\Roles\Presenters\Roles\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\Optional as TypeScriptOptional;

/** @typescript */
class RoleData extends Data
{
    public function __construct(
        public string $id,
		public string $name,
		public string $guard_name,
		#[TypeScriptOptional]
		public ?Carbon $updated_at,
		#[TypeScriptOptional]
		public ?Carbon $created_at,
    ) {
    }
}
