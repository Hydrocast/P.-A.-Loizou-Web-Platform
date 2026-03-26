import { router } from '@inertiajs/react';
import { Plus, Edit, Eye, EyeOff, Search } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import type { RefObject } from 'react';
import Modal, { ConfirmDialog } from '@/components/public/Modal';
import { useTimedFlash } from '@/hooks/useTimedFlash';

interface Category {
  categoryId: number;
  categoryName: string;
}

interface Product {
  productId: number;
  productName: string;
  description: string;
  type: 'Standard' | 'Customizable';
  categoryId?: number | null;
  displayPrice: number | null;
  visibilityStatus: 'Active' | 'Inactive';
  imageReference?: string | null;
  imageUrl?: string | null;
}

type Flash = {
  success?: string;
  error?: string;
};

type ProductFilters = {
  query?: string | null;
  product_type?: string | null;
  category_id?: number | null;
  visibility_status?: string | null;
};

type StaffProductsProps = {
  products?: Product[];
  categories?: Category[];
  filters?: ProductFilters;
  flash?: Flash;
};

export default function StaffProducts({
  products: incomingProducts = [],
  categories = [],
  filters = {},
  flash = {},
}: StaffProductsProps) {
  const { visibleSuccess, visibleError } = useTimedFlash({
    success: flash.success,
    error: flash.error,
  });

  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
  const [isVisibilityConfirmOpen, setIsVisibilityConfirmOpen] = useState(false);
  const [productToToggle, setProductToToggle] = useState<Product | null>(null);

  const [searchQuery, setSearchQuery] = useState(filters.query ?? '');
  const [selectedType, setSelectedType] = useState<'all' | 'standard' | 'customizable'>(
    filters.product_type === 'standard' || filters.product_type === 'customizable'
      ? filters.product_type
      : 'all',
  );
  const [selectedCategory, setSelectedCategory] = useState<number | null>(
    filters.category_id ?? null,
  );
  const [selectedStatus, setSelectedStatus] = useState<'all' | 'Active' | 'Inactive'>(
    filters.visibility_status === 'Active' || filters.visibility_status === 'Inactive'
      ? filters.visibility_status
      : 'all',
  );

  const firstSearchRender = useRef(true);
  const latestFiltersRef = useRef({
    selectedType:
      filters.product_type === 'standard' || filters.product_type === 'customizable'
        ? filters.product_type
        : ('all' as 'all' | 'standard' | 'customizable'),
    selectedCategory: filters.category_id ?? null,
    selectedStatus:
      filters.visibility_status === 'Active' || filters.visibility_status === 'Inactive'
        ? filters.visibility_status
        : ('all' as 'all' | 'Active' | 'Inactive'),
  });
  const addImageInputRef = useRef<HTMLInputElement | null>(null);
  const editImageInputRef = useRef<HTMLInputElement | null>(null);

  const [formData, setFormData] = useState({
    productName: '',
    description: '',
    type: 'Standard' as 'Standard' | 'Customizable',
    categoryId: 0,
    displayPrice: '',
  });

  const [selectedImage, setSelectedImage] = useState<File | null>(null);
  const [removeImage, setRemoveImage] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const navigateWithFilters = useCallback(
    (
      nextQuery: string,
      nextType: 'all' | 'standard' | 'customizable',
      nextCategory: number | null,
      nextStatus: 'all' | 'Active' | 'Inactive',
    ) => {
      router.get(
        '/staff/products',
        {
          query: nextQuery.trim() || undefined,
          product_type: nextType === 'all' ? undefined : nextType,
          category_id: nextCategory ?? undefined,
          visibility_status: nextStatus === 'all' ? undefined : nextStatus,
        },
        {
          preserveScroll: true,
          preserveState: true,
          replace: true,
        },
      );
    },
    [],
  );

  useEffect(() => {
    latestFiltersRef.current = {
      selectedType,
      selectedCategory,
      selectedStatus,
    };
  }, [selectedType, selectedCategory, selectedStatus]);

  useEffect(() => {
    if (firstSearchRender.current) {
      firstSearchRender.current = false;
      return;
    }

    const timeout = setTimeout(() => {
      const {
        selectedType: latestType,
        selectedCategory: latestCategory,
        selectedStatus: latestStatus,
      } = latestFiltersRef.current;

      navigateWithFilters(searchQuery, latestType, latestCategory, latestStatus);
    }, 300);

    return () => clearTimeout(timeout);
  }, [searchQuery, navigateWithFilters]);

  const handleTypeChange = (value: 'all' | 'standard' | 'customizable') => {
    setSelectedType(value);

    const nextCategory =
      value === 'customizable' && selectedCategory !== null ? null : selectedCategory;

    if (value === 'customizable' && selectedCategory !== null) {
      setSelectedCategory(null);
    }

    navigateWithFilters(searchQuery, value, nextCategory, selectedStatus);
  };

  const handleCategoryChange = (value: string) => {
    const nextCategory = value ? Number(value) : null;
    setSelectedCategory(nextCategory);
    navigateWithFilters(searchQuery, selectedType, nextCategory, selectedStatus);
  };

  const handleStatusChange = (value: 'all' | 'Active' | 'Inactive') => {
    setSelectedStatus(value);
    navigateWithFilters(searchQuery, selectedType, selectedCategory, value);
  };

  const handleImageSelection = (file: File | null) => {
    setSelectedImage(file);

    if (file) {
      setRemoveImage(false);
    }
  };

  const validateForm = () => {
    const newErrors: Record<string, string> = {};

    if (!formData.productName || formData.productName.length < 2 || formData.productName.length > 100) {
      newErrors.productName = 'Product name must be between 2 and 100 characters.';
    }

    if (formData.description && formData.description.length > 2000) {
      newErrors.description = 'Description must not exceed 2000 characters.';
    }

    if (selectedImage) {
      const allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];

      if (!allowedMimeTypes.includes(selectedImage.type)) {
        newErrors.image = 'Image must be a PNG, JPG, or JPEG file.';
      }

      if (selectedImage.size > 10 * 1024 * 1024) {
        newErrors.image = 'Image must not exceed 10 MB.';
      }
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleAddProduct = () => {
    if (!validateForm()) return;

    router.post(
      '/staff/products',
      {
        product_name: formData.productName,
        category_id: formData.categoryId === 0 ? null : formData.categoryId,
        display_price:
          formData.displayPrice.trim() === '' ? null : Number(formData.displayPrice),
        description: formData.description,
        image: selectedImage ?? undefined,
      },
      {
        forceFormData: true,
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          closeAddModal();
        },
        onError: (serverErrors) => {
          setErrors(serverErrors as Record<string, string>);
        },
      },
    );
  };

  const handleEditProduct = () => {
    if (!validateForm() || !selectedProduct) return;

    const typeSlug = selectedProduct.type === 'Standard' ? 'standard' : 'customizable';

    router.post(
      `/staff/products/${typeSlug}/${selectedProduct.productId}`,
      {
        _method: 'put',
        product_name: formData.productName,
        category_id: formData.type === 'Standard'
          ? (formData.categoryId === 0 ? null : formData.categoryId)
          : null,
        display_price:
          formData.type === 'Standard'
            ? (formData.displayPrice.trim() === '' ? null : Number(formData.displayPrice))
            : null,
        description: formData.description,
        visibility_status: selectedProduct.visibilityStatus,
        image: selectedImage ?? undefined,
        remove_image: removeImage ? 1 : 0,
      },
      {
        forceFormData: true,
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          closeEditModal();
        },
        onError: (serverErrors) => {
          setErrors(serverErrors as Record<string, string>);
        },
      },
    );
  };

  const handleToggleVisibility = () => {
    if (!productToToggle) return;

    const newStatus = productToToggle.visibilityStatus === 'Active' ? 'Inactive' : 'Active';
    const typeSlug = productToToggle.type === 'Standard' ? 'standard' : 'customizable';

    router.put(
      `/staff/products/${typeSlug}/${productToToggle.productId}`,
      {
        product_name: productToToggle.productName,
        category_id: productToToggle.type === 'Standard' ? productToToggle.categoryId ?? null : null,
        display_price: productToToggle.type === 'Standard' ? productToToggle.displayPrice : null,
        description: productToToggle.description ?? '',
        visibility_status: newStatus,
      },
      {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          setIsVisibilityConfirmOpen(false);
          setProductToToggle(null);
        },
      },
    );
  };

  const openAddModal = () => {
    resetForm();
    setErrors({});
    setIsAddModalOpen(true);
  };

  const closeAddModal = () => {
    setIsAddModalOpen(false);
    resetForm();
    setErrors({});
  };

  const openEditModal = (product: Product) => {
    setSelectedProduct(product);
    setFormData({
      productName: product.productName,
      description: product.description ?? '',
      type: product.type,
      categoryId: product.categoryId || 0,
      displayPrice:
        product.type === 'Standard' && product.displayPrice !== null
          ? String(product.displayPrice)
          : '',
    });
    setSelectedImage(null);
    setRemoveImage(false);
    setErrors({});
    setIsEditModalOpen(true);
  };

  const closeEditModal = () => {
    setIsEditModalOpen(false);
    setSelectedProduct(null);
    resetForm();
    setErrors({});
  };

  const openVisibilityConfirm = (product: Product) => {
    setProductToToggle(product);
    setIsVisibilityConfirmOpen(true);
  };

  const resetForm = () => {
    setFormData({
      productName: '',
      description: '',
      type: 'Standard',
      categoryId: 0,
      displayPrice: '',
    });
    setSelectedImage(null);
    setRemoveImage(false);
  };

  const getCategoryName = (categoryId?: number | null) => {
    if (!categoryId) return '-';
    const category = categories.find((c) => c.categoryId === categoryId);
    return category ? category.categoryName : '-';
  };

  const isCategoryDisabled = selectedType === 'customizable';

  const renderImageUploadField = (
    inputId: string,
    inputRef: RefObject<HTMLInputElement | null>,
    selectedFileLabel: string,
    helperText?: string,
  ) => (
    <div>
      <input
        id={inputId}
        ref={inputRef}
        type="file"
        accept=".png,.jpg,.jpeg,image/png,image/jpeg"
        onChange={(e) => handleImageSelection(e.target.files?.[0] ?? null)}
        className="hidden"
      />

      <div
        className={`rounded-md border ${
          errors.image ? 'border-red-500' : 'border-gray-300'
        } bg-white`}
      >
        <div className="flex flex-col gap-3 p-3 sm:flex-row sm:items-center sm:justify-between">
          <button
            type="button"
            onClick={() => inputRef.current?.click()}
            className="inline-flex items-center justify-center rounded-md border border-purple-300 bg-purple-50 px-4 py-2 text-sm font-medium text-purple-700 transition-colors hover:bg-purple-100 cursor-pointer"
          >
            Choose Image
          </button>

          <div className="min-w-0 flex-1">
            <p className={`truncate text-sm ${selectedImage ? 'text-gray-900' : 'text-gray-500'}`}>
              {selectedFileLabel}
            </p>

            {helperText && (
              <p className="mt-1 text-xs text-gray-500">{helperText}</p>
            )}
          </div>
        </div>
      </div>

      {errors.image && <p className="mt-1 text-sm text-red-600">{errors.image}</p>}
    </div>
  );

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-semibold text-purple-900">Product Management</h2>
        <button
          onClick={openAddModal}
          className="flex items-center bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors cursor-pointer"
        >
          <Plus className="w-5 h-5 mr-2" />
          Add Product
        </button>
      </div>

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

      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search products..."
              maxLength={50}
              className="w-full h-11 pl-10 pr-4 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-purple-500"
            />
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select
            value={selectedType}
            onChange={(e) =>
              handleTypeChange(e.target.value as 'all' | 'standard' | 'customizable')
            }
            className="w-full h-11 px-4 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-purple-500 cursor-pointer"
          >
            <option value="all">All Types</option>
            <option value="standard">Standard</option>
            <option value="customizable">Customizable</option>
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select
            value={selectedCategory ?? ''}
            onChange={(e) => handleCategoryChange(e.target.value)}
            disabled={isCategoryDisabled}
            className={`w-full h-11 px-4 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-purple-500 ${
              isCategoryDisabled ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'cursor-pointer'
            }`}
          >
            <option value="">All Categories</option>
            {categories.map((category) => (
              <option key={category.categoryId} value={category.categoryId}>
                {category.categoryName}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            value={selectedStatus}
            onChange={(e) =>
              handleStatusChange(e.target.value as 'all' | 'Active' | 'Inactive')
            }
            className="w-full h-11 px-4 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-purple-500 cursor-pointer"
          >
            <option value="all">All Statuses</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div className="overflow-x-auto">
        <table className="w-full min-w-96 table-auto">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-3 py-3 text-left text-sm font-medium text-gray-700">Image</th>
              <th className="px-3 py-3 text-left text-sm font-medium text-gray-700">Product Name</th>
              <th className="px-3 py-3 text-left text-sm font-medium text-gray-700">Type</th>
              <th className="px-3 py-3 text-left text-sm font-medium text-gray-700">Category</th>
              <th className="px-3 py-3 text-right text-sm font-medium text-gray-700">Price</th>
              <th className="px-3 py-3 text-left text-sm font-medium text-gray-700">Status</th>
              <th className="px-3 py-3 text-right text-sm font-medium text-gray-700">Actions</th>
            </tr>
          </thead>

          <tbody className="divide-y divide-gray-200">
            {incomingProducts.length === 0 ? (
              <tr>
                <td colSpan={7} className="px-3 py-8 text-center text-gray-500">
                  No products match the selected filters.
                </td>
              </tr>
            ) : (
              incomingProducts.map((product) => (
                <tr key={`${product.type}-${product.productId}`} className="hover:bg-gray-50 transition-colors">
                  <td className="px-3 py-3">
                    {product.imageUrl ? (
                      <img
                        src={product.imageUrl}
                        alt={product.productName}
                        className="w-14 h-14 object-cover rounded-md border border-gray-200"
                      />
                    ) : (
                      <div className="w-14 h-14 rounded-md border border-dashed border-gray-300 flex items-center justify-center text-[11px] text-gray-400">
                        No image
                      </div>
                    )}
                  </td>

                  <td className="max-w-[240px] whitespace-normal break-words px-3 py-3 align-top font-medium text-gray-900">
                    {product.productName}
                  </td>

                  <td className="px-3 py-3">
                    <span
                      className={`px-2 py-1 rounded text-xs font-semibold ${
                        product.type === 'Customizable'
                          ? 'bg-blue-100 text-blue-800'
                          : 'bg-gray-100 text-gray-800'
                      }`}
                    >
                      {product.type}
                    </span>
                  </td>

                  <td className="max-w-[180px] whitespace-normal break-words px-3 py-3 align-top text-gray-700">
                    {getCategoryName(product.categoryId)}
                  </td>

                  <td className="px-3 py-3 text-right text-gray-900">
                    {product.type === 'Standard'
                      ? (product.displayPrice !== null ? `€${Number(product.displayPrice).toFixed(2)}` : '-')
                      : '-'}
                  </td>

                  <td className="px-3 py-3">
                    <span
                      className={`px-2 py-1 rounded text-xs font-semibold ${
                        product.visibilityStatus === 'Active'
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      }`}
                    >
                      {product.visibilityStatus}
                    </span>
                  </td>

                  <td className="px-3 py-3 w-28">
                    <div className="flex justify-end space-x-2 whitespace-nowrap">
                      <button
                        onClick={() => openEditModal(product)}
                        className="p-2 text-purple-600 hover:bg-purple-50 rounded transition-colors cursor-pointer"
                        title="Edit Product"
                      >
                        <Edit className="w-4 h-4" />
                      </button>

                      <button
                        onClick={() => openVisibilityConfirm(product)}
                        className={`p-2 rounded transition-colors cursor-pointer ${
                          product.visibilityStatus === 'Active'
                            ? 'text-red-600 hover:bg-red-50'
                            : 'text-green-600 hover:bg-green-50'
                        }`}
                        title={product.visibilityStatus === 'Active' ? 'Deactivate' : 'Activate'}
                      >
                        {product.visibilityStatus === 'Active' ? (
                          <EyeOff className="w-4 h-4" />
                        ) : (
                          <Eye className="w-4 h-4" />
                        )}
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      <Modal isOpen={isAddModalOpen} onClose={closeAddModal} title="Add New Product">
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
            <input
              type="text"
              value={formData.productName}
              onChange={(e) => setFormData({ ...formData, productName: e.target.value })}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.productName ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={100}
            />
            {errors.productName && <p className="mt-1 text-sm text-red-600">{errors.productName}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              value={formData.description ?? ''}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              rows={4}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.description ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={2000}
            />
            {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select
              value={formData.categoryId}
              onChange={(e) => setFormData({ ...formData, categoryId: parseInt(e.target.value, 10) })}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 cursor-pointer ${
                errors.categoryId ? 'border-red-500' : 'border-gray-300'
              }`}
            >
              <option value={0}>No Category</option>
              {categories.map((category) => (
                <option key={category.categoryId} value={category.categoryId}>
                  {category.categoryName}
                </option>
              ))}
            </select>
            {errors.categoryId && <p className="mt-1 text-sm text-red-600">{errors.categoryId}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Display Price (€)</label>
            <input
              type="number"
              step="0.01"
              min="0"
              value={formData.displayPrice}
              onChange={(e) => setFormData({ ...formData, displayPrice: e.target.value })}
              placeholder="0.00"
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.displayPrice ? 'border-red-500' : 'border-gray-300'
              }`}
            />
            {errors.displayPrice && <p className="mt-1 text-sm text-red-600">{errors.displayPrice}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
            {renderImageUploadField(
              'add-product-image',
              addImageInputRef,
              selectedImage ? selectedImage.name : 'No image selected',
              'PNG, JPG, or JPEG up to 10 MB.',
            )}
          </div>

          <div className="flex justify-end space-x-3 pt-4">
            <button
              onClick={closeAddModal}
              className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <button
              onClick={handleAddProduct}
              className="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors cursor-pointer"
            >
              Add Product
            </button>
          </div>
        </div>
      </Modal>

      <Modal isOpen={isEditModalOpen} onClose={closeEditModal} title="Edit Product">
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
            <input
              type="text"
              value={formData.productName}
              onChange={(e) => setFormData({ ...formData, productName: e.target.value })}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.productName ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={100}
            />
            {errors.productName && <p className="mt-1 text-sm text-red-600">{errors.productName}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              value={formData.description ?? ''}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              rows={4}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.description ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={2000}
            />
            {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Product Type *</label>
            <input
              type="text"
              value={formData.type}
              disabled
              className="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
            />
            <p className="mt-1 text-xs text-gray-500">Product type cannot be changed after creation</p>
          </div>

          {formData.type === 'Standard' && (
            <>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select
                  value={formData.categoryId}
                  onChange={(e) => setFormData({ ...formData, categoryId: parseInt(e.target.value, 10) })}
                  className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 cursor-pointer ${
                    errors.categoryId ? 'border-red-500' : 'border-gray-300'
                  }`}
                >
                  <option value={0}>No Category</option>
                  {categories.map((category) => (
                    <option key={category.categoryId} value={category.categoryId}>
                      {category.categoryName}
                    </option>
                  ))}
                </select>
                {errors.categoryId && <p className="mt-1 text-sm text-red-600">{errors.categoryId}</p>}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Display Price (€)</label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  value={formData.displayPrice}
                  onChange={(e) => setFormData({ ...formData, displayPrice: e.target.value })}
                  placeholder="0.00"
                  className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                    errors.displayPrice ? 'border-red-500' : 'border-gray-300'
                  }`}
                />
                {errors.displayPrice && <p className="mt-1 text-sm text-red-600">{errors.displayPrice}</p>}
              </div>
            </>
          )}

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">Current Product Image</label>

            {selectedProduct?.imageUrl && !removeImage ? (
              <img
                src={selectedProduct.imageUrl}
                alt={selectedProduct.productName}
                className="w-28 h-28 object-cover rounded-md border border-gray-200 mb-3"
              />
            ) : (
              <div className="w-28 h-28 rounded-md border border-dashed border-gray-300 flex items-center justify-center text-sm text-gray-400 mb-3">
                No image
              </div>
            )}

            <label className="block text-sm font-medium text-gray-700 mb-1">Replace Image</label>
            {renderImageUploadField(
              'edit-product-image',
              editImageInputRef,
              selectedImage ? selectedImage.name : 'No new image selected',
              'PNG, JPG, or JPEG up to 10 MB.',
            )}

            {selectedProduct?.imageUrl && (
              <label className="mt-3 flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input
                  type="checkbox"
                  checked={removeImage}
                  onChange={(e) => {
                    const checked = e.target.checked;
                    setRemoveImage(checked);

                    if (checked) {
                      setSelectedImage(null);
                    }
                  }}
                  className="rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer"
                />
                Remove current image
              </label>
            )}
          </div>

          <div className="flex justify-end space-x-3 pt-4">
            <button
              onClick={closeEditModal}
              className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <button
              onClick={handleEditProduct}
              className="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors cursor-pointer"
            >
              Save Changes
            </button>
          </div>
        </div>
      </Modal>

      <ConfirmDialog
        isOpen={isVisibilityConfirmOpen}
        onClose={() => setIsVisibilityConfirmOpen(false)}
        onConfirm={handleToggleVisibility}
        title={productToToggle?.visibilityStatus === 'Active' ? 'Deactivate Product' : 'Activate Product'}
        message={`Are you sure you want to ${productToToggle?.visibilityStatus === 'Active' ? 'deactivate' : 'activate'} "${productToToggle?.productName}"?`}
        confirmText={productToToggle?.visibilityStatus === 'Active' ? 'Deactivate' : 'Activate'}
        cancelText="Cancel"
        type={productToToggle?.visibilityStatus === 'Active' ? 'danger' : 'success'}
      />
    </div>
  );
}