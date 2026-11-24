<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventArtworkTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_and_replace_event_poster(): void
    {
        Storage::fake('public');
        Permission::firstOrCreate(['name'=>'admin.access','guard_name'=>'web']);
        Permission::firstOrCreate(['name'=>'events.create','guard_name'=>'web']);
        $role = Role::firstOrCreate(['name'=>'super-admin','guard_name'=>'web']);
        $role->givePermissionTo(['admin.access','events.create']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $payload = [
            'title'=>'NYFEW Fashion Night', 'venue'=>'Ibadan',
            'starts_at'=>now()->addDays(9)->format('Y-m-d H:i:s'),
            'status'=>'published',
        ];
        $this->actingAs($user)->post(route('admin.events.store'), $payload + [
            'poster_image'=>UploadedFile::fake()->image('poster.jpg',800,1200),
        ])->assertRedirect(route('admin.events.index'));
        $event = Event::where('title','NYFEW Fashion Night')->firstOrFail();
        $this->assertNotNull($event->poster_image);
        Storage::disk('public')->assertExists($event->poster_image);
        $old = $event->poster_image;
        $this->actingAs($user)->patch(route('admin.events.update',$event), $payload + [
            'poster_image'=>UploadedFile::fake()->image('new.png',800,1200),
        ])->assertRedirect(route('admin.events.index'));
        $event->refresh();
        $this->assertNotEquals($old,$event->poster_image);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($event->poster_image);
    }

    public function test_artwork_upload_is_restricted_to_images(): void
    {
        Storage::fake('public');
        Permission::firstOrCreate(['name'=>'admin.access','guard_name'=>'web']);
        Permission::firstOrCreate(['name'=>'events.create','guard_name'=>'web']);
        $role = Role::firstOrCreate(['name'=>'super-admin','guard_name'=>'web']);
        $role->givePermissionTo(['admin.access','events.create']);
        $user = User::factory()->create();
        $this->actingAs($user->assignRole($role))->post(route('admin.events.store'), [
            'title'=>'Test','starts_at'=>now()->addDays(4)->format('Y-m-d H:i:s'),
            'status'=>'published','poster_image'=>UploadedFile::fake()->create('not-image.pdf',50,'application/pdf'),
        ])->assertSessionHasErrors('poster_image');
    }
}
