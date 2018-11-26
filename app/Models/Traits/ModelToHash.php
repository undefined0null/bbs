<?php

namespace App\Models\Traits;

use Redis;

trait ModelToHash
{
    public function getTablenameAttribute()
    {
        $name = '';

        if (is_null($this->table)) {
            $name = class_basename(get_class($this));
            $name = str_plural(camel_case($name));
        } else {
            $name = camel_case($this->table);
        }

        return $name;
    }

    public function getHashKeyPrefixAttribute()
    {
        return $this->tablename . config('database.redis.options.separator');
    }

    public function getHashKeyAttribute()
    {
        return $this->hash_key_prefix . $this->{$this->primaryKey};
    }

    public function getHashFieldExceptAttribute()
    {
        return [];
    }

    public function getHashFieldAllowAttribute()
    {
        return array_diff(array_keys($this->attributes), $this->hash_field_except);
    }

    public function hashExists()
    {
        return Redis::exists($this->hash_key);
    }

    public function hashValidate($field)
    {
        return in_array($field, $this->hash_field_allow) && $this->hashExists();
    }

    public function hashValidateMany($data)
    {
        if (empty($data) || ! is_array($data)) {
            return false;
        }

        if (array_diff($data, $this->hash_field_allow)) {
            return false;
        }

        if ( ! $this->hashExists()) {
            return false;
        }

        return true;
    }

    public function hashInsert()
    {
        if ( ! $this->hashExists()) {
            $data = collect($this->attributes)
                        ->except($this->hash_field_except)
                        ->toArray();

            return Redis::hMset($this->hash_key, $data);
        }

        return null;
    }

    public function hashUpdate($field, $value)
    {
        return $this->hashValidate($field) ? Redis::hSet($this->hash_key, $field, $value) : null;
    }

    public function hashUpdateMany($data)
    {
        return $this->hashValidateMany($data) ? Redis::hMset($this->hash_key, $data) : null;
    }

    public function hashDelete()
    {
        return Redis::del($this->hash_key);
    }

    public function hashIncrement($field, $value)
    {
        return $this->hashValidate($field) && $value > 0 ? Redis::hInCrBy($this->hash_key, $field, $value) : null;
    }

    public function hashDecrement($field, $value)
    {
        return $this->hashValidate($field) && $value < 0 ? Redis::hInCrBy($this->hash_key, $field, $value) : null;
    }
}