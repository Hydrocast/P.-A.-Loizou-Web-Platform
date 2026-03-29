import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Check } from 'lucide-react';
import { useState, useEffect } from 'react';
import logoImage from '../../assets/logo.webp';

type Slide = {
  slide_id: number;
  title: string;
  description: string;
  image_reference?: string | null;
  image_url?: string | null;
  product_id?: number | null;
  product_type?: 'standard' | 'customizable' | null;
};

type HomeProps = {
  slides: Slide[];
};

const SERVICES = [
  { name: 'Graphic Design', icon: '🎨' },
  { name: 'Digital Prints', icon: '🖨️' },
  { name: 'Large Format Printing', icon: '📐' },
  { name: 'Brand/UV Printing', icon: '✨' },
  { name: 'Laser Engraving', icon: '⚡' },
  { name: 'T-Shirt Printing', icon: '👕' },
];

export default function Home({ slides }: HomeProps) {
  const [currentSlide, setCurrentSlide] = useState(0);

  useEffect(() => {
    if (!slides.length) return;

    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % slides.length);
    }, 5000);

    return () => clearInterval(timer);
  }, [slides.length]);

  // Move to the next active slide
  const nextSlide = () => {
    if (!slides.length) return;
    setCurrentSlide((prev) => (prev + 1) % slides.length);
  };

  // Move to the previous active slide
  const prevSlide = () => {
    if (!slides.length) return;
    setCurrentSlide((prev) => (prev - 1 + slides.length) % slides.length);
  };

  const slide = slides.length > 0 ? slides[currentSlide] : null;

  return (
    <>
      <Head title="Loizou Prints - Bookstore & Design Store" />

      <div className="bg-white">
        {/* Hero Section */}
        <div className="relative overflow-hidden bg-linear-to-br from-purple-700 via-purple-600 to-orange-500 py-20 text-white lg:py-22">
          {/* Background Pattern */}
          <div className="absolute inset-0 opacity-10">
            <div className="absolute right-0 top-0 h-96 w-96 translate-x-1/2 -translate-y-1/2 transform rounded-full bg-white blur-3xl"></div>
            <div className="absolute bottom-0 left-0 h-96 w-96 -translate-x-1/2 translate-y-1/2 transform rounded-full bg-orange-300 blur-3xl"></div>
          </div>

          <div className="relative mx-auto max-w-7xl px-6 sm:px-8 lg:px-10">
            <div className="grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-12">
              <div className="z-10 -translate-y-6 text-center lg:text-left">
                <div className="mb-5 inline-block rounded-full bg-white/20 px-4 py-2 text-sm font-semibold backdrop-blur-sm">
                  ✨ Over 40 Years of Excellence
                </div>

                <h1 className="mb-5 text-4xl font-black leading-tight md:text-5xl lg:text-6xl">
                  <span className="block">Design & Printing</span>
                    <span className="mt-1 block -translate-x-7 bg-linear-to-r from-orange-300 to-yellow-200 bg-clip-text text-center text-transparent">
                      Services
                    </span>
                </h1>

                <p className="mb-7 max-w-2xl text-lg leading-relaxed text-purple-100 md:text-xl">
                  With over 40 years of experience in the sector and equipped with the latest technology,
                  we offer a variety of services and products that exceed in quality.
                </p>

                <div className="flex flex-col justify-center gap-4 sm:flex-row lg:justify-start">
                  <Link
                    href="/catalog"
                    className="group flex items-center justify-center rounded-xl bg-white px-8 py-4 text-lg font-bold text-purple-700 shadow-2xl transition-all hover:scale-105 hover:bg-orange-50"
                  >
                    Browse Products
                    <span className="ml-2 transition-transform group-hover:translate-x-1">→</span>
                  </Link>

                  <Link
                    href="/services"
                    className="flex items-center justify-center rounded-xl border-2 border-white/30 bg-purple-800/50 px-8 py-4 text-lg font-bold text-white shadow-xl transition-all hover:bg-purple-900/50"
                  >
                    Our Services
                  </Link>
                </div>
              </div>

              <div className="z-10 flex -translate-y-8 justify-center lg:justify-end">
                <div className="relative">
                  <div className="absolute inset-0 rounded-3xl bg-linear-to-br from-orange-400 to-pink-400 opacity-50 blur-2xl"></div>
                  <img
                    src={logoImage}
                    alt="Loizou Prints"
                    fetchPriority="high"
                    className="relative w-full max-w-sm transform drop-shadow-2xl transition-transform duration-300 hover:scale-105 md:max-w-md"
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Decorative wave */}
          <div className="absolute bottom-0 left-0 right-0 h-30 leading-none">
            <svg
              viewBox="0 0 1440 120"
              preserveAspectRatio="none"
              className="block h-30 w-full"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
                fill="white"
              />
            </svg>
          </div>
        </div>

        {/* Everything below hero gets equal left/right padding */}
        <div className="px-4 sm:px-12 lg:px-14">
          {/* Services Grid */}
          <section className="bg-gray-50 py-12 md:py-14">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
              <div className="mx-auto mb-10 max-w-3xl text-center">
                <h2 className="text-2xl font-bold tracking-tight text-gray-900 md:text-[1.7rem]">
                  More than just Design & Printing Services
                </h2>
                <p className="mt-3 text-sm leading-7 text-gray-600 md:text-[15px]">
                  A complete range of creative and printing solutions designed to support businesses,
                  students, professionals, and personalized projects.
                </p>
              </div>

              <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                {SERVICES.map((service, index) => (
                  <div
                    key={index}
                    className="rounded-2xl border border-gray-100 bg-white px-4 py-5 text-center shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
                  >
                    <div className="mb-3 text-3xl">{service.icon}</div>
                    <h3 className="text-[13px] font-semibold leading-5 text-gray-800 md:text-sm">
                      {service.name}
                    </h3>
                  </div>
                ))}
              </div>
            </div>
          </section>

          {/* Carousel */}
          <div className="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8 py-10 md:py-16">
            <div className="relative overflow-hidden rounded-2xl border border-purple-100 bg-linear-to-br from-purple-50 to-orange-50 shadow-xl md:rounded-3xl md:shadow-2xl">
              <div className="grid grid-cols-1 lg:grid-cols-2">
                {/* Image Side */}
                <div className="relative h-56 bg-linear-to-br from-purple-100 to-orange-100 sm:h-72 lg:h-auto">
                  <div className="absolute left-4 top-4 rounded-full bg-orange-500 px-4 py-1.5 text-xs font-bold text-white shadow-lg sm:left-6 sm:top-6 sm:px-6 sm:py-2 sm:text-sm">
                    Hot Deal
                  </div>

                  <div className="flex h-full items-center justify-center p-4 sm:p-6 lg:p-8">
                    {slide?.image_url ? (
                      <img
                        src={slide.image_url}
                        alt={slide.title}
                        className="max-h-full max-w-full object-contain"
                        loading="lazy"
                      />
                    ) : (
                      <div className="text-8xl">🎨</div>
                    )}
                  </div>
                </div>

                {/* Content Side */}
                <div className="flex flex-col justify-center p-6 sm:p-8 lg:p-12">
                  <h3 className="mb-3 text-2xl font-bold leading-tight wrap-break-word text-purple-900 sm:mb-4 sm:text-3xl lg:text-4xl">
                    {slide?.title ?? 'Latest Promotions'}
                  </h3>
                  <p className="mb-6 text-base leading-7 text-gray-700 sm:mb-8 sm:text-lg lg:text-xl">
                    {slide?.description ?? 'Check out our latest offers and featured products.'}
                  </p>

                  {slide?.product_id && slide?.product_type && (
                    <Link
                      href={`/product/${slide.product_type}/${slide.product_id}`}
                      className="inline-flex w-fit items-center rounded-xl bg-purple-600 px-6 py-3 text-base font-semibold text-white shadow-lg transition-all hover:bg-purple-700 hover:scale-105 sm:px-8 sm:py-4 sm:text-lg"
                    >
                      Shop Now →
                    </Link>
                  )}

                  {/* Dots Navigation */}
                  {slides.length > 0 && (
                    <div className="mt-6 flex space-x-3 sm:mt-8">
                      {slides.map((_, index) => (
                        <button
                          key={index}
                          onClick={() => setCurrentSlide(index)}
                          className={`h-2 rounded-full transition-all ${
                            index === currentSlide ? 'bg-purple-600 w-8' : 'bg-gray-300 w-2'
                          }`}
                          aria-label={`Go to slide ${index + 1}`}
                        />
                      ))}
                    </div>
                  )}
                </div>
              </div>

              {/* Navigation Arrows */}
              {slides.length > 1 && (
                <>
                  <button
                    onClick={prevSlide}
                    className="absolute left-3 top-44 z-10 cursor-pointer rounded-full bg-white p-2.5 shadow-lg transition-all hover:scale-110 hover:bg-gray-100 sm:left-4 sm:top-1/2 sm:-translate-y-1/2 sm:p-3 sm:shadow-xl"
                    aria-label="Previous slide"
                  >
                    <ChevronLeft className="h-5 w-5 text-purple-600 sm:h-6 sm:w-6" />
                  </button>
                  <button
                    onClick={nextSlide}
                    className="absolute right-3 top-44 z-10 cursor-pointer rounded-full bg-white p-2.5 shadow-lg transition-all hover:scale-110 hover:bg-gray-100 sm:right-4 sm:top-1/2 sm:-translate-y-1/2 sm:p-3 sm:shadow-xl"
                    aria-label="Next slide"
                  >
                    <ChevronRight className="h-5 w-5 text-purple-600 sm:h-6 sm:w-6" />
                  </button>
                </>
              )}
            </div>
          </div>

          {/* Why Choose Us */}
          <section className="bg-white py-12 md:py-14">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
              <div className="mx-auto mb-10 max-w-3xl text-center">
                <h2 className="mb-4 text-2xl font-bold tracking-tight text-purple-900 md:text-[1.7rem]">
                  We Print Cool Things & Create Design
                </h2>
                <p className="mx-auto max-w-3xl text-[15px] leading-7 text-gray-600 md:text-base">
                  At our creative design company, we specialize in delivering exceptional and innovative
                  design solutions that capture the essence of your brand and drive business success.
                </p>
              </div>

              <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-2xl px-3 text-center">
                  <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-purple-100">
                    <Check className="h-7 w-7 text-purple-600" />
                  </div>
                  <h3 className="mb-2 text-base font-bold text-gray-900">
                    Large Paper & Stock Selection
                  </h3>
                  <p className="text-sm leading-6 text-gray-600">
                    Wide variety of premium materials for unique prints.
                  </p>
                </div>

                <div className="rounded-2xl px-3 text-center">
                  <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-orange-100">
                    <Check className="h-7 w-7 text-orange-600" />
                  </div>
                  <h3 className="mb-2 text-base font-bold text-gray-900">Tailored Programs</h3>
                  <p className="text-sm leading-6 text-gray-600">
                    Printing programs customized to your company needs.
                  </p>
                </div>

                <div className="rounded-2xl px-3 text-center">
                  <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-purple-100">
                    <Check className="h-7 w-7 text-purple-600" />
                  </div>
                  <h3 className="mb-2 text-base font-bold text-gray-900">Premium Equipment</h3>
                  <p className="text-sm leading-6 text-gray-600">
                    High-quality printing equipment for best results.
                  </p>
                </div>

                <div className="rounded-2xl px-3 text-center">
                  <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-orange-100">
                    <Check className="h-7 w-7 text-orange-600" />
                  </div>
                  <h3 className="mb-2 text-base font-bold text-gray-900">40+ Years Experience</h3>
                  <p className="text-sm leading-6 text-gray-600">
                    Trusted expertise in the printing industry.
                  </p>
                </div>
              </div>
            </div>
          </section>
        </div>

        {/* CTA */}
        <section className="bg-linear-to-r from-purple-600 to-orange-500 py-12 text-white">
          <div className="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
            <h2 className="mb-3 text-2xl font-bold md:text-3xl">Ready to Get Started?</h2>
            <p className="mx-auto mb-7 max-w-2xl text-base leading-7 text-purple-50 md:text-lg">
              Explore our design tool and bring your ideas to life.
            </p>
            <div className="flex flex-col justify-center gap-4 sm:flex-row">
              <Link
                href="/catalog?product_type=customizable"
                className="inline-block rounded-xl bg-white px-8 py-3.5 text-base font-semibold text-purple-600 shadow-lg transition-colors hover:bg-gray-100"
              >
                Start Designing Now
              </Link>
            </div>
          </div>
        </section>
      </div>
    </>
  );
}