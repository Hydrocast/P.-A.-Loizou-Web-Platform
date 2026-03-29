import { router } from '@inertiajs/react';
import { Edit2, Trash2, Plus, Search } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import Modal, { ConfirmDialog } from '@/components/public/Modal';
import { useTimedFlash } from '@/hooks/useTimedFlash';

interface Category {
  categoryId: number;
  categoryName: string;
  description: string;
}

type Flash = {
  success?: string;
  error?: string;
};

type CategoryFilters = {
  query?: string | null;
};

type CategoryManagementProps = {
  categories?: Category[];
  filters?: CategoryFilters;
  flash?: Flash;
};

export default function CategoryManagement({
  categories: incomingCategories = [],
  filters = {},
  flash = {},
}: CategoryManagementProps) {
  const { visibleSuccess, visibleError } = useTimedFlash({
    success: flash.success,
    error: flash.error,
  });

  const categories = Array.isArray(incomingCategories) ? incomingCategories : [];
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [isDeleteConfirmOpen, setIsDeleteConfirmOpen] = useState(false);
  const [selectedCategory, setSelectedCategory] = useState<Category | null>(null);
  const [categoryToDelete, setCategoryToDelete] = useState<Category | null>(null);
  const [searchQuery, setSearchQuery] = useState(filters.query ?? '');

  const firstSearchRender = useRef(true);

  const [formData, setFormData] = useState({
    categoryName: '',
    description: '',
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    if (firstSearchRender.current) {
      firstSearchRender.current = false;
      return;
    }

    const timeout = setTimeout(() => {
      router.get(
        '/staff/categories',
        {
          query: searchQuery.trim() || undefined,
        },
        {
          preserveScroll: true,
          preserveState: true,
          replace: true,
        },
      );
    }, 300);

    return () => clearTimeout(timeout);
  }, [searchQuery]);

  const validateForm = (isEdit = false) => {
    const newErrors: Record<string, string> = {};

    if (!formData.categoryName || formData.categoryName.length < 2 || formData.categoryName.length > 50) {
      newErrors.categoryName = 'Category name must be between 2 and 50 characters.';
    }

    if (formData.description && formData.description.length > 500) {
      newErrors.description = 'Description must not exceed 500 characters.';
    }

    const isDuplicate = categories.some(
      (cat) =>
        cat.categoryName.toLowerCase() === formData.categoryName.trim().toLowerCase() &&
        (!isEdit || cat.categoryId !== selectedCategory?.categoryId),
    );

    if (isDuplicate) {
      newErrors.categoryName = 'A category with this name already exists.';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const resetForm = () => {
    setFormData({
      categoryName: '',
      description: '',
    });
  };

  const handleAddCategory = () => {
    if (!validateForm()) return;

    router.post(
      '/staff/categories',
      {
        category_name: formData.categoryName,
        description: formData.description,
      },
      {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          setIsAddModalOpen(false);
          resetForm();
          setErrors({});
        },
      },
    );
  };

  const handleEditCategory = () => {
    if (!validateForm(true) || !selectedCategory) return;

    router.put(
      `/staff/categories/${selectedCategory.categoryId}`,
      {
        category_name: formData.categoryName,
        description: formData.description,
      },
      {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          setIsEditModalOpen(false);
          setSelectedCategory(null);
          resetForm();
          setErrors({});
        },
      },
    );
  };

  const handleDeleteCategory = () => {
    if (!categoryToDelete) return;

    router.delete(`/staff/categories/${categoryToDelete.categoryId}`, {
      preserveScroll: true,
      preserveState: false,
      onSuccess: () => {
        setIsDeleteConfirmOpen(false);
        setCategoryToDelete(null);
      },
      onError: () => {
        setIsDeleteConfirmOpen(false);
        setCategoryToDelete(null);
      },
    });
  };

  const openAddModal = () => {
    resetForm();
    setErrors({});
    setIsAddModalOpen(true);
  };

  const openEditModal = (category: Category) => {
    setSelectedCategory(category);
    setFormData({
      categoryName: category.categoryName,
      description: category.description ?? '',
    });
    setErrors({});
    setIsEditModalOpen(true);
  };

  const openDeleteConfirm = (category: Category) => {
    setCategoryToDelete(category);
    setIsDeleteConfirmOpen(true);
  };

  return (
    <div className="bg-white rounded-lg shadow-md p-6 overflow-hidden">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-semibold text-purple-900">Product Categories</h2>
        <button
          onClick={openAddModal}
          className="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 flex items-center transition-colors cursor-pointer"
        >
          <Plus className="w-4 h-4 mr-2" />
          Add Category
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

      <div className="mb-6">
        <label className="block text-sm font-medium text-gray-700 mb-1">Search</label>
        <div className="relative max-w-md">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Search categories..."
            maxLength={50}
            className="w-full h-11 pl-10 pr-4 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-purple-500"
          />
        </div>
      </div>

      <div className="overflow-x-auto">
        <table className="w-full table-fixed divide-y divide-gray-200">
          <colgroup>
            <col className="w-[31%]" />
            <col className="w-[35%]" />
            <col className="w-[10%]" />
          </colgroup>

          <thead className="bg-gray-50">
            <tr>
              <th className="pl-4 pr-8 py-3 text-left text-sm font-medium text-gray-700">
                Category Name
              </th>
              <th className="pl-6 pr-4 py-3 text-left text-sm font-medium text-gray-700">
                Description
              </th>
              <th className="px-4 py-3 text-right text-sm font-medium text-gray-700">
                Actions
              </th>
            </tr>
          </thead>

          <tbody className="bg-white divide-y divide-gray-200">
            {categories.length === 0 ? (
              <tr>
                <td colSpan={3} className="px-4 py-8 text-center text-gray-500">
                  No categories match the current search.
                </td>
              </tr>
            ) : (
              categories.map((category) => (
                <tr key={category.categoryId} className="hover:bg-gray-50 align-top">
                  <td className="pl-4 pr-8 py-4 font-medium text-gray-900 wrap-break-word whitespace-normal">
                    {category.categoryName}
                  </td>

                  <td className="pl-6 pr-4 py-4 text-gray-700 wrap-break-word whitespace-normal">
                    {category.description ? (
                      category.description
                    ) : (
                      <span className="text-gray-400 italic">No description</span>
                    )}
                  </td>

                  <td className="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      onClick={() => openEditModal(category)}
                      className="text-purple-600 hover:text-purple-900 mr-4 cursor-pointer"
                      title="Edit Category"
                    >
                      <Edit2 className="w-4 h-4 inline" />
                    </button>

                    <button
                      onClick={() => openDeleteConfirm(category)}
                      className="text-red-600 hover:text-red-900 cursor-pointer"
                      title="Delete Category"
                    >
                      <Trash2 className="w-4 h-4 inline" />
                    </button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      <Modal isOpen={isAddModalOpen} onClose={() => setIsAddModalOpen(false)} title="Create Product Category">
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Category Name *
            </label>
            <input
              type="text"
              value={formData.categoryName}
              onChange={(e) => setFormData({ ...formData, categoryName: e.target.value })}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.categoryName ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={50}
            />
            {errors.categoryName && (
              <p className="mt-1 text-sm text-red-600">{errors.categoryName}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Description (Optional)
            </label>
            <textarea
              value={formData.description ?? ''}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              rows={4}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.description ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={500}
            />
            {errors.description && (
              <p className="mt-1 text-sm text-red-600">{errors.description}</p>
            )}
            <p className="mt-1 text-sm text-gray-500">
              {formData.description.length}/500 characters
            </p>
          </div>

          <div className="flex justify-end space-x-3 pt-4">
            <button
              onClick={() => setIsAddModalOpen(false)}
              className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <button
              onClick={handleAddCategory}
              className="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors cursor-pointer"
            >
              Create Category
            </button>
          </div>
        </div>
      </Modal>

      <Modal isOpen={isEditModalOpen} onClose={() => setIsEditModalOpen(false)} title="Edit Product Category">
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Category Name *
            </label>
            <input
              type="text"
              value={formData.categoryName}
              onChange={(e) => setFormData({ ...formData, categoryName: e.target.value })}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.categoryName ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={50}
            />
            {errors.categoryName && (
              <p className="mt-1 text-sm text-red-600">{errors.categoryName}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Description (Optional)
            </label>
            <textarea
              value={formData.description ?? ''}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              rows={4}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.description ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={500}
            />
            {errors.description && (
              <p className="mt-1 text-sm text-red-600">{errors.description}</p>
            )}
            <p className="mt-1 text-sm text-gray-500">
              {formData.description.length}/500 characters
            </p>
          </div>

          <div className="flex justify-end space-x-3 pt-4">
            <button
              onClick={() => setIsEditModalOpen(false)}
              className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <button
              onClick={handleEditCategory}
              className="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors cursor-pointer"
            >
              Save Changes
            </button>
          </div>
        </div>
      </Modal>

      <ConfirmDialog
        isOpen={isDeleteConfirmOpen}
        onClose={() => setIsDeleteConfirmOpen(false)}
        onConfirm={handleDeleteCategory}
        title="Delete Category"
        message={
          <>
            <p>
              Are you sure you want to delete "{categoryToDelete?.categoryName}"?
            </p>
            <p className="mt-2">
              Products in this category will become uncategorized.
            </p>
          </>
        }
        confirmText="Delete"
        cancelText="Cancel"
        type="danger"
      />
    </div>
  );
}