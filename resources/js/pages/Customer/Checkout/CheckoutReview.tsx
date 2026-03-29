import { Head, Link, useForm, usePage } from '@inertiajs/react';

type CheckoutItem = {
  cart_item_id: number;
  product_id: number;
  quantity: number;
  resolved_unit_price: number;
  resolved_line_subtotal: number;
  shirt_color_label?: string | null;
  size_label?: string | null;
  print_sides_label?: string | null;
  product?: {
    product_name?: string;
  } | null;
};

type CheckoutData = {
  items: CheckoutItem[];
  cart_total: number;
  vat_rate: number;
  vat_amount: number;
  net_amount: number;
};

type PageProps = {
  checkout: CheckoutData;
  auth: {
    customer: null | {
      customer_id: number;
      full_name: string;
      email: string;
      phone_number?: string | null;
    };
  };
};

export default function CheckoutReview() {
  const { props } = usePage<PageProps>();
  const { checkout, auth } = props;

  const customer = auth?.customer;

  const { data, setData, post, processing, errors } = useForm({
    customer_name: customer?.full_name ?? '',
    customer_email: customer?.email ?? '',
    customer_phone: customer?.phone_number ?? '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/checkout');
  };

  const formattedPrices = {
    cartTotal: Number(checkout.cart_total).toFixed(2),
    vatRate: Number(checkout.vat_rate).toFixed(2),
    vatAmount: Number(checkout.vat_amount).toFixed(2),
    netAmount: Number(checkout.net_amount).toFixed(2),
  };

  return (
    <>
      <Head title="Checkout" />

      <div className="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
        <h1 className="mb-6 text-2xl font-bold sm:text-3xl md:mb-8">Checkout</h1>

        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">
          <div className="lg:col-span-2">
            <div className="mb-6 rounded-lg bg-white p-4 shadow-sm sm:p-5 md:p-6">
              <h2 className="text-xl font-semibold mb-4">Order Items</h2>

              <div className="space-y-4">
                {checkout.items.map((item) => (
                  <div
                    key={item.cart_item_id}
                    className="flex flex-col gap-2 border-b pb-4 last:border-b-0 sm:flex-row sm:items-start sm:justify-between"
                  >
                    <div className="flex-1 min-w-0">
                      <h3 className="font-medium wrap-break-word">
                        {item.product?.product_name ?? `Product #${item.product_id}`}
                      </h3>

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

                      <p className="mt-2 text-sm text-gray-600">
                        Quantity: {item.quantity} × €{Number(item.resolved_unit_price).toFixed(2)}
                      </p>
                    </div>

                    <p className="font-semibold sm:ml-4 sm:shrink-0">
                      €{Number(item.resolved_line_subtotal).toFixed(2)}
                    </p>
                  </div>
                ))}
              </div>

              <div className="mt-4">
                <Link href="/cart" className="text-blue-600 hover:underline text-sm">
                  ← Back to cart
                </Link>
              </div>
            </div>

            <div className="rounded-lg bg-white p-4 shadow-sm sm:p-5 md:p-6">
              <h2 className="text-xl font-semibold mb-4">Contact Information</h2>

              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Full Name *
                  </label>
                  <input
                    type="text"
                    value={data.customer_name}
                    onChange={(e) => setData('customer_name', e.target.value)}
                    className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                      errors.customer_name ? 'border-red-500' : 'border-gray-300'
                    }`}
                    maxLength={50}
                  />
                  {errors.customer_name && (
                    <p className="mt-1 text-sm text-red-600">{errors.customer_name}</p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Email Address *
                  </label>
                  <input
                    type="email"
                    value={data.customer_email}
                    onChange={(e) => setData('customer_email', e.target.value)}
                    className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                      errors.customer_email ? 'border-red-500' : 'border-gray-300'
                    }`}
                    maxLength={100}
                  />
                  {errors.customer_email && (
                    <p className="mt-1 text-sm text-red-600">{errors.customer_email}</p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Phone Number *
                  </label>

                  <div className="flex rounded-md shadow-sm">
                    <div className="inline-flex shrink-0 items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-600">
                      +357
                    </div>

                    <input
                      type="tel"
                      value={data.customer_phone}
                      onChange={(e) =>
                        setData('customer_phone', e.target.value.replace(/\D/g, '').slice(0, 8))
                      }
                      className={`w-full px-4 py-2 border rounded-r-md focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                        errors.customer_phone ? 'border-red-500' : 'border-gray-300'
                      }`}
                      placeholder="12345678"
                      inputMode="numeric"
                      maxLength={8}
                    />
                  </div>

                  {errors.customer_phone && (
                    <p className="mt-1 text-sm text-red-600">{errors.customer_phone}</p>
                  )}

                  <p className="mt-1 text-xs text-gray-500">Cyprus number, 8 digits</p>
                </div>

                <button
                  type="submit"
                  disabled={processing}
                  className="w-full cursor-pointer bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 font-semibold mt-6 disabled:opacity-60"
                >
                  {processing ? 'Placing Order...' : 'Place Order'}
                </button>
              </form>
            </div>
          </div>

          <div className="lg:col-span-1">
            <div className="rounded-lg bg-white p-4 shadow-sm sm:p-5 md:p-6 lg:sticky lg:top-24">
              <h2 className="mb-4 text-lg font-semibold sm:text-xl">Pricing Summary</h2>

              <div className="space-y-3 mb-6">
                <div className="flex justify-between text-gray-600">
                  <span>Subtotal</span>
                  <span>€{formattedPrices.cartTotal}</span>
                </div>
                <div className="flex justify-between text-gray-600">
                  <span>VAT ({formattedPrices.vatRate}%)</span>
                  <span>€{formattedPrices.vatAmount}</span>
                </div>
                <div className="flex justify-between text-gray-600">
                  <span>Net Amount</span>
                  <span>€{formattedPrices.netAmount}</span>
                </div>
                <div className="border-t pt-3 flex justify-between font-bold text-lg">
                  <span>Total</span>
                  <span>€{formattedPrices.cartTotal}</span>
                </div>
              </div>

              <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm">
                <p className="font-semibold text-yellow-800 mb-1">In-Store Pickup</p>
                <p className="text-yellow-700">
                  Payment will be collected when you pick up your order at our store.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}