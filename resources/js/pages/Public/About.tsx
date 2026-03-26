import { Head } from '@inertiajs/react';
import { Award, Users, Target, TrendingUp, ArrowUp } from 'lucide-react';
import { useState, useEffect } from 'react';

export default function About() {
  const [showBackToTop, setShowBackToTop] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setShowBackToTop(window.scrollY > 400);
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <>
      <Head title="About Us" />

      <div className="relative">
        {/* Hero Section */}
        <div className="relative overflow-hidden bg-gradient-to-br from-purple-700 via-purple-600 to-orange-500 py-24 text-white">
          {/* Background Pattern */}
          <div className="absolute inset-0 opacity-10">
            <div className="absolute top-0 left-0 h-96 w-96 rounded-full bg-white blur-3xl"></div>
            <div className="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-orange-300 blur-3xl"></div>
          </div>

          <div className="relative z-10 mx-auto max-w-7xl -translate-y-11 px-4 text-center sm:px-6 lg:px-8">
            <div className="mb-6 inline-block rounded-full bg-white/20 px-4 py-2 text-sm font-semibold backdrop-blur-sm">
              🏆 Our Story
            </div>
            <h1 className="mb-6 text-5xl font-black leading-tight md:text-6xl">
              About{' '}
              <span className="bg-gradient-to-r from-orange-300 to-yellow-200 bg-clip-text text-transparent">
                Loizou Prints
              </span>
            </h1>
            <p className="mx-auto max-w-3xl text-2xl font-semibold text-purple-100 md:text-3xl">
              We Provide Anything You Dream Of
            </p>
          </div>

          {/* Decorative wave */}
          <div className="absolute bottom-0 left-0 right-0 leading-none">
            <svg
              viewBox="0 0 1440 120"
              preserveAspectRatio="none"
              className="block h-[120px] w-full"
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
        <div className="px-12 sm:px-14 lg:px-16">
          {/* Who We Are */}
          <section className="bg-white py-10 md:py-12">
            <div className="mx-auto max-w-5xl">
              <div className="grid grid-cols-1 items-center gap-6 lg:grid-cols-2 lg:gap-8">
                <div>
                  <h2 className="mb-3 text-2xl font-bold text-purple-900 md:text-3xl">
                    Who We Are
                  </h2>

                  <div className="space-y-3 text-sm leading-6 text-gray-700 md:text-[15px]">
                    <p>
                      Welcome to Loizou Prints, your premier destination for
                      printing and digital design excellence in Paralimni.
                      Established with a passion for transforming ideas into
                      captivating visual experiences, Loizou Prints is more than a
                      printing office; it&apos;s a hub where creativity meets
                      precision.
                    </p>
                    <p>
                      Our team, comprised of skilled professionals, is dedicated
                      to delivering top-notch printing services and innovative
                      digital designs tailored to your unique needs. With a
                      commitment to quality, efficiency, and customer
                      satisfaction, we take pride in our state-of-the-art
                      facilities equipped with cutting-edge technology.
                    </p>
                    <p>
                      At Loizou Prints, we don&apos;t just print; we bring visions
                      to life. Explore our story, meet the talented individuals
                      behind the scenes, and discover why we stand at the
                      forefront of printing and design in Paralimni.
                    </p>
                  </div>
                </div>

                <div className="flex h-48 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-100 to-orange-100 shadow-lg md:h-56">
                  <p className="text-4xl text-purple-600 md:text-5xl">📸</p>
                </div>
              </div>
            </div>
          </section>

          {/* Our Skills */}
          <section className="rounded-2xl bg-gray-50 py-8 md:py-10">
            <div className="mx-auto max-w-5xl">
              <div className="grid grid-cols-1 items-center gap-6 lg:grid-cols-2 lg:gap-8">
                <div className="flex h-48 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-100 to-purple-100 shadow-lg md:h-56">
                  <p className="text-4xl text-orange-600 md:text-5xl">🏆</p>
                </div>

                <div>
                  <h2 className="mb-3 text-2xl font-bold text-purple-900 md:text-3xl">
                    Our Skills in Printing
                  </h2>

                  <p className="mb-5 text-sm leading-6 text-gray-700 md:text-[15px]">
                    We are experts in the field and probably the most accomplished
                    agency in the Famagusta area. With over 10,000 satisfied
                    customers, our expertise speaks for itself.
                  </p>

                  <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div className="rounded-xl border border-purple-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                      <div className="flex items-start gap-3">
                        <Award className="h-5 w-5 flex-shrink-0 text-purple-600" />
                        <div>
                          <h3 className="mb-1 text-sm font-bold text-purple-900">
                            40+ Years
                          </h3>
                          <p className="text-sm leading-6 text-gray-600">
                            Industry Experience
                          </p>
                        </div>
                      </div>
                    </div>

                    <div className="rounded-xl border border-orange-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                      <div className="flex items-start gap-3">
                        <Users className="h-5 w-5 flex-shrink-0 text-orange-600" />
                        <div>
                          <h3 className="mb-1 text-sm font-bold text-purple-900">
                            10,000+
                          </h3>
                          <p className="text-sm leading-6 text-gray-600">
                            Satisfied Customers
                          </p>
                        </div>
                      </div>
                    </div>

                    <div className="rounded-xl border border-purple-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                      <div className="flex items-start gap-3">
                        <Target className="h-5 w-5 flex-shrink-0 text-purple-600" />
                        <div>
                          <h3 className="mb-1 text-sm font-bold text-purple-900">
                            Premium Quality
                          </h3>
                          <p className="text-sm leading-6 text-gray-600">
                            Every Project
                          </p>
                        </div>
                      </div>
                    </div>

                    <div className="rounded-xl border border-orange-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                      <div className="flex items-start gap-3">
                        <TrendingUp className="h-5 w-5 flex-shrink-0 text-orange-600" />
                        <div>
                          <h3 className="mb-1 text-sm font-bold text-purple-900">
                            Latest Tech
                          </h3>
                          <p className="text-sm leading-6 text-gray-600">
                            Cutting-Edge Equipment
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          {/* Graphic Design */}
          <section className="mx-auto max-w-5xl py-10 md:py-12">
            <div className="grid grid-cols-1 items-center gap-6 lg:grid-cols-2 lg:gap-8">
              <div>
                <h2 className="mb-3 text-2xl font-bold text-purple-900 md:text-3xl">
                  Graphic Design
                </h2>

                <div className="space-y-3 text-sm leading-6 text-gray-700 md:text-[15px]">
                  <p>
                    Our graphic design team brings your ideas to life with
                    creative, eye-catching designs that leave a lasting
                    impression. From logos and branding to marketing materials
                    and website design, we work closely with you to understand
                    your vision and bring it to fruition.
                  </p>
                  <p>
                    Our team uses the latest design software and techniques to
                    create custom designs that are tailored to your specific
                    needs. We deliver high-quality designs that elevate your
                    brand and help you stand out in a crowded market. Trust us
                    to bring your ideas to life with stunning graphic design
                    services.
                  </p>
                </div>
              </div>

              <div className="flex h-48 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-100 to-orange-100 shadow-lg md:h-56">
                <p className="text-4xl text-purple-600 md:text-5xl">🎨</p>
              </div>
            </div>
          </section>

          {/* Parker Pens */}
          <section className="rounded-2xl bg-gray-50 py-8 md:py-10">
            <div className="mx-auto max-w-5xl">
              <div className="grid grid-cols-1 items-center gap-6 lg:grid-cols-2 lg:gap-8">
                <div className="flex h-48 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-100 to-purple-100 shadow-lg md:h-56">
                  <div className="text-center">
                    <p className="mb-2 text-4xl text-orange-600 md:text-5xl">🖊️</p>
                    <p className="text-sm font-semibold text-gray-600">
                      Parker® Pens
                    </p>
                  </div>
                </div>

                <div>
                  <h2 className="mb-3 text-2xl font-bold text-purple-900 md:text-3xl">
                    Official Parker® Distributor
                  </h2>

                  <div className="space-y-3 text-sm leading-6 text-gray-700 md:text-[15px]">
                    <p>
                      Parker pens represent timeless craftsmanship, elegance, and
                      writing comfort—making them the perfect choice for
                      professionals, students, and premium gifting.
                    </p>
                    <p>
                      At Loizou Prints Cyprus, we offer a selection of authentic
                      Parker pens known for their smooth ink flow, durable build,
                      and sophisticated designs.
                    </p>
                    <p>
                      Whether you&apos;re looking for a reliable everyday pen or
                      a personalized engraved Parker gift for a special occasion,
                      we provide high-quality options that combine practicality
                      with luxury. Choose Parker for a writing experience that
                      feels refined, precise, and unmistakably iconic.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </section>

          {/* POLO Bags */}
          <section className="mx-auto max-w-5xl py-10 md:py-12">
            <div className="grid grid-cols-1 items-center gap-6 lg:grid-cols-2 lg:gap-8">
              <div>
                <h2 className="mb-3 text-2xl font-bold text-purple-900 md:text-3xl">
                  POLO® Bags District Distributor
                </h2>

                <div className="space-y-3 text-sm leading-6 text-gray-700 md:text-[15px]">
                  <p>
                    POLO Bags is a well-established high-quality brand
                    specializing in backpacks, school bags, travel bags, laptop
                    bags, and everyday carry solutions designed for comfort,
                    durability, and modern lifestyles.
                  </p>
                  <p>
                    Known for practical layouts, reinforced materials, and
                    ergonomic design, POLO bags are ideal for daily use, work,
                    school, and travel.
                  </p>
                  <p>
                    At Loizou Prints in Cyprus, we are the official District
                    Distributors of the original POLO bags suitable for
                    corporate use, school, promotional campaigns, staff
                    equipment, and personalized gifting. Their clean design and
                    reliable construction make them an excellent choice for
                    branded applications.
                  </p>
                </div>
              </div>

              <div className="flex h-48 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-100 to-orange-100 shadow-lg md:h-56">
                <div className="text-center">
                  <p className="mb-2 text-4xl text-purple-600 md:text-5xl">🎒</p>
                  <p className="text-sm font-semibold text-gray-600">
                    POLO® Bags
                  </p>
                </div>
              </div>
            </div>
          </section>
        </div>

        {/* Back to Top Button */}
        {showBackToTop && (
          <button
            onClick={scrollToTop}
            className="fixed bottom-6 right-6 z-50 cursor-pointer rounded-full bg-purple-600 p-3 text-white shadow-lg transition-all duration-300 hover:bg-purple-700"
            aria-label="Back to top"
          >
            <ArrowUp className="h-5 w-5" />
          </button>
        )}
      </div>
    </>
  );
}