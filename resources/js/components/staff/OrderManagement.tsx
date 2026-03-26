import { router } from '@inertiajs/react';
import { Eye, User, Clock, Image as ImageIcon, Copy } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import Modal from '@/components/public/Modal';
import { useTimedFlash } from '@/hooks/useTimedFlash';

type OrderStatus =
  | 'Pending'
  | 'Processing'
  | 'Ready for Pickup'
  | 'Completed'
  | 'Cancelled';

type OrderNote = {
  note_id: number;
  staff_id: number;
  note_text: string;
  note_timestamp: string;
  staff?: {
    staff_id: number;
    username: string;
    full_name: string | null;
  } | null;
};

type OrderItem = {
  order_item_id: number;
  product_id: number;
  product_name: string;
  quantity: number | string;
  unit_price: number | string | null;
  line_subtotal: number | string | null;
  design_snapshot: string;
  preview_image_reference: string | null;
  shirt_color_label?: string | null;
  print_sides_label?: string | null;
};

type OrderSummary = {
  order_id: number;
  customer_name: string;
  customer_email: string;
  customer_phone: string;
  order_creation_timestamp: string;
  order_status: OrderStatus;
  total_amount: number | string | null;
};

type SelectedOrder = {
  order_id: number;
  customer_name: string;
  customer_email: string;
  customer_phone: string;
  order_creation_timestamp: string;
  order_status: OrderStatus;
  total_amount: number | string | null;
  assigned_staff_id: number | null;
  pickup_notification_sent_at?: string | null;
  pickup_notification_sent_by_staff_id?: number | null;
  items: OrderItem[];
  notes: OrderNote[];
};

type StaffMember = {
  staff_id: number;
  username: string;
  full_name: string | null;
  role: string;
  account_status: string;
};

type Filters = {
  order_status?: string | null;
  start_date?: string | null;
  end_date?: string | null;
  sort_order?: string | null;
};

type Flash = {
  success?: string;
  error?: string;
};

type OrderManagementProps = {
  orders: OrderSummary[];
  filters: Filters;
  selectedOrder: SelectedOrder | null;
  activeStaff: StaffMember[];
  flash: Flash;
};

