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
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex min-h-16 flex-col gap-3 py-3 sm:h-16 sm:flex-row sm:items-center sm:justify-between sm:py-0">
            <div className="min-w-0">
              <h1 className="text-lg font-bold text-purple-600 sm:text-xl wrap-break-word">
                P. &amp; A. Loizou - Staff Dashboard
              </h1>
              <p className="text-sm text-gray-600">{staff?.role ?? 'Staff'}</p>
            </div>

            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
              <span className="text-sm text-gray-600 wrap-break-word">
                {staff?.full_name ?? staff?.username ?? 'Staff'}
              </span>

              <button
                onClick={handleLogout}
                className="flex items-center text-gray-700 transition-colors hover:text-purple-600"
              >
                <LogOut className="mr-2 h-5 w-5" />
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>

      <div className="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
        <div className="grid grid-cols-1 gap-5 md:grid-cols-5 md:gap-6">
          {/* Sidebar */}
          <div className="md:col-span-1">
            <div className="rounded-lg bg-white p-4 shadow-sm sm:p-5 md:p-4">
              <nav className="space-y-1">
                {navigationItems.map((item) => {
                  const Icon = item.icon

                  return (
                    <Link
                      key={item.href}
                      href={item.href}
                      className={`flex items-center rounded-md px-4 py-2 transition-colors ${
                        item.active
                          ? 'bg-purple-100 text-purple-700 font-medium'
                          : 'text-gray-700 hover:bg-gray-100'
                      }`}
                    >
                      <Icon className="mr-3 h-5 w-5" />
                      {item.label}
                    </Link>
                  )
                })}
              </nav>

              <div className="mt-5 border-t pt-5 sm:mt-6 sm:pt-6">
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