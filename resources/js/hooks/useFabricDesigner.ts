import { FabricImage, Shadow, Textbox } from 'fabric';
import type { Canvas, FabricObject } from 'fabric';
import { useEffect, useRef, useState } from 'react';

export const MAX_IMAGES = 20;

type AddTextOptions = {
  text: string;
  fontFamily: string;
  fontSize: number;
  color: string;
  fontWeight: 'normal' | 'bold';
  fontStyle: 'normal' | 'italic';
  underline: boolean;
  textAlign: 'left' | 'center' | 'right';
  letterSpacing: number;
  strokeColor: string;
  strokeWidth: number;
  shadowEnabled: boolean;
};

export type ActiveObjectType = 'text' | 'image' | null;

export type ActiveTextStyle = {
  text: string;
  fontFamily: string;
  fontSize: number;
  color: string;
  fontWeight: 'normal' | 'bold';
  fontStyle: 'normal' | 'italic';
  underline: boolean;
  textAlign: 'left' | 'center' | 'right';
  letterSpacing: number;
  strokeColor: string;
  strokeWidth: number;
  shadowEnabled: boolean;
};

type UseFabricDesignerResult = {
  canUndo: boolean;
  isReady: boolean;
  zoomLevel: number;
  activeObjectType: ActiveObjectType;
  activeTextStyle: ActiveTextStyle | null;
  hasPendingTextPreview: boolean;
  attachCanvas: (canvas: Canvas) => void;
  detachCanvas: () => void;
  initializeWorkspace: (initialDesignJson?: string | null) => Promise<void>;
  addText: (options: AddTextOptions) => void;
  beginTextPreview: (options: AddTextOptions) => void;
  updateTextPreview: (updates: Partial<ActiveTextStyle>) => void;
  commitTextPreview: () => void;
  discardTextPreview: () => void;
  addImageFromFile: (file: File) => Promise<void>;
  addClipart: (imageUrl: string) => Promise<void>;
  updateActiveTextStyle: (updates: Partial<ActiveTextStyle>) => void;
  deleteActiveObject: () => void;
  undo: () => Promise<void>;
  clearDesign: () => void;
  exportDesignJson: () => string;
  exportPreviewDataUrl: () => string | null;
  exportPrintDataUrl: () => string | null;
  hasDesignElements: () => boolean;
  duplicateActiveObject: () => void;
  flipActiveObjectHorizontal: () => void;
  flipActiveObjectVertical: () => void;
  scaleActiveObjectToFill: () => void;
  zoomIn: () => void;
  zoomOut: () => void;
  resetZoom: () => void;
};

function readFileAsDataUrl(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onload = () => {
      if (typeof reader.result === 'string') {
        resolve(reader.result);
        return;
      }

      reject(new Error('Failed to read image file.'));
    };

    reader.onerror = () => reject(new Error('Failed to read image file.'));
    reader.readAsDataURL(file);
  });
}

function loadImageElement(src: string): Promise<HTMLImageElement> {
  return new Promise((resolve, reject) => {
    const image = new Image();

    if (!src.startsWith('data:')) {
      image.crossOrigin = 'anonymous';
    }

    image.onload = () => resolve(image);
    image.onerror = () => reject(new Error('Failed to load image.'));
    image.src = src;
  });
}

function clampFontSize(value: number): number {
  return Math.max(8, Math.min(800, Number.isFinite(value) ? value : 200));
}

function clampLetterSpacing(value: number): number {
  return Math.max(-10, Math.min(40, Number.isFinite(value) ? value : 0));
}

function clampStrokeWidth(value: number): number {
  return Math.max(0, Math.min(8, Number.isFinite(value) ? value : 0));
}

function isTextboxObject(object: FabricObject | null | undefined): object is Textbox {
  return !!object && object instanceof Textbox;
}

function buildShadow(enabled: boolean): Shadow | undefined {
  if (!enabled) {
    return undefined;
  }

  return new Shadow({
    color: 'rgba(0, 0, 0, 0.28)',
    blur: 6,
    offsetX: 2,
    offsetY: 2,
  });
}

