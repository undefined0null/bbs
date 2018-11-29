<?php

namespace App\Transformers;

use Spatie\Permission\Models\Permission;
use League\Fractal\TransformerAbstract;

class PermissionsTransformer extends TransformerAbstract
{
    public function transform(Permission $permission)
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
        ];
    }
}