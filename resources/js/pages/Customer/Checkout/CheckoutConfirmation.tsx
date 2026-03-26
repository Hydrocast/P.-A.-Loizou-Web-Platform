import { Head, Link, usePage } from '@inertiajs/react';
import { CheckCircle, MapPin, Phone, Mail } from 'lucide-react';

type OrderItem = {
  order_item_id: number;
  product_name: string;
  unit_price: number;
  quantity: number;
  line_subtotal: number;
  shirt_color_label?: string | null;
  print_sides_label?: string | null;
};

type Order = {
  order_id: number;
  order_creation_timestamp: string;
  order_status: string;
  total_amount: number;
  vat_amount: number;
  net_amount: number;
  vat_rate: number;
  customer_email: string;
  customer_phone: string;
  items: OrderItem[];
};

type PageProps = {
  order: Order;
};

export default function CheckoutConfirmation() {
  const { props } = usePage<PageProps>();
  const { order } = props;

  return (
    <>
      <Head title="Order Confirmation" />

      <div className="mx-auto max-w-4xl px-4 py-5 sm:px-6 lg:px-8">
        <div className="rounded-lg bg-white p-5 shadow-md sm:p-6">
          <div className="mb-6 text-center">
            <CheckCircle className="mx-auto mb-3 h-12 w-12 text-green-600" />
            <h1 className="mb-1 text-2xl font-bold text-gray-900 sm:text-3xl">
              Order Confirmed!
            </h1>
            <p className="text-sm text-gray-600 sm:text-base">
              Thank you for your order! We will start processing as soon as possible.
            </p>
          </div>

          <div className="mb-5 rounded-lg bg-gray-50 p-4">
            <div className="grid grid-cols-2 gap-x-4 gap-y-3">
              <div>
                <p className="text-xs text-gray-500">Order Number</p>
                <p className="text-sm font-semibold text-gray-900">#{order.order_id}</p>
              </div>

              <div>
                <p className="text-xs text-gray-500">Order Date</p>
                <p className="text-sm font-semibold text-gray-900">
                  {new Date(order.order_creation_timestamp).toLocaleDateString()}
                </p>
              </div>

              <div>
                <p className="text-xs text-gray-500">Status</p>
                <span className="inline-block rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-800">
                  {order.order_status}
                </span>
              </div>

              <div>
                <p className="text-xs text-gray-500">Total Amount</p>
                <p className="text-sm font-semibold text-gray-900">
                  €{Number(order.total_amount).toFixed(2)}
                </p>
              </div>
            </div>
          </div>

          <div className="mb-5">
            <h2 className="mb-3 text-lg font-semibold text-gray-900">Order Items</h2>

            <div className="space-y-3">
              {order.items.map((item) => (
                <div
                  key={item.order_item_id}
                  className="flex items-start justify-between border-b border-gray-200 pb-3"
                >
                  <div className="min-w-0">
                    <h3 className="text-sm font-medium text-gray-900">{item.product_name}</h3>

                    {(item.shirt_color_label || item.print_sides_label) && (
                      <div className="mt-1 space-y-0.5 text-xs text-gray-500">
                        {item.shirt_color_label && (
                          <p>Shirt Color: {item.shirt_color_label}</p>
                        )}

                        {item.print_sides_label && (
                          <p>Print Sides: {item.print_sides_label}</p>
                        )}
                      </div>
                    )}

                    <p className="mt-1.5 text-sm text-gray-600">
                      Quantity: {item.quantity} × €{Number(item.unit_price).toFixed(2)}
                    </p>
                  </div>

                  <p className="ml-4 shrink-0 text-sm font-semibold text-gray-900">
                    €{Number(item.line_subtotal).toFixed(2)}
                  </p>
                </div>
              ))}
            </div>

            <div className="mt-4 space-y-1.5">
              <div className="flex justify-between text-sm text-gray-600">
                <span>Net Amount</span>
                <span>€{Number(order.net_amount).toFixed(2)}</span>
              </div>

              <div className="flex justify-between text-sm text-gray-600">
                <span>VAT ({Number(order.vat_rate).toFixed(2)}%)</span>
                <span>€{Number(order.vat_amount).toFixed(2)}</span>
              </div>

              <div className="flex justify-between border-t pt-2 text-base font-bold text-gray-900">
                <span>Total</span>
                <span>€{Number(order.total_amount).toFixed(2)}</span>
              </div>
            </div>
          </div>

          <div className="mb-5">
            <h2 className="mb-3 text-lg font-semibold text-gray-900">Contact Information</h2>

            <div className="space-y-2">
              <div className="flex items-center text-sm text-gray-700">
                <Mail className="mr-2.5 h-4 w-4 text-gray-400" />
                <span>{order.customer_email}</span>
              </div>

              <div className="flex items-center text-sm text-gray-700">
                <Phone className="mr-2.5 h-4 w-4 text-gray-400" />
                <span>{order.customer_phone}</span>
              </div>
            </div>
          </div>

          <div className="mb-5 rounded-lg border border-blue-200 bg-blue-50 p-4">
            <div className="flex items-start">
              <MapPin className="mt-0.5 mr-3 h-5 w-5 shrink-0 text-blue-600" />

              <div>
                <h3 className="mb-1 text-sm font-semibold text-blue-900">In-Store Pickup</h3>

                <p className="mb-2 text-sm leading-6 text-blue-800">
                  Your order will be ready for pickup at our store. We&apos;ll send you an email
                  notification when it&apos;s ready.
                </p>

                <p className="text-xs leading-5 text-blue-700">
                  <strong>Location:</strong> 1st April 120, Paralimni
                  <br />
                  <strong>Hours:</strong> Mon-Fri 08:30-18:30, Sat 09:00-13:30
                </p>
              </div>
            </div>
          </div>

          <div className="text-center">
            <Link
              href="/catalog"
              className="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
            >
              Continue Shopping
            </Link>
          </div>
        </div>
      </div>
    </>
  );
}