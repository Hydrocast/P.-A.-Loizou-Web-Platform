import { Head, Link, useForm, usePage } from '@inertiajs/react'
import { Eye, EyeOff } from 'lucide-react'
import { useState } from 'react'
import type { FormEventHandler } from 'react'

type PageProps = {
  flash?: {
    success?: string
    error?: string
  }
}

export default function CustomerRegister() {
  const { flash } = usePage<PageProps>().props
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = useState(false)

  const { data, setData, post, processing, errors } = useForm({
    email: '',
    full_name: '',
    phone_number: '',
    password: '',
    password_confirmation: '',
  })

  const submit: FormEventHandler = (e) => {
    e.preventDefault()
    post('/register')
  }

  return (
    <>
      <Head title="Create Account" />

      <div className="mx-auto max-w-md px-4 py-10 sm:px-6 sm:py-14 lg:px-8 lg:py-16">
        <div className="rounded-lg bg-white p-5 shadow-md sm:p-6 md:p-8">
          <h1 className="mb-5 text-center text-xl font-bold sm:text-2xl md:mb-6">Create Account</h1>

          {flash?.success && (
            <div className="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-800">
              {flash.success}
            </div>
          )}

          {flash?.error && (
            <div className="mb-4 rounded-md bg-red-100 px-4 py-3 text-sm text-red-800">
              {flash.error}
            </div>
          )}

          <form onSubmit={submit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Email Address *
              </label>
              <input
                type="email"
                value={data.email}
                onChange={(e) => setData('email', e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="your@email.com"
                maxLength={100}
              />
              {errors.email && (
                <p className="mt-1 text-sm text-red-600">{errors.email}</p>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Full Name *
              </label>
              <input
                type="text"
                value={data.full_name}
                onChange={(e) => setData('full_name', e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="John Doe"
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

              <p className="mt-1 text-xs text-gray-500">Optional, Cyprus number (8 digits after +357)</p>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Password *
              </label>

              <div className="relative">
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  className="w-full px-4 py-2 pr-12 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="••••••••"
                  maxLength={64}
                />

                <button
                  type="button"
                  onClick={() => setShowPassword((prev) => !prev)}
                  className="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 cursor-pointer hover:text-gray-700"
                  aria-label={showPassword ? 'Hide password' : 'Show password'}
                  aria-pressed={showPassword}
                >
                  {showPassword ? (
                    <EyeOff className="w-5 h-5" />
                  ) : (
                    <Eye className="w-5 h-5" />
                  )}
                </button>
              </div>

              {errors.password && (
                <p className="mt-1 text-sm text-red-600">{errors.password}</p>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Confirm Password *
              </label>

              <div className="relative">
                <input
                  type={showConfirmPassword ? 'text' : 'password'}
                  value={data.password_confirmation}
                  onChange={(e) => setData('password_confirmation', e.target.value)}
                  className="w-full px-4 py-2 pr-12 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="••••••••"
                  maxLength={64}
                />

                <button
                  type="button"
                  onClick={() => setShowConfirmPassword((prev) => !prev)}
                  className="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 cursor-pointer hover:text-gray-700"
                  aria-label={showConfirmPassword ? 'Hide password confirmation' : 'Show password confirmation'}
                  aria-pressed={showConfirmPassword}
                >
                  {showConfirmPassword ? (
                    <EyeOff className="w-5 h-5" />
                  ) : (
                    <Eye className="w-5 h-5" />
                  )}
                </button>
              </div>
            </div>

            <button
              type="submit"
              disabled={processing}
              className="cursor-pointer w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 font-medium disabled:opacity-60"
            >
              {processing ? 'Creating account...' : 'Create Account'}
            </button>
          </form>

          <div className="mt-6 text-center">
            <p className="text-sm text-gray-600">
              Already have an account?{' '}
              <Link href="/login" className="text-blue-600 hover:underline">
                Login here
              </Link>
            </p>
          </div>
        </div>
      </div>
    </>
  )
}