function mapTextboxToStyle(textbox: Textbox): ActiveTextStyle {
  const rawTextAlign =
    textbox.textAlign === 'right' || textbox.textAlign === 'center'
      ? textbox.textAlign
      : 'left';

  return {
    text: textbox.text ?? '',
    fontFamily: textbox.fontFamily || 'Arial',
    fontSize: clampFontSize(Number(textbox.fontSize ?? 16)),
    color: typeof textbox.fill === 'string' ? textbox.fill : '#000000',
    fontWeight: textbox.fontWeight === 'bold' ? 'bold' : 'normal',
    fontStyle: textbox.fontStyle === 'italic' ? 'italic' : 'normal',
    underline: Boolean(textbox.underline),
    textAlign: rawTextAlign,
    letterSpacing: clampLetterSpacing(Math.round((textbox.charSpacing ?? 0) / 100)),
    strokeColor: typeof textbox.stroke === 'string' ? textbox.stroke : '#000000',
    strokeWidth: clampStrokeWidth(Number(textbox.strokeWidth ?? 0)),
    shadowEnabled: Boolean(textbox.shadow),
  };
}

function isCornerControlName(corner: unknown): boolean {
  return corner === 'tl' || corner === 'tr' || corner === 'bl' || corner === 'br';
}

function getCanvasDisplayScale(canvas: Canvas): number {
  const element = canvas.getElement();

  if (!element) {
    return 1;
  }

  const rect = element.getBoundingClientRect();
  const logicalWidth = canvas.getWidth();

  if (!rect.width || !logicalWidth) {
    return 1;
  }

  const scale = rect.width / logicalWidth;

  return scale > 0 ? scale : 1;
}

function applyAdaptiveSelectionStyle(
  object: FabricObject,
  canvas: Canvas,
) {
  const displayScale = getCanvasDisplayScale(canvas);

  /**
   * When the canvas is visually shrunk with CSS transform,
   * Fabric controls become tiny. Compensate by enlarging them
   * inversely to the display scale.
   */
  const compensation = Math.min(Math.max(1 / displayScale, 1), 2.4);

  object.set({
    borderColor: '#2563EB',
    cornerColor: '#ffffff',
    cornerStrokeColor: '#2563EB',
    cornerStyle: 'rect',
    transparentCorners: false,
    cornerSize: Math.round(8 * compensation),
    touchCornerSize: Math.round(24 * compensation),
    padding: Math.round(6 * compensation),
    borderScaleFactor: 1.25 * compensation,
  });

  if (object.controls.tl) {
    object.controls.tl.cursorStyleHandler = () => 'nwse-resize';
  }

  if (object.controls.br) {
    object.controls.br.cursorStyleHandler = () => 'nwse-resize';
  }

  if (object.controls.tr) {
    object.controls.tr.cursorStyleHandler = () => 'nesw-resize';
  }

  if (object.controls.bl) {
    object.controls.bl.cursorStyleHandler = () => 'nesw-resize';
  }

  if (object.controls.ml) {
    object.controls.ml.cursorStyleHandler = () => 'ew-resize';
  }

  if (object.controls.mr) {
    object.controls.mr.cursorStyleHandler = () => 'ew-resize';
  }

  if (object.controls.mt) {
    object.controls.mt.cursorStyleHandler = () => 'ns-resize';
  }

  if (object.controls.mb) {
    object.controls.mb.cursorStyleHandler = () => 'ns-resize';
  }
}

function normalizeTextboxCornerScale(textbox: Textbox) {
  const scaleX = Math.abs(Number(textbox.scaleX ?? 1));
  const scaleY = Math.abs(Number(textbox.scaleY ?? 1));
  const uniformScale = Math.max(scaleX, scaleY);

  if (!Number.isFinite(uniformScale) || uniformScale <= 0 || uniformScale === 1) {
    return;
  }

  const currentFontSize = clampFontSize(Number(textbox.fontSize ?? 16));
  const nextFontSize = clampFontSize(currentFontSize * uniformScale);

  textbox.set({
    fontSize: nextFontSize,
    scaleX: 1,
    scaleY: 1,
  });

  textbox.initDimensions();
  textbox.setCoords();
}

