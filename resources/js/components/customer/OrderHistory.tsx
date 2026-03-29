import { usePage } from '@inertiajs/react';
import { Package, CalendarDays } from 'lucide-react';

type OrderItem = {
  order_item_id: number;
  product_id: number;
  product_name: string;
  unit_price: number | string;
  quantity: number;
  line_subtotal: number | string;
  design_snapshot: string;
  preview_image_reference: string | null;
  shirt_color_label?: string | null;
  size_label?: string | null;
  print_sides_label?: string | null;
};

type Order = {
  order_id: number;
  order_creation_timestamp: string;
  order_status: string;
  net_amount: number | string;
  vat_amount: number | string;
  total_amount: number | string;
  vat_rate: number | string;
  items: OrderItem[];
};

type PageProps = {
  orders: Order[];
};

export default function OrderHistory() {
  const { orders } = usePage<PageProps>().props;

  const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
      Pending: 'bg-blue-100 text-blue-800',
      Processing: 'bg-yellow-100 text-yellow-800',
      'Ready for Pickup': 'bg-green-100 text-green-800',
      Completed: 'bg-gray-100 text-gray-800',
      Cancelled: 'bg-red-100 text-red-800',
    };

    return colors[status] || 'bg-gray-100 text-gray-800';
  };

  const formatMoney = (amount: number | string) => {
    return Number(amount).toFixed(2);
  };

  const formatOrderDateTime = (timestamp: string) => {
    return new Date(timestamp).toLocaleString('en-GB', {
      day: '2-digit',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  return (
    <div className="rounded-lg bg-white p-4 shadow-sm sm:p-5 md:p-6">
      <h2 className="mb-4 text-xl font-semibold text-gray-900 sm:text-2xl">Order History</h2>

      {orders.length === 0 ? (
        <div className="py-10 text-center">
          <Package className="mx-auto mb-3 h-10 w-10 text-gray-400" />
          <p className="text-sm text-gray-600">You have not placed any orders yet.</p>
        </div>
      ) : (
        <div className="space-y-4">
          {orders.map((order) => (
            <section
              key={order.order_id}
              className="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
            >
              <div className="border-b border-gray-200 px-4 py-3 sm:px-5 md:px-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                  <div>
                    <h3 className="text-base font-semibold text-gray-900">
                      Order #{order.order_id}
                    </h3>

                    <div className="mt-1.5 flex items-center gap-2 text-xs text-gray-600 sm:text-sm">
                      <CalendarDays className="h-4 w-4" />
                      <span>{formatOrderDateTime(order.order_creation_timestamp)}</span>
                    </div>
                  </div>

                  <span
                    className={`inline-flex w-fit rounded-full px-2.5 py-0.5 text-xs font-medium ${getStatusColor(order.order_status)}`}
                  >
                    {order.order_status}
                  </span>
                </div>
              </div>

              <div className="px-4 py-4 sm:px-5 md:px-6">
                <div className="mb-4">
                  <h4 className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Items Ordered
                  </h4>

                  <div className="space-y-3 md:hidden">
                    {order.items.map((item) => (
                      <div
                        key={item.order_item_id}
                        className="rounded-md border border-gray-200 bg-gray-50 p-3"
                      >
                        <div className="mb-3">
                          <p className="text-sm font-medium text-gray-900 wrap-break-word">
                            {item.product_name}
                          </p>

                          {(item.shirt_color_label || item.print_sides_label || item.size_label) && (
                            <div className="mt-1 space-y-1 text-xs text-gray-500">
                              {item.shirt_color_label && (
                                <p className="wrap-break-word">Shirt Color: {item.shirt_color_label}</p>
                              )}

                              {item.size_label && (
                                <p className="wrap-break-word">Size: {item.size_label}</p>
                              )}

                              {item.print_sides_label && (
                                <p className="wrap-break-word">Print Sides: {item.print_sides_label}</p>
                              )}
                            </div>
                          )}
                        </div>

                        <dl className="space-y-2 text-sm">
                          <div className="flex items-center justify-between gap-4">
                            <dt className="text-gray-600">Unit Price</dt>
                            <dd className="font-medium text-gray-900">€{formatMoney(item.unit_price)}</dd>
                          </div>

                          <div className="flex items-center justify-between gap-4">
                            <dt className="text-gray-600">Quantity</dt>
                            <dd className="text-gray-900">{item.quantity}</dd>
                          </div>

                          <div className="border-t border-gray-200 pt-2">
                            <div className="flex items-center justify-between gap-4">
                              <dt className="font-medium text-gray-700">Subtotal</dt>
                              <dd className="font-semibold text-gray-900">
                                €{formatMoney(item.line_subtotal)}
                              </dd>
                            </div>
                          </div>
                        </dl>
                      </div>
                    ))}
                  </div>

                  <div className="hidden overflow-x-auto md:block">
                    <table className="min-w-full divide-y divide-gray-200">
                      <thead>
                        <tr className="text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                          <th className="pb-2 pr-4">Product</th>
                          <th className="pb-2 pr-4">Unit Price</th>
                          <th className="pb-2 pr-4">Quantity</th>
                          <th className="pb-2 text-right">Subtotal</th>
                        </tr>
                      </thead>

                      <tbody className="divide-y divide-gray-100">
                        {order.items.map((item) => (
                          <tr key={item.order_item_id}>
                            <td className="py-2.5 pr-4 text-sm text-gray-900">
                              <div>
                                <p className="wrap-break-word">{item.product_name}</p>

                                {(item.shirt_color_label || item.print_sides_label || item.size_label) && (
                                  <div className="mt-1 space-y-1 text-xs text-gray-500">
                                    {item.shirt_color_label && (
                                      <p className="wrap-break-word">Shirt Color: {item.shirt_color_label}</p>
                                    )}

                                    {item.size_label && (
                                      <p className="wrap-break-word">Size: {item.size_label}</p>
                                    )}

                                    {item.print_sides_label && (
                                      <p className="wrap-break-word">Print Sides: {item.print_sides_label}</p>
                                    )}
                                  </div>
                                )}
                              </div>
                            </td>
                            <td className="py-2.5 pr-4 text-sm text-gray-700">
                              €{formatMoney(item.unit_price)}
                            </td>
                            <td className="py-2.5 pr-4 text-sm text-gray-700">
                              {item.quantity}
                            </td>
                            <td className="py-2.5 text-right text-sm font-medium text-gray-900">
                              €{formatMoney(item.line_subtotal)}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>

                <div>
                  <h4 className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Order Summary
                  </h4>

                  <div className="rounded-lg border border-gray-200 bg-gray-50 p-3 sm:p-4">
                    <dl className="space-y-2 text-sm text-gray-700">
                      <div className="flex items-center justify-between gap-4">
                        <dt>Net Amount</dt>
                        <dd className="font-medium text-gray-900">
                          €{formatMoney(order.net_amount)}
                        </dd>
                      </div>

                      <div className="flex items-center justify-between gap-4">
                        <dt>VAT ({Number(order.vat_rate).toFixed(2)}%)</dt>
                        <dd className="font-medium text-gray-900">
                          €{formatMoney(order.vat_amount)}
                        </dd>
                      </div>

                      <div className="border-t border-gray-200 pt-2">
                        <div className="flex items-center justify-between gap-4">
                          <dt className="font-semibold text-gray-900">Total</dt>
                          <dd className="font-semibold text-gray-900">
                            €{formatMoney(order.total_amount)}
                          </dd>
                        </div>
                      </div>
                    </dl>
                  </div>
                </div>
              </div>
            </section>
          ))}
        </div>
      )}
    </div>
  );
}