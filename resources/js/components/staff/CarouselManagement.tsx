import { router } from '@inertiajs/react';
import { Plus, Edit, Trash2, ChevronUp, ChevronDown, Image as ImageIcon, Link2 } from 'lucide-react';
import { useMemo, useRef, useState } from 'react';
import type { RefObject } from 'react';
import Modal, { ConfirmDialog } from '@/components/public/Modal';
import { useTimedFlash } from '@/hooks/useTimedFlash';

type Slide = {
  slideId: number;
  title: string;
  description: string;
  imageReference?: string | null;
  imageUrl?: string | null;
  hasCustomImage: boolean;
  linkedProductImageUrl?: string | null;
  hasLinkedProductImage: boolean;
  usingLinkedProductImage: boolean;
  linkedProductKey: string;
  linkedProductName?: string | null;
  productId?: number | null;
  productType?: string | null;
  displaySequence: number;
};

type LinkedProductOption = {
  value: string;
  label: string;
  productId: number;
  productType: string;
  imageReference?: string | null;
  imageUrl?: string | null;
  hasImage: boolean;
};

type Flash = {
  success?: string;
  error?: string;
};

type CarouselManagementProps = {
  slides?: Slide[];
  linkedProducts?: LinkedProductOption[];
  flash?: Flash;
};

