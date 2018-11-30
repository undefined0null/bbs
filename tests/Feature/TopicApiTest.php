<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Topic;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActingJWTUser;

class TopicApiTest extends TestCase
{
    use ActingJWTUser;

    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testStoreTopic()
    {
        $data = [
            'category_id' => 1,
            'body' => 'test body<script>alert(1)</script>',
            'title' => '测试标题',
        ];

        $response = $this->JWTActingAs($this->user)->json('POST', '/api/topics', $data);

        $assertData = [
            'category_id' => 1,
            'user_id' => $this->user->id,
            'title' => '测试标题',
            'body' => clean('test body<script>alert(1)</script>', 'user_topic_body'),
        ];

        $response->assertStatus(201)->assertJsonFragment($assertData);
    }

    public function testUpdateTopic()
    {
        $topic = $this->makeTopic();

        $editData = [
            'category_id' => 2,
            'body' => 'edit body<a>a</a>',
            'title' => 'edit title',
        ];

        $response = $this->JWTActingAs($this->user)
            ->json('PATCH', '/api/topics/'.$topic->id, $editData);

        $assertData = [
            'category_id' => 2,
            'user_id' => $this->user->id,
            'title' => 'edit title',
            'body' => clean('edit body<a>a</a>', 'user_topic_body'),
        ];

        $response->assertStatus(200)
            ->assertJsonFragment($assertData);
    }

    public function testShowTopic()
    {
        $topic = $this->makeTopic();

        $response = $this->json('GET', '/api/topics/'.$topic->id);

        $assertData = [
            'category_id' => $topic->category_id,
            'user_id' => $topic->user_id,
            'title' => $topic->title,
            'body' => clean($topic->body, 'user_topic_body'),
        ];

        $response->assertStatus(200)
            ->assertJsonFragment($assertData);
    }

    public function testIndexTopic()
    {
        $response = $this->json('GET', '/api/topics');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function testDestoryTopic()
    {
        $topic = $this->makeTopic();
        $response = $this->JWTActingAs($this->user)
            ->json('DELETE', '/api/topics/'.$topic->id);
        $response->assertStatus(204);

        $response = $this->json('GET', '/api/topics/'.$topic->id);
        $response->assertStatus(404);
    }

    public function makeTopic()
    {
        return factory(Topic::class)->create([
            'user_id' => $this->user->id,
            'category_id' => 1,
        ]);
    }
}
