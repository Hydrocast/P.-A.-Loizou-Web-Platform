import { Head, Link, router, usePage } from '@inertiajs/react';
import { Heart, Package } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

type ProductDetails = {
  type: 'size_guide' | 'specifications';
  title?: string;
  columns?: string[];
  rows?: string[][];
};

type Product = {
  product_id: number;
  product_name: string;
  description: string | null;
  image_reference?: string | null;
  image_url?: string | null;
  display_price?: number | string | null;
  category_id?: number | null;
};

type Category = {
  category_id: number;
  category_name: string;
};

type PricingTier = {
  pricing_tier_id?: number;
  minimum_quantity: number;
  maximum_quantity: number;
  unit_price: number | string;
};

type ColorOption = {
  id: string;
  label: string;
  swatch_hex: string;
  image_url: string;
  thumbnail_url?: string | null;
};

type SizeOption = {
  value: string;
  label: string;
};

type PageProps = {
  product: Product;
  type: 'standard' | 'customizable';
  inWishlist: boolean;
  wishlistItemId: number | null;
  category: Category | null;
  pricingTiers: PricingTier[];
  colorOptions: ColorOption[];
  sizeOptions: SizeOption[];
  productDetails: ProductDetails | null;
  auth?: {
    customer?: {
      customer_id: number;
      full_name: string;
      email: string;
    } | null;
  };
};

