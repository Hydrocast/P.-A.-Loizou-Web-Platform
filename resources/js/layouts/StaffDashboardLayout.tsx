import { Link, router, usePage } from '@inertiajs/react'
import { LayoutDashboard, LogOut } from 'lucide-react'

type StaffUser = {
  staff_id: number
  username: string
  full_name: string | null
  role: string
}

type PageProps = {
  auth: {
    staff: StaffUser | null
  }
}

type StaffDashboardLayoutProps = {
  children: React.ReactNode
  active: 'dashboard'
}

export default function StaffDashboardLayout({ children, active }: StaffDashboardLayoutProps) {
  const { auth } = usePage<PageProps>().props
  const staff = auth.staff

  const handleLogout = () => {
    router.post('/staff/logout')
  }

  const navigationItems = [
    {
      href: '/staff/dashboard',
      label: 'Dashboard',
      icon: LayoutDashboard,
      active: active === 'dashboard',
    },
  ]

  return (
    <div className="min-h-screen bg-gray-100">
      {/* Header */}
      <div className="bg-white shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div>
              <h1 className="text-xl font-bold text-purple-600">P. &amp; A. Loizou - Staff Dashboard</h1>
              <p className="text-sm text-gray-600">{staff?.role ?? 'Staff'}</p>
            </div>

            <div className="flex items-center space-x-4">
              <span className="text-sm text-gray-600">{staff?.full_name ?? staff?.username ?? 'Staff'}</span>

              <button
                onClick={handleLogout}
                className="flex items-center text-gray-700 hover:text-purple-600 transition-colors"
              >
                <LogOut className="w-5 h-5 mr-2" />
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid grid-cols-1 md:grid-cols-5 gap-6">
          {/* Sidebar */}
          <div className="md:col-span-1">
            <div className="bg-white rounded-lg shadow-sm p-4">
              <nav className="space-y-1">
                {navigationItems.map((item) => {
                  const Icon = item.icon

                  return (
                    <Link
                      key={item.href}
                      href={item.href}
                      className={`flex items-center px-4 py-2 rounded-md transition-colors ${
                        item.active
                          ? 'bg-purple-100 text-purple-700 font-medium'
                          : 'text-gray-700 hover:bg-gray-100'
                      }`}
                    >
                      <Icon className="w-5 h-5 mr-3" />
                      {item.label}
                    </Link>
                  )
                })}
              </nav>

              <div className="mt-6 pt-6 border-t">
                <Link href="/" className="block text-center text-sm text-purple-600 hover:underline">
                  View Main Site
                </Link>
              </div>
            </div>
          </div>

          {/* Content */}
          <div className="md:col-span-4">
            {children}
          </div>
        </div>
      </div>
    </div>
  )
}
