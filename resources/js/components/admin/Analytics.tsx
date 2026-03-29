import { router } from '@inertiajs/react';
import { TrendingUp, Package, DollarSign, ShoppingCart } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import {
  Area,
  AreaChart,
  CartesianGrid,
  Cell,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { useTimedFlash } from '@/hooks/useTimedFlash';

type SalesDataPoint = {
  date_key: string;
  date_label: string;
  sales: number;
};

type SalesSummary = {
  peak_day: {
    date_label: string;
    sales: number;
  } | null;
  low_day: {
    date_label: string;
    sales: number;
  } | null;
  average_daily_sales: number;
};

type AnalyticsDashboard = {
  total_order_value: number;
  order_count: number;
  average_order_value: number;
  average_items_per_order: number;
  status_distribution: Record<string, number>;
  daily_sales: SalesDataPoint[];
  sales_summary: SalesSummary | null;
  start_date: string;
  end_date: string;
  has_data: boolean;
};

type AnalyticsFilters = {
  preset?: string | null;
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

type AnalyticsContentProps = {
  dashboard: AnalyticsDashboard | null;
  initialPreset: string;
  initialStartDate: string;
  initialEndDate: string;
  visibleError?: string;
};

type PieStatusItem = {
  name: string;
  value: number;
  color: string;
};

type PresetOption = {
  value: string;
  label: string;
};

const STATUS_COLORS: Record<string, string> = {
  Pending: '#3b82f6',
  'Ready for Pickup': '#22c55e',
  Completed: '#6b7280',
  Cancelled: '#ef4444',
  Processing: '#f59e0b',
};

const ORDERED_STATUSES = [
  'Pending',
  'Ready for Pickup',
  'Completed',
  'Cancelled',
  'Processing',
];

const PRESET_OPTIONS: PresetOption[] = [
  { value: 'today', label: 'Today' },
  { value: 'last_7_days', label: 'Last 7 Days' },
  { value: 'last_30_days', label: 'Last 30 Days' },
  { value: 'year_to_date', label: 'Year to Date' },
  { value: 'all_time', label: 'All Time' },
  { value: 'custom', label: 'Custom Range' },
];

function hasInvalidDateRange(nextStartDate: string, nextEndDate: string): boolean {
  if (!nextStartDate || !nextEndDate) return false;
  return nextEndDate < nextStartDate;
}

function formatCurrency(value: number): string {
  return `€${Number(value).toFixed(2)}`;
}

function formatSelectedDateRange(startDate: string, endDate: string): string {
  if (!startDate || !endDate) {
    return 'Custom Range';
  }

  const start = new Date(`${startDate}T00:00:00`);
  const end = new Date(`${endDate}T00:00:00`);

  if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
    return 'Custom Range';
  }

  const sameYear = start.getFullYear() === end.getFullYear();
  const sameMonth = sameYear && start.getMonth() === end.getMonth();

  if (sameMonth) {
    return `${start.toLocaleDateString('en-GB', {
      day: 'numeric',
      month: 'short',
    })}–${end.toLocaleDateString('en-GB', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    })}`;
  }

  if (sameYear) {
    return `${start.toLocaleDateString('en-GB', {
      day: 'numeric',
      month: 'short',
    })} – ${end.toLocaleDateString('en-GB', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    })}`;
  }

  return `${start.toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })} – ${end.toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })}`;
}

function AnalyticsContent({
  dashboard,
  initialPreset,
  initialStartDate,
  initialEndDate,
  visibleError,
}: AnalyticsContentProps) {
  const [preset, setPreset] = useState(initialPreset);
  const [startDate, setStartDate] = useState(initialStartDate);
  const [endDate, setEndDate] = useState(initialEndDate);
  const [filterDateError, setFilterDateError] = useState('');

  const startDateInputRef = useRef<HTMLInputElement | null>(null);
  const endDateInputRef = useRef<HTMLInputElement | null>(null);

  useEffect(() => {
    if (!filterDateError) return;

    const timer = window.setTimeout(() => setFilterDateError(''), 4000);
    return () => window.clearTimeout(timer);
  }, [filterDateError]);

  const handlePresetChange = (value: string) => {
    setPreset(value);
    setFilterDateError('');

    if (value === 'custom') {
      return;
    }

    router.get(
      '/staff/analytics',
      {
        preset: value,
      },
      {
        preserveScroll: true,
        preserveState: true,
      },
    );
  };

  const handleDateChange = (field: 'start_date' | 'end_date', value: string) => {
    const nextStartDate = field === 'start_date' ? value : startDate;
    const nextEndDate = field === 'end_date' ? value : endDate;

    if (field === 'start_date') {
      setStartDate(value);
    } else {
      setEndDate(value);
    }

    setPreset('custom');

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
      },
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

  const pieData: PieStatusItem[] = ORDERED_STATUSES.map((status) => ({
    name: status,
    value: Number(statusDistribution[status] ?? 0),
    color: STATUS_COLORS[status],
  }));

  const totalStatusCount = pieData.reduce((sum, item) => sum + item.value, 0);
  const dailySales = dashboard?.daily_sales ?? [];
  const salesSummary = dashboard?.sales_summary ?? null;
  const hasInvalidRange = hasInvalidDateRange(startDate, endDate);
  const selectedRangeLabel = preset === 'custom'
    ? formatSelectedDateRange(startDate, endDate)
    : (PRESET_OPTIONS.find((option) => option.value === preset)?.label ?? 'Custom Range');

  return (
    <div className="overflow-hidden rounded-lg bg-white p-4 shadow-md sm:p-5 md:p-6">
        <h2 className="mb-5 text-xl font-semibold text-purple-900 sm:text-2xl md:mb-6">
          Sales Analytics
        </h2>

        {visibleError && (
          <div className="mb-4 rounded-md border border-red-200 bg-red-100 px-4 py-3 text-sm text-red-800 sm:text-base">
            {visibleError}
          </div>
        )}

        {filterDateError && (
          <div className="mb-4 rounded-md border border-red-200 bg-red-100 px-4 py-3 text-sm text-red-800 sm:text-base">
            {filterDateError}
          </div>
        )}

        {/* Date Range Filter */}
        <div className="mb-6 grid grid-cols-1 gap-4 lg:mb-8 lg:grid-cols-3">
          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Date Preset</label>
            <select
              value={preset}
              onChange={(e) => handlePresetChange(e.target.value)}
              className="h-11 w-full cursor-pointer rounded-md border border-gray-300 bg-white px-4 text-sm focus:ring-2 focus:ring-purple-500"
            >
              {PRESET_OPTIONS.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
            <div
              onClick={() => openDatePicker(startDateInputRef.current)}
              className="flex h-11 w-full cursor-pointer items-center rounded-md border border-gray-300 bg-white text-sm focus-within:ring-2 focus-within:ring-purple-500"
            >
              <input
                ref={startDateInputRef}
                type="date"
                value={startDate}
                onChange={(e) => handleDateChange('start_date', e.target.value)}
                className="h-full w-full cursor-pointer rounded-md bg-transparent px-3 text-sm outline-none sm:px-4"
              />
            </div>
          </div>

          <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">End Date</label>
            <div
              onClick={() => openDatePicker(endDateInputRef.current)}
              className="flex h-11 w-full cursor-pointer items-center rounded-md border border-gray-300 bg-white text-sm focus-within:ring-2 focus-within:ring-purple-500"
            >
              <input
                ref={endDateInputRef}
                type="date"
                value={endDate}
                onChange={(e) => handleDateChange('end_date', e.target.value)}
                className="h-full w-full cursor-pointer rounded-md bg-transparent px-3 text-sm outline-none sm:px-4"
              />
            </div>
          </div>
        </div>

        {hasInvalidRange ? (
          <div className="rounded-lg border border-amber-200 bg-amber-50 px-4 py-5 text-amber-900 sm:px-5 sm:py-6">
            <p className="text-sm font-medium">Please choose a valid date range to view analytics.</p>
            <p className="mt-1 text-sm text-amber-800">
              The end date must be the same as or later than the start date.
            </p>
          </div>
        ) : (
          <>
            {/* Metrics Cards */}
            <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:mb-8 lg:grid-cols-4 lg:gap-4">
              <div className="rounded-lg bg-blue-100 p-4 sm:p-5 md:p-6">
                <div className="mb-2 flex items-center justify-between">
                  <h3 className="text-sm font-medium text-blue-900">Total Revenue</h3>
                  <DollarSign className="h-5 w-5 text-blue-600 sm:h-6 sm:w-6" />
                </div>
                <p className="text-xl font-bold text-blue-900 sm:text-2xl">
                  {formatCurrency(Number(dashboard?.total_order_value ?? 0))}
                </p>
              </div>

              <div className="rounded-lg bg-green-100 p-4 sm:p-5 md:p-6">
                <div className="mb-2 flex items-center justify-between">
                  <h3 className="text-sm font-medium text-green-900">Completed Orders</h3>
                  <Package className="h-5 w-5 text-green-600 sm:h-6 sm:w-6" />
                </div>
                <p className="text-xl font-bold text-green-900 sm:text-2xl">
                  {dashboard?.order_count ?? 0}
                </p>
              </div>

              <div className="rounded-lg bg-purple-100 p-4 sm:p-5 md:p-6">
                <div className="mb-2 flex items-center justify-between">
                  <h3 className="text-sm font-medium text-purple-900">Avg Order Value</h3>
                  <TrendingUp className="h-5 w-5 text-purple-600 sm:h-6 sm:w-6" />
                </div>
                <p className="text-xl font-bold text-purple-900 sm:text-2xl">
                  {formatCurrency(Number(dashboard?.average_order_value ?? 0))}
                </p>
              </div>

              <div className="rounded-lg bg-orange-100 p-4 sm:p-5 md:p-6">
                <div className="mb-2 flex items-center justify-between">
                  <h3 className="text-sm font-medium text-orange-900">Avg Items / Order</h3>
                  <ShoppingCart className="h-5 w-5 text-orange-600 sm:h-6 sm:w-6" />
                </div>
                <p className="text-xl font-bold text-orange-900 sm:text-2xl">
                  {Number(dashboard?.average_items_per_order ?? 0).toFixed(1)}
                </p>
              </div>
            </div>

            {/* Sales Performance Graph */}
            <div className="mb-6 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-6 md:mb-8 md:p-8">
              <div className="mb-4 flex flex-col gap-2 sm:mb-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                  <h3 className="text-lg font-semibold text-slate-900 sm:text-xl">
                    Sales Performance
                  </h3>
                  <p className="mt-1 text-sm text-slate-500">
                    Revenue over selected period
                  </p>
                </div>

                <div className="flex items-center gap-2 text-sm">
                  <span className="rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 font-medium text-blue-700">
                    {selectedRangeLabel}
                  </span>
                </div>
              </div>

              <div className="h-40 w-full sm:h-48 md:h-56">
                <ResponsiveContainer width="100%" height="100%">
                  <AreaChart
                    data={dailySales}
                    margin={{ top: 10, right: 12, left: 0, bottom: 0 }}
                  >
                    <defs>
                      <linearGradient id="salesGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="5%" stopColor="#3b82f6" stopOpacity={0.28} />
                        <stop offset="95%" stopColor="#3b82f6" stopOpacity={0} />
                      </linearGradient>
                    </defs>

                    <CartesianGrid
                      strokeDasharray="3 3"
                      stroke="#e2e8f0"
                      vertical={false}
                    />

                    <XAxis
                      dataKey="date_label"
                      stroke="#94a3b8"
                      tick={{ fill: '#64748b', fontSize: 12 }}
                      tickLine={false}
                      axisLine={{ stroke: '#e2e8f0' }}
                      minTickGap={24}
                    />

                    <YAxis
                      stroke="#94a3b8"
                      tick={{ fill: '#64748b', fontSize: 12 }}
                      tickLine={false}
                      axisLine={{ stroke: '#e2e8f0' }}
                      tickFormatter={(value) => `€${value}`}
                      width={52}
                    />

                    <Tooltip
                      content={({ active, payload }) => {
                        if (active && payload && payload.length) {
                          const point = payload[0];

                          return (
                            <div className="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-lg">
                              <p className="mb-1 text-sm text-slate-600">
                                {point.payload.date_label}
                              </p>
                              <p className="text-lg font-bold text-slate-900">
                                {formatCurrency(Number(point.value ?? 0))}
                              </p>
                              <p className="mt-1 text-xs text-slate-500">
                                Completed order revenue
                              </p>
                            </div>
                          );
                        }

                        return null;
                      }}
                      cursor={{
                        stroke: '#3b82f6',
                        strokeWidth: 2,
                        strokeDasharray: '5 5',
                      }}
                    />

                    <Area
                      type="monotone"
                      dataKey="sales"
                      stroke="#3b82f6"
                      strokeWidth={3}
                      fill="url(#salesGradient)"
                      dot={{ fill: '#3b82f6', strokeWidth: 2, r: 3.5, stroke: '#ffffff' }}
                      activeDot={{ r: 6, fill: '#3b82f6', stroke: '#ffffff', strokeWidth: 3 }}
                    />
                  </AreaChart>
                </ResponsiveContainer>
              </div>

              {/* Graph Stats Summary */}
              <div className="mt-4 grid grid-cols-1 gap-3 border-t border-slate-100 pt-4 sm:grid-cols-3">
                <div className="text-center">
                  <p className="mb-1 text-xs text-slate-500 sm:text-sm">Peak Day</p>
                  <p className="text-sm font-semibold text-slate-900 sm:text-base">
                    {salesSummary?.peak_day ? salesSummary.peak_day.date_label : '-'}
                  </p>
                </div>

                <div className="text-center">
                  <p className="mb-1 text-xs text-slate-500 sm:text-sm">Low Day</p>
                  <p className="text-sm font-semibold text-slate-900 sm:text-base">
                    {salesSummary?.low_day ? salesSummary.low_day.date_label : '-'}
                  </p>
                </div>

                <div className="text-center">
                  <p className="mb-1 text-xs text-slate-500 sm:text-sm">Avg Daily</p>
                  <p className="text-sm font-semibold text-slate-900 sm:text-base">
                    {formatCurrency(Number(salesSummary?.average_daily_sales ?? 0))}
                  </p>
                </div>
              </div>
            </div>

            {/* Order Status Distribution */}
            <div className="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-6 md:p-8">
              <div className="mb-5">
                <h3 className="text-lg font-semibold text-slate-900 sm:text-xl">
                  Order Status Distribution
                </h3>
                <p className="mt-1 text-sm text-slate-500">
                  Order mix across the selected date range
                </p>
              </div>

              {totalStatusCount === 0 ? (
                <p className="py-6 text-center text-sm text-gray-600 sm:py-8 sm:text-base">
                  No orders were found in the selected date range.
                </p>
              ) : (
                <div className="grid grid-cols-1 gap-4 lg:grid-cols-[280px_minmax(0,1fr)] lg:items-center xl:grid-cols-[300px_minmax(0,1fr)]">
                  {/* Pie Chart - Left Side */}
                  <div className="flex items-center justify-center">
                    <div className="relative aspect-square w-full max-w-60 pb-5 sm:max-w-70 sm:pb-6 md:max-w-75 md:pb-8">
                      <ResponsiveContainer width="100%" height="100%">
                        <PieChart>
                          <Pie
                            data={pieData}
                            cx="50%"
                            cy="50%"
                            startAngle={90}
                            endAngle={450}
                            innerRadius="62%"
                            outerRadius="84%"
                            paddingAngle={3}
                            dataKey="value"
                            strokeWidth={0}
                          >
                            {pieData.map((item) => (
                              <Cell key={item.name} fill={item.color} />
                            ))}
                          </Pie>
                        </PieChart>
                      </ResponsiveContainer>

                      <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <div className="text-center">
                          <p className="text-3xl font-bold text-slate-900">
                            {totalStatusCount}
                          </p>
                          <p className="mt-1 text-sm text-slate-500">Total Orders</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    {pieData.map((item) => {
                      const percentage = totalStatusCount > 0
                        ? ((item.value / totalStatusCount) * 100).toFixed(1)
                        : '0.0';

                      return (
                        <div
                          key={item.name}
                          className="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 sm:px-4 sm:py-3"
                        >
                          <div className="flex items-start justify-between gap-3">
                            <div className="min-w-0">
                              <div className="flex items-center gap-2">
                                <span
                                  className="mt-0.5 inline-block h-2.5 w-2.5 shrink-0 rounded-full"
                                  style={{ backgroundColor: item.color }}
                                />
                                <p className="text-sm font-medium text-slate-800">
                                  {item.name}
                                </p>
                              </div>

                              <p className="mt-1 text-xs text-slate-500 sm:mt-2">
                                {percentage}% of selected orders
                              </p>
                            </div>

                            <p className="shrink-0 text-lg font-semibold text-slate-900">
                              {item.value}
                            </p>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              )}
            </div>
          </>
        )}
    </div>
  );
}

export default function Analytics({
  dashboard,
  filters = {},
  flash = {},
}: AnalyticsProps) {
  const { visibleError } = useTimedFlash({
    success: flash?.success,
    error: flash?.error,
  });

  const initialPreset = filters.preset ?? 'last_30_days';
  const initialStartDate = filters.start_date ?? '';
  const initialEndDate = filters.end_date ?? '';
  const filtersKey = `${initialPreset}__${initialStartDate}__${initialEndDate}`;

  return (
    <AnalyticsContent
      key={filtersKey}
      dashboard={dashboard}
      initialPreset={initialPreset}
      initialStartDate={initialStartDate}
      initialEndDate={initialEndDate}
      visibleError={visibleError}
    />
  );
}