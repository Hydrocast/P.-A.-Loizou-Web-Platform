import { Link, router, usePage } from '@inertiajs/react'
import {
  ArrowLeft,
  User,
  Heart,
  ShoppingCart,
  LogOut,
  Menu,
  X,
  ChevronDown,
  ChevronUp,
} from 'lucide-react'
import { useState } from 'react'
import logoImage from '../../assets/logo.webp'

type PageProps = {
  auth: {
    customer: null | {
      customer_id: number
      full_name: string
      email: string
    }
    staff: null | {
      staff_id: number
      username: string
      full_name: string
      role: string
    }
  }
}

export default function Header() {
  const page = usePage<PageProps>()
  const { auth } = page.props
  const customer = auth?.customer
  const staff = auth?.staff
  const currentPath = page.url ?? ''
  const isStaffArea = currentPath.startsWith('/staff')
  const shouldShowStaffBackLink = Boolean(staff && !isStaffArea)
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)
  const [isMobileAccountOpen, setIsMobileAccountOpen] = useState(false)

  const handleLogout = () => {
    setIsMobileMenuOpen(false)
    setIsMobileAccountOpen(false)
    router.post('/logout')
  }

  const handleMobileClose = () => {
    setIsMobileMenuOpen(false)
    setIsMobileAccountOpen(false)
  }

  return (
    <header className="sticky top-0 z-50 bg-white shadow-sm">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex h-16 items-center justify-between">
          <Link href="/" className="flex items-center">
            <img src={logoImage} alt="Loizou Prints" className="h-10 sm:h-12" />
          </Link>

          <nav className="hidden md:flex md:space-x-8">
            <Link href="/" className="text-gray-700 hover:text-purple-600 font-medium transition-colors">
              Home
            </Link>
            <Link href="/about" className="text-gray-700 hover:text-purple-600 font-medium transition-colors">
              About Us
            </Link>
            <Link href="/services" className="text-gray-700 hover:text-purple-600 font-medium transition-colors">
              Our Services
            </Link>
            <Link href="/catalog" className="text-gray-700 hover:text-purple-600 font-medium transition-colors">
              Products
            </Link>
            <Link href="/contact" className="text-gray-700 hover:text-purple-600 font-medium transition-colors">
              Contact
            </Link>
          </nav>

          <div className="flex items-center space-x-3 sm:space-x-4">
            {shouldShowStaffBackLink ? (
              <Link
                href="/staff/dashboard"
                className="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 transition-colors hover:text-purple-600"
                title="Back to Dashboard"
              >
                <ArrowLeft className="h-4 w-4 shrink-0" />
                <span className="hidden sm:inline">Back to Dashboard</span>
              </Link>
            ) : customer ? (
              <>
                <Link
                  href="/account/wishlist"
                  className="relative text-gray-700 transition-colors hover:text-purple-600"
                  title="Wishlist"
                >
                  <Heart className="h-5 w-5" />
                </Link>

                <Link
                  href="/cart"
                  className="relative text-gray-700 transition-colors hover:text-purple-600"
                  title="Cart"
                >
                  <ShoppingCart className="h-5 w-5" />
                </Link>

                <div className="relative hidden md:block">
                  <div className="group">
                    <button className="flex items-center space-x-2 text-gray-700 transition-colors hover:text-purple-600">
                      <User className="h-5 w-5" />
                      <span className="max-w-40 truncate">{customer.full_name}</span>
                    </button>

                    <div className="invisible absolute right-0 mt-2 w-56 rounded-md bg-white py-2 opacity-0 shadow-lg transition-all duration-200 group-hover:visible group-hover:opacity-100">
                      <Link
                        href="/account/profile"
                        className="block px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-100"
                      >
                        My Account
                      </Link>

                      <Link
                        href="/account/orders"
                        className="block px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-100"
                      >
                        Order History
                      </Link>

                      <Link
                        href="/account/designs"
                        className="block px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-100"
                      >
                        Saved Designs
                      </Link>

                      <button
                        onClick={handleLogout}
                        className="flex w-full cursor-pointer items-center px-4 py-2 text-left text-sm text-gray-700 transition-colors hover:bg-gray-100"
                      >
                        <LogOut className="mr-2 h-4 w-4" />
                        Logout
                      </button>
                    </div>
                  </div>
                </div>
              </>
            ) : (
              <div className="hidden items-center space-x-4 md:flex">
                <Link href="/login" className="text-gray-700 transition-colors hover:text-purple-600">
                  Login
                </Link>
                <Link
                  href="/register"
                  className="rounded-md bg-purple-600 px-4 py-2 text-white transition-colors hover:bg-purple-700"
                >
                  Register
                </Link>
              </div>
            )}

            <button
              type="button"
              onClick={() => setIsMobileMenuOpen((prev) => !prev)}
              className="inline-flex items-center justify-center rounded-md p-2 text-gray-700 transition-colors hover:bg-gray-100 hover:text-purple-600 md:hidden"
              aria-label={isMobileMenuOpen ? 'Close menu' : 'Open menu'}
              aria-expanded={isMobileMenuOpen}
            >
              {isMobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </button>
          </div>
        </div>

        {isMobileMenuOpen && (
          <div className="border-t border-gray-200 py-4 md:hidden">
            <nav className="space-y-1">
              <Link
                href="/"
                className="block rounded-md px-3 py-2 text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                onClick={() => setIsMobileMenuOpen(false)}
              >
                Home
              </Link>
              <Link
                href="/about"
                className="block rounded-md px-3 py-2 text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                onClick={() => setIsMobileMenuOpen(false)}
              >
                About Us
              </Link>
              <Link
                href="/services"
                className="block rounded-md px-3 py-2 text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                onClick={() => setIsMobileMenuOpen(false)}
              >
                Our Services
              </Link>
              <Link
                href="/catalog"
                className="block rounded-md px-3 py-2 text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                onClick={() => setIsMobileMenuOpen(false)}
              >
                Products
              </Link>
              <Link
                href="/contact"
                className="block rounded-md px-3 py-2 text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                onClick={() => setIsMobileMenuOpen(false)}
              >
                Contact
              </Link>
            </nav>

            <div className="mt-4 border-t border-gray-200 pt-4">
              {shouldShowStaffBackLink ? (
                <div className="space-y-2">
                  <Link
                    href="/staff/dashboard"
                    className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                    onClick={handleMobileClose}
                  >
                    <ArrowLeft className="h-4 w-4 shrink-0" />
                    <span>Back to Dashboard</span>
                  </Link>
                </div>
              ) : customer ? (
                <div className="space-y-2">
                  <button
                    type="button"
                    onClick={() => setIsMobileAccountOpen((prev) => !prev)}
                    className="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                  >
                    <span className="flex min-w-0 items-center gap-2">
                      <User className="h-5 w-5 shrink-0" />
                      <span className="truncate">{customer.full_name}</span>
                    </span>

                    {isMobileAccountOpen ? (
                      <ChevronUp className="h-4 w-4" />
                    ) : (
                      <ChevronDown className="h-4 w-4" />
                    )}
                  </button>

                  {isMobileAccountOpen && (
                    <div className="space-y-1 pl-2">
                      <Link
                        href="/account/profile"
                        className="block rounded-md px-3 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                        onClick={handleMobileClose}
                      >
                        My Account
                      </Link>

                      <Link
                        href="/account/orders"
                        className="block rounded-md px-3 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                        onClick={handleMobileClose}
                      >
                        Order History
                      </Link>

                      <Link
                        href="/account/designs"
                        className="block rounded-md px-3 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                        onClick={handleMobileClose}
                      >
                        Saved Designs
                      </Link>

                      <Link
                        href="/account/wishlist"
                        className="block rounded-md px-3 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                        onClick={handleMobileClose}
                      >
                        Wishlist
                      </Link>

                      <Link
                        href="/cart"
                        className="block rounded-md px-3 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                        onClick={handleMobileClose}
                      >
                        Cart
                      </Link>

                      <button
                        onClick={handleLogout}
                        className="flex w-full cursor-pointer items-center rounded-md px-3 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                      >
                        <LogOut className="mr-2 h-4 w-4" />
                        Logout
                      </button>
                    </div>
                  )}
                </div>
              ) : (
                <div className="space-y-2">
                  <Link
                    href="/login"
                    className="block rounded-md px-3 py-2 text-gray-700 transition-colors hover:bg-gray-50 hover:text-purple-600"
                    onClick={handleMobileClose}
                  >
                    Login
                  </Link>

                  <Link
                    href="/register"
                    className="block rounded-md bg-purple-600 px-3 py-2 text-center text-white transition-colors hover:bg-purple-700"
                    onClick={handleMobileClose}
                  >
                    Register
                  </Link>
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </header>
  )
}