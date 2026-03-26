<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitContactFormRequest;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles public contact page display and form submission.
 *
 * This controller manages the contact form functionality including
 * rendering the contact page and processing form submissions.
 * All methods are publicly accessible without authentication.
 */
class ContactController extends Controller
{
    public function __construct(private ContactService $contactService) {}

    // Render contact page
    public function show(): Response
    {
        return Inertia::render('Public/Contact');
    }

    // Submit contact form and redirect back with confirmation
    public function submit(SubmitContactFormRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->contactService->submitForm(
            $data['fullName'],
            $data['email'],
            $data['subject'],
            $data['message'],
        );

        return redirect()->route('contact')
            ->with('success', 'Your message has been sent. We will get back to you shortly.');
    }
}