export default function CarouselManagement({
  slides: incomingSlides = [],
  linkedProducts = [],
  flash = {},
}: CarouselManagementProps) {
  const { visibleSuccess, visibleError } = useTimedFlash({
    success: flash.success,
    error: flash.error,
  });

  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [selectedSlide, setSelectedSlide] = useState<Slide | null>(null);
  const [isDeleteConfirmOpen, setIsDeleteConfirmOpen] = useState(false);
  const [slideToDelete, setSlideToDelete] = useState<Slide | null>(null);
  const [selectedImage, setSelectedImage] = useState<File | null>(null);
  const [localSlideOrder, setLocalSlideOrder] = useState<number[] | null>(null);

  const addImageInputRef = useRef<HTMLInputElement | null>(null);
  const editImageInputRef = useRef<HTMLInputElement | null>(null);

  const [formData, setFormData] = useState({
    title: '',
    description: '',
    linkedProductKey: '',
    useLinkedProductImage: false,
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  const linkedProductLookup = useMemo(
    () => new Map(linkedProducts.map((product) => [product.value, product])),
    [linkedProducts],
  );

  const selectedLinkedProduct = useMemo(
    () => linkedProductLookup.get(formData.linkedProductKey) ?? null,
    [formData.linkedProductKey, linkedProductLookup],
  );

  const validateForm = () => {
    const newErrors: Record<string, string> = {};

    if (!formData.title || formData.title.length < 2 || formData.title.length > 50) {
      newErrors.title = 'Title must be between 2 and 50 characters.';
    }

    if (formData.description && formData.description.length > 100) {
      newErrors.description = 'Description must not exceed 100 characters.';
    }

    const linkedProductHasImage = selectedLinkedProduct?.hasImage ?? false;

    let hasImageSource = false;

    if (selectedImage) {
      hasImageSource = true;
    } else if (isEditModalOpen && selectedSlide) {
      if (formData.useLinkedProductImage) {
        hasImageSource = linkedProductHasImage;
      } else if (selectedSlide.hasCustomImage) {
        hasImageSource = true;
      } else if (selectedSlide.usingLinkedProductImage) {
        hasImageSource = linkedProductHasImage;
      } else {
        hasImageSource = false;
      }
    } else {
      hasImageSource = linkedProductHasImage;
    }

    if (!hasImageSource) {
      newErrors.image = 'Upload a custom slide image or link a product that already has an image.';
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

  const parseLinkedProduct = (value: string) => {
    if (!value) {
      return { product_id: null, product_type: null };
    }

    const [productType, productId] = value.split(':');

    return {
      product_id: productId ? Number(productId) : null,
      product_type: productType || null,
    };
  };

  const resetForm = () => {
    setFormData({
      title: '',
      description: '',
      linkedProductKey: '',
      useLinkedProductImage: false,
    });
    setSelectedImage(null);
    setErrors({});
  };

  const openAddModal = () => {
    resetForm();
    setIsAddModalOpen(true);
  };

  const openEditModal = (slide: Slide) => {
    setSelectedSlide(slide);
    setFormData({
      title: slide.title ?? '',
      description: slide.description ?? '',
      linkedProductKey: slide.linkedProductKey ?? '',
      useLinkedProductImage: slide.usingLinkedProductImage,
    });
    setSelectedImage(null);
    setErrors({});
    setIsEditModalOpen(true);
  };

  const openDeleteConfirm = (slide: Slide) => {
    setSlideToDelete(slide);
    setIsDeleteConfirmOpen(true);
  };

  const handleImageSelection = (file: File | null) => {
    setSelectedImage(file);

    if (file) {
      setFormData((prev) => ({
        ...prev,
        useLinkedProductImage: false,
      }));
    }
  };

  const handleLinkedProductChange = (value: string) => {
    setFormData((prev) => ({
      ...prev,
      linkedProductKey: value,
      useLinkedProductImage: value ? prev.useLinkedProductImage : false,
    }));
  };

  const handleUseProductImage = () => {
    if (!selectedLinkedProduct?.hasImage) return;

    setSelectedImage(null);
    setFormData((prev) => ({
      ...prev,
      useLinkedProductImage: true,
    }));
  };

  const handleAddSlide = () => {
    if (!validateForm()) return;

    const linked = parseLinkedProduct(formData.linkedProductKey);

    router.post(
      '/staff/carousel',
      {
        title: formData.title,
        description: formData.description || null,
        image: selectedImage ?? undefined,
        use_linked_product_image: formData.useLinkedProductImage ? 1 : 0,
        product_id: linked.product_id,
        product_type: linked.product_type,
      },
      {
        forceFormData: true,
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          setIsAddModalOpen(false);
          resetForm();
        },
        onError: (serverErrors) => {
          setErrors(serverErrors as Record<string, string>);
        },
      },
    );
  };

  const handleEditSlide = () => {
    if (!validateForm() || !selectedSlide) return;

    const linked = parseLinkedProduct(formData.linkedProductKey);

    router.post(
      `/staff/carousel/${selectedSlide.slideId}`,
      {
        _method: 'put',
        title: formData.title,
        description: formData.description || null,
        image: selectedImage ?? undefined,
        use_linked_product_image: formData.useLinkedProductImage ? 1 : 0,
        product_id: linked.product_id,
        product_type: linked.product_type,
      },
      {
        forceFormData: true,
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          setIsEditModalOpen(false);
          setSelectedSlide(null);
          resetForm();
        },
        onError: (serverErrors) => {
          setErrors(serverErrors as Record<string, string>);
        },
      },
    );
  };

  const handleDeleteSlide = () => {
    if (!slideToDelete) return;

    router.delete(`/staff/carousel/${slideToDelete.slideId}`, {
      preserveScroll: true,
      preserveState: false,
      onSuccess: () => {
        setIsDeleteConfirmOpen(false);
        setSlideToDelete(null);
      },
    });
  };

  const moveSlide = (index: number, direction: 'up' | 'down') => {
    const targetIndex = direction === 'up' ? index - 1 : index + 1;

    if (targetIndex < 0 || targetIndex >= sortedSlides.length) return;

    const reorderedSlides = [...sortedSlides];
    [reorderedSlides[index], reorderedSlides[targetIndex]] = [
      reorderedSlides[targetIndex],
      reorderedSlides[index],
    ];

    const reorderedIds = reorderedSlides.map((slide) => slide.slideId);

    setLocalSlideOrder(reorderedIds);

    router.put(
      '/staff/carousel/reorder',
      {
        slide_ids: reorderedIds,
      },
      {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          setLocalSlideOrder(null);
        },
        onError: () => {
          setLocalSlideOrder(null);
        },
      },
    );
  };

  const safeSlides = Array.isArray(incomingSlides) ? incomingSlides : [];
  const baseSlides = [...safeSlides].sort((a, b) => a.displaySequence - b.displaySequence);

  const sortedSlides = (() => {
    if (!localSlideOrder || localSlideOrder.length !== baseSlides.length) {
      return baseSlides;
    }

    const slideMap = new Map(baseSlides.map((slide) => [slide.slideId, slide]));
    const reorderedSlides = localSlideOrder
      .map((slideId) => slideMap.get(slideId))
      .filter((slide): slide is Slide => slide !== undefined);

    if (reorderedSlides.length !== baseSlides.length) {
      return baseSlides;
    }

    return reorderedSlides.map((slide, index) => ({
      ...slide,
      displaySequence: index + 1,
    }));
  })();

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

  const renderImageSourcePanel = (mode: 'add' | 'edit') => {
    const currentSlideHasLinkedProductImage = selectedSlide?.hasLinkedProductImage ?? false;
    const linkedProductHasImage = selectedLinkedProduct?.hasImage ?? false;
    const effectiveLinkedProductHasImage = mode === 'edit'
      ? linkedProductHasImage || currentSlideHasLinkedProductImage
      : linkedProductHasImage;

    return (
      <div className="space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
        <div className="flex items-center justify-between gap-3">
          <div>
            <h4 className="text-sm font-semibold text-gray-800">Slide Image Source</h4>
            <p className="text-xs text-gray-500">
              Upload a custom image, or use the linked product image when available.
            </p>
          </div>

          {effectiveLinkedProductHasImage && (
            <button
              type="button"
              onClick={handleUseProductImage}
              className="inline-flex items-center rounded-md border border-purple-300 bg-white px-3 py-2 text-sm font-medium text-purple-700 transition-colors hover:bg-purple-50 cursor-pointer"
            >
              <Link2 className="mr-2 h-4 w-4" />
              Use Product Image
            </button>
          )}
        </div>

        {mode === 'edit' && selectedSlide && (
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div className="rounded-md border border-gray-200 bg-white p-3">
              <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                Current Slide Image
              </p>

              <div className="flex h-32 items-center justify-center rounded-md bg-gray-100 p-3">
                {selectedSlide.imageUrl ? (
                  <img
                    src={selectedSlide.imageUrl}
                    alt={selectedSlide.title}
                    className="h-full w-full object-contain"
                  />
                ) : (
                  <span className="text-sm text-gray-400">No image</span>
                )}
              </div>

              <p className="mt-2 text-xs text-gray-600">
                {selectedSlide.hasCustomImage
                  ? 'Currently using a custom slide image.'
                  : selectedSlide.usingLinkedProductImage
                    ? 'Currently using the linked product image.'
                    : 'No current image source.'}
              </p>
            </div>

            <div className="rounded-md border border-gray-200 bg-white p-3">
              <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                Linked Product Image
              </p>

              <div className="flex h-32 items-center justify-center rounded-md bg-gray-100 p-3">
                {selectedLinkedProduct?.imageUrl ? (
                  <img
                    src={selectedLinkedProduct.imageUrl}
                    alt={selectedLinkedProduct.label}
                    className="h-full w-full object-contain"
                  />
                ) : selectedSlide.linkedProductImageUrl && formData.linkedProductKey === selectedSlide.linkedProductKey ? (
                  <img
                    src={selectedSlide.linkedProductImageUrl}
                    alt={selectedSlide.linkedProductName ?? 'Linked product'}
                    className="h-full w-full object-contain"
                  />
                ) : (
                  <span className="text-sm text-gray-400">Linked product has no image</span>
                )}
              </div>

              <p className="mt-2 text-xs text-gray-600">
                {selectedLinkedProduct
                  ? selectedLinkedProduct.hasImage
                    ? `Linked to ${selectedLinkedProduct.label}.`
                    : `${selectedLinkedProduct.label} does not currently have an image.`
                  : selectedSlide.linkedProductName
                    ? selectedSlide.hasLinkedProductImage
                      ? `Linked to ${selectedSlide.linkedProductName}.`
                      : `${selectedSlide.linkedProductName} does not currently have an image.`
                    : 'No linked product selected.'}
              </p>
            </div>
          </div>
        )}

        {mode === 'add' && selectedLinkedProduct && (
          <div className="rounded-md border border-gray-200 bg-white p-3">
            <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
              Linked Product Image
            </p>

            <div className="flex h-32 items-center justify-center rounded-md bg-gray-100 p-3">
              {selectedLinkedProduct.imageUrl ? (
                <img
                  src={selectedLinkedProduct.imageUrl}
                  alt={selectedLinkedProduct.label}
                  className="h-full w-full object-contain"
                />
              ) : (
                <span className="text-sm text-gray-400">Linked product has no image</span>
              )}
            </div>

            <p className="mt-2 text-xs text-gray-600">
              {selectedLinkedProduct.hasImage
                ? `If you do not upload a custom image, the slide can use ${selectedLinkedProduct.label}'s image.`
                : `${selectedLinkedProduct.label} does not currently have an image. Upload a custom slide image.`}
            </p>
          </div>
        )}

        {renderImageUploadField(
          mode === 'add' ? 'add-carousel-image' : 'edit-carousel-image',
          mode === 'add' ? addImageInputRef : editImageInputRef,
          selectedImage ? selectedImage.name : 'No custom image selected',
          'PNG, JPG, or JPEG up to 10 MB.',
        )}
      </div>
    );
  };

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-semibold text-purple-900">Carousel Management</h2>
        <button
          onClick={openAddModal}
          className="flex items-center bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors cursor-pointer"
        >
          <Plus className="w-5 h-5 mr-2" />
          Add Slide
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

      <div className="overflow-x-auto">
        <table className="w-full table-fixed">
          <colgroup>
            <col className="w-28" />
            <col className="w-24" />
            <col className="w-[26%]" />
            <col className="w-[30%]" />
            <col className="w-[22%]" />
            <col className="w-28" />
          </colgroup>

          <thead className="bg-gray-50">
            <tr>
              <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Order</th>
              <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Preview</th>
              <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Title</th>
              <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Description</th>
              <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Linked Product</th>
              <th className="px-4 py-3 text-right text-sm font-medium text-gray-700">Actions</th>
            </tr>
          </thead>

          <tbody className="divide-y divide-gray-200">
            {sortedSlides.length === 0 ? (
              <tr>
                <td colSpan={6} className="px-4 py-8 text-center text-gray-500">
                  No carousel slides found. Click "Add Slide" to create one.
                </td>
              </tr>
            ) : (
              sortedSlides.map((slide, index) => (
                <tr key={slide.slideId} className="hover:bg-gray-50 transition-colors align-top">
                  <td className="px-4 py-3">
                    <div className="flex items-center space-x-1">
                      <span className="font-medium text-gray-900">{slide.displaySequence}</span>

                      <div className="flex flex-col">
                        <button
                          onClick={() => moveSlide(index, 'up')}
                          disabled={index === 0}
                          className={`p-0.5 rounded transition-colors cursor-pointer ${
                            index === 0
                              ? 'text-gray-300 cursor-not-allowed'
                              : 'text-purple-600 hover:bg-purple-50'
                          }`}
                          title="Move up"
                        >
                          <ChevronUp className="w-4 h-4" />
                        </button>

                        <button
                          onClick={() => moveSlide(index, 'down')}
                          disabled={index === sortedSlides.length - 1}
                          className={`p-0.5 rounded transition-colors cursor-pointer ${
                            index === sortedSlides.length - 1
                              ? 'text-gray-300 cursor-not-allowed'
                              : 'text-purple-600 hover:bg-purple-50'
                          }`}
                          title="Move down"
                        >
                          <ChevronDown className="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </td>

                  <td className="px-4 py-3">
                    <div className="flex h-16 w-16 items-center justify-center overflow-hidden rounded-md border border-gray-200 bg-gray-100">
                      {slide.imageUrl ? (
                        <img
                          src={slide.imageUrl}
                          alt={slide.title}
                          className="h-full w-full object-contain"
                        />
                      ) : (
                        <ImageIcon className="h-5 w-5 text-gray-400" />
                      )}
                    </div>
                  </td>

                  <td className="px-4 py-3 font-medium text-gray-900 text-sm leading-6 break-words whitespace-normal">
                    {slide.title}
                  </td>

                  <td className="px-4 py-3 text-gray-700 text-sm leading-6 break-words whitespace-normal">
                    {slide.description || '-'}
                  </td>

                  <td className="px-4 py-3 text-gray-700 text-sm leading-6 break-words whitespace-normal">
                    {slide.linkedProductName || '-'}
                  </td>

                  <td className="px-4 py-3">
                    <div className="flex justify-end space-x-2">
                      <button
                        onClick={() => openEditModal(slide)}
                        className="p-2 text-purple-600 hover:bg-purple-50 rounded transition-colors cursor-pointer"
                        title="Edit Slide"
                      >
                        <Edit className="w-4 h-4" />
                      </button>

                      <button
                        onClick={() => openDeleteConfirm(slide)}
                        className="p-2 text-red-600 hover:bg-red-50 rounded transition-colors cursor-pointer"
                        title="Delete Slide"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      <Modal isOpen={isAddModalOpen} onClose={() => setIsAddModalOpen(false)} title="Create Carousel Slide">
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input
              type="text"
              value={formData.title}
              onChange={(e) => setFormData({ ...formData, title: e.target.value })}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.title ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={50}
            />
            {errors.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              value={formData.description ?? ''}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              rows={3}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.description ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={100}
            />
            {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Linked Product (Optional)</label>
            <select
              value={formData.linkedProductKey}
              onChange={(e) => handleLinkedProductChange(e.target.value)}
              className="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 border-gray-300 cursor-pointer"
            >
              <option value="">No linked product</option>
              {linkedProducts.map((product) => (
                <option key={product.value} value={product.value}>
                  {product.label}
                </option>
              ))}
            </select>
          </div>

          {renderImageSourcePanel('add')}

          <div className="flex justify-end space-x-3 pt-4">
            <button
              onClick={() => setIsAddModalOpen(false)}
              className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <button
              onClick={handleAddSlide}
              className="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors cursor-pointer"
            >
              Create Slide
            </button>
          </div>
        </div>
      </Modal>

      <Modal isOpen={isEditModalOpen} onClose={() => setIsEditModalOpen(false)} title="Edit Carousel Slide">
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input
              type="text"
              value={formData.title}
              onChange={(e) => setFormData({ ...formData, title: e.target.value })}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.title ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={50}
            />
            {errors.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              value={formData.description ?? ''}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              rows={3}
              className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 ${
                errors.description ? 'border-red-500' : 'border-gray-300'
              }`}
              maxLength={100}
            />
            {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Linked Product (Optional)</label>
            <select
              value={formData.linkedProductKey}
              onChange={(e) => handleLinkedProductChange(e.target.value)}
              className="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 border-gray-300 cursor-pointer"
            >
              <option value="">No linked product</option>
              {linkedProducts.map((product) => (
                <option key={product.value} value={product.value}>
                  {product.label}
                </option>
              ))}
            </select>
          </div>

          {renderImageSourcePanel('edit')}

          <div className="flex justify-end space-x-3 pt-4">
            <button
              onClick={() => setIsEditModalOpen(false)}
              className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <button
              onClick={handleEditSlide}
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
        onConfirm={handleDeleteSlide}
        title="Delete Carousel Slide"
        message={`Are you sure you want to delete "${slideToDelete?.title}"?`}
        confirmText="Delete"
        cancelText="Cancel"
        type="danger"
      />
    </div>
  );
}