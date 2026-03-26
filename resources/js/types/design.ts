export type ProductTemplatePrintArea = {
  left: number;
  top: number;
  width: number;
  height: number;
};

export type ProductTemplateConfig = {
  canvas_width?: number;
  canvas_height?: number;
  background_image?: string | null;
  print_area?: ProductTemplatePrintArea | null;
};

export type DesignProduct = {
  product_id: number;
  product_name: string;
  description?: string | null;
};

export type ClipartItem = {
  clipart_id: number;
  clipart_name: string;
  image_reference: string;
};

export type InitialDesign = {
  design_id?: number;
  saved_design_id?: number;
  design_name?: string;
  design_data?: string;
  preview_image_reference?: string | null;
};

export type ShirtColorOption = {
  id: string;
  label: string;
  swatchHex: string;
  mockupImageUrl: string;
  thumbnailImageUrl?: string | null;
};

export type PrintSideChoice = {
  value: string;
  label: string;
};

export type WorkspaceOptions = {
  print_sides?: {
    enabled?: boolean;
    default?: string | null;
    choices?: PrintSideChoice[];
  };
};

export type SelectedPrintSide = {
  value: string;
  label: string;
};

export type WorkspacePageProps = {
  product: DesignProduct;
  clipart: ClipartItem[];
  templateConfig?: ProductTemplateConfig | null;
  initialDesign?: InitialDesign | null;
  shirtColorOptions?: ShirtColorOption[];
  selectedShirtColorId?: string | null;
  workspaceOptions?: WorkspaceOptions;
  selectedPrintSide?: SelectedPrintSide | null;
};

export type SavedDesignListItem = {
  saved_design_id?: number;
  design_id?: number;
  id?: number;
  product_id?: number;
  design_name: string;
  date_created?: string | number | null;
  design_data?: string;
  preview_image_reference?: string | null;
  shirt_color_label?: string | null;
  print_sides_label?: string | null;
  product?: {
    product_name?: string;
  } | null;
};