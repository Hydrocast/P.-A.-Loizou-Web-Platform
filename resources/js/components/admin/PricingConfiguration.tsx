import { router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { Plus, Trash2, Save, AlertCircle, CheckCircle2, Search } from 'lucide-react';
import { useTimedFlash } from '@/hooks/useTimedFlash';

interface CustomizableProduct {
  product_id: number;
  product_name: string;
}

interface ExistingPricingTier {
  minimum_quantity: number;
  maximum_quantity: number;
  unit_price: number;
}

interface PricingTierFormRow {
  tier_id: string;
  minimum_quantity: number | string;
  maximum_quantity: number | string;
  unit_price: number | string;
}

interface PricingConfigurationProps {
  customizableProducts?: CustomizableProduct[];
  existingTiers?: ExistingPricingTier[];
  selectedProductId?: number | null;
  flash?: {
    success?: string;
    error?: string;
  };
}

const generateId = () => `tier-${Date.now()}-${Math.random().toString(36).slice(2, 11)}`;

export default function PricingConfiguration({
  customizableProducts = [],
  existingTiers = [],
  selectedProductId = null,
  flash = {},
}: PricingConfigurationProps) {
  const { visibleSuccess, visibleError } = useTimedFlash({
    success: flash.success,
    error: flash.error,
  });
  
  const [selectedProduct, setSelectedProduct] = useState<number | null>(selectedProductId);
  const [tiers, setTiers] = useState<PricingTierFormRow[]>([]);
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [successMessage, setSuccessMessage] = useState('');
  const [errorMessage, setErrorMessage] = useState('');

  useEffect(() => {
    setSelectedProduct(selectedProductId);
  }, [selectedProductId]);

  useEffect(() => {
    if (existingTiers.length > 0) {
      setTiers(
        existingTiers.map((tier) => ({
          tier_id: generateId(),
          minimum_quantity: tier.minimum_quantity,
          maximum_quantity: tier.maximum_quantity,
          unit_price: tier.unit_price,
        })),
      );
      return;
    }

    setTiers([
      {
        tier_id: generateId(),
        minimum_quantity: 1,
        maximum_quantity: '',
        unit_price: '',
      },
    ]);
  }, [existingTiers]);

  const filteredProducts = useMemo(() => {
    const trimmedQuery = searchQuery.trim().toLowerCase();

    if (!trimmedQuery) {
      return customizableProducts;
    }

    return customizableProducts.filter((product) =>
      product.product_name.toLowerCase().includes(trimmedQuery),
    );
  }, [customizableProducts, searchQuery]);

  const handleProductChange = (productId: number | null) => {
    setValidationErrors({});
    setSuccessMessage('');
    setErrorMessage('');

    if (productId === null) {
      setSelectedProduct(null);
      setTiers([
        {
          tier_id: generateId(),
          minimum_quantity: 1,
          maximum_quantity: '',
          unit_price: '',
        },
      ]);

      router.get(
        '/staff/pricing',
        {},
        {
          preserveScroll: true,
          preserveState: false,
          replace: true,
        },
      );
      return;
    }

    setSelectedProduct(productId);

    router.get(
      '/staff/pricing',
      { product_id: productId },
      {
        preserveScroll: true,
        preserveState: false,
        replace: true,
      },
    );
  };

  const handleSearchSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    const trimmedQuery = searchQuery.trim().toLowerCase();

    if (!trimmedQuery) {
      handleProductChange(null);
      return;
    }

    const matchedProduct = customizableProducts.find(
      (product) => product.product_name.trim().toLowerCase() === trimmedQuery,
    );

    if (!matchedProduct) {
      handleProductChange(null);
      return;
    }

    handleProductChange(matchedProduct.product_id);
  };

  const handleAddTier = () => {
    if (tiers.length >= 5) {
      setErrorMessage('A maximum of five pricing tiers is allowed.');
      return;
    }

    const lastTier = tiers[tiers.length - 1];
    const nextMin =
      lastTier && lastTier.maximum_quantity !== ''
        ? Number(lastTier.maximum_quantity) + 1
        : '';

    setTiers([
      ...tiers,
      {
        tier_id: generateId(),
        minimum_quantity: nextMin,
        maximum_quantity: '',
        unit_price: '',
      },
    ]);
  };

  const handleRemoveTier = (tierId: string) => {
    if (tiers.length <= 1) {
      setErrorMessage('At least one pricing tier is required.');
      return;
    }

    setTiers(tiers.filter((tier) => tier.tier_id !== tierId));
  };

  const handleTierChange = (
    tierId: string,
    field: keyof PricingTierFormRow,
    value: string,
  ) => {
    setTiers(
      tiers.map((tier) =>
        tier.tier_id === tierId
          ? {
              ...tier,
              [field]: value,
            }
          : tier,
      ),
    );

    const nextErrors = { ...validationErrors };
    delete nextErrors[`${tierId}-${field}`];
    delete nextErrors.tiers;
    setValidationErrors(nextErrors);
  };

  const validateTiers = (): boolean => {
    const errors: Record<string, string> = {};

    if (!selectedProduct) {
      setErrorMessage('Please select a customizable product.');
      return false;
    }

    if (tiers.length < 1) {
      setErrorMessage('At least one pricing tier is required.');
      return false;
    }

    if (tiers.length > 5) {
      setErrorMessage('A maximum of five pricing tiers is allowed.');
      return false;
    }

    const normalized = tiers.map((tier) => ({
      ...tier,
      minimum_quantity: Number(tier.minimum_quantity),
      maximum_quantity: Number(tier.maximum_quantity),
      unit_price: Number(tier.unit_price),
    }));

    normalized.forEach((tier, index) => {
      const rowNumber = index + 1;

      if (!Number.isInteger(tier.minimum_quantity) || tier.minimum_quantity < 1) {
        errors[`${tier.tier_id}-minimum_quantity`] = `Tier ${rowNumber}: minimum quantity must be at least 1.`;
      }

      if (!Number.isInteger(tier.maximum_quantity) || tier.maximum_quantity < 1) {
        errors[`${tier.tier_id}-maximum_quantity`] = `Tier ${rowNumber}: maximum quantity must be at least 1.`;
      }

      if (
        Number.isInteger(tier.minimum_quantity) &&
        Number.isInteger(tier.maximum_quantity) &&
        tier.maximum_quantity < tier.minimum_quantity
      ) {
        errors[`${tier.tier_id}-maximum_quantity`] =
          `Tier ${rowNumber}: maximum quantity must be greater than or equal to minimum quantity.`;
      }

      if (Number.isNaN(tier.unit_price) || tier.unit_price < 0) {
        errors[`${tier.tier_id}-unit_price`] = `Tier ${rowNumber}: unit price must be 0.00 or greater.`;
      }
    });

    const sorted = [...normalized].sort(
      (a, b) => a.minimum_quantity - b.minimum_quantity,
    );

    if (sorted.length > 0 && sorted[0].minimum_quantity !== 1) {
      errors.tiers = 'The first pricing tier must start at a minimum quantity of 1.';
    }

    for (let i = 1; i < sorted.length; i += 1) {
      const previous = sorted[i - 1];
      const current = sorted[i];

      if (current.minimum_quantity !== previous.maximum_quantity + 1) {
        errors.tiers = 'Pricing tiers must be contiguous with no gaps or overlaps.';
        break;
      }
    }

    setValidationErrors(errors);

    if (Object.keys(errors).length > 0) {
      if (errors.tiers) {
        setErrorMessage(errors.tiers);
      } else {
        setErrorMessage('Please correct the pricing tier validation errors.');
      }
      return false;
    }

    setErrorMessage('');
    return true;
  };

  const handleSubmit = () => {
    setSuccessMessage('');
    setErrorMessage('');

    if (!validateTiers() || !selectedProduct) {
      return;
    }

    setIsSubmitting(true);

    router.put(
      `/staff/pricing/${selectedProduct}`,
      {
        tiers: tiers.map((tier) => ({
          minimum_quantity: Number(tier.minimum_quantity),
          maximum_quantity: Number(tier.maximum_quantity),
          unit_price: Number(tier.unit_price),
        })),
      },
      {
        preserveScroll: true,
        preserveState: false,
        onFinish: () => {
          setIsSubmitting(false);
        },
      },
    );
  };

  const selectedProductName = useMemo(() => {
    return (
      customizableProducts.find((product) => product.product_id === selectedProduct)?.product_name ??
      'Unknown Product'
    );
  }, [customizableProducts, selectedProduct]);

  return (
    <div className="bg-white rounded-lg shadow-md p-6 overflow-hidden">
      <div className="mb-6">
        <h2 className="text-2xl font-semibold text-purple-900">
          Tiered Pricing Configuration
        </h2>
        <p className="text-sm text-gray-600 mt-1">
          Configure quantity-based pricing tiers for customizable products
        </p>
      </div>

      {successMessage && (
        <div className="mb-6 p-4 bg-green-100 text-green-800 rounded-md border border-green-200 flex items-start gap-3">
          <CheckCircle2 className="w-5 h-5 flex-shrink-0 mt-0.5" />
          <div className="flex-1">
            <p className="font-medium">Success</p>
            <p className="text-sm mt-1">{successMessage}</p>
          </div>
        </div>
      )}

      {errorMessage && (
        <div className="mb-6 p-4 bg-red-100 text-red-800 rounded-md border border-red-200 flex items-start gap-3">
          <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5" />
          <div className="flex-1">
            <p className="font-medium">Error</p>
            <p className="text-sm mt-1">{errorMessage}</p>
          </div>
        </div>
      )}

      <div className="space-y-6">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Select Product
          </label>

          <form onSubmit={handleSearchSubmit} className="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
              <input
                type="text"
                value={searchQuery}
                onChange={(event) => setSearchQuery(event.target.value)}
                placeholder="Search customizable products"
                className="w-full rounded-md border border-gray-300 py-2 pl-9 pr-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500"
              />
            </div>

            <button
              type="submit"
              className="inline-flex items-center justify-center rounded-md border border-purple-600 bg-purple-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-purple-700"
            >
              Search Product
            </button>
          </form>

          {filteredProducts.length > 0 && (
            <div className="mt-4 max-h-48 overflow-y-auto rounded-md border border-gray-200">
              {filteredProducts.map((product) => {
                const isSelected = selectedProduct === product.product_id;

                return (
                  <button
                    key={product.product_id}
                    type="button"
                    onClick={() => handleProductChange(product.product_id)}
                    className={`w-full px-4 py-2 text-left text-sm transition-colors ${
                      isSelected
                        ? 'bg-purple-100 text-purple-900 font-medium'
                        : 'hover:bg-gray-100 text-gray-700'
                    }`}
                  >
                    {product.product_name}
                  </button>
                );
              })}
            </div>
          )}

          {filteredProducts.length === 0 && customizableProducts.length > 0 && searchQuery.trim() !== '' && (
            <p className="mt-3 text-sm text-gray-500">No products match your search.</p>
          )}

          {selectedProduct && (
            <p className="text-sm text-gray-600 mt-2">
              Configuring tiers for: <span className="font-medium">{selectedProductName}</span>
            </p>
          )}
        </div>

        <div>
          <div className="flex items-center justify-between mb-3">
            <h3 className="text-lg font-semibold text-gray-900">Pricing Tiers</h3>
            <button
              type="button"
              onClick={handleAddTier}
              className="inline-flex items-center gap-2 px-3 py-2 text-sm bg-purple-600 text-white rounded-md hover:bg-purple-700"
            >
              <Plus className="w-4 h-4" />
              Add Tier
            </button>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full min-w-[640px] border border-gray-200 rounded-md overflow-hidden">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Tier</th>
                  <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Min Quantity</th>
                  <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Max Quantity</th>
                  <th className="px-4 py-3 text-left text-sm font-medium text-gray-700">Unit Price (€)</th>
                  <th className="px-4 py-3 text-center text-sm font-medium text-gray-700">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {tiers.map((tier, index) => (
                  <tr key={tier.tier_id}>
                    <td className="px-4 py-3 text-sm text-gray-700">Tier {index + 1}</td>
                    <td className="px-4 py-3">
                      <input
                        type="number"
                        min={1}
                        value={tier.minimum_quantity}
                        onChange={(e) => handleTierChange(tier.tier_id, 'minimum_quantity', e.target.value)}
                        className={`w-full px-3 py-2 text-sm border rounded-md focus:ring-2 focus:ring-purple-500 ${
                          validationErrors[`${tier.tier_id}-minimum_quantity`] ? 'border-red-500' : 'border-gray-300'
                        }`}
                      />
                      {validationErrors[`${tier.tier_id}-minimum_quantity`] && (
                        <p className="text-xs text-red-600 mt-1">{validationErrors[`${tier.tier_id}-minimum_quantity`]}</p>
                      )}
                    </td>
                    <td className="px-4 py-3">
                      <input
                        type="number"
                        min={1}
                        value={tier.maximum_quantity}
                        onChange={(e) => handleTierChange(tier.tier_id, 'maximum_quantity', e.target.value)}
                        className={`w-full px-3 py-2 text-sm border rounded-md focus:ring-2 focus:ring-purple-500 ${
                          validationErrors[`${tier.tier_id}-maximum_quantity`] ? 'border-red-500' : 'border-gray-300'
                        }`}
                      />
                      {validationErrors[`${tier.tier_id}-maximum_quantity`] && (
                        <p className="text-xs text-red-600 mt-1">{validationErrors[`${tier.tier_id}-maximum_quantity`]}</p>
                      )}
                    </td>
                    <td className="px-4 py-3">
                      <input
                        type="number"
                        min={0}
                        step="0.01"
                        value={tier.unit_price}
                        onChange={(e) => handleTierChange(tier.tier_id, 'unit_price', e.target.value)}
                        className={`w-full px-3 py-2 text-sm border rounded-md focus:ring-2 focus:ring-purple-500 ${
                          validationErrors[`${tier.tier_id}-unit_price`] ? 'border-red-500' : 'border-gray-300'
                        }`}
                      />
                      {validationErrors[`${tier.tier_id}-unit_price`] && (
                        <p className="text-xs text-red-600 mt-1">{validationErrors[`${tier.tier_id}-unit_price`]}</p>
                      )}
                    </td>
                    <td className="px-4 py-3 text-center">
                      <button
                        type="button"
                        onClick={() => handleRemoveTier(tier.tier_id)}
                        className="inline-flex items-center gap-1 px-2 py-1 text-sm text-red-600 hover:text-red-700"
                        disabled={tiers.length <= 1}
                      >
                        <Trash2 className="w-4 h-4" />
                        Remove
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {validationErrors.tiers && (
            <p className="text-sm text-red-600 mt-2">{validationErrors.tiers}</p>
          )}
        </div>

        <div className="flex justify-end">
          <button
            type="button"
            onClick={handleSubmit}
            disabled={isSubmitting || !selectedProduct}
            className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <Save className="w-4 h-4" />
            {isSubmitting ? 'Saving...' : 'Save Pricing Configuration'}
          </button>
        </div>
      </div>
    </div>
  );
}
