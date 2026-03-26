import { Link, router, usePage } from '@inertiajs/react'
import { User, Heart, ShoppingCart, LogOut } from 'lucide-react'
import logoImage from '../../assets/logo.png'

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
  const { auth } = usePage<PageProps>().props
  const customer = auth?.customer

  const handleLogout = () => {
    router.post('/logout')
  }

  return (
    <header className="bg-white shadow-sm sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <Link href="/" className="flex items-center">
            <img src={logoImage} alt="Loizou Prints" className="h-12" />
          </Link>

          <nav className="hidden md:flex space-x-8">
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

          <div className="flex items-center space-x-4">
            {customer ? (
              <>
                <Link
                  href="/account/wishlist"
                  className="text-gray-700 hover:text-purple-600 relative transition-colors"
                  title="Wishlist"
                >
                  <Heart className="w-5 h-5" />
                </Link>

                <Link
                  href="/cart"
                  className="text-gray-700 hover:text-purple-600 relative transition-colors"
                  title="Cart"
                >
                  <ShoppingCart className="w-5 h-5" />
                </Link>

                <div className="relative group">
                  <button className="flex items-center space-x-2 text-gray-700 hover:text-purple-600 transition-colors">
                    <User className="w-5 h-5" />
                    <span className="hidden sm:inline">{customer.full_name}</span>
                  </button>

                  <div className="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                    <Link
                      href="/account/profile"
                      className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                    >
                      My Account
                    </Link>

                    <Link
                      href="/account/orders"
                      className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                    >
                      Order History
                    </Link>

                    <Link
                      href="/account/designs"
                      className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                    >
                      Saved Designs
                    </Link>

                    <button
                      onClick={handleLogout}
                      className="cursor-pointer w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                    >
                      <LogOut className="w-4 h-4 mr-2" />
                      Logout
                    </button>
                  </div>
                </div>
              </>
            ) : (
              <>
                <Link href="/login" className="text-gray-700 hover:text-purple-600 transition-colors">
                  Login
                </Link>
                <Link
                  href="/register"
                  className="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors"
                >
                  Register
                </Link>
              </>
            )}
          </div>
        </div>
      </div>
    </header>
  )
}