function formatAmount(amount: number | string | null | undefined): string {
  const num = Number(amount ?? 0);
  return Number.isFinite(num) ? num.toFixed(2) : '0.00';
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);

  if (Number.isNaN(date.getTime())) {
    return dateString;
  }

  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function getStatusColor(status: OrderStatus): string {
  switch (status) {
    case 'Pending':
      return 'bg-blue-100 text-blue-800';
    case 'Processing':
      return 'bg-yellow-100 text-yellow-800';
    case 'Ready for Pickup':
      return 'bg-green-100 text-green-800';
    case 'Completed':
      return 'bg-gray-100 text-gray-800';
    case 'Cancelled':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}

export default function OrderManagement({
  orders,
  filters,
  selectedOrder,
  activeStaff,
  flash,
}: OrderManagementProps) {
  const { visibleSuccess, visibleError } = useTimedFlash({
    success: flash.success,
    error: flash.error,
  });
  
  const [filterStatus, setFilterStatus] = useState(filters.order_status ?? 'all');
  const [filterSortOrder, setFilterSortOrder] = useState(filters.sort_order ?? 'desc');
  const [filterStartDate, setFilterStartDate] = useState(filters.start_date ?? '');
  const [filterEndDate, setFilterEndDate] = useState(filters.end_date ?? '');
  const [isDetailsModalOpen, setIsDetailsModalOpen] = useState(false);

  const startDateInputRef = useRef<HTMLInputElement | null>(null);
  const endDateInputRef = useRef<HTMLInputElement | null>(null);
  const [isPickupEmailConfirmOpen, setIsPickupEmailConfirmOpen] = useState(false);
  const [newNote, setNewNote] = useState('');
  const [noteError, setNoteError] = useState('');

  const [selectedDesignItem, setSelectedDesignItem] = useState<OrderItem | null>(null);
  const [isDesignReferenceModalOpen, setIsDesignReferenceModalOpen] = useState(false);
  const [designReferenceMessage, setDesignReferenceMessage] = useState('');
  const [filterDateError, setFilterDateError] = useState('');

  useEffect(() => {
    setFilterStatus(filters.order_status ?? 'all');
    setFilterSortOrder(filters.sort_order ?? 'desc');
    setFilterStartDate(filters.start_date ?? '');
    setFilterEndDate(filters.end_date ?? '');
  }, [filters.order_status, filters.sort_order, filters.start_date, filters.end_date]);

  useEffect(() => {
    setIsDetailsModalOpen(selectedOrder !== null);
  }, [selectedOrder]);

  useEffect(() => {
    if (!filterDateError) return;

    const timer = window.setTimeout(() => setFilterDateError(''), 4000);
    return () => window.clearTimeout(timer);
  }, [filterDateError]);

  const hasInvalidDateRange = (startDate: string, endDate: string): boolean => {
    if (!startDate || !endDate) return false;
    return endDate < startDate;
  };

  const buildFilterPayload = (
    nextStatus = filterStatus,
    nextSortOrder = filterSortOrder,
    nextStartDate = filterStartDate,
    nextEndDate = filterEndDate,
  ) => ({
    order_status: nextStatus === 'all' ? '' : nextStatus,
    sort_order: nextSortOrder,
    start_date: nextStartDate,
    end_date: nextEndDate,
  });

  const applyFilters = (
    nextStatus = filterStatus,
    nextSortOrder = filterSortOrder,
    nextStartDate = filterStartDate,
    nextEndDate = filterEndDate,
  ) => {
    if (hasInvalidDateRange(nextStartDate, nextEndDate)) {
      setFilterDateError('End date cannot be earlier than start date.');
      return;
    }

    setFilterDateError('');

    router.get(
      '/staff/orders',
      buildFilterPayload(nextStatus, nextSortOrder, nextStartDate, nextEndDate),
      {
        preserveScroll: true,
        replace: true,
      },
    );
  };

  const openDetailsModal = (orderId: number) => {
    router.get(`/staff/orders/${orderId}`, buildFilterPayload(), {
      preserveScroll: true,
    });
  };

  const closePickupEmailConfirmModal = () => {
    setIsPickupEmailConfirmOpen(false);
  };

  const closeDetailsModal = () => {
    setIsDetailsModalOpen(false);
    closePickupEmailConfirmModal();
    setSelectedDesignItem(null);
    setIsDesignReferenceModalOpen(false);
    setDesignReferenceMessage('');

    router.get('/staff/orders', buildFilterPayload(), {
      preserveScroll: true,
      replace: true,
    });
  };

  const submitStatusChange = (newStatus: OrderStatus, sendEmail: boolean) => {
    if (!selectedOrder) return;

    router.put(
      `/staff/orders/${selectedOrder.order_id}/status`,
      {
        order_status: newStatus,
        send_email: sendEmail,
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          closePickupEmailConfirmModal();
        },
        onError: () => {
          closePickupEmailConfirmModal();
        },
      },
    );
  };

  const handleStatusChange = (newStatus: SelectedOrder['order_status']) => {
    if (!selectedOrder) return;

    if (newStatus === selectedOrder.order_status) {
      return;
    }

    if (newStatus === 'Ready for Pickup') {
      setIsPickupEmailConfirmOpen(true);
      return;
    }

    submitStatusChange(newStatus, false);
  };

  const handleSendPickupEmail = () => {
    if (!selectedOrder) return;

    router.post(
      `/staff/orders/${selectedOrder.order_id}/pickup-email`,
      {},
      {
        preserveScroll: true,
      },
    );
  };

  const handleReassign = (newStaffId: number) => {
    if (!selectedOrder) return;

    router.put(
      `/staff/orders/${selectedOrder.order_id}/reassign`,
      { staff_id: newStaffId },
      { preserveScroll: true },
    );
  };

  const handleAddNote = () => {
    if (!selectedOrder) return;

    setNoteError('');

    if (!newNote.trim() || newNote.length > 1000) {
      setNoteError('Note cannot be empty and must not exceed 1000 characters.');
      return;
    }

    router.post(
      `/staff/orders/${selectedOrder.order_id}/notes`,
      { note_text: newNote },
      {
        preserveScroll: true,
        onSuccess: () => {
          setNewNote('');
          setNoteError('');
        },
        onError: () => {
          setNoteError('Note cannot be empty and must not exceed 1000 characters.');
        },
      },
    );
  };

  const openDesignReferenceModal = (item: OrderItem) => {
    setSelectedDesignItem(item);
    setDesignReferenceMessage('');
    setIsDesignReferenceModalOpen(true);
  };

  const closeDesignReferenceModal = () => {
    setSelectedDesignItem(null);
    setIsDesignReferenceModalOpen(false);
    setDesignReferenceMessage('');
  };

  const copyDesignReference = async () => {
    if (!selectedDesignItem?.preview_image_reference) return;

    try {
      await navigator.clipboard.writeText(selectedDesignItem.preview_image_reference);
      setDesignReferenceMessage('Image reference copied.');
      window.setTimeout(() => setDesignReferenceMessage(''), 2500);
    } catch {
      setDesignReferenceMessage('Could not copy the image reference.');
      window.setTimeout(() => setDesignReferenceMessage(''), 2500);
    }
  };

  const safeOrders = useMemo(() => (Array.isArray(orders) ? orders : []), [orders]);
  const safeSelectedOrder = selectedOrder;

  const openDatePicker = (input: HTMLInputElement | null) => {
    if (!input) return;

    input.focus();

    if ('showPicker' in input) {
      try {
        (input as HTMLInputElement & { showPicker?: () => void }).showPicker?.();
      } catch {
        // Fallback to native focus behavior when showPicker is not available.
      }
    }
  };

  return (
    <div className="bg-white rounded-lg shadow-md p-6 overflow-hidden">
      <h2 className="text-2xl font-semibold text-purple-900 mb-6">Order Management</h2>

      {visibleSuccess && (
        <div className="mb-4 p-4 bg-green-100 text-green-800 rounded-md border border-green-200">
          {visibleSuccess}
        </div>
      )}

      {visibleError && (
        <div className="mb-4 p-4 bg-red-100 text-red-800 rounded-md border border-red-200">
          {visibleError}
        </div>
      )}

      {filterDateError && (
        <div className="mb-4 p-4 bg-red-100 text-red-800 rounded-md border border-red-200">
          {filterDateError}
        </div>
      )}

      <div className="mb-6">
        <div className="grid grid-cols-1 lg:grid-cols-4 gap-4">
          <div className="min-w-0">
            <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              value={filterStatus}
              onChange={(e) => {
                const value = e.target.value;
                setFilterStatus(value);
                applyFilters(value, filterSortOrder, filterStartDate, filterEndDate);
              }}
              className="w-full h-11 px-4 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-purple-500 cursor-pointer"
            >
              <option value="all">All Statuses</option>
              <option value="Pending">Pending</option>
              <option value="Processing">Processing</option>
              <option value="Ready for Pickup">Ready for Pickup</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>

          <div className="min-w-0">
            <label className="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
            <select
              value={filterSortOrder}
              onChange={(e) => {
                const value = e.target.value;
                setFilterSortOrder(value);
                applyFilters(filterStatus, value, filterStartDate, filterEndDate);
              }}
              className="w-full h-11 px-4 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-purple-500 cursor-pointer"
            >
              <option value="desc">Newest First</option>
              <option value="asc">Oldest First</option>
            </select>
          </div>

          <div className="min-w-0">
            <label className="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <div
              onClick={() => openDatePicker(startDateInputRef.current)}
              className="w-full h-11 border border-gray-300 rounded-md text-sm focus-within:ring-2 focus-within:ring-purple-500 bg-white cursor-pointer flex items-center"
            >
              <input
                ref={startDateInputRef}
                type="date"
                value={filterStartDate}
                onChange={(e) => {
                  const value = e.target.value;
                  setFilterStartDate(value);
                  applyFilters(filterStatus, filterSortOrder, value, filterEndDate);
                }}
                className="w-full h-full px-4 rounded-md bg-transparent cursor-pointer outline-none"
              />
            </div>
          </div>

          <div className="min-w-0">
            <label className="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <div
              onClick={() => openDatePicker(endDateInputRef.current)}
              className="w-full h-11 border border-gray-300 rounded-md text-sm focus-within:ring-2 focus-within:ring-purple-500 bg-white cursor-pointer flex items-center"
            >
              <input
                ref={endDateInputRef}
                type="date"
                value={filterEndDate}
                onChange={(e) => {
                  const value = e.target.value;
                  setFilterEndDate(value);
                  applyFilters(filterStatus, filterSortOrder, filterStartDate, value);
                }}
                className="w-full h-full px-4 rounded-md bg-transparent cursor-pointer outline-none"
              />
            </div>
          </div>
        </div>
      </div>

      <div className="overflow-x-auto">
        <table className="w-full table-fixed">
          <colgroup>
            <col className="w-[15%]" />
            <col className="w-[24%]" />
            <col className="w-[19%]" />
            <col className="w-[13%]" />
            <col className="w-[11%]" />
            <col className="w-[18%]" />
          </colgroup>

          <thead className="bg-gray-50">
            <tr>
              <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Order Number</th>
              <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Customer</th>
              <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Date</th>
              <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
              <th className="px-4 py-3 text-right text-sm font-medium text-gray-700">Total</th>
              <th className="px-3 py-3 text-center text-sm font-medium text-gray-700">Actions</th>
            </tr>
          </thead>

          <tbody className="divide-y divide-gray-200">
            {safeOrders.length === 0 ? (
              <tr>
                <td colSpan={6} className="px-4 py-8 text-center text-gray-500">
                  No orders match the selected filters.
                </td>
              </tr>
            ) : (
              safeOrders.map((order) => (
                <tr key={order.order_id} className="hover:bg-gray-50 transition-colors align-top">
                  <td className="px-4 py-3 font-medium text-purple-700 whitespace-nowrap">
                    ORD-{order.order_id}
                  </td>

                  <td className="px-4 py-3 text-gray-900">
                    <div className="min-w-0 max-w-full truncate" title={order.customer_name}>
                      {order.customer_name}
                    </div>
                  </td>

                  <td className="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                    {formatDate(order.order_creation_timestamp)}
                  </td>

                  <td className="px-4 py-3">
                    <span className={`inline-flex px-2 py-1 rounded text-xs font-semibold ${getStatusColor(order.order_status)}`}>
                      {order.order_status}
                    </span>
                  </td>

                  <td className="px-4 py-3 text-right font-medium text-gray-900 whitespace-nowrap">
                    €{formatAmount(order.total_amount)}
                  </td>

                  <td className="px-3 py-3 text-center">
                    <button
                      onClick={() => openDetailsModal(Number(order.order_id))}
                      className="inline-flex items-center px-2.5 py-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors cursor-pointer text-sm whitespace-nowrap"
                    >
                      <Eye className="w-4 h-4 mr-1" />
                      View Details
                    </button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      <Modal
        isOpen={isDetailsModalOpen}
        onClose={closeDetailsModal}
        title={safeSelectedOrder ? `Order Details - ORD-${safeSelectedOrder.order_id}` : 'Order Details'}
        size="large"
      >
        {safeSelectedOrder && (
          <div className="space-y-4">
            <div className="bg-gray-50 p-4 rounded-lg">
              <h3 className="font-semibold text-base mb-3 text-purple-900">Customer Information</h3>

              <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 text-sm">
                <div>
                  <span className="text-gray-600">Name:</span>
                  <p className="font-medium text-gray-900">{safeSelectedOrder.customer_name}</p>
                </div>

                <div>
                  <span className="text-gray-600">Email:</span>
                  <p className="font-medium text-gray-900 break-word">{safeSelectedOrder.customer_email}</p>
                </div>

                <div>
                  <span className="text-gray-600">Phone:</span>
                  <p className="font-medium text-gray-900">{safeSelectedOrder.customer_phone}</p>
                </div>

                <div>
                  <span className="text-gray-600">Order Date:</span>
                  <p className="font-medium text-gray-900">
                    {formatDate(safeSelectedOrder.order_creation_timestamp)}
                  </p>
                </div>
              </div>
            </div>

            <div>
              <h3 className="font-semibold text-base mb-3 text-purple-900">Order Items</h3>

              <div className="overflow-x-auto">
                <table className="w-full border border-gray-200 rounded-lg table-fixed">
                  <colgroup>
                    <col className="w-[28%]" />
                    <col className="w-[9%]" />
                    <col className="w-[15%]" />
                    <col className="w-[16%]" />
                    <col className="w-[32%]" />
                  </colgroup>

                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-3 py-2 text-left text-sm font-medium text-gray-700">Product</th>
                      <th className="px-3 py-2 text-center text-sm font-medium text-gray-700">Qty</th>
                      <th className="px-3 py-2 text-right text-sm font-medium text-gray-700">Unit Price</th>
                      <th className="px-3 py-2 text-right text-sm font-medium text-gray-700">Subtotal</th>
                      <th className="px-3 py-2 text-center text-sm font-medium text-gray-700">Design</th>
                    </tr>
                  </thead>

                  <tbody className="divide-y divide-gray-200">
                    {safeSelectedOrder.items.map((item) => (
                      <tr key={item.order_item_id}>
                        <td className="px-3 py-2 text-gray-900 break-word">
                          <div>
                            <p>{item.product_name}</p>

                            {(item.shirt_color_label || item.print_sides_label) && (
                              <div className="mt-1 space-y-1 text-xs text-gray-500">
                                {item.shirt_color_label && (
                                  <p>Shirt Color: {item.shirt_color_label}</p>
                                )}

                                {item.print_sides_label && (
                                  <p>Print Sides: {item.print_sides_label}</p>
                                )}
                              </div>
                            )}
                          </div>
                        </td>
                        <td className="px-3 py-2 text-center text-gray-700 whitespace-nowrap">{item.quantity}</td>
                        <td className="px-3 py-2 text-right text-gray-700 whitespace-nowrap">
                          €{formatAmount(item.unit_price)}
                        </td>
                        <td className="px-3 py-2 text-right font-medium text-gray-900 whitespace-nowrap">
                          €{formatAmount(item.line_subtotal)}
                        </td>
                        <td className="px-3 py-2 text-center">
                          {item.preview_image_reference ? (
                            <button
                              onClick={() => openDesignReferenceModal(item)}
                              className="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition-colors cursor-pointer text-sm whitespace-nowrap"
                            >
                              <ImageIcon className="w-4 h-4 mr-1.5" />
                              View Design
                            </button>
                          ) : (
                            <span className="text-xs text-gray-400">No design ref</span>
                          )}
                        </td>
                      </tr>
                    ))}

                    <tr className="bg-gray-50 font-semibold">
                      <td colSpan={3} className="px-3 py-2 text-right text-gray-900">Total:</td>
                      <td className="px-3 py-2 text-right text-purple-700 whitespace-nowrap">
                        €{formatAmount(safeSelectedOrder.total_amount)}
                      </td>
                      <td className="px-3 py-2" />
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div className="grid grid-cols-1 xl:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Order Status</label>
                <select
                  value={safeSelectedOrder.order_status}
                  onChange={(e) => handleStatusChange(e.target.value as SelectedOrder['order_status'])}
                  className="w-full h-11 px-4 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-purple-500 cursor-pointer"
                >
                  <option value="Pending">Pending</option>
                  <option value="Processing">Processing</option>
                  <option value="Ready for Pickup">Ready for Pickup</option>
                  <option value="Completed">Completed</option>
                  <option value="Cancelled">Cancelled</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Assigned Staff</label>
                <select
                  value={safeSelectedOrder.assigned_staff_id ?? 0}
                  onChange={(e) => handleReassign(parseInt(e.target.value, 10))}
                  className="w-full h-11 px-4 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-purple-500 cursor-pointer"
                >
                  <option value={0}>Unassigned</option>
                  {activeStaff.map((staff) => (
                    <option key={staff.staff_id} value={staff.staff_id}>
                      {staff.full_name ?? staff.username} ({staff.role})
                    </option>
                  ))}
                </select>
              </div>
            </div>

            {safeSelectedOrder.order_status === 'Ready for Pickup' && (
              <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                  <div>
                    <h3 className="font-semibold text-green-900">Customer Pickup Email</h3>
                    <p className="text-sm text-green-800 mt-1">
                      Send or resend the ready-for-pickup email to the customer.
                    </p>
                    {safeSelectedOrder.pickup_notification_sent_at && (
                      <p className="text-xs text-green-700 mt-2">
                        Last sent: {formatDate(safeSelectedOrder.pickup_notification_sent_at)}
                      </p>
                    )}
                  </div>

                  <button
                    onClick={handleSendPickupEmail}
                    className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-colors cursor-pointer whitespace-nowrap"
                  >
                    {safeSelectedOrder.pickup_notification_sent_at
                      ? 'Resend Pickup Email'
                      : 'Send Pickup Email'}
                  </button>
                </div>
              </div>
            )}

            <div>
              <h3 className="font-semibold text-base mb-3 text-purple-900">Internal Notes</h3>

              <div className="space-y-3 mb-4 max-h-40 overflow-y-auto pr-1">
                {safeSelectedOrder.notes.length === 0 ? (
                  <div className="text-sm text-gray-500">No internal notes yet.</div>
                ) : (
                  safeSelectedOrder.notes.map((note) => (
                    <div key={note.note_id} className="bg-blue-50 p-3 rounded-lg border border-blue-100">
                      <div className="flex items-start justify-between mb-1 gap-3">
                        <div className="flex items-center text-sm text-gray-600 min-w-0">
                          <User className="w-4 h-4 mr-1 shrink-0" />
                          <span className="font-medium truncate">
                            {note.staff?.full_name ?? note.staff?.username ?? 'Staff'}
                          </span>
                        </div>

                        <div className="flex items-center text-xs text-gray-500 whitespace-nowrap">
                          <Clock className="w-3 h-3 mr-1" />
                          {formatDate(note.note_timestamp)}
                        </div>
                      </div>

                      <p className="text-sm text-gray-800 wrap-break-word">{note.note_text}</p>
                    </div>
                  ))
                )}
              </div>

              <div className="space-y-2">
                <label className="block text-sm font-medium text-gray-700">Add Note</label>

                <textarea
                  value={newNote}
                  onChange={(e) => setNewNote(e.target.value)}
                  rows={3}
                  maxLength={1000}
                  className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                    noteError ? 'border-red-500' : 'border-gray-300'
                  }`}
                  placeholder="Enter internal note (max 1000 characters)"
                />

                {noteError && <p className="text-sm text-red-600">{noteError}</p>}

                <div className="flex justify-between items-center">
                  <span className="text-xs text-gray-500">{newNote.length}/1000 characters</span>

                  <button
                    onClick={handleAddNote}
                    className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors cursor-pointer"
                  >
                    Add Note
                  </button>
                </div>
              </div>
            </div>

            <div className="flex justify-end pt-2">
              <button
                onClick={closeDetailsModal}
                className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors cursor-pointer"
              >
                Close
              </button>
            </div>
          </div>
        )}
      </Modal>

      <Modal
        isOpen={isDesignReferenceModalOpen}
        onClose={closeDesignReferenceModal}
        title={selectedDesignItem ? `Design Reference - ${selectedDesignItem.product_name}` : 'Design Reference'}
        size="large"
      >
        {selectedDesignItem && (
          <div className="space-y-4">
            <div className="grid grid-cols-1 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)] gap-4">
              <div className="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 className="font-semibold text-base mb-3 text-purple-900">Preview</h3>

                <div className="bg-white border border-gray-200 rounded-lg min-h-80 flex items-center justify-center overflow-hidden">
                  {selectedDesignItem.preview_image_reference ? (
                    <img
                      src={selectedDesignItem.preview_image_reference}
                      alt={selectedDesignItem.product_name}
                      className="max-h-105 w-full object-contain"
                    />
                  ) : (
                    <div className="text-sm text-gray-500">No preview image available.</div>
                  )}
                </div>
              </div>

              <div className="space-y-4">
                <div className="bg-gray-50 border border-gray-200 rounded-lg p-4">
                  <h3 className="font-semibold text-base mb-3 text-purple-900">Item Information</h3>

                  <div className="grid grid-cols-1 gap-3 text-sm">
                    <div>
                      <span className="text-gray-600">Product:</span>
                      <p className="font-medium text-gray-900 wrap-break-word">{selectedDesignItem.product_name}</p>
                    </div>

                    <div>
                      <span className="text-gray-600">Quantity:</span>
                      <p className="font-medium text-gray-900">{selectedDesignItem.quantity}</p>
                    </div>

                    {selectedDesignItem.shirt_color_label && (
                      <div>
                        <span className="text-gray-600">Shirt Color:</span>
                        <p className="font-medium text-gray-900">
                          {selectedDesignItem.shirt_color_label}
                        </p>
                      </div>
                    )}

                    {selectedDesignItem.print_sides_label && (
                      <div>
                        <span className="text-gray-600">Print Sides:</span>
                        <p className="font-medium text-gray-900">
                          {selectedDesignItem.print_sides_label}
                        </p>
                      </div>
                    )}

                    <div>
                      <span className="text-gray-600">Unit Price:</span>
                      <p className="font-medium text-gray-900">€{formatAmount(selectedDesignItem.unit_price)}</p>
                    </div>
                  </div>
                </div>

                <div className="bg-gray-50 border border-gray-200 rounded-lg p-4">
                  <div className="flex items-center justify-between gap-3 mb-3">
                    <h3 className="font-semibold text-base text-purple-900">Image Reference</h3>

                    <button
                      onClick={copyDesignReference}
                      disabled={!selectedDesignItem.preview_image_reference}
                      className="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer text-sm whitespace-nowrap"
                    >
                      <Copy className="w-4 h-4 mr-1.5" />
                      Copy Reference
                    </button>
                  </div>

                  <textarea
                    readOnly
                    value={selectedDesignItem.preview_image_reference ?? 'No image reference available.'}
                    rows={8}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-xs text-gray-700 resize-none"
                  />

                  {designReferenceMessage && (
                    <p className="mt-2 text-sm text-green-700">{designReferenceMessage}</p>
                  )}

                  <p className="mt-3 text-xs text-gray-500">
                    Staff can use this visual reference and stored image reference when recreating
                    the item in the graphic design workshop.
                  </p>
                </div>
              </div>
            </div>

            <div className="flex justify-end pt-2">
              <button
                onClick={closeDesignReferenceModal}
                className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors cursor-pointer"
              >
                Close
              </button>
            </div>
          </div>
        )}
      </Modal>

      <Modal
        isOpen={isPickupEmailConfirmOpen}
        onClose={closePickupEmailConfirmModal}
        title="Notify Customer?"
        size="sm"
      >
        <div className="space-y-5">
          <div className="space-y-2">
            <p className="text-sm leading-6 text-gray-700">
              This order is being marked as <strong>Ready for Pickup</strong>. Do you want
              to send an email to the customer now?
            </p>
          </div>

          <div className="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
            <ul className="space-y-2">
              <li>
                <strong>Update &amp; Send Email</strong> will update the status and queue the
                customer email.
              </li>
              <li>
                <strong>Update Without Email</strong> will update the status without sending
                an email.
              </li>
              <li>
                You can still send or resend the pickup email later from the order details.
              </li>
            </ul>
          </div>

          <div className="grid grid-cols-1 gap-3 pt-1 sm:grid-cols-3">
            <button
              onClick={closePickupEmailConfirmModal}
              className="h-12 w-full border border-gray-300 rounded-lg hover:bg-gray-50 font-medium text-sm transition-colors cursor-pointer text-center"
            >
              Cancel
            </button>

            <button
              onClick={() => submitStatusChange('Ready for Pickup', false)}
              className="h-12 w-full bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium text-sm transition-colors cursor-pointer text-center leading-tight"
            >
              <span className="block">Update</span>
              <span className="block">Without Email</span>
            </button>

            <button
              onClick={() => submitStatusChange('Ready for Pickup', true)}
              className="h-12 w-full bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium text-sm transition-colors cursor-pointer text-center leading-tight"
            >
              <span className="block">Update &amp;</span>
              <span className="block">Send Email</span>
            </button>
          </div>
        </div>
      </Modal>
    </div>
  );
}