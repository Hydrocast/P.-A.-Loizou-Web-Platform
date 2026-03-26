import type { ProductTemplateConfig, ProductTemplatePrintArea } from '@/types/design';

export type NormalizedTemplateConfig = {
  canvasWidth: number;
  canvasHeight: number;
  backgroundImage: string | null;
  printArea: ProductTemplatePrintArea | null;
};

const DEFAULT_CANVAS_WIDTH = 1200;
const DEFAULT_CANVAS_HEIGHT = 1400;

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