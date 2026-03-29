import { Head, Link, router, usePage } from '@inertiajs/react';
import { Search, Heart, ChevronDown, ArrowUp } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import shoppingBagImage from '../../assets/shopping-bag.png';

type Product = {
  product_id?: number;
  standard_product_id?: number;
  customizable_product_id?: number;
  product_name: string;
  description: string | null;
  image_reference?: string | null;
  image_url?: string | null;
  display_price?: number | string | null;
  category_id?: number | null;
  in_wishlist?: boolean;
  wishlist_item_id?: number | null;
};

type Category = {
  category_id: number;
  category_name: string;
};

type Filters = {
  category_id?: number | null;
  product_type?: string | null;
  sort_order?: string | null;
  query?: string | null;
  page?: number | null;
  per_page?: number | null;
};

type Pagination = {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
};

type PageProps = {
  auth?: {
    customer?: {
      customer_id: number;
      full_name: string;
      email: string;
    } | null;
  };
};

type Props = {
  products: Product[];
  categories: Category[];
  filters: Filters;
  pagination: Pagination;
};

export default function ProductCatalog({
  products,
  categories,
  filters,
  pagination,
}: Props) {
  const { auth } = usePage<PageProps>().props;

  const [showBackToTop, setShowBackToTop] = useState(false);
  const [searchQuery, setSearchQuery] = useState(filters.query ?? '');
  const [selectedCategory, setSelectedCategory] = useState<number | null>(
    filters.category_id ?? null,
  );
  const [productType, setProductType] = useState<'all' | 'standard' | 'customizable'>(
    filters.product_type === 'standard' || filters.product_type === 'customizable'
      ? filters.product_type
      : 'all',
  );
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>(
    filters.sort_order === 'desc' ? 'desc' : 'asc',
  );
  const [perPage, setPerPage] = useState<12 | 24 | 36 | 100>(
    filters.per_page === 24 || filters.per_page === 36 || filters.per_page === 100
      ? filters.per_page
      : 12,
  );

  const firstSearchRender = useRef(true);
  const latestFiltersRef = useRef({
    selectedCategory,
    productType,
    sortOrder,
    perPage,
  });

  useEffect(() => {
    const handleScroll = () => {
      setShowBackToTop(window.scrollY > 400);
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    latestFiltersRef.current = {
      selectedCategory,
      productType,
      sortOrder,
      perPage,
    };
  }, [selectedCategory, productType, sortOrder, perPage]);

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const navigateWithFilters = (
    nextQuery: string,
    nextCategory: number | null,
    nextProductType: 'all' | 'standard' | 'customizable',
    nextSortOrder: 'asc' | 'desc',
    nextPage: number,
    nextPerPage: 12 | 24 | 36 | 100,
  ) => {
    router.get(
      '/catalog',
      {
        query: nextQuery.trim() || undefined,
        category_id: nextCategory ?? undefined,
        product_type: nextProductType === 'all' ? undefined : nextProductType,
        sort_order: nextSortOrder,
        page: nextPage,
        per_page: nextPerPage,
      },
      { preserveState: true, preserveScroll: true, replace: true },
    );
  };

  useEffect(() => {
    if (firstSearchRender.current) {
      firstSearchRender.current = false;
      return;
    }

    const timeout = setTimeout(() => {
      navigateWithFilters(
        searchQuery,
        latestFiltersRef.current.selectedCategory,
        latestFiltersRef.current.productType,
        latestFiltersRef.current.sortOrder,
        1,
        latestFiltersRef.current.perPage,
      );
    }, 300);

    return () => clearTimeout(timeout);
  }, [searchQuery]);

  const handleCategoryChange = (value: string) => {
    const nextCategory = value ? Number(value) : null;
    setSelectedCategory(nextCategory);
    navigateWithFilters(searchQuery, nextCategory, productType, sortOrder, 1, perPage);
  };

  const handleProductTypeChange = (value: 'all' | 'standard' | 'customizable') => {
    const nextCategory = value === 'customizable' ? null : selectedCategory;
    setProductType(value);
    setSelectedCategory(nextCategory);
    navigateWithFilters(searchQuery, nextCategory, value, sortOrder, 1, perPage);
  };

  const handleSortOrderChange = (value: 'asc' | 'desc') => {
    setSortOrder(value);
    navigateWithFilters(searchQuery, selectedCategory, productType, value, 1, perPage);
  };

  const handlePerPageChange = (value: '12' | '24' | '36' | '100') => {
    const nextPerPage = Number(value) as 12 | 24 | 36 | 100;
    setPerPage(nextPerPage);
    navigateWithFilters(searchQuery, selectedCategory, productType, sortOrder, 1, nextPerPage);
  };

  const handlePageChange = (value: string) => {
    const nextPage = Number(value);
    if (!Number.isInteger(nextPage) || nextPage < 1 || nextPage > pagination.last_page) {
      return;
    }

    navigateWithFilters(
      searchQuery,
      selectedCategory,
      productType,
      sortOrder,
      nextPage,
      perPage,
    );
  };

  const getProductType = (product: Product) =>
    product.standard_product_id !== null && product.standard_product_id !== undefined
      ? 'standard'
      : 'customizable';

  const getProductId = (product: Product) =>
    product.standard_product_id ?? product.customizable_product_id ?? product.product_id;

  const getPriceLabel = (product: Product): string | null => {
    if (getProductType(product) === 'standard') {
      if (product.display_price === null || product.display_price === undefined) {
        return null;
      }
      return `€${Number(product.display_price).toFixed(2)}`;
    }
    return 'Customizable';
  };

  const handleWishlistToggle = (
    e: React.MouseEvent<HTMLButtonElement>,
    product: Product,
  ) => {
    e.preventDefault();
    e.stopPropagation();

    if (!auth?.customer) {
      router.visit('/login');
      return;
    }

    const productId = getProductId(product);
    const productTypeValue = getProductType(product);

    if (!productId) return;

    const refreshFilters = {
      query: searchQuery.trim() || undefined,
      category_id: selectedCategory ?? undefined,
      product_type: productType === 'all' ? undefined : productType,
      sort_order: sortOrder,
      page: pagination.current_page,
      per_page: perPage,
    };

    if (product.in_wishlist && product.wishlist_item_id) {
      router.delete(`/wishlist/${product.wishlist_item_id}`, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () =>
          router.get(window.location.pathname, refreshFilters, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
          }),
      });
      return;
    }

    router.post('/wishlist', { product_id: productId, product_type: productTypeValue }, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () =>
        router.get(window.location.pathname, refreshFilters, {
          preserveScroll: true,
          preserveState: true,
          replace: true,
        }),
    });
  };

  const isCategoryDisabled = productType === 'customizable';

  return (
    <>
      <Head title="Catalog" />

      <div
        className="relative min-h-screen overflow-hidden"
        style={{
          backgroundColor: '#FAF8F5',
          backgroundImage: `
            radial-gradient(ellipse 80% 60% at -10% 0%,   rgba(109, 40, 217, 0.09) 0%, transparent 70%),
            radial-gradient(ellipse 58% 44% at 68% -6%,   rgba(79, 70, 229, 0.08) 0%, transparent 60%),
            radial-gradient(ellipse 38% 30% at 104% 8%,   rgba(96, 165, 250, 0.07) 0%, transparent 56%),
            radial-gradient(ellipse 62% 40% at 34% 52%,   rgba(147, 197, 253, 0.06) 0%, transparent 64%),
            radial-gradient(ellipse 70% 55% at 110% 100%, rgba(251, 146, 60, 0.06) 0%, transparent 65%),
            radial-gradient(ellipse 92% 42% at 52% 72%,   rgba(191, 219, 254, 0.06) 0%, transparent 72%)
          `,
        }}
      >
        <div className="relative z-10 mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8 lg:px-10 xl:px-12">
          <div className="mb-2 flex items-center gap-3 sm:mb-4 sm:gap-4">
            <h1 className="bg-linear-to-r from-purple-700 via-fuchsia-500 to-orange-500 bg-clip-text text-4xl font-black leading-tight text-transparent sm:text-[2.65rem] md:text-5xl">
              Product Catalog
            </h1>

            <img
              src={shoppingBagImage}
              alt=""
              aria-hidden="true"
              className="ml-1 h-16 w-16 rotate-12 object-contain sm:ml-2 sm:h-20 sm:w-20 md:ml-3 md:h-24 md:w-24"
            />
          </div>

          <div className="mb-8 rounded-lg border border-white/70 bg-white/90 p-4 shadow-sm backdrop-blur-[1.5px] sm:p-5 md:p-6">
            <div className="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 md:grid-cols-12">
              <div className="md:col-span-4">
                <label className="mb-1 block text-sm font-medium text-gray-700">Search</label>
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                  <input
                    type="text"
                    placeholder="Search products..."
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="h-11 w-full rounded-md border border-gray-300 pl-10 pr-4 text-sm focus:ring-2 focus:ring-purple-500"
                    maxLength={50}
                  />
                </div>
              </div>

              <div className="md:col-span-3">
                <label className="mb-1 block text-sm font-medium text-gray-700">Category</label>
                <select
                  value={selectedCategory ?? ''}
                  onChange={(e) => handleCategoryChange(e.target.value)}
                  disabled={isCategoryDisabled}
                  className={`h-11 w-full cursor-pointer rounded-md border border-gray-300 px-4 text-sm focus:ring-2 focus:ring-purple-500 ${
                    isCategoryDisabled ? 'cursor-not-allowed bg-gray-100 text-gray-400' : ''
                  }`}
                >
                  <option value="">All Categories</option>
                  {categories.map((cat) => (
                    <option key={cat.category_id} value={cat.category_id}>
                      {cat.category_name}
                    </option>
                  ))}
                </select>
              </div>

              <div className="md:col-span-2">
                <label className="mb-1 block text-sm font-medium text-gray-700">Type</label>
                <select
                  value={productType}
                  onChange={(e) =>
                    handleProductTypeChange(
                      e.target.value as 'all' | 'standard' | 'customizable',
                    )
                  }
                  className="h-11 w-full cursor-pointer rounded-md border border-gray-300 px-4 text-sm focus:ring-2 focus:ring-purple-500"
                >
                  <option value="all">All Types</option>
                  <option value="standard">Standard</option>
                  <option value="customizable">Customizable</option>
                </select>
              </div>

              <div className="md:col-span-3">
                <label className="mb-1 block text-sm font-medium text-gray-700">Sort By</label>
                <select
                  value={sortOrder}
                  onChange={(e) => handleSortOrderChange(e.target.value as 'asc' | 'desc')}
                  className="h-11 w-full cursor-pointer rounded-md border border-gray-300 px-4 text-sm focus:ring-2 focus:ring-purple-500"
                >
                  <option value="asc">Price: Low to High</option>
                  <option value="desc">Price: High to Low</option>
                </select>
              </div>
            </div>

            <div className="mt-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <p className="text-gray-600">{pagination.total} products found</p>

              <div className="flex flex-col gap-3 text-sm text-gray-700 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end sm:gap-4">
                <div className="flex flex-wrap items-center gap-2">
                  <span className="font-medium text-gray-600">Page</span>
                  <div className="relative">
                    <select
                      value={pagination.current_page}
                      onChange={(e) => handlePageChange(e.target.value)}
                      className="appearance-none cursor-pointer rounded-md border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm focus:ring-2 focus:ring-purple-500"
                    >
                      {Array.from({ length: pagination.last_page }, (_, index) => {
                        const pageNumber = index + 1;
                        return (
                          <option key={pageNumber} value={pageNumber}>
                            {pageNumber}
                          </option>
                        );
                      })}
                    </select>
                    <ChevronDown className="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
                  </div>
                  <span className="text-gray-500">of {pagination.last_page}</span>
                </div>

                <div className="flex flex-wrap items-center gap-2">
                  <span className="font-medium text-gray-600">Per page</span>
                  <div className="relative">
                    <select
                      value={perPage}
                      onChange={(e) =>
                        handlePerPageChange(e.target.value as '12' | '24' | '36' | '100')
                      }
                      className="appearance-none cursor-pointer rounded-md border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm focus:ring-2 focus:ring-purple-500"
                    >
                      <option value="12">12</option>
                      <option value="24">24</option>
                      <option value="36">36</option>
                      <option value="100">100</option>
                    </select>
                    <ChevronDown className="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          {products.length > 0 ? (
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3 xl:grid-cols-4 xl:gap-6">
              {products.map((product) => {
                const productTypeValue = getProductType(product);
                const productId = getProductId(product);
                const priceLabel = getPriceLabel(product);

                return (
                  <Link
                    key={`${productTypeValue}-${productId}`}
                    href={`/product/${productTypeValue}/${productId}`}
                    className="group relative cursor-pointer overflow-hidden rounded-lg bg-white/92 shadow-sm transition-all backdrop-blur-[1px] hover:shadow-lg"
                  >
                    <button
                      type="button"
                      onClick={(e) => handleWishlistToggle(e, product)}
                      className={`absolute right-2 top-2 z-10 cursor-pointer rounded-full p-1.5 shadow-sm transition-colors sm:right-3 sm:top-3 sm:p-2 ${
                        product.in_wishlist
                          ? 'bg-red-100 text-red-600 hover:bg-red-200'
                          : 'bg-gray-100 text-gray-600 hover:bg-red-200 hover:text-red-600'
                      }`}
                      title={product.in_wishlist ? 'Remove from wishlist' : 'Add to wishlist'}
                    >
                      <Heart className={`h-4 w-4 sm:h-5 sm:w-5 ${product.in_wishlist ? 'fill-current' : ''}`} />
                    </button>

                    <div className="flex h-44 w-full items-center justify-center overflow-hidden bg-gray-100 p-3 sm:h-48 lg:h-52">
                      {product.image_url ? (
                        <img
                          src={product.image_url}
                          alt={product.product_name}
                          loading="lazy"
                          className="h-full w-full object-contain transition-opacity group-hover:opacity-90"
                        />
                      ) : (
                        <span className="text-sm text-gray-400">No image</span>
                      )}
                    </div>

                    <div className="p-3 transition-colors group-hover:bg-purple-50 sm:p-3.5">
                      <div className="mb-1.5 flex items-start justify-between gap-2 pr-5 sm:pr-6">
                        <h3 className="min-h-10 min-w-0 flex-1 line-clamp-2 text-sm font-semibold leading-5 transition-colors group-hover:text-purple-700 sm:text-base">
                          {product.product_name}
                        </h3>

                        <span
                          className={`shrink-0 self-start whitespace-nowrap rounded px-1.5 py-1 text-[10px] sm:text-[11px] ${
                            productTypeValue === 'customizable'
                              ? 'bg-blue-100 text-blue-800'
                              : 'bg-gray-100 text-gray-800'
                          }`}
                        >
                          {productTypeValue === 'customizable' ? 'Customizable' : 'Standard'}
                        </span>
                      </div>

                      <p className="mb-2 min-h-10 line-clamp-2 text-sm leading-5 text-gray-600 sm:mb-2.5">
                        {product.description ?? 'No description available.'}
                      </p>

                      {priceLabel && (
                        <p className="font-bold text-purple-600">{priceLabel}</p>
                      )}
                    </div>
                  </Link>
                );
              })}
            </div>
          ) : (
            <div className="py-12 text-center">
              <p className="text-gray-600">No products match your search criteria.</p>
            </div>
          )}
        </div>

        {showBackToTop && (
          <button
            onClick={scrollToTop}
            className="fixed bottom-4 right-4 z-50 cursor-pointer rounded-full bg-purple-600 p-2.5 text-white shadow-lg transition-all duration-300 hover:bg-purple-700 sm:bottom-6 sm:right-6 sm:p-3"
            aria-label="Back to top"
          >
            <ArrowUp className="h-4 w-4 sm:h-5 sm:w-5" />
          </button>
        )}
      </div>
    </>
  );
}