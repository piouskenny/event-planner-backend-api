<?php

namespace App\Http\Controllers;

use App\Models\Events;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Auth;

class EventsController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(Request $request)
    {
        $user_id = Auth::id();
        $filter = $request->query('status');

        $eventsQuery = Events::where('user_id', $user_id);

        if ($filter === 'completed') {
            $eventsQuery->where('end_date', '<', now());
        } elseif ($filter === 'upcoming') {
            $eventsQuery->where('end_date', '>=', now());
        }

        $events = $eventsQuery->get();

        if ($events->isEmpty()) {
            return response()->json([
                'message' => 'No events found matching the criteria.',
                'status' => 1,
            ]);
        }

        $filteredEvents = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'start_date' => $event->start_date,
                'type' => $event->type,
                'attendance_capacity' => $event->attendance_capacity,
                'status' => now()->greaterThan($event->end_date) ? 'completed' : 'upcoming',
            ];
        });

        return response()->json([
            'message' => 'Events retrieved successfully.',
            'status' => 1,
            'data' => $filteredEvents,
        ]);
    }

    /**
     * Store a newly created events in storage.
     */
    public function store(Request $request)
    {
        $user_id = Auth::id();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required',
            'tags' => 'array',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s',
            'location_link' => 'required',
            'attendance_capacity' => 'integer',
            'ticket_pricing' => 'string',
            'ticket_price' => 'integer',
            'draft' => 'bool'
        ]);

        $slug = str_replace(' ', '-', strtolower($validated['name']));

        $event = Events::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'],
            'tags' => json_encode($validated['tags']) ?? [],
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'location_link' => $validated['location_link'],
            'attendance_capacity' => $validated['attendance_capacity'],
            'ticket_pricing' => $validated['ticket_pricing'],
            'ticket_price' => $validated['ticket_price'],
            'draft' => $validated['draft'],
            'event_url' => Env('APP_URL') . "/api/v1/event/$slug",
            'user_id' => $user_id,
        ]);

        return response()->json([
            'message' => 'Your event has been published and is now live!',
            'status' => 1,
            'data' => $event->event_url,
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show($id)
    {
        $user_id = Auth::id();

        $event = Events::where('id', $id)->where('user_id', $user_id)->first();

        if (!$event) {
            return response()->json([
                'message' => 'Event not found or unauthorized access.',
                'status' => 0,
            ], 404);
        }

        // Decode tags
        $event->tags = json_decode($event->tags);

        // Dynamically set the status based on the current date
        $event->status = now()->greaterThan($event->end_date) ? 'completed' : 'upcoming';

        return response()->json([
            'message' => 'Event retrieved successfully.',
            'status' => 1,
            'data' => $event,
        ]);
    }

    public function showByUrl($slug)
    {
        // Find the event by the slug
        $event = Events::where('event_url', 'like', '%' . $slug)->first();

        if (!$event) {
            return response()->json([
                'message' => 'Event not found.',
                'status' => 0,
            ], 404);
        }

        // Decode tags for better readability
        $event->tags = json_decode($event->tags);

        return response()->json([
            'message' => 'Event retrieved successfully.',
            'status' => 1,
            'data' => $event,
        ]);
    }


    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, $id)
    {
        $user_id = Auth::id();

        $event = Events::where('id', $id)->where('user_id', $user_id)->first();

        if (!$event) {
            return response()->json([
                'message' => 'Event not found or unauthorized access.',
                'status' => 0,
            ], 404);
        }

        // Validate the request
        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => 'string|max:255',
            'description' => 'string',
            'tags' => 'array',
            'start_date' => 'date_format:Y-m-d H:i:s',
            'end_date' => 'date_format:Y-m-d H:i:s',
            'location_link' => 'string',
            'attendance_capacity' => 'integer',
            'ticket_pricing' => 'string',
            'ticket_price' => 'integer',
            'status' => 'string',
        ]);

        $event->update(array_merge($validated, [
            'tags' => isset($validated['tags']) ? json_encode($validated['tags']) : $event->tags,
        ]));

        return response()->json([
            'message' => 'Event updated successfully.',
            'status' => 1,
            'data' => $event,
        ]);
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy($id)
    {
        $user_id = Auth::id();

        $event = Events::where('id', $id)->where('user_id', $user_id)->first();

        if (!$event) {
            return response()->json([
                'message' => 'Event not found or unauthorized access.',
                'status' => 0,
            ], 404);
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully.',
            'status' => 1,
        ]);
    }

    /**
     * Get the list of available event types.
     */
    public function getEventTypes()
    {
        $eventTypes = [
            'Conference',
            'Workshop',
            'Webinar',
            'Networking',
            'Fundraiser',
            'Product Launch',
            'Party or Celebration',
            'Meetup',
            'Hackathon',
            'Ceremony',
            'Sports Event',
            'Training Session',
            'Panel Discussion',
            'Festival'
        ];

        return response()->json([
            'message' => 'Event types retrieved successfully.',
            'status' => 1,
            'data' => $eventTypes,
        ]);
    }

    /**
     * Get the list of available event tags.
     */
    public function getEventTags()
    {
        $eventTags = [
            'Networking',
            'Marketing',
            'Tech Event',
            'Business',
            'Public Speaking',
            'Entrepreneurship',
            'Finance & Investment',
            'Motivational',
            'Others'
        ];

        return response()->json([
            'message' => 'Event tags retrieved successfully.',
            'status' => 1,
            'data' => $eventTags,
        ]);
    }
}
