<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;

class TopicAddHash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabbs:topic-add-hash {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '话题数据添加至hash';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $topicId = $this->argument('id') ?: $this->ask('请输入话题 ID');

        $topic = Topic::find($topicId);

        if ( ! $topic) {
            return $this->error('话题不存在');
        }

        if ($topic->hashInsert()) {
            return $this->info('添加成功');
        }

        return $this->error('话题已经添加过');
    }
}
