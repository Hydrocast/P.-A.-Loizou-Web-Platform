<?php

namespace App\Http\Controllers\Staff;

use App\Enums\AccountStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddOrderNoteRequest;
use App\Http\Requests\FilterOrdersRequest;
use App\Http\Requests\ReassignOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Clipart;
use App\Models\CustomerOrder;
use App\Models\Staff;
use App\Services\OrderProcessingService;
use App\Support\DesignDocument;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles staff order management operations.
 */
class OrderManagementController extends Controller
{
    public function __construct(
        private OrderProcessingService $orderProcessingService,
    ) {}

    /**
     * Render the order list inside the dashboard shell.
     */
    public function index(FilterOrdersRequest $request): Response
    {
        $staff = $request->user('staff');
        Gate::forUser($staff)->authorize('viewAny', CustomerOrder::class);

        $validated = $request->validated();

        $orderNumber = ! empty($validated['order_number'])
            ? (int) $validated['order_number']
            : null;

        $status = ! empty($validated['order_status'])
            ? OrderStatus::from($validated['order_status'])
            : null;

        $startDate = ! empty($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : null;

        $endDate = ! empty($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : null;

        $sortOrder = $validated['sort_order'] ?? 'desc';

        $orders = $this->orderProcessingService->filterOrders(
            $orderNumber,
            $status,
            $startDate,
            $endDate,
            $sortOrder,
        );

        return Inertia::render('Staff/StaffDashboard', [
            'tab' => 'orders',
            'orders' => $orders,
            'filters' => [
                'order_number' => $validated['order_number'] ?? null,
                'order_status' => $validated['order_status'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'sort_order' => $sortOrder,
            ],
            'selectedOrder' => null,
            'activeStaff' => [],
        ]);
    }

    /**
     * Render the order detail modal inside the dashboard shell.
     */
    public function show(FilterOrdersRequest $request, CustomerOrder $order): Response
    {
        $staff = $request->user('staff');
        Gate::forUser($staff)->authorize('view', $order);

        $validated = $request->validated();

        $orderNumber = ! empty($validated['order_number'])
            ? (int) $validated['order_number']
            : null;

        $status = ! empty($validated['order_status'])
            ? OrderStatus::from($validated['order_status'])
            : null;

        $startDate = ! empty($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : null;

        $endDate = ! empty($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : null;

        $sortOrder = $validated['sort_order'] ?? 'desc';

        $orders = $this->orderProcessingService->filterOrders(
            $orderNumber,
            $status,
            $startDate,
            $endDate,
            $sortOrder,
        );

        $selectedOrder = $this->orderProcessingService->viewOrderDetails($order->order_id);

        $activeStaff = Staff::where('account_status', AccountStatus::Active)
            ->orderByRaw('COALESCE(full_name, username)')
            ->get([
                'staff_id',
                'username',
                'full_name',
                'role',
                'account_status',
            ]);

        $clipartNamesByImageReference = Clipart::query()
            ->whereNotNull('image_reference')
            ->whereNotNull('clipart_name')
            ->get(['image_reference', 'clipart_name'])
            ->reduce(function (array $carry, Clipart $clipart): array {
                $imageReference = trim((string) $clipart->image_reference);
                $clipartName = trim((string) $clipart->clipart_name);

                if ($imageReference !== '' && $clipartName !== '') {
                    $carry[$imageReference] = $clipartName;
                }

                return $carry;
            }, []);

        $selectedOrderPayload = [
            'order_id' => $selectedOrder->order_id,
            'customer_name' => $selectedOrder->customer_name,
            'customer_email' => $selectedOrder->customer_email,
            'customer_phone' => $selectedOrder->customer_phone,
            'order_creation_timestamp' => $selectedOrder->order_creation_timestamp,
            'order_status' => $selectedOrder->order_status,
            'total_amount' => $selectedOrder->total_amount,
            'assigned_staff_id' => $selectedOrder->assigned_staff_id,
            'pickup_notification_sent_at' => $selectedOrder->pickup_notification_sent_at,
            'pickup_notification_sent_by_staff_id' => $selectedOrder->pickup_notification_sent_by_staff_id,
            'items' => $selectedOrder->items->map(function ($item) use ($clipartNamesByImageReference) {
                return [
                    'order_item_id' => $item->order_item_id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_subtotal' => $item->line_subtotal,
                    'design_snapshot' => $item->design_snapshot,
                    'preview_image_reference' => $item->preview_image_reference,
                    'print_file_reference' => $item->print_file_reference,
                    'shirt_color_label' => DesignDocument::extractShirtColorLabel($item->design_snapshot),
                    'size_label' => DesignDocument::extractSizeLabel($item->design_snapshot),
                    'print_sides_label' => DesignDocument::extractPrintSidesLabel($item->design_snapshot),
                    'clipart_used' => $this->resolveClipartNames(
                        $item->design_snapshot,
                        $clipartNamesByImageReference,
                    ),
                ];
            })->values(),
            'notes' => $selectedOrder->notes,
        ];

        return Inertia::render('Staff/StaffDashboard', [
            'tab' => 'orders',
            'orders' => $orders,
            'filters' => [
                'order_number' => $validated['order_number'] ?? null,
                'order_status' => $validated['order_status'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'sort_order' => $sortOrder,
            ],
            'selectedOrder' => $selectedOrderPayload,
            'activeStaff' => $activeStaff,
        ]);
    }

    /**
     * @param  array<string, string>  $clipartNamesByImageReference
     * @return array<int, string>
     */
    private function resolveClipartNames(?string $designSnapshot, array $clipartNamesByImageReference): array
    {
        $imageSources = DesignDocument::extractImageSrcs($designSnapshot);

        if ($imageSources === []) {
            return [];
        }

        $clipartNames = [];

        foreach ($imageSources as $imageSource) {
            if (! array_key_exists($imageSource, $clipartNamesByImageReference)) {
                continue;
            }

            $clipartNames[] = $clipartNamesByImageReference[$imageSource];
        }

        return array_values(array_unique($clipartNames));
    }

    /**
     * Update an order status and return to the same detail view.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, CustomerOrder $order): RedirectResponse
    {
        $staff = $request->user('staff');
        Gate::forUser($staff)->authorize('updateStatus', $order);

        $validated = $request->validated();

        $newStatus = OrderStatus::from($validated['order_status']);
        $sendEmail = (bool) ($validated['send_email'] ?? false);

        $this->orderProcessingService->updateOrderStatus(
            $order->order_id,
            $newStatus,
            $sendEmail,
            $staff->staff_id,
        );

        if ($newStatus === OrderStatus::ReadyForPickup && $sendEmail) {
            return redirect()->back()->with('success', 'Order marked as Ready for Pickup and customer email queued.');
        }

        if ($newStatus === OrderStatus::ReadyForPickup) {
            return redirect()->back()->with('success', 'Order marked as Ready for Pickup without sending a customer email.');
        }

        return redirect()->back()->with('success', 'Order status updated.');
    }

    /**
     * Manually send or resend the Ready for Pickup email.
     */
    public function sendPickupEmail(Request $request, CustomerOrder $order): RedirectResponse
    {
        $staff = $request->user('staff');
        Gate::forUser($staff)->authorize('updateStatus', $order);

        $this->orderProcessingService->sendPickupReadyEmail(
            $order->order_id,
            $staff->staff_id,
        );

        return redirect()->back()->with('success', 'Pickup email queued for the customer.');
    }

    /**
     * Add an internal note to the order and return to the same detail view.
     */
    public function storeNote(AddOrderNoteRequest $request, CustomerOrder $order): RedirectResponse
    {
        $staff = $request->user('staff');
        Gate::forUser($staff)->authorize('addNote', $order);

        $this->orderProcessingService->addOrderNote(
            $order->order_id,
            $staff->staff_id,
            $request->validated()['note_text'],
        );

        return redirect()->back()->with('success', 'Note added.');
    }

    /**
     * Reassign the order to another active staff member.
     */
    public function reassign(ReassignOrderRequest $request, CustomerOrder $order): RedirectResponse
    {
        $staff = $request->user('staff');
        Gate::forUser($staff)->authorize('reassign', $order);

        $this->orderProcessingService->reassignOrder(
            $order->order_id,
            (int) $request->validated()['staff_id'],
        );

        return redirect()->back()->with('success', 'Order reassigned.');
    }
}
