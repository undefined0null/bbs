<?php

namespace App\Models\Traits;

use Hashids;

trait HashidHelper
{
    private $hashId;

    // 调用 $model->hash_id 时触发
    public function getHashIdAttribute()
    {
        if (!$this->hashId) {
            $this->hashId = Hashids::encode(parent::getRouteKey());
        }

        return $this->hashId;
    }

    // 扩展模型路由绑定，模型ID替换成hashId
    public function resolveRouteBinding($value)
    {
        if (!is_numeric($value)) {
            $value = current(Hashids::decode($value));
            if (!$value) {
                return;
            }
        }

        return parent::resolveRouteBinding($value);
    }

    // 扩展路由生成器，模型ID替换成hashId
    public function getRouteKey()
    {
        return $this->hash_id;
    }
}