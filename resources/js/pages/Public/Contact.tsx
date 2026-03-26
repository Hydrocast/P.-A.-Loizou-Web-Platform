import { Head, useForm, usePage } from '@inertiajs/react';
import { Mail, Phone, MapPin, Facebook, Instagram } from 'lucide-react';
import { useEffect } from 'react';

type PageProps = {
  auth: {
    customer: null | {
      customer_id: number;
      full_name: string;
      email: string;
    };
    staff: null | {
      staff_id: number;
      username: string;
      full_name: string;
      role: string;
    };
  };
  flash?: {
    success?: string;
    error?: string;
    status?: string;
  };
};

export default function Contact() {
  const { auth, flash } = usePage<PageProps>().props;
  const customer = auth?.customer;

  const { data, setData, post, processing, errors, reset } = useForm({
    fullName: customer?.full_name ?? '',
    email: customer?.email ?? '',
    subject: '',
    message: '',
  });

  useEffect(() => {
    setData((currentData) => ({
      ...currentData,
      fullName: currentData.fullName || customer?.full_name || '',
      email: currentData.email || customer?.email || '',
    }));
  }, [customer?.full_name, customer?.email, setData]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    post('/contact', {
      preserveScroll: true,
      onSuccess: () => {
        reset('subject', 'message');

        setData((currentData) => ({
          ...currentData,
          fullName: customer?.full_name ?? currentData.fullName,
          email: customer?.email ?? currentData.email,
        }));
      },
    });
  };

  return (
    <>
      <Head title="Contact Us" />

      <div>
        {/* Hero Section */}
        <div className="bg-gradient-to-r from-purple-600 to-orange-500 text-white py-20">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 className="text-5xl font-bold mb-6">Contact Us</h1>
            <p className="text-2xl text-purple-100 max-w-3xl mx-auto">
              Get in touch with our team for any inquiries
            </p>
          </div>
        </div>

        {/* Everything below hero gets equal left/right padding */}
        <div className="px-10 sm:px-12 lg:px-14">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-7">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {/* Contact Information */}
              <div className="pb-1">
                <h2 className="text-2xl font-semibold mb-3 text-purple-900">Get in Touch</h2>
                <p className="text-gray-600 mb-3 leading-relaxed">
                  Have a question about our products or services? We&apos;re here to help!
                  Fill out the form and we&apos;ll get back to you as soon as possible.
                </p>

                <div className="space-y-3">
                  <div className="flex items-start">
                    <MapPin className="w-5 h-5 text-purple-600 mr-3 mt-1 flex-shrink-0" />
                    <div>
                      <h3 className="font-semibold text-purple-900 mb-1">Main Shop Location</h3>
                      <a
                        href="https://www.google.com/maps/place/Loizou+Prints/@35.0375461,33.9753359,17z/data=!3m1!4b1!4m6!3m5!1s0x14dfc5f70b7e71f9:0x20541734bac708a2!8m2!3d35.0375461!4d33.9779108!16s%2Fg%2F11c2dxdjtb?entry=ttu&g_ep=EgoyMDI2MDMxMC4wIKXMDSoASAFQAw%3D%3D"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-gray-600 hover:text-purple-600 transition-colors"
                      >
                        1st April 120, Paralimni
                      </a>
                    </div>
                  </div>

                  <div className="flex items-start">
                    <MapPin className="w-5 h-5 text-orange-600 mr-3 mt-1 flex-shrink-0" />
                    <div>
                      <h3 className="font-semibold text-purple-900 mb-1">2nd Shop Location</h3>
                      <a
                        href="https://www.google.com/maps/place/P.+%26+A.+Loizou/@34.9776928,33.8531778,14z/data=!4m15!1m8!3m7!1s0x14dfd24fd3daa23b:0x7994552ce3c01572!2zzqbPgc6vzr7Ov8-FIM6gzrHOvc6xzrPOuc-Oz4TOv8-FIDI4LCDOns-FzrvOv8-GzqzOs86_z4UgNzUyMCwgzprPjc-Az4HOv8-C!3b1!8m2!3d34.9776155!4d33.8525068!10e5!3m5!1s0x14dfd24fce8cc21f:0xdeaf2eec86e7bbc2!8m2!3d34.9775941!4d33.8531956!16s%2Fg%2F11g6mqsn4t?entry=ttu&g_ep=EgoyMDI2MDMxMC4wIKXMDSoASAFQAw%3D%3D"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-gray-600 hover:text-orange-600 transition-colors"
                      >
                        Frixou Panayiotou 28, Xylofagou
                      </a>
                    </div>
                  </div>

                  <div className="flex items-start">
                    <Mail className="w-5 h-5 text-purple-600 mr-3 mt-1" />
                    <div>
                      <h3 className="font-semibold text-purple-900">Email</h3>
                      <a
                        href="https://mail.google.com/mail/?view=cm&fs=1&to=info@loizouprints.com&su=Inquiry%20from%20Loizou%20Prints%20Website&body=Hello%20Loizou%20Prints%2C%0A%0AI%20would%20like%20to%20ask%20about%20your%20products%20or%20services.%0A%0AKind%20regards%2C"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-gray-600 hover:text-purple-600 transition-colors"
                      >
                        info@loizouprints.com
                      </a>
                    </div>
                  </div>

                  <div className="flex items-start">
                    <Phone className="w-5 h-5 text-purple-600 mr-3 mt-1" />
                    <div>
                      <h3 className="font-semibold text-purple-900">Phone</h3>
                      <span className="text-gray-600">+357 23 730760</span>
                    </div>
                  </div>
                </div>

                <div className="mt-3 bg-purple-50 p-4 rounded-lg border border-purple-100">
                  <h3 className="font-semibold mb-2 text-purple-900">Business Hours</h3>
                  <p className="text-gray-700 text-sm">Monday - Friday: 08:30 - 18:30</p>
                  <p className="text-gray-700 text-sm">Saturday: 09:00 - 13:30</p>
                  <p className="text-gray-700 text-sm">Sunday: Closed</p>
                </div>

                <div className="mt-3">
                  <h3 className="font-semibold mb-2 text-purple-900">Follow Us</h3>
                  <div className="flex space-x-3">
                    <a
                      href="https://www.facebook.com/paloizou"
                      target="_blank"
                      rel="noopener noreferrer"
                      className="bg-purple-600 p-2.5 rounded-full hover:bg-purple-700 text-white transition-colors"
                      aria-label="Facebook"
                    >
                      <Facebook className="w-4 h-4" />
                    </a>
                    <a
                      href="https://www.instagram.com/loizouprints"
                      target="_blank"
                      rel="noopener noreferrer"
                      className="bg-orange-500 p-2.5 rounded-full hover:bg-orange-600 text-white transition-colors"
                      aria-label="Instagram"
                    >
                      <Instagram className="w-4 h-4" />
                    </a>
                  </div>
                </div>
              </div>

              {/* Contact Form */}
              <div className="bg-white px-6 pt-6 pb-6 rounded-lg shadow-md border border-gray-200">
                <h2 className="text-2xl font-semibold mb-3 text-purple-900">Send us a Message</h2>

                {flash?.success && (
                  <div className="mb-4 p-3 bg-green-100 text-green-800 rounded-md text-sm border border-green-200">
                    {flash.success}
                  </div>
                )}

                {flash?.error && (
                  <div className="mb-4 p-3 bg-red-100 text-red-800 rounded-md text-sm border border-red-200">
                    {flash.error}
                  </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-3">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Full Name *
                    </label>
                    <input
                      type="text"
                      value={data.fullName}
                      onChange={(e) => setData('fullName', e.target.value)}
                      className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent ${
                        errors.fullName ? 'border-red-500' : 'border-gray-300'
                      }`}
                      maxLength={50}
                    />
                    {errors.fullName && (
                      <p className="mt-1 text-sm text-red-600">{errors.fullName}</p>
                    )}
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Email Address *
                    </label>
                    <input
                      type="email"
                      value={data.email}
                      onChange={(e) => setData('email', e.target.value)}
                      className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent ${
                        errors.email ? 'border-red-500' : 'border-gray-300'
                      }`}
                      maxLength={100}
                    />
                    {errors.email && (
                      <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                    )}
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Subject *
                    </label>
                    <input
                      type="text"
                      value={data.subject}
                      onChange={(e) => setData('subject', e.target.value)}
                      className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent ${
                        errors.subject ? 'border-red-500' : 'border-gray-300'
                      }`}
                      maxLength={100}
                    />
                    {errors.subject && (
                      <p className="mt-1 text-sm text-red-600">{errors.subject}</p>
                    )}
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Message *
                    </label>
                    <textarea
                      value={data.message}
                      onChange={(e) => setData('message', e.target.value)}
                      rows={5}
                      className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent ${
                        errors.message ? 'border-red-500' : 'border-gray-300'
                      }`}
                      maxLength={2000}
                    />
                    {errors.message && (
                      <p className="mt-1 text-sm text-red-600">{errors.message}</p>
                    )}
                  </div>

                  <button
                    type="submit"
                    disabled={processing}
                    className="cursor-pointer w-full bg-purple-600 text-white py-2.5 px-4 rounded-md hover:bg-purple-700 font-medium transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                  >
                    {processing ? 'Sending...' : 'Send Message'}
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}