import { Link } from '@inertiajs/react'
import { MapPin, Phone, Mail, Facebook, Instagram } from 'lucide-react'

export default function Footer() {
  const currentYear = new Date().getFullYear()

  return (
    <div className="bg-white">
      <div className="h-16 bg-white" />

      <footer className="bg-gray-900 text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8 items-start">
            <div className="md:pl-8">
              <h3 className="font-bold text-lg mb-4">Loizou Prints</h3>
              <p className="text-gray-400 text-sm leading-relaxed">
                Welcome to P. &amp; A. Loizou Prints, your go-to destination for printing, stationery, and graphic
                design in Paralimni. With over 30 years of expertise, we blend creativity with precision to bring your
                vision to life. Experience excellence with us!
              </p>
            </div>

            <div className="text-center md:pl-2">
              <h3 className="font-bold text-lg mb-4">Quick Links</h3>
              <ul className="space-y-2">
                <li>
                  <Link href="/catalog" className="text-gray-400 hover:text-white transition-colors">
                    Products
                  </Link>
                </li>
                <li>
                  <Link href="/about" className="text-gray-400 hover:text-white transition-colors">
                    About Us
                  </Link>
                </li>
                <li>
                  <Link href="/services" className="text-gray-400 hover:text-white transition-colors">
                    Our Services
                  </Link>
                </li>
                <li>
                  <Link href="/staff/login" className="text-gray-400 hover:text-white transition-colors">
                    Staff Login
                  </Link>
                </li>
              </ul>
            </div>

            <div className="md:pl-4">
              <h3 className="font-bold text-lg mb-4 ml-6">Contact Info</h3>
              <ul className="space-y-3">
                <li className="flex items-start">
                  <MapPin className="w-5 h-5 text-purple-400 mr-3 mt-0.5 flex-shrink-0" />
                  <div>
                    <p className="text-gray-400 text-sm font-semibold mb-1">Main Shop</p>
                    <a
                      href="https://www.google.com/maps/place/Loizou+Prints/@35.0375461,33.9753359,541m/data=!3m2!1e3!4b1!4m6!3m5!1s0x14dfc5f70b7e71f9:0x20541734bac708a2!8m2!3d35.0375461!4d33.9779108!16s%2Fg%2F11c2dxdjtb?entry=ttu&g_ep=EgoyMDI2MDMxMC4wIKXMDSoASAFQAw%3D%3D"
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-gray-400 text-sm hover:text-purple-400 transition-colors"
                    >
                      1st April 120, Paralimni
                    </a>
                  </div>
                </li>

                <li className="flex items-start">
                  <MapPin className="w-5 h-5 text-orange-400 mr-3 mt-0.5 flex-shrink-0" />
                  <div>
                    <p className="text-gray-400 text-sm font-semibold mb-1">2nd Shop</p>
                    <a
                      href="https://www.google.com/maps/place/P.+%26+A.+Loizou/@34.9776928,33.8531778,14z/data=!4m15!1m8!3m7!1s0x14dfd24fd3daa23b:0x7994552ce3c01572!2zzqbPgc6vzr7Ov8-FIM6gzrHOvc6xzrPOuc-Oz4TOv8-FIDI4LCDOns-FzrvOv8-GzqzOs86_z4UgNzUyMCwgzprPjc-Az4HOv8-C!3b1!8m2!3d34.9776155!4d33.8525068!10e5!3m5!1s0x14dfd24fce8cc21f:0xdeaf2eec86e7bbc2!8m2!3d34.9775941!4d33.8531956!16s%2Fg%2F11g6mqsn4t?entry=ttu&g_ep=EgoyMDI2MDMxMC4wIKXMDSoASAFQAw%3D%3D"
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-gray-400 text-sm hover:text-orange-400 transition-colors"
                    >
                      Frixou Panayiotou 28, Xylofagou
                    </a>
                  </div>
                </li>

                <li className="flex items-center">
                  <Phone className="w-5 h-5 text-purple-400 mr-3 flex-shrink-0" />
                  <span className="text-gray-400 text-sm">+357 23 730760</span>
                </li>

                <li className="flex items-center">
                  <Mail className="w-5 h-5 text-purple-400 mr-3 flex-shrink-0" />
                  <a
                    href="https://mail.google.com/mail/?view=cm&fs=1&to=info@loizouprints.com&su=Inquiry%20from%20Loizou%20Prints%20Website&body=Hello%20Loizou%20Prints%2C%0A%0AI%20would%20like%20to%20ask%20about%20your%20products%20or%20services.%0A%0AKind%20regards%2C"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-gray-400 text-sm hover:text-white transition-colors"
                  >
                    info@loizouprints.com
                  </a>
                </li>
              </ul>
            </div>

            <div className="flex flex-col items-center">
              <h3 className="font-bold text-lg mb-4 text-center">Business Hours</h3>

              <div className="space-y-2 text-sm text-gray-400 min-w-[240px]">
                <div className="grid grid-cols-[1fr_auto_1fr] items-center">
                  <span className="text-right">Monday - Friday</span>
                  <span className="px-2">:</span>
                  <span className="text-left">08:30 - 18:30</span>
                </div>

                <div className="grid grid-cols-[1fr_auto_1fr] items-center">
                  <span className="text-right">Saturday</span>
                  <span className="px-2">:</span>
                  <span className="text-left">09:00 - 13:30</span>
                </div>

                <div className="grid grid-cols-[1fr_auto_1fr] items-center">
                  <span className="text-right">Sunday</span>
                  <span className="px-2">:</span>
                  <span className="text-left">Closed</span>
                </div>
              </div>
            </div>
          </div>

          <div className="border-t border-gray-700 mt-8 pt-8">
            <div className="flex flex-col md:flex-row justify-between items-center">
              <div className="text-center text-gray-400 text-sm mb-4 md:mb-0 md:flex-1" />

              <div className="text-center text-gray-400 text-sm mb-4 md:mb-0 md:flex-1">
                <p className="mb-2">&copy; {currentYear} Cyprus University of Technology</p>
                <p className="text-sm whitespace-nowrap">
                  P. & A. Loizou Prints
                </p>
              </div>

              <div className="flex space-x-4 md:flex-1 md:justify-end">
                <a
                  href="https://www.facebook.com/paloizou"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="bg-purple-600 p-3 rounded-full hover:bg-purple-700 text-white transition-colors"
                  aria-label="Facebook"
                >
                  <Facebook className="w-5 h-5" />
                </a>
                <a
                  href="https://www.instagram.com/loizouprints"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="bg-orange-500 p-3 rounded-full hover:bg-orange-600 text-white transition-colors"
                  aria-label="Instagram"
                >
                  <Instagram className="w-5 h-5" />
                </a>
              </div>
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
}