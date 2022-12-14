<?php

namespace Tests\Feature\Http\Controller\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Post;
use App\Models\User;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store()
    {
        //$this->withoutExceptionHandling();

        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->json('POST','/api/post', [
            'title' => 'primer post'
        ]);

        $response->assertJsonStructure(['id','title','created_at'])
        ->assertJson(['title'=>'primer post'])
        ->assertStatus(201);

        $this->assertDatabaseHas('posts',['title'=>'primer post']);

    }

    public function test_validate_title()
    {
        //$this->withoutExceptionHandling();
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->json('POST','/api/post', [
            'title' => ''
        ]);

        $response->assertStatus(422)
        ->assertJsonValidationErrors('title');
    }

    public function test_show()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user, 'api')->json('GET',"/api/post/$post->id");
        $response->assertJsonStructure(['id','title','created_at'])
        ->assertJson(['title'=>$post->title])
        ->assertStatus(200);

    }

    public function test_404_show()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->json('GET',"/api/post/1000");
        $response->assertStatus(404);

    }

    public function test_update()
    {
        //$this->withoutExceptionHandling();
        $user = User::factory()->create();
        $post = Post::factory()->create();

        
        $response = $this->actingAs($user, 'api')->json('PUT',"/api/post/$post->id", [
            'title' => 'nuevo'
        ]);

        $response->assertJsonStructure(['id','title','created_at'])
        ->assertJson(['title'=>'nuevo'])
        ->assertStatus(200);

        $this->assertDatabaseHas('posts',['title'=>'nuevo']);

    }

    public function test_delete()
    
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "/api/post/$post->id");

        $response->assertSee(null)->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id'=> $post->id]);
    }

    public function test_index()
    {
        Post::factory(5)->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/post');

        $response->assertJsonStructure([
            'data'=>[
                '*' => ['id', 'title', 'created_at','updated_at']
            ]
        ])->assertStatus(200);
    }

    public function test_guest()
    {
         //$this->withoutExceptionHandling();

        $this->json('GET', '/api/post')->assertStatus(401);    
        $this->json('POST', '/api/post')->assertStatus(401);    
        // $this->json('GET', '/api/posts')->assertStatus(401);    
        $this->json('PUT', '/api/post/1000')->assertStatus(401);    
        $this->json('DELETE', '/api/post/1000')->assertStatus(401);    
    }

}
