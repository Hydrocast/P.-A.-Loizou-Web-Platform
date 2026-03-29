import { Head, Link } from '@inertiajs/react';

export default function NotFound() {
  return (
    <>
      <Head title="Page Not Found" />

      <div className="mx-auto max-w-7xl px-4 py-12 text-center sm:px-6 sm:py-14 lg:px-8 lg:py-16">
        <h1 className="mb-4 text-5xl font-bold text-gray-900 sm:text-6xl">404</h1>
        <h2 className="mb-4 text-xl font-semibold text-gray-700 sm:text-2xl">Page Not Found</h2>
        <p className="text-gray-600 mb-8">
          The page you're looking for doesn't exist or has been moved.
        </p>
        <Link
          href="/"
          className="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 inline-block"
        >
          Go Home
        </Link>
      </div>
    </>
  );
}