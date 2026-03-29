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

  const handlePhoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setData('phone_number', e.target.value.replace(/\D/g, '').slice(0, 8));
  };

  return (
    <div className="rounded-lg bg-white p-4 shadow-sm sm:p-5 md:p-6">
      <h2 className="mb-5 text-xl font-semibold sm:text-2xl md:mb-6">Profile Information</h2>

      {visibleSuccess && (
        <div className="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-800 sm:text-base">
          {visibleSuccess}
        </div>
      )}

      {visibleError && (
        <div className="mb-4 rounded-md bg-red-100 px-4 py-3 text-sm text-red-800 sm:text-base">
          {visibleError}
        </div>
      )}

      <form onSubmit={handleSubmit} className="max-w-md space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Email Address
          </label>
          <input
            type="email"
            value={customer.email}
            disabled
            className="w-full rounded-md border border-gray-300 bg-gray-50 px-4 py-2 text-gray-500"
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
            className={`w-full rounded-md border px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 ${
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
            <div className="inline-flex shrink-0 items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-600">
              +357
            </div>

            <input
              type="tel"
              value={data.phone_number}
              onChange={handlePhoneChange}
              className={`w-full rounded-r-md border px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500 ${
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
          className="w-full cursor-pointer rounded-md bg-blue-600 px-6 py-2 font-medium text-white hover:bg-blue-700 disabled:opacity-60 sm:w-auto"
        >
          {processing ? 'Saving...' : 'Save Changes'}
        </button>
      </form>
    </div>
  );
}