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
import { useEffect, useMemo, useRef, useState } from 'react';
import type { NormalizedTemplateConfig } from '@/lib/design/template';

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
  initializeWorkspace: (initialDesignJson?: string | null, zoom?: number) => Promise<void>;
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

function getTShirtPrintArea(): PrintAreaBox {
  return {
    left: '26%',
    top: '26%',
    width: '44%',
    height: '52%',
  };
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

  const [stageSize, setStageSize] = useState(760);

  useEffect(() => {
    const calculateStageSize = () => {
      const availableWidth = window.innerWidth - 500;
      const availableHeight = window.innerHeight - 250;
      const nextSize = Math.min(availableWidth, availableHeight, 860);

      return Math.max(320, nextSize > 0 ? nextSize : 320);
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

  const printArea = useMemo(() => getTShirtPrintArea(), []);

  const printAreaWidthPx = useMemo(
    () => stageSize * percentToDecimal(printArea.width),
    [printArea.width, stageSize],
  );

  const printAreaHeightPx = useMemo(
    () => stageSize * percentToDecimal(printArea.height),
    [printArea.height, stageSize],
  );

  const canvasScale = useMemo(() => {
    const widthScale = printAreaWidthPx / template.canvasWidth;
    const heightScale = printAreaHeightPx / template.canvasHeight;
    const nextScale = Math.min(widthScale, heightScale);

    return nextScale > 0 ? nextScale : 1;
  }, [printAreaHeightPx, printAreaWidthPx, template.canvasHeight, template.canvasWidth]);

  const scaledCanvasWidth = useMemo(
    () => Math.round(template.canvasWidth * canvasScale),
    [template.canvasWidth, canvasScale],
  );

  const scaledCanvasHeight = useMemo(
    () => Math.round(template.canvasHeight * canvasScale),
    [template.canvasHeight, canvasScale],
  );

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
    attachCanvas(canvas);

    return () => {
      detachCanvas();
      canvas.dispose();
      fabricCanvasRef.current = null;
    };
  }, [
    attachCanvas,
    detachCanvas,
    template.canvasHeight,
    template.canvasWidth,
    scaledCanvasWidth,
    scaledCanvasHeight,
  ]);

  useEffect(() => {
    if (!fabricCanvasRef.current) {
      return;
    }

    const zoom = scaledCanvasWidth / template.canvasWidth;

    initializeWorkspace(initialDesignJson, zoom).catch((error) => {
      console.error('Failed to initialize Fabric workspace:', error);
    });
  }, [initializeWorkspace, initialDesignJson, scaledCanvasWidth, template.canvasWidth]);

  const showMockup = Boolean(mockupImageUrl);

  return (
    <div
      ref={stageRef}
      className="relative flex h-full w-full items-center justify-center overflow-auto p-4"
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
          className="relative rounded-2xl border border-gray-200 bg-linear-to-b from-gray-50 to-white shadow-sm"
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
        <div className="absolute left-6 top-1/2 flex -translate-y-1/2 flex-col items-center gap-1 rounded-full border border-gray-200 bg-white px-1.5 py-2 shadow-md">
          <button
            type="button"
            onClick={onDuplicate}
            className="flex h-7 w-7 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100"
            title="Duplicate"
          >
            <Copy className="h-4 w-4" />
          </button>

          <div className="h-px w-4 bg-gray-200" />

          <button
            type="button"
            onClick={onFlipH}
            className="flex h-7 w-7 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100"
            title="Flip horizontal"
          >
            <FlipHorizontal2 className="h-4 w-4" />
          </button>

          <button
            type="button"
            onClick={onFlipV}
            className="flex h-7 w-7 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100"
            title="Flip vertical"
          >
            <FlipVertical2 className="h-4 w-4" />
          </button>

          <div className="h-px w-4 bg-gray-200" />

          <button
            type="button"
            onClick={onScaleToFill}
            className="flex h-7 w-7 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100"
            title="Scale to fill print area"
          >
            <Maximize2 className="h-3.5 w-3.5" />
          </button>
        </div>
      )}

      <div className="absolute bottom-6 right-6 flex items-center gap-1 rounded-full border border-gray-200 bg-white px-2 py-1.5 shadow-md">
        <button
          type="button"
          onClick={onZoomOut}
          disabled={zoomLevel <= 1}
          className="flex h-7 w-7 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40"
          title="Zoom out"
        >
          <ZoomOut className="h-4 w-4" />
        </button>

        <button
          type="button"
          onClick={onResetZoom}
          className="min-w-12 cursor-pointer px-1 text-center text-xs font-medium text-gray-700 transition hover:text-gray-900"
          title="Reset zoom"
        >
          {Math.round(zoomLevel * 100)}%
        </button>

        <button
          type="button"
          onClick={onZoomIn}
          disabled={zoomLevel >= 3.0}
          className="flex h-7 w-7 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40"
          title="Zoom in"
        >
          <ZoomIn className="h-4 w-4" />
        </button>

        <div className="mx-1 h-4 w-px bg-gray-200" />

        <button
          type="button"
          onClick={onResetZoom}
          className="flex h-7 w-7 cursor-pointer items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100"
          title="Fit to screen"
        >
          <Maximize className="h-3.5 w-3.5" />
        </button>
      </div>
    </div>
  );
}