import { Link, router, usePage } from '@inertiajs/react';
import { Palette, ShoppingCart, Trash2, PenSquare } from 'lucide-react';
import { useState } from 'react';
import Modal from '@/components/public/Modal';
import { useTimedFlash } from '@/hooks/useTimedFlash';
import { extractPrintSidesLabel, extractShirtColorLabel, extractSizeLabel } from '@/lib/design/document';
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

  const processedDesigns = designs.map((design) => {
    const designId = getDesignId(design);
    const shirtColorLabel = design.shirt_color_label ?? extractShirtColorLabel(design.design_data);
    const printSidesLabel = design.print_sides_label ?? extractPrintSidesLabel(design.design_data);
    const sizeLabel = design.size_label ?? extractSizeLabel(design.design_data);

    return { design, designId, shirtColorLabel, printSidesLabel, sizeLabel };
  });

  return (
    <>
      <div className="rounded-lg bg-white p-4 shadow-sm sm:p-5 md:p-6">
        <h2 className="mb-5 text-xl font-semibold sm:text-2xl md:mb-6">Saved Designs</h2>

        {visibleSuccess && (
          <div className="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-800 sm:text-base">
            {visibleSuccess}
          </div>
        )}

        {visibleError && (
          <div className="mb-4 rounded-md bg-red-100 px-4 py-3 text-sm text-red-800 sm:text-base">
            {visibleError}
          </div>
        )}

        {designs.length === 0 ? (
          <div className="py-10 text-center sm:py-12">
            <Palette className="mx-auto mb-4 h-10 w-10 text-gray-400 sm:h-12 sm:w-12" />
            <p className="text-sm text-gray-600 sm:text-base">You haven't saved any designs yet.</p>
            <Link
              href="/catalog?product_type=customizable"
              className="mt-4 inline-block text-sm text-blue-600 hover:underline sm:text-base"
            >
              Browse customizable products
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {processedDesigns.map(({ design, designId, shirtColorLabel, printSidesLabel, sizeLabel }) => {
              return (
                <div
                  key={designId}
                  className="rounded-lg border p-3 transition-shadow hover:shadow-md"
                >
                  <div className="mb-3 flex h-40 items-center justify-center overflow-hidden rounded-lg bg-gray-100">
                    {design.preview_image_reference ? (
                      <img
                        src={design.preview_image_reference}
                        alt={design.design_name}
                        className="h-full w-full object-contain"
                      />
                    ) : (
                      <Palette className="w-12 h-12 text-gray-400" />
                    )}
                  </div>

                  <h3 className="mb-0.5 text-sm font-semibold wrap-break-word">{design.design_name}</h3>
                  <div className="mb-2">
                    <p className="text-xs text-gray-600 wrap-break-word">
                      {design.product?.product_name ?? 'Custom design'}
                    </p>

                    {(shirtColorLabel || printSidesLabel || sizeLabel) && (
                      <div className="mt-1 space-y-0.5 text-[11px] text-gray-500">
                        {shirtColorLabel && <p className="wrap-break-word">Shirt Color: {shirtColorLabel}</p>}
                        {sizeLabel && <p className="wrap-break-word">Size: {sizeLabel}</p>}
                        {printSidesLabel && <p className="wrap-break-word">Print Sides: {printSidesLabel}</p>}
                      </div>
                    )}
                  </div>
                  <p className="mb-3 text-[11px] text-gray-500">
                    Created:{' '}
                    {design.date_created
                      ? new Date(design.date_created).toLocaleDateString()
                      : 'Unknown'}
                  </p>

                  <div className="space-y-1.5">
                    <Link
                      href={`/designs/${designId}/load`}
                      className="inline-flex w-full items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100"
                    >
                      <PenSquare className="w-4 h-4 mr-2" />
                      Load Design
                    </Link>

                    <button
                      onClick={() => handleAddToCart(designId)}
                      className="flex w-full items-center justify-center rounded-md bg-blue-600 px-3 py-2 text-sm text-white cursor-pointer hover:bg-blue-700"
                    >
                      <ShoppingCart className="w-4 h-4 mr-1" />
                      Add to Cart
                    </button>

                    <button
                      onClick={() => setDesignToDelete(designId ?? null)}
                      className="flex w-full items-center justify-center rounded-md border border-red-200 px-3 py-2 text-sm text-red-600 cursor-pointer hover:bg-red-50"
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

          <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button
              onClick={() => setDesignToDelete(null)}
              className="w-full cursor-pointer rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:w-auto"
            >
              Cancel
            </button>

            <button
              onClick={confirmDelete}
              className="w-full cursor-pointer rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700 sm:w-auto"
            >
              Delete Design
            </button>
          </div>
        </div>
      </Modal>
    </>
  );
}