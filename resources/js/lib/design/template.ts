import type { ProductTemplateConfig, ProductTemplatePrintArea } from '@/types/design';

export type NormalizedTemplateConfig = {
  canvasWidth: number;
  canvasHeight: number;
  backgroundImage: string | null;
  printArea: ProductTemplatePrintArea | null;
};

const DEFAULT_CANVAS_WIDTH = 1200;
const DEFAULT_CANVAS_HEIGHT = 1400;

/**
 * Default print area used by the current t-shirt workspace/layout.
 *
 * This remains the safe fallback for any profile that does not yet define
 * a valid custom print area.
 */
export const DEFAULT_PRINT_AREA: ProductTemplatePrintArea = {
  left: 26,
  top: 26,
  width: 44,
  height: 52,
};

function isValidPositiveNumber(value: unknown): value is number {
  return typeof value === 'number' && Number.isFinite(value) && value > 0;
}

function normalizePrintArea(
  printArea: ProductTemplateConfig['print_area'],
): ProductTemplatePrintArea | null {
  if (!printArea) {
    return null;
  }

  const { left, top, width, height } = printArea;

  if (
    !isValidPositiveNumber(width) ||
    !isValidPositiveNumber(height) ||
    typeof left !== 'number' ||
    typeof top !== 'number'
  ) {
    return null;
  }

  return { left, top, width, height };
}

export function resolvePrintArea(
  config?: ProductTemplateConfig | null,
): ProductTemplatePrintArea {
  return normalizePrintArea(config?.print_area) ?? DEFAULT_PRINT_AREA;
}

export function normalizeTemplateConfig(
  config?: ProductTemplateConfig | null,
): NormalizedTemplateConfig {
  return {
    canvasWidth: isValidPositiveNumber(config?.canvas_width)
      ? config.canvas_width
      : DEFAULT_CANVAS_WIDTH,

    canvasHeight: isValidPositiveNumber(config?.canvas_height)
      ? config.canvas_height
      : DEFAULT_CANVAS_HEIGHT,

    backgroundImage:
      typeof config?.background_image === 'string' && config.background_image.trim() !== ''
        ? config.background_image
        : null,

    printArea: normalizePrintArea(config?.print_area),
  };
}