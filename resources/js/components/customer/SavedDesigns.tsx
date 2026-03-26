import { Link, router, usePage } from '@inertiajs/react';
import { Palette, ShoppingCart, Trash2, PenSquare } from 'lucide-react';
import { useState } from 'react';
import Modal from '@/components/public/Modal';
import { useTimedFlash } from '@/hooks/useTimedFlash';
import { extractPrintSidesLabel, extractShirtColorLabel } from '@/lib/design/document';
import type { SavedDesignListItem } from '@/types/design';

type PageProps = {
  designs: SavedDesignListItem[];
  flash?: {
    success?: string;
    error?: string;
  };
};

export default function SavedDesigns() {
  const { designs, flash } = usePage<PageProps>().props;
  const [designToDelete, setDesignToDelete] = useState<number | null>(null);
  const { visibleSuccess, visibleError } = useTimedFlash({
    success: flash?.success,
    error: flash?.error,
  });

  const getDesignId = (design: SavedDesignListItem) =>
    design.saved_design_id ?? design.design_id ?? design.id;

  const handleAddToCart = (designId: number | undefined) => {
    if (!designId) return;

    router.post('/cart/from-design', {
      design_id: designId,
    });
  };

  const confirmDelete = () => {
    if (!designToDelete) return;

    router.delete(`/designs/${designToDelete}`, {
      onFinish: () => setDesignToDelete(null),
    });
  };

  return (
    <>
      <div className="bg-white rounded-lg shadow-sm p-6">
        <h2 className="text-2xl font-semibold mb-6">Saved Designs</h2>

        {visibleSuccess && (
          <div className="mb-4 p-3 bg-green-100 text-green-800 rounded-md">
            {visibleSuccess}
          </div>
        )}

        {visibleError && (
          <div className="mb-4 p-3 bg-red-100 text-red-800 rounded-md">
            {visibleError}
          </div>
        )}

        {designs.length === 0 ? (
          <div className="text-center py-12">
            <Palette className="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p className="text-gray-600">You haven't saved any designs yet.</p>
            <Link
              href="/catalog?product_type=customizable"
              className="mt-4 inline-block text-blue-600 hover:underline"
            >
              Browse customizable products
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {designs.map((design) => {
              const designId = getDesignId(design);
              const shirtColorLabel = design.shirt_color_label ?? extractShirtColorLabel(design.design_data);
              const printSidesLabel = design.print_sides_label ?? extractPrintSidesLabel(design.design_data);

              return (
                <div
                  key={designId}
                  className="border rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <div className="bg-gray-100 h-40 rounded-lg flex items-center justify-center mb-3 overflow-hidden">
                    {design.preview_image_reference ? (
                      <img
                        src={design.preview_image_reference}
                        alt={design.design_name}
                        className="w-full h-full object-cover"
                      />
                    ) : (
                      <Palette className="w-12 h-12 text-gray-400" />
                    )}
                  </div>

                  <h3 className="font-semibold mb-1">{design.design_name}</h3>
                  <div className="mb-3">
                    <p className="text-sm text-gray-600">
                      {design.product?.product_name ?? 'Custom design'}
                    </p>

                    {(shirtColorLabel || printSidesLabel) && (
                      <div className="mt-1 space-y-1 text-xs text-gray-500">
                        {shirtColorLabel && <p>Shirt Color: {shirtColorLabel}</p>}
                        {printSidesLabel && <p>Print Sides: {printSidesLabel}</p>}
                      </div>
                    )}
                  </div>
                  <p className="text-xs text-gray-500 mb-4">
                    Created:{' '}
                    {design.date_created
                      ? new Date(design.date_created).toLocaleDateString()
                      : 'Unknown'}
                  </p>

                  <div className="space-y-2">
                    <Link
                      href={`/designs/${designId}/load`}
                      className="w-full inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-medium text-blue-700 transition hover:bg-blue-100"
                    >
                      <PenSquare className="w-4 h-4 mr-2" />
                      Load Design
                    </Link>

                    <button
                      onClick={() => handleAddToCart(designId)}
                      className="w-full flex items-center justify-center bg-blue-600 text-white py-2 px-3 rounded-md hover:bg-blue-700 text-sm cursor-pointer"
                    >
                      <ShoppingCart className="w-4 h-4 mr-1" />
                      Add to Cart
                    </button>

                    <button
                      onClick={() => setDesignToDelete(designId ?? null)}
                      className="w-full flex items-center justify-center border border-red-200 text-red-600 py-2 px-3 rounded-md hover:bg-red-50 text-sm cursor-pointer"
                    >
                      <Trash2 className="w-4 h-4 mr-1" />
                      Delete
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>

      <Modal
        isOpen={designToDelete !== null}
        onClose={() => setDesignToDelete(null)}
        title="Delete Design?"
        size="sm"
      >
        <div className="space-y-5">
          <div className="space-y-2">
            <p className="text-sm leading-6 text-gray-700">
              Are you sure you want to delete this saved design?
            </p>

            <p className="text-sm text-gray-500">
              This action cannot be undone.
            </p>
          </div>

          <div className="flex gap-3 justify-end">
            <button
              onClick={() => setDesignToDelete(null)}
              className="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 cursor-pointer"
            >
              Cancel
            </button>

            <button
              onClick={confirmDelete}
              className="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700 cursor-pointer"
            >
              Delete Design
            </button>
          </div>
        </div>
      </Modal>
    </>
  );
}