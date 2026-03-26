import { useForm, usePage } from '@inertiajs/react';
import { useTimedFlash } from '@/hooks/useTimedFlash';

type Customer = {
  customer_id: number;
  full_name: string;
  email: string;
  phone_number?: string | null;
};

type PageProps = {
  customer: Customer;
  flash?: {
    success?: string;
    error?: string;
  };
};

export default function CustomerProfile() {
  const { customer, flash } = usePage<PageProps>().props;
  const { visibleSuccess, visibleError } = useTimedFlash({
    success: flash?.success,
    error: flash?.error,
  });

  const { data, setData, put, processing, errors } = useForm({
    full_name: customer.full_name ?? '',
    phone_number: customer.phone_number ?? '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put('/account/profile');
  };

  return (
    <div className="bg-white rounded-lg shadow-sm p-6">
      <h2 className="text-2xl font-semibold mb-6">Profile Information</h2>

      {visibleSuccess && (
        <div className="mb-4 p-3 bg-green-100 text-green-800 rounded-md">
          {visibleSuccess}
        </div>
      )}

      {visibleError && (
        <div className="mb-4 p-3 bg-red-100 text-red-800 rounded-md">
          {visibleError}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-4 max-w-md">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Email Address
          </label>
          <input
            type="email"
            value={customer.email}
            disabled
            className="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
          />
          <p className="mt-1 text-xs text-gray-500">Email cannot be changed</p>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Full Name *
          </label>
          <input
            type="text"
            value={data.full_name}
            onChange={(e) => setData('full_name', e.target.value)}
            className={`w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
              errors.full_name ? 'border-red-500' : 'border-gray-300'
            }`}
            maxLength={50}
          />
          {errors.full_name && (
            <p className="mt-1 text-sm text-red-600">{errors.full_name}</p>
          )}
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Phone Number
          </label>

          <div className="flex rounded-md shadow-sm">
            <div className="inline-flex items-center px-4 py-2 border border-r-0 border-gray-300 rounded-l-md bg-gray-50 text-gray-600 text-sm font-medium">
              +357
            </div>

            <input
              type="tel"
              value={data.phone_number}
              onChange={(e) => setData('phone_number', e.target.value.replace(/\D/g, '').slice(0, 8))}
              className={`w-full px-4 py-2 border rounded-r-md focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                errors.phone_number ? 'border-red-500' : 'border-gray-300'
              }`}
              placeholder="12345678"
              inputMode="numeric"
              maxLength={8}
            />
          </div>
            
          {errors.phone_number && (
            <p className="mt-1 text-sm text-red-600">{errors.phone_number}</p>
          )}

          <p className="mt-1 text-xs text-gray-500">Cyprus number, 8 digits</p>
        </div>

        <button
          type="submit"
          disabled={processing}
          className="cursor-pointer bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 font-medium disabled:opacity-60"
        >
          {processing ? 'Saving...' : 'Save Changes'}
        </button>
      </form>
    </div>
  );
}