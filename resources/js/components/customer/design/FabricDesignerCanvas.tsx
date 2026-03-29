import { Canvas } from 'fabric';
import {
  Copy,
  FlipHorizontal2,
  FlipVertical2,
  Maximize2,
  Maximize,
  ZoomIn,
  ZoomOut,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import {
  resolvePrintArea
  
} from '@/lib/design/template';
import type {NormalizedTemplateConfig} from '@/lib/design/template';

type FabricDesignerCanvasProps = {
  template: NormalizedTemplateConfig;
  initialDesignJson?: string | null;
  mockupImageUrl?: string | null;
  zoomLevel: number;
  onZoomIn: () => void;
  onZoomOut: () => void;
  onResetZoom: () => void;
  attachCanvas: (canvas: Canvas) => void;
  detachCanvas: () => void;
  initializeWorkspace: (initialDesignJson?: string | null) => Promise<void>;
  activeObjectType: 'text' | 'image' | null;
  onDuplicate: () => void;
  onFlipH: () => void;
  onFlipV: () => void;
  onScaleToFill: () => void;
};

type PrintAreaBox = {
  left: string;
  top: string;
  width: string;
  height: string;
};

function toPercent(value: number): string {
  return `${value}%`;
}

function percentToDecimal(value: string): number {
  return Number(value.replace('%', '')) / 100;
}

export default function FabricDesignerCanvas({
  template,
  initialDesignJson,
  mockupImageUrl,
  zoomLevel,
  onZoomIn,
  onZoomOut,
  onResetZoom,
  attachCanvas,
  detachCanvas,
  initializeWorkspace,
  activeObjectType,
  onDuplicate,
  onFlipH,
  onFlipV,
  onScaleToFill,
}: FabricDesignerCanvasProps) {
  const stageRef = useRef<HTMLDivElement | null>(null);
  const canvasElementRef = useRef<HTMLCanvasElement | null>(null);
  const fabricCanvasRef = useRef<Canvas | null>(null);
  const attachCanvasRef = useRef(attachCanvas);
  const detachCanvasRef = useRef(detachCanvas);
  const initializeWorkspaceRef = useRef(initializeWorkspace);

  useEffect(() => {
    attachCanvasRef.current = attachCanvas;
    detachCanvasRef.current = detachCanvas;
    initializeWorkspaceRef.current = initializeWorkspace;
  }, [attachCanvas, detachCanvas, initializeWorkspace]);

  const [stageSize, setStageSize] = useState(760);

  useEffect(() => {
    const calculateStageSize = () => {
      const isMobile = window.innerWidth < 768;

      const availableWidth = isMobile
        ? window.innerWidth - 32
        : window.innerWidth - 500;

      const availableHeight = isMobile
        ? window.innerHeight - 220
        : window.innerHeight - 250;

      const maxStageSize = isMobile ? 520 : 860;
      const minStageSize = isMobile ? 280 : 320;

      const nextSize = Math.min(availableWidth, availableHeight, maxStageSize);

      return Math.max(minStageSize, nextSize > 0 ? nextSize : minStageSize);
    };

    const applyResizeScale = () => {
      setStageSize(calculateStageSize());
    };

    applyResizeScale();

    let lastWidth = window.innerWidth;
    let lastHeight = window.innerHeight;

    const handleResize = () => {
      if (window.innerWidth === lastWidth && window.innerHeight === lastHeight) {
        return;
      }

      lastWidth = window.innerWidth;
      lastHeight = window.innerHeight;
      applyResizeScale();
    };

    window.addEventListener('resize', handleResize);

    return () => {
      window.removeEventListener('resize', handleResize);
    };
  }, []);

  const resolvedPrintArea = resolvePrintArea({
    canvas_width: template.canvasWidth,
    canvas_height: template.canvasHeight,
    background_image: template.backgroundImage,
    print_area: template.printArea,
  });

  const printArea: PrintAreaBox = {
    left: toPercent(resolvedPrintArea.left),
    top: toPercent(resolvedPrintArea.top),
    width: toPercent(resolvedPrintArea.width),
    height: toPercent(resolvedPrintArea.height),
  };

  const printAreaWidthPx = stageSize * percentToDecimal(printArea.width);

  const printAreaHeightPx = stageSize * percentToDecimal(printArea.height);

  const canvasScale = (() => {
    const widthScale = printAreaWidthPx / template.canvasWidth;
    const heightScale = printAreaHeightPx / template.canvasHeight;
    const nextScale = Math.min(widthScale, heightScale);

    return nextScale > 0 ? nextScale : 1;
  })();

  const scaledCanvasWidth = Math.round(template.canvasWidth * canvasScale);

  const scaledCanvasHeight = Math.round(template.canvasHeight * canvasScale);

  useEffect(() => {
    const element = canvasElementRef.current;
    if (!element) {
      return;
    }

    const canvas = new Canvas(element, {
      width: scaledCanvasWidth,
      height: scaledCanvasHeight,
      preserveObjectStacking: true,
      selection: true,
    });

    const zoom = scaledCanvasWidth / template.canvasWidth;
    canvas.setZoom(zoom);

    if (stageRef.current) {
      (
        canvas as Canvas & {
          hiddenTextareaContainer?: HTMLElement;
        }
      ).hiddenTextareaContainer = stageRef.current;
    }

    if (canvas.upperCanvasEl) {
      canvas.upperCanvasEl.tabIndex = 0;
      canvas.upperCanvasEl.style.outline = 'none';
    }

    fabricCanvasRef.current = canvas;
    attachCanvasRef.current(canvas);

    return () => {
      detachCanvasRef.current();
      canvas.dispose();
      fabricCanvasRef.current = null;
    };
  }, [
    template.canvasHeight,
    template.canvasWidth,
    scaledCanvasWidth,
    scaledCanvasHeight,
  ]);

  useEffect(() => {
    if (!fabricCanvasRef.current) {
      return;
    }

    initializeWorkspaceRef.current(initialDesignJson).catch((error) => {
      console.error('Failed to initialize Fabric workspace:', error);
    });
  }, [initialDesignJson]);

  const showMockup = Boolean(mockupImageUrl);

  return (
    <div
      ref={stageRef}
      className="relative flex h-full w-full items-center justify-center overflow-auto p-2 sm:p-3 md:p-4"
    >
      <div
        style={{
          width: `${Math.round(stageSize * zoomLevel)}px`,
          height: `${Math.round(stageSize * zoomLevel)}px`,
          flexShrink: 0,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
        }}
      >
        <div
          className="relative rounded-xl border border-gray-200 bg-linear-to-b from-gray-50 to-white shadow-sm md:rounded-2xl"
        style={{
          width: `${stageSize}px`,
          height: `${stageSize}px`,
          transform: `scale(${zoomLevel})`,
          transformOrigin: 'center center',
        }}
      >
        {showMockup ? (
          <div
            className="absolute inset-0 bg-center bg-contain bg-no-repeat"
            style={{
              backgroundImage: `url(${mockupImageUrl})`,
            }}
          />
        ) : (
          <div className="absolute inset-0 rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50" />
        )}

        <div
          className="absolute"
          style={{
            left: printArea.left,
            top: printArea.top,
            width: printArea.width,
            height: printArea.height,
          }}
        >
          <div className="relative flex h-full w-full items-center justify-center">
            <div
              className="absolute rounded-md border border-dashed border-gray-400/60 bg-white/10"
              style={{
                width: `${scaledCanvasWidth}px`,
                height: `${scaledCanvasHeight}px`,
              }}
            />

            <div
              style={{
                width: `${scaledCanvasWidth}px`,
                height: `${scaledCanvasHeight}px`,
              }}
            >
              <canvas
                ref={canvasElementRef}
                width={scaledCanvasWidth}
                height={scaledCanvasHeight}
              />
            </div>
          </div>
        </div>
        </div>
      </div>

      {activeObjectType === 'image' && (
        <div className="absolute left-1/2 top-3 flex -translate-x-1/2 items-center gap-1 rounded-full border border-gray-200 bg-white px-2 py-1.5 shadow-md md:left-6 md:top-1/2 md:translate-x-0 md:-translate-y-1/2 md:flex-col md:px-1.5 md:py-2">
          <button
            type="button"
            onClick={onDuplicate}
            className="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100 md:h-7 md:w-7"
            title="Duplicate"
          >
            <Copy className="h-4 w-4" />
          </button>

          <div className="h-4 w-px bg-gray-200 md:h-px md:w-4" />

          <button
            type="button"
            onClick={onFlipH}
            className="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100 md:h-7 md:w-7"
            title="Flip horizontal"
          >
            <FlipHorizontal2 className="h-4 w-4" />
          </button>

          <button
            type="button"
            onClick={onFlipV}
            className="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100 md:h-7 md:w-7"
            title="Flip vertical"
          >
            <FlipVertical2 className="h-4 w-4" />
          </button>

          <div className="h-4 w-px bg-gray-200 md:h-px md:w-4" />

          <button
            type="button"
            onClick={onScaleToFill}
            className="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100 md:h-7 md:w-7"
            title="Scale to fill print area"
          >
            <Maximize2 className="h-3.5 w-3.5" />
          </button>
        </div>
      )}

      <div className="absolute bottom-3 left-1/2 flex -translate-x-1/2 items-center gap-1 rounded-full border border-gray-200 bg-white px-2 py-1.5 shadow-md md:bottom-6 md:left-auto md:right-6 md:translate-x-0">
        <button
          type="button"
          onClick={onZoomOut}
          disabled={zoomLevel <= 1}
          className="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 md:h-7 md:w-7"
          title="Zoom out"
        >
          <ZoomOut className="h-4 w-4" />
        </button>

        <button
          type="button"
          onClick={onResetZoom}
          className="min-w-14 cursor-pointer px-1.5 text-center text-xs font-medium text-gray-700 transition hover:text-gray-900 md:min-w-12 md:px-1"
          title="Reset zoom"
        >
          {Math.round(zoomLevel * 100)}%
        </button>

        <button
          type="button"
          onClick={onZoomIn}
          disabled={zoomLevel >= 3.0}
          className="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 md:h-7 md:w-7"
          title="Zoom in"
        >
          <ZoomIn className="h-4 w-4" />
        </button>

        <div className="mx-1 h-4 w-px bg-gray-200" />

        <button
          type="button"
          onClick={onResetZoom}
          className="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100 md:h-7 md:w-7"
          title="Fit to screen"
        >
          <Maximize className="h-3.5 w-3.5" />
        </button>
      </div>
    </div>
  );
}