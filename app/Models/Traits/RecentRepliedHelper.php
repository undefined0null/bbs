<?php

namespace App\Models\Traits;

use Redis;

trait RecentRepliedHelper
{
    private $separator = ':';//config('database.redis.options.separator');
    private $zset_prefix = 'topicRecentReplied';
    private $field_prefix = 'topic';

    public function updateRecentReplied()
    {
        // 是否在zset中
        if ($this->topicKeyExists()) {
            // 更新分值
            $this->addToZset();
            return;
        }

        // zset是否已填满
        if ($this->zsetHasFull()) {
            // 是否大于zset中的最小值
            if ($min = $this->isLargeMin()) {
                // 去掉旧话题，添加新话题
                Redis::zRem($this->getZsetKey(), $this->getZsetMinKey($min));
                $this->addToZset();
            }
        } else {
            $this->addToZset();
        }
    }

    public function scopeRedisRecentReplied($query)
    {
        $data = Redis::zRevrange($this->getZsetKey(), 0, -1, 'withscores');
        $topicIds = explode(',', str_replace(
                            $this->field_prefix . $this->separator,
                            '',
                            implode(',', array_keys($data))
                             ));

        return $query->whereIn('id', $topicIds)->orderBy('last_reply_at', 'desc');
    }

    public function zsetHasFull()
    {
        return Redis::zCard($this->getZsetKey()) >= self::PER_PAGE;
    }

    public function getZsetMin()
    {
        return Redis::zRange($this->getZsetKey(), 0, 0, 'withscores');
    }

    public function getZsetMinScore($min)
    {
        return count($min) ? array_values($min)[0] : 0;
    }

    public function getZsetMinKey($min)
    {
        return count($min) ? array_keys($min)[0] : '';
    }

    private function getTopicKey()
    {
        return $this->field_prefix . $this->separator . $this->id;
    }

    private function getZsetKey()
    {
        return $this->zset_prefix . $this->separator;
    }

    private function topicKeyExists()
    {
        return is_null(Redis::zScore($this->getZsetKey(), $this->getTopicKey())) ? false : true;
    }

    private function isLargeMin()
    {
        $min = $this->getZsetMin();

        return $this->last_reply_at->timestamp > $this->getZsetMinScore($min) ? $min : false;
    }

    private function addToZset()
    {
        Redis::zAdd($this->getZsetKey(), $this->last_reply_at->timestamp, $this->getTopicKey());
    }
}