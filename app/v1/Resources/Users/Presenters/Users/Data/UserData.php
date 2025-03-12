<?php

namespace App\v1\Resources\Users\Presenters\Users\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\Optional as TypeScriptOptional;

/** @typescript */
class UserData extends Data
{
    public function __construct(
        public string $id,
		public string $name,
		public string $email,
		#[TypeScriptOptional]
		public ?string $email_verified_at,
		#[TypeScriptOptional]
		public ?string $remember_token,
		#[TypeScriptOptional]
		public ?Carbon $created_at,
		#[TypeScriptOptional]
		public ?Carbon $updated_at,
		#[TypeScriptOptional]
		public ?string $deleted_at,
    ) {
    }
}
