<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TicketCategoryController extends Controller
{
    public function index(Event $event)
    {
        $categories = $event->ticketCategories()->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.ticket-categories.index', compact('event', 'categories'));
    }

    public function create(Event $event)
    {
        return view('admin.ticket-categories.form', ['event'=>$event, 'category'=>new TicketCategory()]);
    }

    public function store(Request $request, Event $event)
    {
        $values = $this->validated($request);
        $values['slug'] = $this->uniqueSlug($event, $values['name']);
        $event->ticketCategories()->create($values);
        return redirect()->route('admin.events.categories.index', $event)->with('success', 'Ticket category created.');
    }

    public function edit(Event $event, TicketCategory $category)
    {
        $this->assertBelongsTo($event, $category);
        return view('admin.ticket-categories.form', compact('event', 'category'));
    }

    public function update(Request $request, Event $event, TicketCategory $category)
    {
        $this->assertBelongsTo($event, $category);
        $values = $this->validated($request);
        // Keep the existing slug stable so future ticket links/reports never break.
        // Guard capacity against already issued tickets when issuance is implemented.
        if (method_exists($category, 'tickets') && $category->tickets()->count() > $values['capacity']) {
            throw ValidationException::withMessages(['capacity'=>'Capacity cannot be lower than the number of issued tickets.']);
        }
        $category->update($values);
        return redirect()->route('admin.events.categories.index', $event)->with('success', 'Ticket category updated.');
    }

    private function assertBelongsTo(Event $event, TicketCategory $category): void
    {
        abort_unless($category->event_id === $event->id, 404);
    }

    private function validated(Request $request): array
    {
        $v = $request->validate([
            'name'=>['required','string','max:120'],
            'description'=>['nullable','string','max:2000'],
            'type'=>['required',Rule::in(['paid','complimentary'])],
            'price_naira'=>['nullable','required_if:type,paid','numeric','min:0.01','max:999999999','decimal:0,2'],
            'capacity'=>['required','integer','min:1','max:10000000'],
            'sales_start_at'=>['nullable','date'],
            'sales_end_at'=>['nullable','date','after:sales_start_at'],
            'sort_order'=>['nullable','integer','min:0','max:65535'],
            'is_active'=>['sometimes','boolean'],
        ]);
        if ($v['type'] === 'paid') {
            // Input is validated to two decimal places. Convert to integer kobo.
            $amount = (string)$v['price_naira'];
            [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
            $v['price_kobo'] = ((int)$whole * 100) + (int)str_pad($fraction, 2, '0');
        } else {
            $v['price_kobo'] = 0;
        }
        unset($v['price_naira']);
        $v['is_active'] = $request->boolean('is_active');
        $v['sort_order'] = $v['sort_order'] ?? 0;
        return $v;
    }

    private function uniqueSlug(Event $event, string $name): string
    {
        $base = substr(Str::slug($name) ?: 'ticket', 0, 135);
        $slug = $base;
        for ($i=2; $event->ticketCategories()->where('slug', $slug)->exists(); $i++) {
            $tail = '-'.$i;
            $slug = substr($base,0,150-strlen($tail)).$tail;
        }
        return $slug;
    }
}
