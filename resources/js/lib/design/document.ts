type CustomizationMetadata = {
  shirt_color?: {
    id?: string | null;
    label?: string | null;
  };
  print_sides?: {
    value?: string | null;
    label?: string | null;
  };
};

type StoredDesignEnvelope = {
  schema_version: number;
  canvas_json?: string;
  customization?: CustomizationMetadata;
};

function parseJsonSafely(value: string): unknown {
  try {
    return JSON.parse(value);
  } catch {
    return null;
  }
}

export function extractCanvasJson(storedValue?: string | null): string | null {
  if (!storedValue || typeof storedValue !== 'string') {
    return null;
  }

  const parsed = parseJsonSafely(storedValue);

  if (
    parsed &&
    typeof parsed === 'object' &&
    !Array.isArray(parsed) &&
    'schema_version' in parsed &&
    'canvas_json' in parsed &&
    typeof (parsed as StoredDesignEnvelope).canvas_json === 'string'
  ) {
    return (parsed as StoredDesignEnvelope).canvas_json ?? null;
  }

  return storedValue;
}

export function extractCustomization(storedValue?: string | null): CustomizationMetadata {
  if (!storedValue || typeof storedValue !== 'string') {
    return {};
  }

  const parsed = parseJsonSafely(storedValue);

  if (
    parsed &&
    typeof parsed === 'object' &&
    !Array.isArray(parsed) &&
    'schema_version' in parsed &&
    'customization' in parsed
  ) {
    const customization = (parsed as StoredDesignEnvelope).customization;

    return customization && typeof customization === 'object' ? customization : {};
  }

  return {};
}

export function extractShirtColorLabel(storedValue?: string | null): string | null {
  const customization = extractCustomization(storedValue);
  const label = customization.shirt_color?.label;

  return typeof label === 'string' && label.trim() !== '' ? label : null;
}

export function extractPrintSidesLabel(storedValue?: string | null): string | null {
  const customization = extractCustomization(storedValue);
  const label = customization.print_sides?.label;

  return typeof label === 'string' && label.trim() !== '' ? label : null;
}
