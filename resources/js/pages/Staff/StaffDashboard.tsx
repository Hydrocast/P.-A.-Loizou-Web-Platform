import { Head, Link, router, usePage } from '@inertiajs/react';
import {
  Package,
  ShoppingBag,
  Image as ImageIcon,
  BarChart3,
  Users,
  LogOut,
  FolderOpen,
  BadgePercent,
} from 'lucide-react';

import StaffAnalytics from '../../components/admin/Analytics';
import PricingConfiguration from '../../components/admin/PricingConfiguration';
import StaffManagement from '../../components/admin/StaffManagement';
import CarouselManagement from '../../components/staff/CarouselManagement';
import CategoryManagement from '../../components/staff/CategoryManagement';
import OrderManagement from '../../components/staff/OrderManagement';
import StaffProducts from '../../components/staff/ProductManagement';

type StaffUser = {
  staff_id: number;
  username: string;
  full_name: string | null;
  role: string;
};

type StaffAccount = {
  staff_id: number;
  username: string;
  role: 'Employee' | 'Administrator';
  full_name: string | null;
  account_status: 'Active' | 'Inactive';
};

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

type CustomizableProduct = {
  product_id: number;
  product_name: string;
};

type ExistingPricingTier = {
  minimum_quantity: number;
  maximum_quantity: number;
  unit_price: number;
};

type PageProps = {
  auth: {
    staff: StaffUser | null;
  };
  flash?: {
    success?: string;
    error?: string;
  };
  tab?: string;
  orders?: any[];
  filters?: {
    order_status?: string | null;
    start_date?: string | null;
    end_date?: string | null;
    sort_order?: string | null;
  };
  productFilters?: {
    query?: string | null;
    product_type?: string | null;
    category_id?: number | null;
    visibility_status?: string | null;
  };
  categoryFilters?: {
    query?: string | null;
  };
  analyticsFilters?: {
    start_date?: string | null;
    end_date?: string | null;
  };
  dashboard?: AnalyticsDashboard | null;
  selectedOrder?: any;
  activeStaff?: any[];
  products?: any[];
  categories?: any[];
  slides?: any[];
  linkedProducts?: any[];
  accounts?: StaffAccount[];
  customizableProducts?: CustomizableProduct[];
  existingTiers?: ExistingPricingTier[];
  selectedProductId?: number | null;
};

export default function StaffDashboard() {
  const { props, url } = usePage<PageProps>();
  const staff = props.auth.staff;

  if (!staff) {
    return null;
  }

  const handleLogout = () => {
    router.post('/staff/logout');
  };

  const menuItems = [
    { path: '/staff/orders', label: 'Orders', icon: Package, roles: ['Employee', 'Administrator'] },
    { path: '/staff/products', label: 'Products', icon: ShoppingBag, roles: ['Employee', 'Administrator'] },
    { path: '/staff/categories', label: 'Categories', icon: FolderOpen, roles: ['Employee', 'Administrator'] },
    { path: '/staff/carousel', label: 'Carousel', icon: ImageIcon, roles: ['Employee', 'Administrator'] },
    { path: '/staff/management', label: 'Staff', icon: Users, roles: ['Administrator'] },
    { path: '/staff/pricing', label: 'Pricing', icon: BadgePercent, roles: ['Administrator'] },
    { path: '/staff/analytics', label: 'Analytics', icon: BarChart3, roles: ['Administrator'] },
  ];

  const visibleMenuItems = menuItems.filter((item) => item.roles.includes(staff.role));
  const isActivePath = (path: string) => url === path || url.startsWith(`${path}/`);

  const pageTitle =
    url.startsWith('/staff/orders')
      ? 'Order Management'
      : url.startsWith('/staff/products')
        ? 'Product Management'
        : url.startsWith('/staff/categories')
          ? 'Category Management'
          : url.startsWith('/staff/carousel')
            ? 'Carousel Management'
            : url === '/staff/management'
              ? 'Staff Management'
              : url.startsWith('/staff/pricing')
                ? 'Pricing Management'
                : url.startsWith('/staff/analytics')
                  ? 'Analytics'
                  : 'Staff Dashboard';

  return (
    <>
      <Head title={pageTitle} />

      <div className="min-h-screen bg-gray-100">
        <div className="bg-white shadow-sm">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div className="flex h-16 items-center justify-between">
              <div>
                <h1 className="text-xl font-bold text-purple-600">Staff Dashboard</h1>
                <p className="text-sm text-gray-600">{staff.role}</p>
              </div>

              <div className="flex items-center space-x-4">
                <span className="text-sm text-gray-600">{staff.full_name ?? staff.username}</span>

                <button
                  onClick={handleLogout}
                  className="flex cursor-pointer items-center text-gray-700 transition-colors hover:text-purple-600"
                >
                  <LogOut className="mr-2 h-5 w-5" />
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>

        <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 gap-6 md:grid-cols-5">
            <div className="md:col-span-1">
              <div className="rounded-lg bg-white p-4 shadow-sm">
                <nav className="space-y-1">
                  {visibleMenuItems.map((item) => {
                    const Icon = item.icon;
                    const active = isActivePath(item.path);

                    return (
                      <Link
                        key={item.path}
                        href={item.path}
                        className={`flex items-center rounded-md px-4 py-2 transition-colors cursor-pointer ${
                          active
                            ? 'bg-purple-100 font-medium text-purple-700'
                            : 'text-gray-700 hover:bg-gray-100'
                        }`}
                      >
                        <Icon className="mr-3 h-5 w-5" />
                        {item.label}
                      </Link>
                    );
                  })}
                </nav>

                <div className="mt-6 border-t pt-6">
                  <Link href="/" className="block text-center text-sm text-purple-600 hover:underline">
                    View Main Site
                  </Link>
                </div>
              </div>
            </div>

            <div className="min-w-0 md:col-span-4">
              {url.startsWith('/staff/orders') && (
                <OrderManagement
                  orders={props.orders ?? []}
                  filters={props.filters ?? {}}
                  selectedOrder={props.selectedOrder ?? null}
                  activeStaff={props.activeStaff ?? []}
                  flash={props.flash ?? {}}
                />
              )}

              {url.startsWith('/staff/products') && (
                <StaffProducts
                  products={props.products ?? []}
                  categories={props.categories ?? []}
                  filters={props.productFilters ?? {}}
                  flash={props.flash ?? {}}
                />
              )}

              {url.startsWith('/staff/categories') && (
                <CategoryManagement
                  categories={props.categories ?? []}
                  filters={props.categoryFilters ?? {}}
                  flash={props.flash ?? {}}
                />
              )}

              {url.startsWith('/staff/carousel') && (
                <CarouselManagement
                  slides={props.slides ?? []}
                  linkedProducts={props.linkedProducts ?? []}
                  flash={props.flash ?? {}}
                />
              )}

              {url === '/staff/management' && (
                <StaffManagement
                  accounts={props.accounts ?? []}
                  flash={props.flash ?? {}}
                  currentStaffUsername={staff.username}
                />
              )}

              {url.startsWith('/staff/pricing') && (
                <PricingConfiguration
                  customizableProducts={props.customizableProducts ?? []}
                  existingTiers={props.existingTiers ?? []}
                  selectedProductId={props.selectedProductId ?? null}
                  flash={props.flash ?? {}}
                />
              )}

              {url.startsWith('/staff/analytics') && (
                <StaffAnalytics
                  dashboard={props.dashboard ?? null}
                  filters={props.analyticsFilters ?? {}}
                  flash={props.flash ?? {}}
                />
              )}
            </div>
          </div>
        </div>
      </div>
    </>
  );
}