import { Head, Link } from '@inertiajs/react';
import {
  ArrowRight,
  ArrowUp,
} from 'lucide-react';
import { useEffect, useState } from 'react';

export default function Services() {
  const [showBackToTop, setShowBackToTop] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setShowBackToTop(window.scrollY > 400);
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <>
      <Head title="Services" />

      <div>
        {/* Hero Section */}
        <div className="relative overflow-hidden bg-linear-to-br from-purple-700 via-purple-600 to-orange-500 py-18 text-white sm:py-20 md:py-24">
          {/* Background Pattern */}
          <div className="absolute inset-0 opacity-10">
            <div className="absolute right-0 top-0 h-96 w-96 rounded-full bg-white blur-3xl"></div>
            <div className="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-orange-300 blur-3xl"></div>
          </div>

          <div className="relative z-10 mx-auto max-w-7xl -translate-y-4 px-4 text-center sm:-translate-y-7 sm:px-6 lg:-translate-y-11 lg:px-8">
            <div className="mb-5 inline-block rounded-full bg-white/20 px-4 py-2 text-xs font-semibold backdrop-blur-sm sm:mb-6 sm:text-sm">
              ✨ What We Do Best
            </div>

            <h1 className="mb-5 text-4xl font-black leading-tight sm:mb-6 sm:text-5xl md:text-6xl">
              Our{' '}
              <span className="bg-linear-to-r from-orange-300 to-yellow-200 bg-clip-text text-transparent">
                Services
              </span>
            </h1>

            <p className="mx-auto max-w-3xl text-lg font-semibold text-purple-100 sm:text-2xl">
              Comprehensive printing and design solutions for all your needs
            </p>
          </div>

          {/* Decorative wave */}
          <div className="absolute bottom-0 left-0 right-0 h-30 leading-none">
            <svg
              viewBox="0 0 1440 120"
              preserveAspectRatio="none"
              className="block h-20 w-full sm:h-24 md:h-30"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
                fill="white"
              />
            </svg>
          </div>
        </div>

        {/* Everything below hero keeps wide outer page padding */}
        <div className="px-4 sm:px-8 lg:px-16">
          {/* T-Shirt Printing */}
          <section className="bg-white py-8 sm:py-10 md:py-12">
            <div className="mx-auto max-w-5xl">
              <div className="mb-8 text-center">
                <h2 className="mb-4 text-xl font-bold leading-tight text-purple-900 sm:text-2xl md:text-3xl">
                  Looking for T-shirt Printing?
                </h2>

                <div className="mx-auto max-w-3xl space-y-3 text-sm leading-6 text-gray-700 md:text-[15px]">
                  <p>
                    Welcome to Loizou Prints, your premier destination for custom
                    T-shirt printing. We&apos;re passionate about bringing your
                    designs to life on high-quality T-shirts and transforming
                    creative ideas into wearable art.
                  </p>

                  <p>
                    Our service makes it simple to design your own T-shirts for
                    events, businesses, personal projects, and unique gifts.
                    Whether you need apparel for promotion or for fun, our{' '}
                    <strong>intuitive design tool</strong> helps you create it
                    easily.
                  </p>

                  <p className="font-semibold text-purple-900">
                    Add text, upload images, choose from our clipart library,
                    preview your work in real time, save your designs, and order
                    when you&apos;re ready.
                  </p>
                </div>

                <Link
                  href="/catalog?product_type=customizable"
                  className="mt-6 inline-flex items-center rounded-lg bg-linear-to-r from-purple-600 to-orange-500 px-6 py-3 text-sm font-bold text-white shadow-lg transition-all hover:from-purple-700 hover:to-orange-600"
                >
                  Explore Design Tool
                  <ArrowRight className="ml-2 h-4 w-4" />
                </Link>
              </div>

              <div className="mt-8 flex h-40 items-center justify-center rounded-2xl bg-linear-to-br from-purple-100 to-orange-100 shadow-xl sm:h-48 md:h-56">
                <p className="text-5xl text-orange-600 md:text-6xl">👕</p>
              </div>
            </div>
          </section>

          {/* Types of Printing */}
          <section className="rounded-2xl bg-gray-50 py-7 sm:py-8 md:py-10">
            <div className="mx-auto max-w-5xl">
              <h2 className="mb-6 text-center text-xl font-bold text-purple-900 sm:text-2xl md:text-3xl">
                Types of Printing We Offer
              </h2>

              <div className="space-y-4">
                <div className="rounded-xl border-l-4 border-purple-600 bg-white p-5 shadow-md transition-shadow hover:shadow-lg">
                  <div className="flex items-start gap-4">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-purple-600 to-purple-700 text-sm font-bold text-white shadow-md md:h-11 md:w-11">
                      01
                    </div>

                    <div>
                      <h3 className="mb-1.5 text-lg font-bold text-purple-900 md:text-xl">
                        DTF - Direct to Film
                      </h3>
                      <p className="text-sm leading-6 text-gray-700 md:text-[15px]">
                        DTF printing offers vibrant, durable designs that work on
                        cotton, polyester, and blends. It creates flexible,
                        detailed prints with strong colour and excellent wash
                        durability, making it ideal for uniforms, merchandise,
                        and personalised clothing.
                      </p>
                    </div>
                  </div>
                </div>

                <div className="rounded-xl border-l-4 border-orange-500 bg-white p-5 shadow-md transition-shadow hover:shadow-lg">
                  <div className="flex items-start gap-4">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-orange-500 to-orange-600 text-sm font-bold text-white shadow-md md:h-11 md:w-11">
                      02
                    </div>

                    <div>
                      <h3 className="mb-1.5 text-lg font-bold text-purple-900 md:text-xl">
                        Direct-to-Garment (DTG) Printing
                      </h3>
                      <p className="text-sm leading-6 text-gray-700 md:text-[15px]">
                        DTG applies ink directly onto the fabric and is excellent
                        for intricate, multi-colour designs. It works especially
                        well for highly detailed artwork and photo-style prints
                        with a softer finish on the garment.
                      </p>
                    </div>
                  </div>
                </div>

                <div className="rounded-xl border-l-4 border-purple-600 bg-white p-5 shadow-md transition-shadow hover:shadow-lg">
                  <div className="flex items-start gap-4">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-purple-600 to-purple-700 text-sm font-bold text-white shadow-md md:h-11 md:w-11">
                      03
                    </div>

                    <div>
                      <h3 className="mb-1.5 text-lg font-bold text-purple-900 md:text-xl">
                        Heat Transfer Printing
                      </h3>
                      <p className="text-sm leading-6 text-gray-700 md:text-[15px]">
                        Heat transfer printing uses special transfer media plus
                        heat and pressure to apply designs to garments. It is a
                        versatile option for full-colour prints, promotional
                        apparel, and event T-shirts.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          {/* Quality Factors */}
          <section className="mx-auto max-w-5xl py-8 sm:py-10 md:py-12">
            <h2 className="mb-6 text-center text-xl font-bold text-purple-900 sm:text-2xl md:text-3xl">
              Key Points for High-Quality T-Shirt Printing
            </h2>

            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
              <div className="rounded-xl border border-purple-200 bg-linear-to-br from-purple-50 to-purple-100 p-4 shadow-sm transition-shadow hover:shadow-md">
                <h3 className="mb-2 text-base font-bold text-purple-900">
                  Print Durability
                </h3>
                <p className="text-sm leading-6 text-gray-700">
                  Choose a method that keeps the design vibrant without cracking
                  or fading after repeated washing.
                </p>
              </div>

              <div className="rounded-xl border border-orange-200 bg-linear-to-br from-orange-50 to-orange-100 p-4 shadow-sm transition-shadow hover:shadow-md">
                <h3 className="mb-2 text-base font-bold text-purple-900">
                  Fabric Quality
                </h3>
                <p className="text-sm leading-6 text-gray-700">
                  The garment itself should be comfortable, durable, and suitable
                  for regular use.
                </p>
              </div>

              <div className="rounded-xl border border-purple-200 bg-linear-to-br from-purple-50 to-purple-100 p-4 shadow-sm transition-shadow hover:shadow-md">
                <h3 className="mb-2 text-base font-bold text-purple-900">
                  Printing Resolution
                </h3>
                <p className="text-sm leading-6 text-gray-700">
                  Sharp lines, clear details, and clean finishing are essential
                  for a professional result.
                </p>
              </div>

              <div className="rounded-xl border border-orange-200 bg-linear-to-br from-orange-50 to-orange-100 p-4 shadow-sm transition-shadow hover:shadow-md">
                <h3 className="mb-2 text-base font-bold text-purple-900">
                  Color Accuracy
                </h3>
                <p className="text-sm leading-6 text-gray-700">
                  The printed colours should stay close to the original design as
                  much as possible.
                </p>
              </div>

              <div className="rounded-xl border border-purple-200 bg-linear-to-br from-purple-50 to-purple-100 p-4 shadow-sm transition-shadow hover:shadow-md">
                <h3 className="mb-2 text-base font-bold text-purple-900">
                  Customization Options
                </h3>
                <p className="text-sm leading-6 text-gray-700">
                  Good services should provide different garment styles, sizes,
                  and print methods for different use cases.
                </p>
              </div>

              <div className="rounded-xl border border-orange-200 bg-linear-to-br from-orange-50 to-orange-100 p-4 shadow-sm transition-shadow hover:shadow-md">
                <h3 className="mb-2 text-base font-bold text-purple-900">
                  Eco-Friendly Practices
                </h3>
                <p className="text-sm leading-6 text-gray-700">
                  Sustainable materials and lower-impact inks can improve quality
                  while reducing environmental impact.
                </p>
              </div>
            </div>
          </section>

          {/* Digital Printing */}
          <section className="rounded-2xl bg-gray-50 py-7 sm:py-8 md:py-10">
            <div className="mx-auto max-w-5xl">
              <div className="grid grid-cols-1 items-center gap-6 lg:grid-cols-2 lg:gap-8">
                <div>
                  <h2 className="mb-3 text-xl font-bold text-purple-900 sm:text-2xl md:text-3xl">
                    Digital Printing
                  </h2>
                  <div className="space-y-3 text-sm leading-6 text-gray-700 md:text-[15px]">
                    <p>
                      We offer high-quality and cost-effective digital printing
                      for businesses and individuals, from business cards to
                      banners and posters.
                    </p>
                    <p>
                      Digital printing supports faster turnaround times, multiple
                      material types, and flexible customisation for a wide range
                      of projects.
                    </p>
                  </div>
                </div>

                <div className="flex h-40 items-center justify-center rounded-2xl bg-linear-to-br from-purple-100 to-orange-100 shadow-lg sm:h-48 md:h-56">
                  <p className="text-4xl text-purple-600 md:text-5xl">🖨️</p>
                </div>
              </div>
            </div>
          </section>

          {/* Stamps */}
          <section className="mx-auto max-w-5xl py-8 sm:py-10 md:py-12">
            <div className="grid grid-cols-1 items-center gap-6 lg:grid-cols-2 lg:gap-8">
              <div className="flex h-40 items-center justify-center rounded-2xl bg-linear-to-br from-orange-100 to-purple-100 shadow-lg sm:h-48 md:h-56">
                  <p className="text-4xl text-orange-600 md:text-5xl">✓</p>
                </div>

              <div>
                <h2 className="mb-3 text-xl font-bold text-purple-900 sm:text-2xl md:text-3xl">
                  Stamps
                </h2>
                <div className="space-y-3 text-sm leading-6 text-gray-700 md:text-[15px]">
                  <p>
                    We proudly partner with <strong>Trodat</strong>, one of the
                    leading names in the stamp industry, to provide durable,
                    high-quality stamp solutions.
                  </p>
                  <p>
                    These stamps are efficient, long-lasting, and ideal for
                    business, educational, and personal use, with clean and
                    precise impressions.
                  </p>
                </div>
              </div>
            </div>
          </section>

          {/* Large Format Printing */}
          <section className="rounded-2xl bg-gray-50 py-7 sm:py-8 md:py-10">
            <div className="mx-auto max-w-5xl">
              <div className="grid grid-cols-1 items-center gap-6 lg:grid-cols-2 lg:gap-8">
                <div>
                  <h2 className="mb-3 text-xl font-bold text-purple-900 sm:text-2xl md:text-3xl">
                    Large Format Printing
                  </h2>
                  <div className="space-y-3 text-sm leading-6 text-gray-700 md:text-[15px]">
                    <p>
                      We provide a wide range of printing services including
                      digital, offset, and large format printing, with guidance
                      throughout the whole process.
                    </p>
                    <p>
                      Using quality equipment and materials, we deliver strong
                      visual results with competitive pricing and reliable
                      turnaround times.
                    </p>
                  </div>
                </div>

                <div className="flex h-40 items-center justify-center rounded-2xl bg-linear-to-br from-purple-100 to-orange-100 shadow-lg sm:h-48 md:h-56">
                  <p className="text-4xl text-purple-600 md:text-5xl">📐</p>
                </div>
              </div>
            </div>
          </section>

          {/* Graphic Design */}
          <section className="mx-auto max-w-5xl py-8 sm:py-10 md:py-12">
            <div className="grid grid-cols-1 items-center gap-6 lg:grid-cols-2 lg:gap-8">
              <div className="flex h-40 items-center justify-center rounded-2xl bg-linear-to-br from-orange-100 to-purple-100 shadow-lg sm:h-48 md:h-56">
                <p className="text-4xl text-orange-600 md:text-5xl">🎨</p>
              </div>

              <div>
                <h2 className="mb-3 text-xl font-bold text-purple-900 sm:text-2xl md:text-3xl">
                  Graphic Design
                </h2>
                <div className="space-y-3 text-sm leading-6 text-gray-700 md:text-[15px]">
                  <p>
                    Our design team creates eye-catching visuals for logos,
                    branding, marketing material, and other custom creative
                    needs.
                  </p>
                  <p>
                    We focus on tailored design work that strengthens your brand
                    and helps you stand out clearly and professionally.
                  </p>
                </div>
              </div>
            </div>
          </section>

          {/* Photocopying */}
          <section className="rounded-2xl bg-gray-50 py-7 sm:py-8 md:py-10">
            <div className="mx-auto max-w-5xl">
              <div className="grid grid-cols-1 items-center gap-6 lg:grid-cols-2 lg:gap-8">
                <div>
                  <h2 className="mb-3 text-xl font-bold text-purple-900 sm:text-2xl md:text-3xl">
                    Photocopying
                  </h2>
                  <div className="space-y-3 text-sm leading-6 text-gray-700 md:text-[15px]">
                    <p>
                      We provide dependable photocopying for documents of all
                      kinds, including black and white or colour, single-sided
                      and double-sided output.
                    </p>
                    <p>
                      Our equipment delivers consistent quality with fast service
                      and practical pricing for everyday document needs.
                    </p>
                  </div>
                </div>

                <div className="flex h-40 items-center justify-center rounded-2xl bg-linear-to-br from-purple-100 to-orange-100 shadow-lg sm:h-48 md:h-56">
                  <p className="text-4xl text-purple-600 md:text-5xl">📄</p>
                </div>
              </div>
            </div>
          </section>
        </div>

        {/* Back to Top Button */}
        {showBackToTop && (
          <button
            onClick={scrollToTop}
            className="fixed bottom-4 right-4 z-50 cursor-pointer rounded-full bg-purple-600 p-2.5 text-white shadow-lg transition-all duration-300 hover:bg-purple-700 sm:bottom-6 sm:right-6 sm:p-3"
            aria-label="Back to top"
          >
            <ArrowUp className="h-4 w-4 sm:h-5 sm:w-5" />
          </button>
        )}
      </div>
    </>
  );
}