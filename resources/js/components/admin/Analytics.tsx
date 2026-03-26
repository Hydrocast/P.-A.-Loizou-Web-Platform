import { useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import { TrendingUp, Package, DollarSign, ShoppingCart } from 'lucide-react';
import { useTimedFlash } from '@/hooks/useTimedFlash';

type AnalyticsDashboard = {
  total_order_value: number;
  order_count: number;
  average_order_value: number;
  average_items_per_order: number;
  status_distribution: Record<string, number>;
  start_date: string;
  end_date: string;
  has_data: boolean;
};

type AnalyticsFilters = {
  start_date?: string | null;
  end_date?: string | null;
};

type AnalyticsProps = {
  dashboard: AnalyticsDashboard | null;
  filters?: AnalyticsFilters;
  flash?: {
    success?: string;
    error?: string;
  };
};

export default function Analytics({
  dashboard,
  filters = {},
  flash = {},
}: AnalyticsProps) {
  const { visibleError } = useTimedFlash({
    success: flash?.success,
    error: flash?.error,
  });
  
  const [startDate, setStartDate] = useState(filters.start_date ?? '');
  const [endDate, setEndDate] = useState(filters.end_date ?? '');
  const [filterDateError, setFilterDateError] = useState('');

  const startDateInputRef = useRef<HTMLInputElement | null>(null);
  const endDateInputRef = useRef<HTMLInputElement | null>(null);

  useEffect(() => {
    setStartDate(filters.start_date ?? '');
    setEndDate(filters.end_date ?? '');
  }, [filters.start_date, filters.end_date]);

  useEffect(() => {
    if (!filterDateError) return;

    const timer = window.setTimeout(() => setFilterDateError(''), 4000);
    return () => window.clearTimeout(timer);
  }, [filterDateError]);

  const hasInvalidDateRange = (nextStartDate: string, nextEndDate: string): boolean => {
    if (!nextStartDate || !nextEndDate) return false;
    return nextEndDate < nextStartDate;
  };

  const handleDateChange = (field: 'start_date' | 'end_date', value: string) => {
    const nextStartDate = field === 'start_date' ? value : startDate;
    const nextEndDate = field === 'end_date' ? value : endDate;

    if (field === 'start_date') {
      setStartDate(value);
    } else {
      setEndDate(value);
    }

    if (hasInvalidDateRange(nextStartDate, nextEndDate)) {
      setFilterDateError('End date cannot be earlier than start date.');
      return;
    }

    setFilterDateError('');

    router.get(
      '/staff/analytics',
      {
        start_date: nextStartDate,
        end_date: nextEndDate,
      },
      {
        preserveScroll: true,
        preserveState: true,
      }
    );
  };

  const openDatePicker = (input: HTMLInputElement | null) => {
    if (!input) return;

    input.focus();

    if ('showPicker' in input) {
      try {
        (input as HTMLInputElement & { showPicker?: () => void }).showPicker?.();
      } catch {
        // Fallback to native focus behavior when showPicker is not available.
      }
    }
  };

  const statusDistribution = dashboard?.status_distribution ?? {};
  const totalStatusCount = Object.values(statusDistribution).reduce((sum, count) => sum + count, 0);
  const hasInvalidRange = hasInvalidDateRange(startDate, endDate);

  return (
    <div className="max-w-7xl mx-auto px-4 py-8">
      <div className="bg-white rounded-lg shadow-sm p-6">
        <h2 className="text-2xl font-semibold mb-6 text-purple-900">Sales Analytics</h2>

        {visibleError && (
          <div className="mb-4 p-4 bg-red-100 text-red-800 rounded-md border border-red-200">
            {visibleError}
          </div>
        )}

        {filterDateError && (
          <div className="mb-4 p-4 bg-red-100 text-red-800 rounded-md border border-red-200">
            {filterDateError}
          </div>
        )}

        {/* Date Range Filter */}
        <div className="mb-8 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <div
              onClick={() => openDatePicker(startDateInputRef.current)}
              className="w-full h-11 border border-gray-300 rounded-md text-sm focus-within:ring-2 focus-within:ring-purple-500 bg-white cursor-pointer flex items-center"
            >
              <input
                ref={startDateInputRef}
                type="date"
                value={startDate}
                onChange={(e) => handleDateChange('start_date', e.target.value)}
                className="w-full h-full px-4 rounded-md bg-transparent cursor-pointer outline-none"
              />
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <div
              onClick={() => openDatePicker(endDateInputRef.current)}
              className="w-full h-11 border border-gray-300 rounded-md text-sm focus-within:ring-2 focus-within:ring-purple-500 bg-white cursor-pointer flex items-center"
            >
              <input
                ref={endDateInputRef}
                type="date"
                value={endDate}
                onChange={(e) => handleDateChange('end_date', e.target.value)}
                className="w-full h-full px-4 rounded-md bg-transparent cursor-pointer outline-none"
              />
            </div>
          </div>
        </div>

        {hasInvalidRange ? (
          <div className="rounded-lg border border-amber-200 bg-amber-50 px-5 py-6 text-amber-900">
            <p className="text-sm font-medium">
              Please choose a valid date range to view analytics.
            </p>
            <p className="mt-1 text-sm text-amber-800">
              The end date must be the same as or later than the start date.
            </p>
          </div>
        ) : (
          <>
            {/* Metrics Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
              <div className="bg-blue-50 p-6 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <h3 className="text-sm font-medium text-blue-900">Total Revenue</h3>
                  <DollarSign className="w-6 h-6 text-blue-600" />
                </div>
                <p className="text-2xl font-bold text-blue-900">
                  €{Number(dashboard?.total_order_value ?? 0).toFixed(2)}
                </p>
              </div>

              <div className="bg-green-50 p-6 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <h3 className="text-sm font-medium text-green-900">Completed Orders</h3>
                  <Package className="w-6 h-6 text-green-600" />
                </div>
                <p className="text-2xl font-bold text-green-900">
                  {dashboard?.order_count ?? 0}
                </p>
              </div>

              <div className="bg-purple-50 p-6 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <h3 className="text-sm font-medium text-purple-900">Avg Order Value</h3>
                  <TrendingUp className="w-6 h-6 text-purple-600" />
                </div>
                <p className="text-2xl font-bold text-purple-900">
                  €{Number(dashboard?.average_order_value ?? 0).toFixed(2)}
                </p>
              </div>

              <div className="bg-orange-50 p-6 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <h3 className="text-sm font-medium text-orange-900">Avg Items / Order</h3>
                  <ShoppingCart className="w-6 h-6 text-orange-600" />
                </div>
                <p className="text-2xl font-bold text-orange-900">
                  {Number(dashboard?.average_items_per_order ?? 0).toFixed(1)}
                </p>
              </div>
            </div>

            {/* Status Distribution */}
            <div>
              <h3 className="text-lg font-semibold mb-4 text-purple-900">Order Status Distribution</h3>

              {totalStatusCount === 0 && (
                <p className="text-center text-gray-600 py-8">
                  No orders were found in the selected date range.
                </p>
              )}

              {totalStatusCount > 0 && (
                <div className="space-y-4">
                  {Object.entries(statusDistribution).map(([status, count]) => {
                    const percentage = totalStatusCount > 0 ? (count / totalStatusCount) * 100 : 0;

                    return (
                      <div
                        key={status}
                        className="grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)_48px] gap-3 md:gap-4 items-center"
                      >
                        <span className="text-sm font-medium text-gray-700">
                          {status}
                        </span>

                        <div className="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                          <div
                            className="bg-purple-600 h-3 rounded-full transition-all"
                            style={{ width: `${percentage}%` }}
                          />
                        </div>

                        <span className="text-sm text-gray-600 text-right">
                          {count}
                        </span>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          </>
        )}
      </div>
    </div>
  );
}