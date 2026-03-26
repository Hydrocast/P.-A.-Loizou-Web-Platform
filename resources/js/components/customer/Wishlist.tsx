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
    <div className="bg-white rounded-lg shadow-sm p-6">
      <h2 className="text-2xl font-semibold mb-6">My Wishlist</h2>

      {visibleSuccess && (
        <div className="mb-4 p-3 rounded-md bg-green-100 text-green-800 text-sm">
          {visibleSuccess}
        </div>
      )}

      {visibleError && (
        <div className="mb-4 p-3 rounded-md bg-red-100 text-red-800 text-sm">
          {visibleError}
        </div>
      )}

      {wishlist.length === 0 ? (
        <div className="text-center py-12">
          <Heart className="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <p className="text-gray-600">Your wishlist is empty.</p>
          <Link href="/catalog" className="mt-4 inline-block text-blue-600 hover:underline">
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
                className="flex items-start justify-between border rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div className="flex-1">
                  {productLink ? (
                    <Link
                      href={productLink}
                      className="font-semibold mb-1 inline-block text-gray-900 hover:text-purple-700 hover:underline transition-colors"
                    >
                      {productTitle}
                    </Link>
                  ) : (
                    <h3 className="font-semibold mb-1">{productTitle}</h3>
                  )}

                  <p className="text-sm text-gray-600 mb-2">
                    {item.product?.description ?? 'No description available.'}
                  </p>

                  <div className="flex items-center space-x-3">
                    <span
                      className={`text-xs px-2 py-1 rounded ${
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
                  className="cursor-pointer ml-4 p-2 text-red-600 hover:bg-red-50 rounded-md"
                  title="Remove from wishlist"
                >
                  <Trash2 className="w-5 h-5" />
                </button>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}