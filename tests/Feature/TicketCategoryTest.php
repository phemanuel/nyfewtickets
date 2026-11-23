<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function event(): Event
    {
        return Event::create(['title'=>'Grand Finale','slug'=>'grand-finale','starts_at'=>now()->addWeek(), 'status'=>'draft']);
    }

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        return $user;
    }

    public function test_guest_cannot_access_admin_categories(): void
    {
        $event = $this->event();
        $this->get(route('admin.events.categories.index',$event))->assertRedirect();
    }

    public function test_admin_can_create_paid_category_in_kobo(): void
    {
        $event = $this->event();
        $this->actingAs($this->admin())->post(route('admin.events.categories.store',$event), [
            'name'=>'VIP','type'=>'paid','price_naira'=>'15000.50','capacity'=>150,'is_active'=>'1'
        ])->assertRedirect(route('admin.events.categories.index',$event));
        $this->assertDatabaseHas('ticket_categories',['event_id'=>$event->id,'slug'=>'vip','price_kobo'=>1500050,'capacity'=>150]);
    }

    public function test_complimentary_category_has_zero_price_and_unique_slug(): void
    {
        $event = $this->event();
        $user = $this->admin();
        foreach (range(1,2) as $i) {
            $this->actingAs($user)->post(route('admin.events.categories.store',$event), [
                'name'=>'Special Guest','type'=>'complimentary','capacity'=>40,'price_naira'=>100000,'is_active'=>'1'
            ])->assertRedirect();
        }
        $this->assertDatabaseHas('ticket_categories',['event_id'=>$event->id,'slug'=>'special-guest','price_kobo'=>0]);
        $this->assertDatabaseHas('ticket_categories',['event_id'=>$event->id,'slug'=>'special-guest-2','price_kobo'=>0]);
    }

    public function test_staff_without_event_create_permission_cannot_create_category(): void
    {
        $event = $this->event();
        $this->seed(RolesAndPermissionsSeeder::class);
        $user=User::factory()->create(); $user->assignRole('gate-officer');
        $this->actingAs($user)->post(route('admin.events.categories.store',$event), [
            'name'=>'VIP','type'=>'paid','price_naira'=>'1000','capacity'=>20
        ])->assertForbidden();
    }

    public function test_category_cannot_be_edited_through_another_event(): void
    {
        $first=$this->event();
        $second=Event::create(['title'=>'Other','slug'=>'other','starts_at'=>now()->addWeek(),'status'=>'draft']);
        $cat=$first->ticketCategories()->create(['name'=>'VIP','slug'=>'vip','type'=>'paid','price_kobo'=>100000,'capacity'=>50]);
        $this->actingAs($this->admin())->get(route('admin.events.categories.edit',[$second,$cat]))->assertNotFound();
    }
}
