import { Head, router, usePage } from '@inertiajs/react';
import {
  AlignCenter,
  AlignLeft,
  AlignRight,
  Image as ImageIcon,
  PaintBucket,
  Save,
  ShoppingCart,
  Smile,
  Sparkles,
  Trash2,
  Type,
  Undo,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { ReactNode } from 'react';
import FabricDesignerCanvas from '@/components/customer/design/FabricDesignerCanvas';
import type { ActiveTextStyle } from '@/hooks/useFabricDesigner';
import { MAX_IMAGES, useFabricDesigner } from '@/hooks/useFabricDesigner';
import { extractCanvasJson } from '@/lib/design/document';
import { normalizeTemplateConfig, resolvePrintArea } from '@/lib/design/template';
import type { WorkspacePageProps } from '@/types/design';

type FlashProps = {
  success?: string;
  error?: string;
};

const FONT_OPTIONS = [
  'Arial',
  'Helvetica',
  'Verdana',
  'Georgia',
  'Times New Roman',
  'Trebuchet MS',
  'Courier New',
  'Impact',
];

const COLOR_SWATCHES = [
  '#000000',
  '#4B5563',
  '#9CA3AF',
  '#FFFFFF',
  '#2563EB',
  '#16A34A',
  '#EAB308',
  '#EA580C',
  '#DC2626',
  '#DB2777',
  '#9333EA',
  '#0891B2',
];

const DEFAULT_TEXT_STYLE: ActiveTextStyle = {
  text: '',
  fontFamily: 'Arial',
  fontSize: 100,
  color: '#000000',
  fontWeight: 'normal',
  fontStyle: 'normal',
  underline: false,
  textAlign: 'left',
  letterSpacing: 0,
  strokeColor: '#000000',
  strokeWidth: 0,
  shadowEnabled: false,
};


function MiniToggleButton({
  active,
  title,
  onClick,
  children,
}: {
  active: boolean;
  title: string;
  onClick: () => void;
  children: ReactNode;
}) {
  return (
    <button
      type="button"
      title={title}
      onClick={onClick}
      className={`flex h-7 w-7 cursor-pointer items-center justify-center rounded border transition ${
        active
          ? 'border-blue-300 bg-blue-50 text-blue-700'
          : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
      }`}
    >
      {children}
    </button>
  );
}

export default function DesignWorkspace() {
  const {
    product,
    clipart,
    templateConfig,
    initialDesign,
    shirtColorOptions = [],
    selectedShirtColorId: initialSelectedShirtColorId,
    workspaceOptions = {},
    selectedPrintSide: initialSelectedPrintSide,
    selectedSize: initialSelectedSize,
    flash,
  } = usePage<WorkspacePageProps & { flash?: FlashProps }>().props;

  const normalizedTemplate = normalizeTemplateConfig(templateConfig);
  const resolvedPrintArea = resolvePrintArea(templateConfig);

  const imageInputRef = useRef<HTMLInputElement | null>(null);
  const messageTimeoutRef = useRef<number | null>(null);

  const {
    canUndo,
    isReady,
    zoomLevel,
    activeObjectType,
    activeTextStyle,
    hasPendingTextPreview,
    attachCanvas,
    detachCanvas,
    initializeWorkspace,
    addText,
    beginTextPreview,
    updateTextPreview,
    commitTextPreview,
    discardTextPreview,
    addImageFromFile,
    addClipart,
    updateActiveTextStyle,
    deleteActiveObject,
    undo,
    clearDesign,
    exportDesignJson,
    exportPreviewDataUrl,
    exportPrintDataUrl,
    hasDesignElements,
    duplicateActiveObject,
    flipActiveObjectHorizontal,
    flipActiveObjectVertical,
    scaleActiveObjectToFill,
    zoomIn,
    zoomOut,
    resetZoom,
  } = useFabricDesigner();

  const [selectedTool, setSelectedTool] = useState<
    'text' | 'image' | 'clipart' | 'layout' | 'product' | null
  >(null);
  const [quantity, setQuantity] = useState(1);
  const [showSaveDialog, setShowSaveDialog] = useState(false);
  const [showClearDialog, setShowClearDialog] = useState(false);
  const [designName, setDesignName] = useState(initialDesign?.design_name ?? '');
  const [message, setMessage] = useState('');
  const [messageType, setMessageType] = useState<'success' | 'error'>('success');
  const [isSavingDesign, setIsSavingDesign] = useState(false);
  const [isAddingToCart, setIsAddingToCart] = useState(false);
  const [selectedImageName, setSelectedImageName] = useState('');
  const [showImageLimitDialog, setShowImageLimitDialog] = useState(false);
  const [textStyle, setTextStyle] = useState<ActiveTextStyle>(DEFAULT_TEXT_STYLE);
  const [selectedShirtColorId, setSelectedShirtColorId] = useState(
    initialSelectedShirtColorId ?? shirtColorOptions[0]?.id ?? '',
  );
  const [selectedPrintSideValue, setSelectedPrintSideValue] = useState(
    initialSelectedPrintSide?.value ?? '',
  );
  const [selectedSizeValue, setSelectedSizeValue] = useState(
    initialSelectedSize?.value ?? '',
  );

  const selectedShirtColor = (() => {
    return (
      shirtColorOptions.find((option) => option.id === selectedShirtColorId) ??
      shirtColorOptions[0] ??
      null
    );
  })();

  const printSideOptions =
    workspaceOptions.print_sides?.enabled && Array.isArray(workspaceOptions.print_sides?.choices)
      ? workspaceOptions.print_sides.choices
      : [];

  const selectedPrintSide =
    printSideOptions.find((option) => option.value === selectedPrintSideValue) ?? null;

  const selectedSize =
    selectedSizeValue.trim() !== ''
      ? {
          value: selectedSizeValue,
          label: initialSelectedSize?.label ?? selectedSizeValue,
        }
      : null;

  const isEditingSelectedText =
    selectedTool === 'text' &&
    activeObjectType === 'text' &&
    activeTextStyle !== null &&
    !hasPendingTextPreview;

  useEffect(() => {
    setDesignName(initialDesign?.design_name ?? '');
  }, [initialDesign?.design_name]);

  useEffect(() => {
    if (selectedTool === 'text' && activeTextStyle) {
      setTextStyle(activeTextStyle);
    }
  }, [activeTextStyle, selectedTool]);

  useEffect(() => {
    setSelectedShirtColorId(
      initialSelectedShirtColorId ?? shirtColorOptions[0]?.id ?? '',
    );
  }, [initialSelectedShirtColorId, shirtColorOptions]);

  useEffect(() => {
    setSelectedPrintSideValue(initialSelectedPrintSide?.value ?? '');
  }, [initialSelectedPrintSide?.value]);

  useEffect(() => {
    setSelectedSizeValue(initialSelectedSize?.value ?? '');
  }, [initialSelectedSize?.value]);

  useEffect(() => {
    return () => {
      if (messageTimeoutRef.current !== null) {
        window.clearTimeout(messageTimeoutRef.current);
      }
    };
  }, []);

  const showMessage = (msg: string, type: 'success' | 'error' = 'error') => {
    setMessage(msg);
    setMessageType(type);

    if (messageTimeoutRef.current !== null) {
      window.clearTimeout(messageTimeoutRef.current);
    }

    messageTimeoutRef.current = window.setTimeout(() => {
      setMessage('');
      messageTimeoutRef.current = null;
    }, 3500);
  };

  useEffect(() => {
    if (flash?.success) {
      setMessage(flash.success);
      setMessageType('success');

      if (messageTimeoutRef.current !== null) {
        window.clearTimeout(messageTimeoutRef.current);
      }

      messageTimeoutRef.current = window.setTimeout(() => {
        setMessage('');
        messageTimeoutRef.current = null;
      }, 3500);
    } else if (flash?.error) {
      setMessage(flash.error);
      setMessageType('error');

      if (messageTimeoutRef.current !== null) {
        window.clearTimeout(messageTimeoutRef.current);
      }

      messageTimeoutRef.current = window.setTimeout(() => {
        setMessage('');
        messageTimeoutRef.current = null;
      }, 3500);
    }
  }, [flash?.success, flash?.error]);

  const compositePreviewWithMockup = async (mockupUrl: string): Promise<string | null> => {
      const designDataUrl = exportPreviewDataUrl();
      if (!designDataUrl) {
        return null;
      }

      const size = 800;
      const offscreen = document.createElement('canvas');
      offscreen.width = size;
      offscreen.height = size;
      const ctx = offscreen.getContext('2d');

      if (!ctx) {
        return null;
      }

      const loadImg = (src: string, cors = false) =>
        new Promise<HTMLImageElement | null>((resolve) => {
          const img = new Image();

          if (cors) {
            img.crossOrigin = 'anonymous';
          }

          img.onload = () => resolve(img);
          img.onerror = () => resolve(null);
          img.src = src;
        });

      const mockupImg = await loadImg(mockupUrl, true);

      if (mockupImg) {
        ctx.drawImage(mockupImg, 0, 0, size, size);
      }

      // These percentages must stay in sync with the resolved print area
      // used by FabricDesignerCanvas.tsx.
      const printLeft = (resolvedPrintArea.left / 100) * size;
      const printTop = (resolvedPrintArea.top / 100) * size;
      const printW = (resolvedPrintArea.width / 100) * size;
      const printH = (resolvedPrintArea.height / 100) * size;

      const designImg = await loadImg(designDataUrl);

      if (designImg) {
        const cw = normalizedTemplate.canvasWidth;
        const ch = normalizedTemplate.canvasHeight;
        const scale = Math.min(printW / cw, printH / ch);
        const dw = cw * scale;
        const dh = ch * scale;
        const dx = printLeft + (printW - dw) / 2;
        const dy = printTop + (printH - dh) / 2;

        ctx.drawImage(designImg, dx, dy, dw, dh);
      }

      return offscreen.toDataURL('image/jpeg', 0.85);
    };

  const handleToolSelect = (
    tool: 'text' | 'image' | 'clipart' | 'product',
  ) => {
    setSelectedTool((current) => {
      const nextTool = current === tool ? null : tool;

      if (current === 'text' && nextTool !== 'text' && hasPendingTextPreview) {
        discardTextPreview();
        setTextStyle((previous) => ({
          ...previous,
          text: '',
        }));
      }

      if (nextTool === 'text') {
        const isCurrentlyEditingExistingText =
          activeObjectType === 'text' &&
          activeTextStyle !== null &&
          !hasPendingTextPreview;

        if (!isCurrentlyEditingExistingText && !hasPendingTextPreview) {
          beginTextPreview(textStyle);
        }
      }

      return nextTool;
    });
  };

  const applyTextStyleUpdate = (updates: Partial<ActiveTextStyle>) => {
    setTextStyle((current) => {
      const next = { ...current, ...updates };

      if (hasPendingTextPreview) {
        updateTextPreview(updates);
      } else if (isEditingSelectedText) {
        updateActiveTextStyle(updates);
      }

      return next;
    });
  };

  const addTextElement = () => {
    try {
      if (hasPendingTextPreview) {
        commitTextPreview();
        setTextStyle((current) => ({
          ...current,
          text: '',
        }));
        return;
      }

      addText({
        text: textStyle.text,
        fontFamily: textStyle.fontFamily,
        fontSize: textStyle.fontSize,
        color: textStyle.color,
        fontWeight: textStyle.fontWeight,
        fontStyle: textStyle.fontStyle,
        underline: textStyle.underline,
        textAlign: textStyle.textAlign,
        letterSpacing: textStyle.letterSpacing,
        strokeColor: textStyle.strokeColor,
        strokeWidth: textStyle.strokeWidth,
        shadowEnabled: textStyle.shadowEnabled,
      });

      setTextStyle((current) => ({
        ...current,
        text: '',
      }));
    } catch (error) {
      showMessage(error instanceof Error ? error.message : 'Failed to add text.', 'error');
    }
  };

  const addImageElement = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setSelectedImageName(file.name);

    try {
      await addImageFromFile(file);
      setSelectedTool(null);
    } catch (error) {
      if (
        error instanceof Error &&
        error.message.includes(`up to ${MAX_IMAGES} images`)
      ) {
        setShowImageLimitDialog(true);
      }

      showMessage(error instanceof Error ? error.message : 'Failed to add image.', 'error');
    } finally {
      if (imageInputRef.current) {
        imageInputRef.current.value = '';
      }
    }
  };

  const addClipartElement = async (imageReference: string) => {
    try {
      await addClipart(imageReference);
      setSelectedTool(null);
    } catch (error) {
      if (
        error instanceof Error &&
        error.message.includes(`up to ${MAX_IMAGES} images`)
      ) {
        setShowImageLimitDialog(true);
      }

      showMessage(error instanceof Error ? error.message : 'Failed to add clipart.', 'error');
    }
  };

  const duplicateActiveObjectHandler = () => {
    try {
      duplicateActiveObject();
    } catch (error) {
      if (
        error instanceof Error &&
        error.message.includes(`up to ${MAX_IMAGES} images`)
      ) {
        setShowImageLimitDialog(true);
      }

      showMessage(error instanceof Error ? error.message : 'Failed to duplicate element.', 'error');
    }
  };

  const undoHandler = async () => {
    try {
      await undo();
    } catch {
      showMessage('Failed to undo the last action.', 'error');
    }
  };

  const requestClearDesign = () => {
    if (!hasDesignElements()) {
      showMessage('There are no design elements to clear.', 'error');
      return;
    }

    setShowClearDialog(true);
  };

  const confirmClearDesign = () => {
    clearDesign();
    setSelectedImageName('');
    setShowClearDialog(false);
    showMessage('Design cleared', 'success');
  };

  const saveDesign = async () => {
    if (!designName.trim() || designName.length > 100) {
      showMessage('Please enter a valid design name (1-100 characters).', 'error');
      return;
    }

    if (!hasDesignElements()) {
      showMessage('Please add design elements before saving.', 'error');
      return;
    }

    setIsSavingDesign(true);

    const preview = selectedShirtColor
      ? await compositePreviewWithMockup(selectedShirtColor.mockupImageUrl)
      : null;

    const payload = {
      product_id: product.product_id,
      design_name: designName.trim(),
      design_data: exportDesignJson(),
      preview_image_reference: preview,
      print_file_reference: exportPrintDataUrl(),
      customization_options: {
        shirt_color: selectedShirtColor
          ? {
              id: selectedShirtColor.id,
              label: selectedShirtColor.label,
            }
          : null,
        print_sides: selectedPrintSide
          ? {
              value: selectedPrintSide.value,
              label: selectedPrintSide.label,
            }
          : null,
        size: selectedSize
          ? {
              value: selectedSize.value,
              label: selectedSize.label,
            }
          : null,
      },
    };

    router.post('/design/save', payload, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        setShowSaveDialog(false);
        showMessage('Design saved successfully.', 'success');
      },
      onError: (errors) => {
        const firstError =
          errors.design_name ||
          errors.design_data ||
          errors.product_id ||
          Object.values(errors)[0] ||
          'Failed to save design.';

        setShowSaveDialog(false);
        showMessage(String(firstError), 'error');
      },
      onFinish: () => {
        setIsSavingDesign(false);
      },
    });
  };

  const addToCartHandler = async () => {
    if (!hasDesignElements()) {
      showMessage('Please add design elements first.', 'error');
      return;
    }

    setIsAddingToCart(true);

    const preview = selectedShirtColor
      ? await compositePreviewWithMockup(selectedShirtColor.mockupImageUrl)
      : null;

    const payload = {
      product_id: product.product_id,
      quantity,
      design_data: exportDesignJson(),
      preview_image_reference: preview,
      print_file_reference: exportPrintDataUrl(),
      customization_options: {
        shirt_color: selectedShirtColor
          ? {
              id: selectedShirtColor.id,
              label: selectedShirtColor.label,
            }
          : null,
        print_sides: selectedPrintSide
          ? {
              value: selectedPrintSide.value,
              label: selectedPrintSide.label,
            }
          : null,
        size: selectedSize
          ? {
              value: selectedSize.value,
              label: selectedSize.label,
            }
          : null,
      },
    };

    router.post('/cart', payload, {
      onSuccess: () => {
        router.visit('/cart');
      },
      onError: () => {
        showMessage('Failed to add to cart.', 'error');
      },
      onFinish: () => {
        setIsAddingToCart(false);
      },
    });
  };

  return (
    <>
      <Head title={`Design: ${product.product_name}`} />

      <div className="flex min-h-screen flex-col bg-gray-100 md:h-screen md:overflow-hidden">
        <div className="shrink-0 bg-white px-4 py-4 shadow-sm sm:px-6">
          <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div className="min-w-0">
              <h1 className="text-lg font-bold sm:text-xl wrap-break-word">
                Design: {product.product_name}
              </h1>
              <p className="text-sm text-gray-600">Create your custom design</p>
            </div>

            <button
              onClick={() => router.visit(`/product/customizable/${product.product_id}`)}
              className="inline-flex w-full cursor-pointer items-center justify-center rounded-md border border-blue-200 px-4 py-2 text-blue-600 transition hover:bg-blue-50 md:w-auto md:border-0 md:px-0 md:py-0 md:hover:bg-transparent md:hover:underline"
            >
              Exit Designer
            </button>
          </div>
        </div>

        {message && (
          <div
            className={`px-4 py-2 text-center text-sm sm:px-6 ${
              messageType === 'success'
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-800'
            }`}
          >
            {message}
          </div>
        )}

        <div className="flex flex-1 flex-col md:min-h-0 md:flex-row md:overflow-hidden">
          <div className="shrink-0 bg-white shadow-md md:w-72 md:overflow-hidden">
            <div className="overflow-y-auto p-4 md:h-full">
              <h2 className="mb-3 font-semibold">Design Tools</h2>

              <div className="mb-4 grid grid-cols-2 gap-2 md:block md:space-y-1.5">
                <button
                  onClick={() => handleToolSelect('product')}
                  className={`w-full cursor-pointer rounded px-3 py-2 text-left text-sm ${
                    selectedTool === 'product' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'
                  }`}
                >
                  <div className="flex items-center">
                    <PaintBucket className="mr-2 h-4 w-4" />
                    Colour Options
                  </div>
                </button>

                <button
                  onClick={() => handleToolSelect('text')}
                  className={`w-full cursor-pointer rounded px-3 py-2 text-left text-sm ${
                    selectedTool === 'text' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'
                  }`}
                >
                  <div className="flex items-center">
                    <Type className="mr-2 h-4 w-4" />
                    Add Text
                  </div>
                </button>

                <button
                  onClick={() => handleToolSelect('image')}
                  className={`w-full cursor-pointer rounded px-3 py-2 text-left text-sm ${
                    selectedTool === 'image' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'
                  }`}
                >
                  <div className="flex items-center">
                    <ImageIcon className="mr-2 h-4 w-4" />
                    Add Image
                  </div>
                </button>

                <button
                  onClick={() => handleToolSelect('clipart')}
                  className={`w-full cursor-pointer rounded px-3 py-2 text-left text-sm ${
                    selectedTool === 'clipart' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'
                  }`}
                >
                  <div className="flex items-center">
                    <Smile className="mr-2 h-4 w-4" />
                    Add Clipart
                  </div>
                </button>
              </div>

              {selectedTool === 'product' && (
                <div className="border-t pt-3">
                  <div className="mb-2">
                    <h3 className="text-sm font-medium">Colour Options</h3>
                    <p className="mt-1 text-[11px] leading-4 text-gray-500">
                      Choose a colour for the workspace preview and saved design preview.
                    </p>
                  </div>

                  <div className="space-y-3">
                    <div className="rounded-xl border border-gray-200 bg-gray-50 p-3">
                      <p className="text-xs font-medium text-gray-700">
                        Selected Colour: {selectedShirtColor?.label ?? '-'}
                      </p>

                      <div className="mt-3 grid grid-cols-4 gap-2">
                        {shirtColorOptions.map((option) => {
                          const isActive = option.id === selectedShirtColorId;

                          return (
                            <button
                              key={option.id}
                              type="button"
                              onClick={() => setSelectedShirtColorId(option.id)}
                              className={`flex min-h-23 cursor-pointer flex-col rounded-xl border p-1.5 text-center transition ${
                                isActive
                                  ? 'border-blue-300 bg-blue-50 ring-2 ring-blue-100'
                                  : 'border-gray-300 bg-white hover:bg-gray-50'
                              }`}
                              title={option.label}
                            >
                              <div className="flex h-14 items-center justify-center rounded-lg border border-gray-200 bg-gray-100">
                                {option.thumbnailImageUrl ? (
                                  <img
                                    src={option.thumbnailImageUrl}
                                    alt={option.label}
                                    className="h-10 w-10 object-contain"
                                  />
                                ) : (
                                  <div
                                    className="h-8 w-8 rounded-full border border-gray-300"
                                    style={{ backgroundColor: option.swatchHex }}
                                  />
                                )}
                              </div>

                              <div className="mt-1 flex min-h-7 items-start justify-center">
                                <span className="line-clamp-2 block text-[10px] font-medium leading-3 text-gray-700 wrap-break-word">
                                  {option.label}
                                </span>
                              </div>
                            </button>
                          );
                        })}
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {selectedTool === 'text' && (
                <div className="border-t pt-3">
                  <h3 className="mb-2 text-sm font-medium">
                    {isEditingSelectedText ? 'Edit Selected Text' : 'Text Properties'}
                  </h3>

                  <div className="space-y-2.5">
                    <textarea
                      value={textStyle.text}
                      onChange={(e) => applyTextStyleUpdate({ text: e.target.value })}
                      placeholder="Enter text..."
                      className="w-full rounded-md border px-3 py-2 text-sm"
                      rows={2}
                      maxLength={100}
                    />

                    <button
                      onClick={addTextElement}
                      disabled={!isReady}
                      className="w-full cursor-pointer rounded-md bg-blue-600 py-2 text-sm text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      Add Text
                    </button>

                    <select
                      value={textStyle.fontFamily}
                      onChange={(e) => applyTextStyleUpdate({ fontFamily: e.target.value })}
                      className="w-full cursor-pointer rounded-md border px-3 py-2 text-sm"
                    >
                      {FONT_OPTIONS.map((font) => (
                        <option key={font} value={font}>
                          {font}
                        </option>
                      ))}
                    </select>

                    <div className="grid grid-cols-[1fr_64px] gap-2">
                      <input
                        type="range"
                        min={8}
                        max={800}
                        value={textStyle.fontSize}
                        onChange={(e) =>
                          applyTextStyleUpdate({ fontSize: Number(e.target.value) })
                        }
                        className="w-full cursor-pointer"
                      />

                      <input
                        type="number"
                        value={textStyle.fontSize}
                        onChange={(e) =>
                          applyTextStyleUpdate({ fontSize: Number(e.target.value) })
                        }
                        min={8}
                        max={800}
                        className="w-full rounded-md border px-2 py-1.5 text-sm"
                      />
                    </div>

                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                      <div>
                        <label className="mb-1 block text-xs">Style</label>
                        <div className="flex gap-1.5">
                          <MiniToggleButton
                            active={textStyle.fontWeight === 'bold'}
                            title="Bold"
                            onClick={() =>
                              applyTextStyleUpdate({
                                fontWeight:
                                  textStyle.fontWeight === 'bold' ? 'normal' : 'bold',
                              })
                            }
                          >
                            <span className="text-xs font-bold">B</span>
                          </MiniToggleButton>

                          <MiniToggleButton
                            active={textStyle.fontStyle === 'italic'}
                            title="Italic"
                            onClick={() =>
                              applyTextStyleUpdate({
                                fontStyle:
                                  textStyle.fontStyle === 'italic' ? 'normal' : 'italic',
                              })
                            }
                          >
                            <span className="text-xs italic">I</span>
                          </MiniToggleButton>

                          <MiniToggleButton
                            active={textStyle.underline}
                            title="Underline"
                            onClick={() =>
                              applyTextStyleUpdate({
                                underline: !textStyle.underline,
                              })
                            }
                          >
                            <span className="text-xs underline">U</span>
                          </MiniToggleButton>
                        </div>
                      </div>

                      <div>
                        <label className="mb-1 block text-xs">Align</label>
                        <div className="flex gap-1.5">
                          <MiniToggleButton
                            active={textStyle.textAlign === 'left'}
                            title="Align Left"
                            onClick={() => applyTextStyleUpdate({ textAlign: 'left' })}
                          >
                            <AlignLeft className="h-3.5 w-3.5" />
                          </MiniToggleButton>

                          <MiniToggleButton
                            active={textStyle.textAlign === 'center'}
                            title="Align Center"
                            onClick={() => applyTextStyleUpdate({ textAlign: 'center' })}
                          >
                            <AlignCenter className="h-3.5 w-3.5" />
                          </MiniToggleButton>

                          <MiniToggleButton
                            active={textStyle.textAlign === 'right'}
                            title="Align Right"
                            onClick={() => applyTextStyleUpdate({ textAlign: 'right' })}
                          >
                            <AlignRight className="h-3.5 w-3.5" />
                          </MiniToggleButton>
                        </div>
                      </div>
                    </div>

                    <div>
                      <label className="mb-1 block text-xs">Letter Spacing</label>
                      <div className="grid grid-cols-[1fr_28px] items-center gap-2">
                        <input
                          type="range"
                          min={-10}
                          max={40}
                          value={textStyle.letterSpacing}
                          onChange={(e) =>
                            applyTextStyleUpdate({
                              letterSpacing: Number(e.target.value),
                            })
                          }
                          className="w-full cursor-pointer"
                        />
                        <span className="text-right text-[11px] text-gray-600">
                          {textStyle.letterSpacing}
                        </span>
                      </div>
                    </div>

                    <div>
                      <label className="mb-1 block text-xs">Color</label>

                      <div className="mb-2 flex flex-wrap gap-1">
                        {COLOR_SWATCHES.map((color) => (
                          <button
                            key={color}
                            type="button"
                            onClick={() => applyTextStyleUpdate({ color })}
                            className={`h-5 w-5 cursor-pointer rounded-full border ${
                              textStyle.color === color
                                ? 'border-gray-900 ring-2 ring-gray-300'
                                : 'border-gray-300'
                            }`}
                            style={{ backgroundColor: color }}
                            title={color}
                          />
                        ))}
                      </div>

                      <input
                        type="color"
                        value={textStyle.color}
                        onChange={(e) => applyTextStyleUpdate({ color: e.target.value })}
                        className="h-8 w-full cursor-pointer"
                      />
                    </div>

                    <div className="space-y-2 rounded-md border p-2">
                      <div className="flex items-center justify-between">
                        <h4 className="text-xs font-medium text-gray-700">Outline</h4>
                        <PaintBucket className="h-3.5 w-3.5 text-gray-500" />
                      </div>

                      <input
                        type="color"
                        value={textStyle.strokeColor}
                        onChange={(e) =>
                          applyTextStyleUpdate({ strokeColor: e.target.value })
                        }
                        className="h-8 w-full cursor-pointer"
                      />

                      <div className="grid grid-cols-[1fr_28px] items-center gap-2">
                        <input
                          type="range"
                          min={0}
                          max={8}
                          value={textStyle.strokeWidth}
                          onChange={(e) =>
                            applyTextStyleUpdate({
                              strokeWidth: Number(e.target.value),
                            })
                          }
                          className="w-full cursor-pointer"
                        />
                        <span className="text-right text-[11px] text-gray-600">
                          {textStyle.strokeWidth}
                        </span>
                      </div>
                    </div>

                    <div className="rounded-md border p-2">
                      <div className="flex items-center justify-between gap-2">
                        <div>
                          <h4 className="text-xs font-medium text-gray-700">Shadow</h4>
                        </div>

                        <button
                          type="button"
                          onClick={() =>
                            applyTextStyleUpdate({
                              shadowEnabled: !textStyle.shadowEnabled,
                            })
                          }
                          className={`inline-flex cursor-pointer items-center rounded border px-2 py-1 text-[11px] transition ${
                            textStyle.shadowEnabled
                              ? 'border-blue-300 bg-blue-50 text-blue-700'
                              : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                          }`}
                        >
                          <Sparkles className="mr-1 h-3 w-3" />
                          {textStyle.shadowEnabled ? 'On' : 'Off'}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {selectedTool === 'image' && (
                <div className="border-t pt-3">
                  <h3 className="mb-2 text-sm font-medium">Upload Image</h3>

                  <input
                    ref={imageInputRef}
                    type="file"
                    accept="image/png,image/jpeg,image/jpg"
                    onChange={addImageElement}
                    className="hidden"
                  />

                  <div className="rounded-md border border-gray-300 bg-white">
                    <div className="flex flex-col gap-2 p-3">
                      <button
                        type="button"
                        onClick={() => imageInputRef.current?.click()}
                        className="inline-flex w-full cursor-pointer items-center justify-center rounded-md border border-blue-300 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100"
                      >
                        Choose Image
                      </button>

                      <div className="min-w-0">
                        <p
                          className={`text-sm wrap-break-word ${
                            selectedImageName ? 'text-gray-900' : 'text-gray-500'
                          }`}
                        >
                          {selectedImageName || 'No image selected'}
                        </p>

                        <p className="mt-1 text-xs text-gray-500">
                          PNG, JPG, or JPEG up to 10 MB.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {selectedTool === 'clipart' && (
                <div className="border-t pt-3">
                  <h3 className="mb-2 text-sm font-medium">Select Clipart</h3>

                  <div className="grid grid-cols-3 gap-1.5">
                    {clipart.map((clipartItem) => (
                      <button
                        key={clipartItem.clipart_id}
                        onClick={() => addClipartElement(clipartItem.image_reference)}
                        className="flex cursor-pointer items-center justify-center rounded border p-2 hover:bg-gray-50"
                        title={clipartItem.clipart_name}
                      >
                        <img
                          src={clipartItem.image_reference}
                          alt={clipartItem.clipart_name}
                          className="h-9 w-9 object-contain"
                        />
                      </button>
                    ))}
                  </div>
                </div>
              )}

              <div className="mt-4 space-y-1.5 border-t pt-3">
                <button
                  onClick={undoHandler}
                  disabled={!canUndo}
                  className="w-full cursor-pointer rounded bg-gray-100 px-4 py-2 text-sm hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  <div className="flex items-center justify-center">
                    <Undo className="mr-2 h-4 w-4" />
                    Undo
                  </div>
                </button>

                <button
                  onClick={deleteActiveObject}
                  disabled={!isReady || activeObjectType === null}
                  className="w-full cursor-pointer rounded bg-amber-100 px-4 py-2 text-sm text-amber-800 hover:bg-amber-200 disabled:cursor-not-allowed disabled:opacity-60"
                >
                  <div className="flex items-center justify-center">
                    <Trash2 className="mr-2 h-4 w-4" />
                    Delete Selected
                  </div>
                </button>

                <button
                  onClick={requestClearDesign}
                  disabled={!isReady}
                  className="w-full cursor-pointer rounded bg-red-100 px-4 py-2 text-sm text-red-700 hover:bg-red-200 disabled:cursor-not-allowed disabled:opacity-60"
                >
                  <div className="flex items-center justify-center">
                    <Trash2 className="mr-2 h-4 w-4" />
                    Clear All
                  </div>
                </button>

                <button
                  onClick={() => setShowSaveDialog(true)}
                  disabled={!isReady}
                  className="w-full cursor-pointer rounded bg-green-100 px-4 py-2 text-sm text-green-700 hover:bg-green-200 disabled:cursor-not-allowed disabled:opacity-60"
                >
                  <div className="flex items-center justify-center">
                    <Save className="mr-2 h-4 w-4" />
                    Save Design
                  </div>
                </button>
              </div>
            </div>
          </div>

          <div className="flex min-w-0 flex-1 flex-col p-4 sm:p-5 md:min-h-0 md:overflow-hidden md:p-8">
            <div className="min-h-85 flex-1 overflow-hidden rounded-lg bg-white shadow-lg">
              <FabricDesignerCanvas
                template={normalizedTemplate}
                initialDesignJson={extractCanvasJson(initialDesign?.design_data ?? null)}
                mockupImageUrl={selectedShirtColor?.mockupImageUrl ?? null}
                zoomLevel={zoomLevel}
                onZoomIn={zoomIn}
                onZoomOut={zoomOut}
                onResetZoom={resetZoom}
                attachCanvas={attachCanvas}
                detachCanvas={detachCanvas}
                initializeWorkspace={initializeWorkspace}
                activeObjectType={activeObjectType}
                onDuplicate={duplicateActiveObjectHandler}
                onFlipH={flipActiveObjectHorizontal}
                onFlipV={flipActiveObjectVertical}
                onScaleToFill={scaleActiveObjectToFill}
              />
            </div>

            <div className="mt-4 flex shrink-0 flex-col gap-4 rounded-lg bg-white p-4 shadow-md md:flex-row md:items-end md:justify-between">
              <div className="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end md:flex-nowrap">
                <div className="flex flex-col justify-end">
                  <label className="mb-1 block text-sm font-medium text-gray-700">Quantity</label>
                  <input
                    type="number"
                    value={quantity}
                    onChange={(e) =>
                      setQuantity(Math.max(1, Math.min(99, Number(e.target.value))))
                    }
                    min={1}
                    max={99}
                    className="h-10 w-full rounded-md border px-3 text-sm sm:w-20"
                  />
                </div>

                {printSideOptions.length > 0 && (
                  <div className="flex flex-col justify-end">
                    <label className="mb-1 block text-sm font-medium text-gray-700">
                      Print Sides
                    </label>

                    <select
                      value={selectedPrintSideValue}
                      onChange={(e) => setSelectedPrintSideValue(e.target.value)}
                      className="h-10 w-full cursor-pointer rounded-md border px-3 text-sm sm:min-w-60"
                    >
                      {printSideOptions.map((option) => (
                        <option key={option.value} value={option.value}>
                          {option.label}
                        </option>
                      ))}
                    </select>
                  </div>
                )}
              </div>

              <button
                onClick={addToCartHandler}
                disabled={isAddingToCart || !isReady}
                className="w-full cursor-pointer rounded-md bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60 md:w-auto"
              >
                <div className="flex items-center">
                  <ShoppingCart className="mr-2 h-5 w-5" />
                  {isAddingToCart ? 'Adding...' : 'Add to Cart'}
                </div>
              </button>
            </div>
          </div>
        </div>

        {showSaveDialog && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
              <div className="mb-5">
                <h3 className="text-lg font-semibold text-gray-900">Save Design</h3>
                <p className="mt-1 text-sm text-gray-500">
                  Give your design a clear name so you can find it later.
                </p>
              </div>

              <input
                type="text"
                value={designName}
                onChange={(e) => setDesignName(e.target.value)}
                placeholder="Enter design name..."
                className="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                maxLength={100}
              />

              <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row">
                <button
                  onClick={() => setShowSaveDialog(false)}
                  className="w-full cursor-pointer rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:flex-1"
                >
                  Cancel
                </button>

                <button
                  onClick={saveDesign}
                  disabled={isSavingDesign}
                  className="w-full cursor-pointer rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60 sm:flex-1"
                >
                  {isSavingDesign ? 'Saving...' : 'Save Design'}
                </button>
              </div>
            </div>
          </div>
        )}

        {showClearDialog && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
              <div className="mb-5">
                <h3 className="text-lg font-semibold text-gray-900">Clear Design?</h3>
                <p className="mt-1 text-sm text-gray-500">
                  Are you sure you want to clear all current design elements from the canvas?
                </p>
                <p className="mt-1 text-sm text-gray-500">
                  This action cannot be undone.
                </p>
              </div>

              <div className="flex flex-col-reverse gap-3 sm:flex-row">
                <button
                  onClick={() => setShowClearDialog(false)}
                  className="w-full cursor-pointer rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:flex-1"
                >
                  Cancel
                </button>

                <button
                  onClick={confirmClearDesign}
                  className="w-full cursor-pointer rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700 sm:flex-1"
                >
                  Clear Design
                </button>
              </div>
            </div>
          </div>
        )}

        {showImageLimitDialog && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
              <div className="mb-5">
                <h3 className="text-lg font-semibold text-gray-900">Image Limit Reached</h3>
                <p className="mt-1 text-sm text-gray-500">
                  You can add up to {MAX_IMAGES} images or clipart items to one design.
                </p>
                <p className="mt-1 text-sm text-gray-500">
                  Remove an existing image before adding a new one.
                </p>
              </div>

              <div className="flex justify-end">
                <button
                  onClick={() => setShowImageLimitDialog(false)}
                  className="w-full cursor-pointer rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 sm:w-auto"
                >
                  Got It
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  );
}

(DesignWorkspace as typeof DesignWorkspace & {
  layout?: (page: ReactNode) => ReactNode;
}).layout = (page: ReactNode) => page;