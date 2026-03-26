import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { User, Package, Heart, Palette, LogOut } from 'lucide-react';

type ActiveSection = 'profile' | 'orders' | 'designs' | 'wishlist';

type PageProps = {
  auth: {
    customer: null | {
      customer_id: number;
      full_name: string;
      email: string;
    };
  };
};

type Props = {
  active: ActiveSection;
  children: React.ReactNode;
};

export default function CustomerAccountLayout({ active, children }: Props) {
  const { auth } = usePage<PageProps>().props;
  const customer = auth?.customer;

  const handleLogout = () => {
    router.post('/logout');
  };

  if (!customer) {
    return null;
  }

  const menuItems = [
    { path: '/account/profile', label: 'My Profile', icon: User, key: 'profile' as const },
    { path: '/account/orders', label: 'Order History', icon: Package, key: 'orders' as const },
    { path: '/account/designs', label: 'Saved Designs', icon: Palette, key: 'designs' as const },
    { path: '/account/wishlist', label: 'Wishlist', icon: Heart, key: 'wishlist' as const },
  ];

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 className="text-3xl font-bold mb-8">My Account</h1>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="md:col-span-1">
          <div className="bg-white rounded-lg shadow-sm p-4">
            <div className="mb-6 pb-4 border-b">
              <p className="font-semibold">{customer.full_name}</p>
              <p className="text-sm text-gray-600">{customer.email}</p>
            </div>

            <nav className="space-y-1">
              {menuItems.map((item) => {
                const Icon = item.icon;
                const isActive = active === item.key;

                return (
                  <Link
                    key={item.path}
                    href={item.path}
                    className={`flex items-center px-4 py-2 rounded-md ${
                      isActive
                        ? 'bg-blue-100 text-blue-700 font-medium'
                        : 'text-gray-700 hover:bg-gray-100'
                    }`}
                  >
                    <Icon className="w-5 h-5 mr-3" />
                    {item.label}
                  </Link>
                );
              })}

              <button
                onClick={handleLogout}
                className="cursor-pointer w-full flex items-center px-4 py-2 rounded-md text-gray-700 hover:bg-gray-100"
              >
                <LogOut className="w-5 h-5 mr-3" />
                Logout
              </button>
            </nav>
          </div>
        </div>

        <div className="md:col-span-3">{children}</div>
      </div>
    </div>
  );
}