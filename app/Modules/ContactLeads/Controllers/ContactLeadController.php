<?php

namespace App\Modules\ContactLeads\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContactLeads\Models\ContactLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactLeadController extends Controller
{
    /**
     * Store a new contact form submission (public API endpoint).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
            'source'  => 'nullable|string|max:100',
        ]);

        $validated['ip_address'] = $request->ip();
        $validated['status'] = 'new';

        $lead = ContactLead::create($validated);
 
        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your message has been received. We will get back to you shortly.',
        ], 201);
    }

    /**
     * List all contact leads (authenticated API).
     */
    public function index(Request $request): JsonResponse
    {
        $leads = ContactLead::query()
            ->when($request->input('status'), fn($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return response()->json($leads);
    }

    /**
     * View a single lead (authenticated API).
     */
    public function show(ContactLead $contactLead): JsonResponse
    {
        $contactLead->markAsRead();

        return response()->json($contactLead);
    }
}
