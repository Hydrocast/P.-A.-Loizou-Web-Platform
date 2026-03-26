<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitContactFormRequest;
use App\Services\CarouselService;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles public-facing page rendering for the main website.
 *
 * This controller is responsible for serving the homepage, about page,
 * services page, and contact page. All methods are publicly accessible
 * without authentication.
 */
class HomepageController extends Controller
{
    public function __construct(
        private CarouselService $carouselService,
        private ContactService $contactService,
    ) {}

    // Render home page with active carousel slides
    public function index(): Response
    {
        $slides = $this->carouselService->retrieveActiveSlides()
            ->map(function ($slide) {
                $resolvedImageReference = $this->carouselService->resolveSlideImageReference($slide);

                return [
                    'slide_id' => $slide->slide_id,
                    'title' => $slide->title,
                    'description' => $slide->description,
                    'image_reference' => $resolvedImageReference,
                    'image_url' => $this->resolveImageUrl($resolvedImageReference),
                    'product_id' => $slide->product_id,
                    'product_type' => $slide->product_type?->value,
                ];
            })
            ->values();

        return Inertia::render('Public/Home', [
            'slides' => $slides,
        ]);
    }

    // Render static about page
    public function about(): Response
    {
        return Inertia::render('Public/About');
    }

    // Render static services page
    public function services(): Response
    {
        return Inertia::render('Public/Services');
    }

    // Render contact page
    public function contact(): Response
    {
        return Inertia::render('Public/Contact');
    }

    // Handle public contact form submission
    public function submitContact(SubmitContactFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->contactService->submitForm(
            $validated['fullName'],
            $validated['email'],
            $validated['subject'],
            $validated['message'],
        );

        return back()->with('success', 'Your message has been sent successfully. We will be in touch shortly.');
    }

    /**
     * Builds a public URL for an image_reference value.
     *
     * Stored relative paths are resolved through the public disk. Absolute
     * URLs and root-relative paths are returned unchanged.
     */
    private function resolveImageUrl(?string $imageReference): ?string
    {
        if ($imageReference === null || $imageReference === '') {
            return null;
        }

        if (
            str_starts_with($imageReference, 'http://') ||
            str_starts_with($imageReference, 'https://') ||
            str_starts_with($imageReference, '/')
        ) {
            return $imageReference;
        }

        return Storage::disk('public')->url($imageReference);
    }
}