function createTextbox(canvas: Canvas, options: AddTextOptions, displayText?: string): Textbox {
  const textbox = new Textbox(displayText ?? options.text, {
    left: canvas.getWidth() / canvas.getZoom() / 2,
    top: canvas.getHeight() / canvas.getZoom() / 2,
    originX: 'center',
    originY: 'center',
    width: Math.round((canvas.getWidth() / canvas.getZoom()) * 0.7),
    editable: true,
    fill: options.color,
    fontFamily: options.fontFamily,
    fontSize: clampFontSize(options.fontSize),
    fontWeight: options.fontWeight,
    fontStyle: options.fontStyle,
    underline: options.underline,
    textAlign: options.textAlign,
    charSpacing: clampLetterSpacing(options.letterSpacing) * 100,
    stroke: options.strokeWidth > 0 ? options.strokeColor : undefined,
    strokeWidth: clampStrokeWidth(options.strokeWidth),
    paintFirst: 'stroke',
    shadow: buildShadow(options.shadowEnabled),
    lockScalingFlip: true,
  });

  textbox.setControlsVisibility({
    mt: true,
    mb: true,
    ml: true,
    mr: true,
    tl: true,
    tr: true,
    bl: true,
    br: true,
    mtr: true,
  });

  applyAdaptiveSelectionStyle(textbox, canvas);

  return textbox;
}

function isTypingIntoField(target: EventTarget | null): boolean {
  if (!(target instanceof HTMLElement)) {
    return false;
  }

  const tagName = target.tagName.toLowerCase();

  return (
    target.isContentEditable ||
    tagName === 'input' ||
    tagName === 'textarea' ||
    tagName === 'select'
  );
}

function countCanvasImages(canvas: Canvas, previewText: Textbox | null): number {
  return canvas
    .getObjects()
    .filter((object) => object !== previewText && object instanceof FabricImage).length;
}

