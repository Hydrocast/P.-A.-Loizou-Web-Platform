import { Link, router, usePage } from '@inertiajs/react';
import { Heart, Trash2 } from 'lucide-react';
import { useTimedFlash } from '@/hooks/useTimedFlash';

type WishlistProduct = {
  product_id: number;
  product_name: string;
  description: string | null;
  image_reference: string | null;
  display_price: string | number | null;
};

type WishlistItem = {
  wishlist_item_id: number;
  product_id: number;
  product_type: string;
  date_added: string | null;
  product: WishlistProduct | null;
};

type PageProps = {
  wishlist: WishlistItem[];
  flash?: {
    success?: string;
    error?: string;
  };
};

export default function Wishlist() {
  const { wishlist, flash } = usePage<PageProps>().props;
  const { visibleSuccess, visibleError } = useTimedFlash({
    success: flash?.success,
    error: flash?.error,
  });

  const removeItem = (wishlistItemId: number) => {
    router.delete(`/wishlist/${wishlistItemId}`);
  };

  const getProductLink = (item: WishlistItem) => {
    if (item.product_type !== 'standard' && item.product_type !== 'customizable') {
      return null;
    }

    return `/product/${item.product_type}/${item.product_id}`;
  };

  return (
    <div className="rounded-lg bg-white p-4 shadow-sm sm:p-5 md:p-6">
      <h2 className="mb-5 text-xl font-semibold sm:text-2xl md:mb-6">My Wishlist</h2>

      {visibleSuccess && (
        <div className="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-800">
          {visibleSuccess}
        </div>
      )}

      {visibleError && (
        <div className="mb-4 rounded-md bg-red-100 px-4 py-3 text-sm text-red-800">
          {visibleError}
        </div>
      )}

      {wishlist.length === 0 ? (
        <div className="py-10 text-center sm:py-12">
          <Heart className="mx-auto mb-4 h-10 w-10 text-gray-400 sm:h-12 sm:w-12" />
          <p className="text-sm text-gray-600 sm:text-base">Your wishlist is empty.</p>
          <Link href="/catalog" className="mt-4 inline-block text-sm text-blue-600 hover:underline sm:text-base">
            Browse products
          </Link>
        </div>
      ) : (
        <div className="space-y-4">
          {wishlist.map((item) => {
            const productLink = getProductLink(item);
            const productTitle = item.product?.product_name ?? `Product #${item.product_id}`;

            return (
              <div
                key={item.wishlist_item_id}
                className="rounded-lg border p-4 transition-shadow hover:shadow-md"
              >
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                  <div className="min-w-0 flex-1">
                    {productLink ? (
                      <Link
                        href={productLink}
                        className="mb-1 inline-block font-semibold text-gray-900 transition-colors hover:text-purple-700 hover:underline wrap-break-word"
                      >
                        {productTitle}
                      </Link>
                    ) : (
                      <h3 className="mb-1 font-semibold wrap-break-word">{productTitle}</h3>
                    )}

                    <p className="mb-2 text-sm text-gray-600 wrap-break-word">
                      {item.product?.description ?? 'No description available.'}
                    </p>

                    <div className="flex flex-wrap items-center gap-2 sm:gap-3">
                      <span
                        className={`rounded px-2 py-1 text-xs ${
                          item.product_type === 'customizable'
                            ? 'bg-blue-100 text-blue-800'
                            : 'bg-gray-100 text-gray-800'
                        }`}
                      >
                        {item.product_type === 'customizable' ? 'Customizable' : 'Standard'}
                      </span>

                      {item.product?.display_price !== null &&
                        item.product?.display_price !== undefined && (
                          <span className="font-semibold text-blue-600">
                            €{Number(item.product.display_price).toFixed(2)}
                          </span>
                        )}
                    </div>
                  </div>

                  <button
                    onClick={() => removeItem(item.wishlist_item_id)}
                    className="inline-flex w-full cursor-pointer items-center justify-center rounded-md border border-red-200 px-3 py-2 text-red-600 hover:bg-red-50 sm:ml-4 sm:w-auto sm:shrink-0"
                    title="Remove from wishlist"
                  >
                    <Trash2 className="h-5 w-5 sm:h-5 sm:w-5" />
                    <span className="ml-2 text-sm sm:hidden">Remove</span>
                  </button>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}