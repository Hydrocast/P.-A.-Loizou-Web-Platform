import { Head, Link, router, usePage } from '@inertiajs/react';
import { Trash2, Plus, Minus } from 'lucide-react';
import { useEffect, useState } from 'react';
import Modal from '@/components/public/Modal';

type CartItem = {
  cart_item_id: number;
  product_id: number;
  quantity: number;
  design_snapshot: string;
  preview_image_reference?: string | null;
  shirt_color_label?: string | null;
  print_sides_label?: string | null;
  product?: {
    product_name?: string;
  } | null;
};

type PageProps = {
  cartItems: CartItem[];
  flash?: {
    success?: string | null;
    error?: string | null;
    status?: string | null;
  };
  auth: {
    customer: null | {
      customer_id: number;
      full_name: string;
      email: string;
    };
  };
};

export default function Cart() {
  const { props } = usePage<PageProps>();
  const { cartItems, auth, flash } = props;

  const customer = auth?.customer;
  const [itemPendingRemoval, setItemPendingRemoval] = useState<CartItem | null>(null);
  const [errorMessage, setErrorMessage] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  useEffect(() => {
    const error = flash?.error;

    if (!error) return;

    const showTimer = window.setTimeout(() => {
      setErrorMessage(error ?? '');
    }, 0);

    const timer = window.setTimeout(() => setErrorMessage(''), 4000);

    return () => {
      window.clearTimeout(showTimer);
      window.clearTimeout(timer);
    };
  }, [flash?.error]);

  useEffect(() => {
    const success = flash?.success;

    if (!success) return;

    const showTimer = window.setTimeout(() => {
      setSuccessMessage(success ?? '');
    }, 0);

    const timer = window.setTimeout(() => setSuccessMessage(''), 3000);

    return () => {
      window.clearTimeout(showTimer);
      window.clearTimeout(timer);
    };
  }, [flash?.success]);

  if (!customer) {
    return null;
  }

  const openRemoveConfirmation = (item: CartItem) => {
    setItemPendingRemoval(item);
  };

  const closeRemoveConfirmation = () => {
    setItemPendingRemoval(null);
  };

  const confirmRemove = () => {
    if (!itemPendingRemoval) return;

    router.delete(`/cart/${itemPendingRemoval.cart_item_id}`, {
      preserveScroll: true,
      onFinish: () => {
        closeRemoveConfirmation();
      },
    });
  };

  const handleUpdateQuantity = (cartItemId: number, quantity: number) => {
    if (quantity < 1 || quantity > 99) return;

    router.patch(
      `/cart/${cartItemId}`,
      {
        quantity,
      },
      {
        preserveScroll: true,
      },
    );
  };

  return (
    <>
      <Head title="Cart" />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 className="text-3xl font-bold mb-8">Shopping Cart</h1>

      {errorMessage && (
        <div className="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {errorMessage}
        </div>
      )}

      {successMessage && (
        <div className="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
          {successMessage}
        </div>
      )}

      {cartItems.length === 0 ? (
        <div className="bg-white p-12 rounded-lg shadow-sm text-center">
          <p className="text-gray-600 mb-4">Your cart is empty.</p>
          <Link
            href="/catalog"
            className="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 inline-block transition-colors"
          >
            Browse Products
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div className="lg:col-span-2 space-y-4">
            {cartItems.map((item) => (
              <div key={item.cart_item_id} className="bg-white p-6 rounded-lg shadow-sm">
                <div className="flex items-start justify-between gap-4">
                  <div className="flex-1 min-w-0">
                    <h3 className="font-semibold text-lg mb-2 wrap-break-word">
                      {item.product?.product_name ?? `Product #${item.product_id}`}
                    </h3>

                    <div className="mb-3 space-y-1">
                      <p className="text-sm text-gray-600">
                        Custom design item
                      </p>

                      {(item.shirt_color_label || item.print_sides_label) && (
                        <div className="space-y-1 text-xs text-gray-500">
                          {item.shirt_color_label && (
                            <p>Shirt Color: {item.shirt_color_label}</p>
                          )}

                          {item.print_sides_label && (
                            <p>Print Sides: {item.print_sides_label}</p>
                          )}
                        </div>
                      )}
                    </div>

                    <div className="flex items-center space-x-4">
                      <div className="flex items-center border border-gray-300 rounded-md overflow-hidden">
                        <button
                          type="button"
                          onClick={() => handleUpdateQuantity(item.cart_item_id, item.quantity - 1)}
                          disabled={item.quantity <= 1}
                          className="p-2 hover:bg-gray-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                          aria-label={`Decrease quantity for ${item.product?.product_name ?? `product ${item.product_id}`}`}
                        >
                          <Minus className="w-4 h-4" />
                        </button>

                        <span className="px-4 py-2 border-x border-gray-300 min-w-13 text-center">
                          {item.quantity}
                        </span>

                        <button
                          type="button"
                          onClick={() => handleUpdateQuantity(item.cart_item_id, item.quantity + 1)}
                          disabled={item.quantity >= 99}
                          className="p-2 hover:bg-gray-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                          aria-label={`Increase quantity for ${item.product?.product_name ?? `product ${item.product_id}`}`}
                        >
                          <Plus className="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </div>

                  <div className="text-right shrink-0">
                    <button
                      type="button"
                      onClick={() => openRemoveConfirmation(item)}
                      className="inline-flex items-center text-sm text-red-600 hover:text-red-700 transition-colors cursor-pointer"
                    >
                      <Trash2 className="w-4 h-4 mr-1" />
                      Remove
                    </button>
                  </div>
                </div>

                {item.preview_image_reference && (
                  <div className="mt-4">
                    <img
                      src={item.preview_image_reference}
                      alt="Design preview"
                      className="w-32 h-32 object-cover rounded border border-gray-200"
                    />
                  </div>
                )}
              </div>
            ))}
          </div>

          <div className="lg:col-span-1">
            <div className="bg-white p-6 rounded-lg shadow-sm sticky top-24">
              <h2 className="text-xl font-semibold mb-4">Order Summary</h2>

              <div className="space-y-3 mb-6">
                <div className="flex justify-between text-gray-600">
                  <span>Items</span>
                  <span>{cartItems.length}</span>
                </div>

                <div className="text-sm text-gray-500">
                  Pricing and VAT will be calculated at checkout.
                </div>
              </div>

              <Link
                href="/checkout"
                className="block w-full bg-blue-600 text-white text-center py-3 rounded-md hover:bg-blue-700 transition-colors font-semibold mb-3"
              >
                Proceed to Checkout
              </Link>

              <Link
                href="/catalog"
                className="block w-full text-center text-blue-600 hover:underline"
              >
                Continue Shopping
              </Link>
            </div>
          </div>
        </div>
      )}

        <Modal
          isOpen={itemPendingRemoval !== null}
          onClose={closeRemoveConfirmation}
          title="Remove Item?"
          size="sm"
        >
          <div className="space-y-5">
            <div className="space-y-2">
              <p className="text-sm leading-6 text-gray-700">
                Are you sure you want to remove this item from your cart?
              </p>

              {itemPendingRemoval && (
                <div className="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                  <p className="text-sm font-medium text-gray-900 wrap-break-word">
                    {itemPendingRemoval.product?.product_name ?? `Product #${itemPendingRemoval.product_id}`}
                  </p>
                  <p className="text-xs text-gray-500 mt-1">
                    Quantity: {itemPendingRemoval.quantity}
                  </p>
                </div>
              )}

              <p className="text-xs text-gray-500">
                This action will remove the item from your current shopping cart.
              </p>
            </div>

            <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
              <button
                type="button"
                onClick={closeRemoveConfirmation}
                className="h-11 px-5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium cursor-pointer"
              >
                Cancel
              </button>

              <button
                type="button"
                onClick={confirmRemove}
                className="h-11 px-5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium cursor-pointer"
              >
                Remove Item
              </button>
            </div>
          </div>
        </Modal>
      </div>
    </>
  );
}