export function useFabricDesigner(): UseFabricDesignerResult {
  const canvasRef = useRef<Canvas | null>(null);
  const historyRef = useRef<string[]>([]);
  const isRestoringRef = useRef(false);
  const previewTextRef = useRef<Textbox | null>(null);
  const previewTextStyleRef = useRef<ActiveTextStyle | null>(null);
  const deleteActiveObjectRef = useRef<(() => void) | null>(null);

  const [canUndo, setCanUndo] = useState(false);
  const [isReady, setIsReady] = useState(false);
  const [activeObjectType, setActiveObjectType] = useState<ActiveObjectType>(null);
  const [activeTextStyle, setActiveTextStyle] = useState<ActiveTextStyle | null>(null);
  const [hasPendingTextPreview, setHasPendingTextPreview] = useState(false);
  const ZOOM_STEP = 0.15;
  const ZOOM_MIN = 1;
  const ZOOM_MAX = 3.0;
  const [zoomLevel, setZoomLevel] = useState(1);

  const zoomIn = () => {
    setZoomLevel((previous) =>
      Math.min(+(previous + ZOOM_STEP).toFixed(2), ZOOM_MAX),
    );
  };

  const zoomOut = () => {
    setZoomLevel((previous) =>
      Math.max(+(previous - ZOOM_STEP).toFixed(2), ZOOM_MIN),
    );
  };

  const resetZoom = () => {
    setZoomLevel(1);
  };

  const getCanvas = () => {
    if (!canvasRef.current) {
      throw new Error('Canvas is not ready yet.');
    }

    return canvasRef.current;
  };

  const ensureImageLimitNotExceeded = (canvas: Canvas) => {
    const currentImageCount = countCanvasImages(canvas, previewTextRef.current);

    if (currentImageCount >= MAX_IMAGES) {
      throw new Error(`You can add up to ${MAX_IMAGES} images in a single design.`);
    }
  };

  const isPreviewTextbox = (object: FabricObject | null | undefined) => {
    return !!object && object === previewTextRef.current;
  };

  const refreshUndoState = () => {
    setCanUndo(historyRef.current.length > 1);
  };

  const withPreviewHidden = <T,>(callback: () => T): T => {
    const canvas = canvasRef.current;
    const previewText = previewTextRef.current;

    if (!canvas || !previewText) {
      return callback();
    }

    const activeObject = canvas.getActiveObject();
    const shouldRestoreSelection = activeObject === previewText;

    canvas.remove(previewText);

    try {
      return callback();
    } finally {
      canvas.add(previewText);

      if (shouldRestoreSelection) {
        canvas.setActiveObject(previewText);
      }

      canvas.requestRenderAll();
    }
  };

  const getSnapshot = () => {
    return withPreviewHidden(() => JSON.stringify(getCanvas().toJSON()));
  };

  const syncSelectionState = () => {
    const canvas = canvasRef.current;

    if (!canvas) {
      setActiveObjectType(null);
      setActiveTextStyle(null);
      return;
    }

    const activeObject = canvas.getActiveObject();

    if (isTextboxObject(activeObject)) {
      setActiveObjectType('text');

      if (isPreviewTextbox(activeObject) && previewTextStyleRef.current) {
        setActiveTextStyle(previewTextStyleRef.current);
        return;
      }

      setActiveTextStyle(mapTextboxToStyle(activeObject));
      return;
    }

    if (activeObject) {
      setActiveObjectType('image');
      setActiveTextStyle(null);
      return;
    }

    setActiveObjectType(null);
    setActiveTextStyle(null);
  };

  const pushSnapshot = () => {
    if (isRestoringRef.current) {
      return;
    }

    const snapshot = getSnapshot();
    const last = historyRef.current[historyRef.current.length - 1];

    if (snapshot === last) {
      return;
    }

    historyRef.current.push(snapshot);
    refreshUndoState();
  };

  const clearPreviewState = () => {
    previewTextRef.current = null;
    previewTextStyleRef.current = null;
    setHasPendingTextPreview(false);
  };

  const discardTextPreview = () => {
    const canvas = canvasRef.current;
    const previewText = previewTextRef.current;

    if (!canvas || !previewText) {
      clearPreviewState();
      syncSelectionState();
      return;
    }

    canvas.remove(previewText);
    canvas.discardActiveObject();
    canvas.requestRenderAll();

    clearPreviewState();
    syncSelectionState();
  };

  const deleteActiveObject = () => {
    const canvas = getCanvas();
    const activeObject = canvas.getActiveObject();

    if (!activeObject) {
      return;
    }

    if (isPreviewTextbox(activeObject)) {
      discardTextPreview();
      return;
    }

    canvas.remove(activeObject);
    canvas.discardActiveObject();
    canvas.requestRenderAll();
    pushSnapshot();
    syncSelectionState();
  };

  deleteActiveObjectRef.current = deleteActiveObject;

  const attachCanvas = (canvas: Canvas) => {
      canvasRef.current = canvas;

      canvas.on('selection:created', (event) => {
        const selected = event.selected ?? [];

        selected.forEach((object) => {
          applyAdaptiveSelectionStyle(object, canvas);
        });

        syncSelectionState();
        canvas.requestRenderAll();
      });

      canvas.on('selection:updated', (event) => {
        const selected = event.selected ?? [];

        selected.forEach((object) => {
          applyAdaptiveSelectionStyle(object, canvas);
        });

        syncSelectionState();
        canvas.requestRenderAll();
      });

      canvas.on('selection:cleared', syncSelectionState);

      canvas.on('object:scaling', (event) => {
        const target = event?.target;

        if (!isTextboxObject(target)) {
          return;
        }

        const corner = event?.transform?.corner;

        if (!isCornerControlName(corner)) {
          return;
        }

        normalizeTextboxCornerScale(target);
        syncSelectionState();
        canvas.requestRenderAll();
      });

      canvas.on('object:modified', () => {
        if (isPreviewTextbox(canvas.getActiveObject())) {
          return;
        }

        pushSnapshot();
        syncSelectionState();
      });

      canvas.on('text:changed', () => {
        if (isPreviewTextbox(canvas.getActiveObject())) {
          return;
        }

        pushSnapshot();
        syncSelectionState();
      });

      setIsReady(true);
    };

  const detachCanvas = () => {
    const canvas = canvasRef.current;

    if (canvas) {
      canvas.off('selection:created');
      canvas.off('selection:updated');
      canvas.off('selection:cleared');
      canvas.off('object:scaling');
      canvas.off('object:modified');
      canvas.off('text:changed');
    }

    canvasRef.current = null;
    historyRef.current = [];
    clearPreviewState();
    setCanUndo(false);
    setIsReady(false);
    setActiveObjectType(null);
    setActiveTextStyle(null);
  };

  const initializeWorkspace = async (initialDesignJson?: string | null) => {
      const canvas = getCanvas();

      isRestoringRef.current = true;
      canvas.clear();
      clearPreviewState();

      if (initialDesignJson) {
        try {
          const parsed = JSON.parse(initialDesignJson);

          if (
            parsed &&
            typeof parsed === 'object' &&
            !Array.isArray(parsed) &&
            ('objects' in parsed || 'version' in parsed)
          ) {
            await canvas.loadFromJSON(initialDesignJson);
          }
        } catch {
          // Ignore legacy or invalid JSON for now.
        }
      }

      canvas.discardActiveObject();
      canvas.requestRenderAll();

      isRestoringRef.current = false;
      historyRef.current = [getSnapshot()];
      refreshUndoState();
      syncSelectionState();
    };

  const addText = (options: AddTextOptions) => {
      const canvas = getCanvas();
      const text = options.text.trim();

      if (!text || text.length > 100) {
        throw new Error('Text must be between 1 and 100 characters.');
      }

      const textbox = createTextbox(canvas, options, text);

      canvas.add(textbox);
      canvas.setActiveObject(textbox);
      canvas.requestRenderAll();
      pushSnapshot();
      syncSelectionState();
    };

  const beginTextPreview = (options: AddTextOptions) => {
      const canvas = getCanvas();
      const activeObject = canvas.getActiveObject();

      if (isTextboxObject(activeObject) && !isPreviewTextbox(activeObject)) {
        syncSelectionState();
        return;
      }

      if (previewTextRef.current) {
        canvas.setActiveObject(previewTextRef.current);
        canvas.requestRenderAll();
        syncSelectionState();
        return;
      }

      const previewStyle: ActiveTextStyle = {
        text: options.text,
        fontFamily: options.fontFamily,
        fontSize: options.fontSize,
        color: options.color,
        fontWeight: options.fontWeight,
        fontStyle: options.fontStyle,
        underline: options.underline,
        textAlign: options.textAlign,
        letterSpacing: options.letterSpacing,
        strokeColor: options.strokeColor,
        strokeWidth: options.strokeWidth,
        shadowEnabled: options.shadowEnabled,
      };

      const previewText = options.text.trim() ? options.text : 'Your text';
      const textbox = createTextbox(canvas, options, previewText);

      textbox.set({
        opacity: options.text.trim() ? 1 : 0.65,
      });

      previewTextRef.current = textbox;
      previewTextStyleRef.current = previewStyle;
      setHasPendingTextPreview(true);

      canvas.add(textbox);
      canvas.setActiveObject(textbox);
      canvas.requestRenderAll();
      syncSelectionState();
    };

  const updateTextPreview = (updates: Partial<ActiveTextStyle>) => {
      const canvas = getCanvas();
      const previewText = previewTextRef.current;

      if (!previewText || !previewTextStyleRef.current) {
        return;
      }

      const nextStyle = {
        ...previewTextStyleRef.current,
        ...updates,
      };

      previewTextStyleRef.current = nextStyle;

      const displayText = nextStyle.text.trim() ? nextStyle.text : 'Your text';

      previewText.set({
        text: displayText,
        fontFamily: nextStyle.fontFamily,
        fontSize: clampFontSize(nextStyle.fontSize),
        fill: nextStyle.color,
        fontWeight: nextStyle.fontWeight,
        fontStyle: nextStyle.fontStyle,
        underline: nextStyle.underline,
        textAlign: nextStyle.textAlign,
        charSpacing: clampLetterSpacing(nextStyle.letterSpacing) * 100,
        stroke: nextStyle.strokeWidth > 0 ? nextStyle.strokeColor : undefined,
        strokeWidth: clampStrokeWidth(nextStyle.strokeWidth),
        paintFirst: 'stroke',
        shadow: buildShadow(nextStyle.shadowEnabled),
        opacity: nextStyle.text.trim() ? 1 : 0.65,
      });

      previewText.initDimensions();
      previewText.setCoords();
      canvas.setActiveObject(previewText);
      canvas.requestRenderAll();
      syncSelectionState();
    };

  const commitTextPreview = () => {
    const canvas = getCanvas();
    const previewText = previewTextRef.current;
    const previewStyle = previewTextStyleRef.current;

    if (!previewText || !previewStyle) {
      return;
    }

    const committedText = previewStyle.text.trim();

    if (!committedText || committedText.length > 100) {
      throw new Error('Text must be between 1 and 100 characters.');
    }

    previewText.set({
      text: committedText,
      opacity: 1,
    });

    clearPreviewState();

    canvas.setActiveObject(previewText);
    previewText.setCoords();
    canvas.requestRenderAll();
    pushSnapshot();
    syncSelectionState();
  };

  const addImageFromUrl = async (src: string) => {
      const canvas = getCanvas();
      ensureImageLimitNotExceeded(canvas);
      const imageElement = await loadImageElement(src);

      const image = new FabricImage(imageElement, {
        left: canvas.getWidth() / canvas.getZoom() / 2,
        top: canvas.getHeight() / canvas.getZoom() / 2,
        originX: 'center',
        originY: 'center',
      });

      const baseWidth = image.width ?? imageElement.naturalWidth ?? 1;
      const baseHeight = image.height ?? imageElement.naturalHeight ?? 1;

      const maxWidth = (canvas.getWidth() / canvas.getZoom()) * 0.65;
      const maxHeight = (canvas.getHeight() / canvas.getZoom()) * 0.65;
      const scale = Math.min(maxWidth / baseWidth, maxHeight / baseHeight, 1);

      image.scale(scale);

      image.setControlsVisibility({
        mt: true,
        mb: true,
        ml: true,
        mr: true,
        tl: true,
        tr: true,
        bl: true,
        br: true,
        mtr: true,
      });

      image.set({
        lockUniScaling: false,
      });

      applyAdaptiveSelectionStyle(image, canvas);

      canvas.add(image);
      canvas.setActiveObject(image);
      canvas.requestRenderAll();
      pushSnapshot();
      syncSelectionState();
    };

  const addImageFromFile = async (file: File) => {
      if (!['image/png', 'image/jpeg', 'image/jpg'].includes(file.type)) {
        throw new Error('Only PNG, JPG, and JPEG files are allowed.');
      }

      if (file.size > 10 * 1024 * 1024) {
        throw new Error('Image must be 10MB or smaller.');
      }

      const dataUrl = await readFileAsDataUrl(file);
      await addImageFromUrl(dataUrl);
    };

  const addClipart = async (imageUrl: string) => {
      await addImageFromUrl(imageUrl);
    };

  const updateActiveTextStyle = (updates: Partial<ActiveTextStyle>) => {
      const canvas = getCanvas();
      const activeObject = canvas.getActiveObject();

      if (!isTextboxObject(activeObject) || isPreviewTextbox(activeObject)) {
        return;
      }

      const nextStyle = {
        ...mapTextboxToStyle(activeObject),
        ...updates,
      };

      activeObject.set({
        text: nextStyle.text,
        fontFamily: nextStyle.fontFamily,
        fontSize: clampFontSize(nextStyle.fontSize),
        fill: nextStyle.color,
        fontWeight: nextStyle.fontWeight,
        fontStyle: nextStyle.fontStyle,
        underline: nextStyle.underline,
        textAlign: nextStyle.textAlign,
        charSpacing: clampLetterSpacing(nextStyle.letterSpacing) * 100,
        stroke: nextStyle.strokeWidth > 0 ? nextStyle.strokeColor : undefined,
        strokeWidth: clampStrokeWidth(nextStyle.strokeWidth),
        paintFirst: 'stroke',
        shadow: buildShadow(nextStyle.shadowEnabled),
      });

      activeObject.initDimensions();
      activeObject.setCoords();
      canvas.requestRenderAll();
      pushSnapshot();
      syncSelectionState();
    };

  const undo = async () => {
    const canvas = getCanvas();

    if (previewTextRef.current) {
      discardTextPreview();
    }

    if (historyRef.current.length <= 1) {
      return;
    }

    historyRef.current.pop();
    const previous = historyRef.current[historyRef.current.length - 1];

    isRestoringRef.current = true;
    await canvas.loadFromJSON(previous);
    canvas.discardActiveObject();
    canvas.requestRenderAll();
    isRestoringRef.current = false;

    refreshUndoState();
    syncSelectionState();
  };

  const clearDesign = () => {
    const canvas = getCanvas();
    const objects = [...canvas.getObjects()];

    objects.forEach((object) => canvas.remove(object));

    canvas.discardActiveObject();
    canvas.requestRenderAll();
    clearPreviewState();
    pushSnapshot();
    syncSelectionState();
  };

  const exportDesignJson = () => {
    return getSnapshot();
  };

  const exportPreviewDataUrl = () => {
    const canvas = canvasRef.current;
    if (!canvas) {
      return null;
    }

    try {
      const currentZoom = canvas.getZoom();

      return withPreviewHidden(() =>
        canvas.toDataURL({
          format: 'png',
          multiplier: (1 / currentZoom) * 0.6,
        }),
      );
    } catch {
      return null;
    }
  };

  const exportPrintDataUrl = () => {
    const canvas = canvasRef.current;
    if (!canvas) {
      return null;
    }

    try {
      const currentZoom = canvas.getZoom();

      return withPreviewHidden(() =>
        canvas.toDataURL({
          format: 'png',
          multiplier: 1 / currentZoom,
        }),
      );
    } catch {
      return null;
    }
  };

  const hasDesignElements = () => {
    const canvas = canvasRef.current;
    if (!canvas) {
      return false;
    }

    return canvas
      .getObjects()
      .some((object) => object !== previewTextRef.current);
  };

  const duplicateActiveObject = () => {
    const canvas = getCanvas();
    const activeObject = canvas.getActiveObject();

    if (!activeObject || isPreviewTextbox(activeObject)) {
      return;
    }

    if (activeObject instanceof FabricImage) {
      ensureImageLimitNotExceeded(canvas);
    }

    activeObject.clone().then((cloned: FabricObject) => {
      cloned.set({
        left: (activeObject.left ?? 0) + 20,
        top: (activeObject.top ?? 0) + 20,
        evented: true,
      });

      canvas.add(cloned);
      canvas.setActiveObject(cloned);
      canvas.requestRenderAll();
      pushSnapshot();
      syncSelectionState();
    });
  };

  const flipActiveObjectHorizontal = () => {
    const canvas = getCanvas();
    const activeObject = canvas.getActiveObject();

    if (!activeObject || isPreviewTextbox(activeObject)) {
      return;
    }

    activeObject.set({ flipX: !activeObject.flipX });
    canvas.requestRenderAll();
    pushSnapshot();
  };

  const flipActiveObjectVertical = () => {
    const canvas = getCanvas();
    const activeObject = canvas.getActiveObject();

    if (!activeObject || isPreviewTextbox(activeObject)) {
      return;
    }

    activeObject.set({ flipY: !activeObject.flipY });
    canvas.requestRenderAll();
    pushSnapshot();
  };

  const scaleActiveObjectToFill = () => {
    const canvas = getCanvas();
    const activeObject = canvas.getActiveObject();

    if (!activeObject || isPreviewTextbox(activeObject)) {
      return;
    }

    const canvasW = canvas.getWidth() / canvas.getZoom();
    const canvasH = canvas.getHeight() / canvas.getZoom();
    const objectW = activeObject.width ?? 1;
    const objectH = activeObject.height ?? 1;

    const scaleX = canvasW / objectW;
    const scaleY = canvasH / objectH;
    const scale = Math.min(scaleX, scaleY);

    activeObject.set({
      scaleX: scale,
      scaleY: scale,
      left: canvasW / 2,
      top: canvasH / 2,
      originX: 'center',
      originY: 'center',
    });

    activeObject.setCoords();
    canvas.requestRenderAll();
    pushSnapshot();
    syncSelectionState();
  };

  useEffect(() => {
    if (typeof window === 'undefined') {
      return;
    }

    const handleKeyDown = (event: KeyboardEvent) => {
      if (isTypingIntoField(event.target)) {
        return;
      }

      if (event.key !== 'Delete' && event.key !== 'Backspace') {
        return;
      }

      const canvas = canvasRef.current;
      const activeObject = canvas?.getActiveObject();

      if (!canvas || !activeObject) {
        return;
      }

      event.preventDefault();
      deleteActiveObjectRef.current?.();
    };

    window.addEventListener('keydown', handleKeyDown);

    return () => {
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, []);

  useEffect(() => {
    const handleWheel = (event: WheelEvent) => {
      if (!event.ctrlKey) {
        return;
      }

      event.preventDefault();

      const delta = event.deltaY > 0 ? -ZOOM_STEP : ZOOM_STEP;

      setZoomLevel((previous) =>
        Math.min(
          Math.max(+(previous + delta).toFixed(2), ZOOM_MIN),
          ZOOM_MAX,
        ),
      );
    };

    window.addEventListener('wheel', handleWheel, { passive: false });

    return () => {
      window.removeEventListener('wheel', handleWheel);
    };
  }, []);

  return {
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
  };
}