export default function ProductDetail() {
  const {
    product,
    type,
    inWishlist,
    wishlistItemId,
    category,
    pricingTiers,
    colorOptions,
    sizeOptions,
    productDetails,
    auth,
  } = usePage<PageProps>().props;

  const customer = auth?.customer ?? null;
  const messageTimeoutRef = useRef<number | null>(null);

  const [isInWishlist, setIsInWishlist] = useState(inWishlist);
  const [currentWishlistItemId, setCurrentWishlistItemId] = useState<number | null>(wishlistItemId);
  const [showMessage, setShowMessage] = useState('');
  const [selectedColorId, setSelectedColorId] = useState<string | null>(null);
  const [selectedSizeValue, setSelectedSizeValue] = useState<string>('');

  useEffect(() => {
    return () => {
      if (messageTimeoutRef.current !== null) {
        window.clearTimeout(messageTimeoutRef.current);
      }
    };
  }, []);

  const flashMessage = (message: string) => {
    setShowMessage(message);

    if (messageTimeoutRef.current !== null) {
      window.clearTimeout(messageTimeoutRef.current);
    }

    messageTimeoutRef.current = window.setTimeout(() => {
      setShowMessage('');
      messageTimeoutRef.current = null;
    }, 3000);
  };

  const effectiveSelectedColorId =
    selectedColorId &&
    colorOptions.some((option) => option.id === selectedColorId)
      ? selectedColorId
      : (colorOptions[0]?.id ?? null);

  const effectiveSelectedSizeValue =
    selectedSizeValue !== '' &&
    sizeOptions.some((option) => option.value === selectedSizeValue)
      ? selectedSizeValue
      : (sizeOptions[0]?.value ?? '');

  const selectedColor =
    colorOptions.find((option) => option.id === effectiveSelectedColorId) ??
    colorOptions[0] ??
    null;

  const displayedProductImage =
    selectedColor?.image_url ?? product.image_url ?? null;

  const handleAddToWishlist = () => {
    if (!customer) {
      router.visit('/login');
      return;
    }

    if (isInWishlist && currentWishlistItemId) {
      router.delete(`/wishlist/${currentWishlistItemId}`, {
        preserveScroll: true,
        onSuccess: () => {
          setIsInWishlist(false);
          setCurrentWishlistItemId(null);
          flashMessage('Removed from wishlist');
        },
      });

      return;
    }

    router.post(
      '/wishlist',
      {
        product_id: product.product_id,
        product_type: type,
      },
      {
        preserveScroll: true,
        onSuccess: (page) => {
          setIsInWishlist(true);
          setCurrentWishlistItemId(page.props.wishlistItemId as number | null);
          flashMessage('Added to wishlist');
        },
      },
    );
  };

  const handleCustomize = () => {
    if (!customer) {
      const returnTo = window.location.pathname + window.location.search;

      router.visit('/login', {
        data: { redirect: returnTo },
      });
      return;
    }

    const params = new URLSearchParams();

    if (effectiveSelectedColorId) {
      params.set('color', effectiveSelectedColorId);
    }

    if (effectiveSelectedSizeValue) {
      params.set('size', effectiveSelectedSizeValue);
    }

    const queryString = params.toString();
    const suffix = queryString ? `?${queryString}` : '';

    router.visit(`/design/${product.product_id}${suffix}`);
  };

  return (
    <>
      <Head title={product.product_name} />

      <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        {showMessage && (
          <div className="mb-4 rounded-md bg-green-100 p-4 text-green-800">
            {showMessage}
          </div>
        )}

        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-8">
          <div>
            <div className="flex h-72 w-full items-center justify-center overflow-hidden rounded-lg bg-gray-100 p-4 shadow-md sm:h-80 md:h-96">
              {displayedProductImage ? (
                <img
                  src={displayedProductImage}
                  alt={selectedColor ? `${product.product_name} - ${selectedColor.label}` : product.product_name}
                  className="h-full w-full object-contain"
                />
              ) : (
                <span className="text-gray-400">No image available</span>
              )}
            </div>

            {type === 'customizable' && colorOptions.length > 0 && (
              <div className="mt-4">
                <h3 className="mb-2 text-sm font-semibold text-gray-900">Color</h3>

                {selectedColor && (
                  <p className="mb-3 text-sm text-gray-600">
                    {selectedColor.label}
                  </p>
                )}

                <div className="grid grid-cols-4 gap-2 sm:grid-cols-5 md:grid-cols-4 lg:grid-cols-5">
                  {colorOptions.map((option) => {
                    const isSelected = option.id === effectiveSelectedColorId;

                    return (
                      <button
                        key={option.id}
                        type="button"
                        onClick={() => setSelectedColorId(option.id)}
                        className={`flex cursor-pointer items-center justify-center rounded-md border bg-gray-100 p-1 transition hover:bg-gray-200 ${
                          isSelected
                            ? 'border-gray-500 ring-2 ring-gray-300'
                            : 'border-gray-200'
                        }`}
                        title={option.label}
                        aria-label={`Select ${option.label} color`}
                      >
                        {option.thumbnail_url ? (
                          <img
                            src={option.thumbnail_url}
                            alt={option.label}
                            className="h-8 w-8 object-contain"
                          />
                        ) : (
                          <div
                            className="h-8 w-8 rounded-full border border-gray-300"
                            style={{ backgroundColor: option.swatch_hex }}
                          />
                        )}
                      </button>
                    );
                  })}
                </div>
              </div>
            )}
          </div>

          <div>
            <div className="mb-4 flex items-start gap-3">
              <div className="min-w-0 flex-1 pr-2">
                <h1 className="mb-2 wrap-break-word text-2xl font-bold leading-tight sm:text-3xl">
                  {product.product_name}
                </h1>

                {category && (
                  <span className="text-sm text-gray-600">
                    Category: {category.category_name}
                  </span>
                )}
              </div>

              <button
                onClick={handleAddToWishlist}
                className={`shrink-0 rounded-full p-1.5 sm:p-2 ${
                  isInWishlist ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'
                } hover:bg-red-200`}
                title={isInWishlist ? 'Remove from wishlist' : 'Add to wishlist'}
              >
                <Heart className={`h-5 w-5 cursor-pointer sm:h-6 sm:w-6 ${isInWishlist ? 'fill-current' : ''}`} />
              </button>
            </div>

            <p className="mb-5 whitespace-pre-line text-sm leading-6 text-gray-700">
              {product.description ?? 'No description available.'}
            </p>

            {type === 'customizable' &&
              productDetails?.type === 'size_guide' &&
              productDetails.columns?.length === 3 &&
              productDetails.rows &&
              productDetails.rows.length > 0 && (
                <div className="mb-5">
                  <h3 className="mb-2 text-sm font-semibold text-gray-900">
                    {productDetails.title ?? 'Size Guide'}
                  </h3>

                  <div className="overflow-x-auto rounded-md border border-gray-200">
                    <table className="min-w-full divide-y divide-gray-200 text-xs">
                      <thead className="bg-gray-50">
                        <tr>
                          {productDetails.columns.map((column) => (
                            <th
                              key={column}
                              className="px-2 py-2 text-left font-semibold text-gray-700 sm:px-3"
                            >
                              {column}
                            </th>
                          ))}
                        </tr>
                      </thead>

                      <tbody className="divide-y divide-gray-200 bg-white">
                        {productDetails.rows.map((row, index) => (
                          <tr key={`${row.join('-')}-${index}`}>
                            <td className="px-2 py-2 font-medium text-gray-900 sm:px-3">
                              {row[0] ?? ''}
                            </td>
                            <td className="px-2 py-2 text-gray-700 sm:px-3">
                              {row[1] ?? ''}
                            </td>
                            <td className="px-2 py-2 text-gray-700 sm:px-3">
                              {row[2] ?? ''}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}

            {type === 'customizable' && sizeOptions.length > 0 && (
              <div className="mb-5">
                <h3 className="mb-2 text-sm font-semibold text-gray-900">Size</h3>

                <div className="max-w-xs">
                  <select
                    value={effectiveSelectedSizeValue}
                    onChange={(e) => setSelectedSizeValue(e.target.value)}
                    className="h-10 w-full cursor-pointer rounded-md border border-gray-300 bg-white px-3 text-sm text-gray-700"
                    aria-label="Select size"
                  >
                    {sizeOptions.map((option) => (
                      <option key={option.value} value={option.value}>
                        {option.label}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
            )}

            {type === 'standard' ? (
              <div className="mb-6">
                <div className="rounded-lg bg-blue-50 p-4">
                  {product.display_price !== null && product.display_price !== undefined ? (
                    <p className="text-2xl font-bold text-blue-600">
                      €{Number(product.display_price).toFixed(2)}
                    </p>
                  ) : null}
                  <p className="mt-1 text-sm text-gray-600">
                    {product.display_price !== null && product.display_price !== undefined
                      ? 'Display price (reference only)'
                      : 'No display price available'}
                  </p>
                </div>

                <div className="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                  <div className="flex items-start">
                    <Package className="mr-2 mt-0.5 h-5 w-5 shrink-0 text-yellow-600" />
                    <p className="text-sm text-yellow-800">
                      This is a standard reference product. For ordering, please contact us directly
                      or explore our customizable products.
                    </p>
                  </div>
                </div>
              </div>
            ) : (
              <div className="mb-5">
                <h3 className="mb-2 text-sm font-semibold text-gray-900">Pricing</h3>

                <div className="space-y-1.5">
                  {pricingTiers.length > 0 ? (
                    pricingTiers.map((tier, index) => (
                      <div
                        key={tier.pricing_tier_id ?? index}
                        className="flex flex-col gap-1 rounded-md bg-gray-50 px-3 py-2 text-sm sm:flex-row sm:items-center sm:justify-between"
                      >
                        <span className="text-gray-700 wrap-break-word">
                          {tier.minimum_quantity} - {tier.maximum_quantity} items
                        </span>
                        <span className="font-semibold text-blue-600">
                          €{Number(tier.unit_price).toFixed(2)} each
                        </span>
                      </div>
                    ))
                  ) : (
                    <div className="rounded-md bg-gray-50 px-3 py-2 text-sm text-gray-600">
                      Pricing tiers are not available yet.
                    </div>
                  )}
                </div>

                <p className="mt-2 text-xs leading-5 text-gray-600">
                  * Prices include VAT. Final price depends on quantity ordered.
                </p>
              </div>
            )}

            {type === 'customizable' && (
              <button
                onClick={handleCustomize}
                className="w-full cursor-pointer rounded-md bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700"
              >
                Start Designing
              </button>
            )}

            <Link href="/catalog" className="mt-4 block text-center text-blue-600 hover:underline sm:mt-5">
              ← Back to Catalog
            </Link>
          </div>
        </div>
      </div>
    </>
  );
}