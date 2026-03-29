import { Head, Link, useForm, usePage } from '@inertiajs/react'
import { Eye, EyeOff } from 'lucide-react'
import { useState } from 'react'
import type { FormEventHandler } from 'react'

type Props = {
  token: string
  email: string
  flash?: {
    status?: string
    error?: string
  }
}

export default function ResetPassword() {
  const { token, email, flash } = usePage<Props>().props
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = useState(false)

  const { data, setData, post, processing, errors } = useForm({
    token: token ?? '',
    email: email ?? '',
    password: '',
    password_confirmation: '',
  })

  const submit: FormEventHandler = (e) => {
    e.preventDefault()
    post(`/reset-password/${token}`)
  }

  return (
    <>
      <Head title="Reset Password" />

      <div className="mx-auto max-w-md px-4 py-10 sm:px-6 sm:py-14 lg:px-8 lg:py-16">
        <div className="rounded-lg bg-white p-5 shadow-md sm:p-6 md:p-8">
          <h1 className="mb-5 text-center text-xl font-bold sm:text-2xl md:mb-6">Reset Password</h1>

          {flash?.status && (
            <div className="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-800">
              {flash.status}
            </div>
          )}

          {flash?.error && (
            <div className="mb-4 rounded-md bg-red-100 px-4 py-3 text-sm text-red-800">
              {flash.error}
            </div>
          )}

          <form onSubmit={submit} className="space-y-4">
            <input type="hidden" value={data.token} readOnly />
            <input type="hidden" value={data.email} readOnly />

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Email Address
              </label>
              <input
                type="email"
                value={data.email}
                onChange={(e) => setData('email', e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50"
                maxLength={100}
              />
              {errors.email && (
                <p className="mt-1 text-sm text-red-600">{errors.email}</p>
              )}
              {errors.token && (
                <p className="mt-1 text-sm text-red-600">{errors.token}</p>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                New Password
              </label>

              <div className="relative">
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  className="w-full rounded-md border border-gray-300 px-4 py-2 pr-12 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                  placeholder="••••••••"
                  maxLength={64}
                />

                <button
                  type="button"
                  onClick={() => setShowPassword((prev) => !prev)}
                  className="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 cursor-pointer hover:text-gray-700"
                  aria-label={showPassword ? 'Hide new password' : 'Show new password'}
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
                Confirm New Password
              </label>

              <div className="relative">
                <input
                  type={showConfirmPassword ? 'text' : 'password'}
                  value={data.password_confirmation}
                  onChange={(e) => setData('password_confirmation', e.target.value)}
                  className="w-full rounded-md border border-gray-300 px-4 py-2 pr-12 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                  placeholder="••••••••"
                  maxLength={64}
                />

                <button
                  type="button"
                  onClick={() => setShowConfirmPassword((prev) => !prev)}
                  className="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 cursor-pointer hover:text-gray-700"
                  aria-label={showConfirmPassword ? 'Hide new password confirmation' : 'Show new password confirmation'}
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
              className="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 font-medium disabled:opacity-60"
            >
              {processing ? 'Updating...' : 'Set New Password'}
            </button>
          </form>

          <div className="mt-6 space-y-2 text-center">
            <p className="text-sm text-gray-600">
              Back to{' '}
              <Link href="/login" className="text-blue-600 hover:underline">
                Login
              </Link>
            </p>
          </div>

          <div className="mt-6 border-t border-gray-200 pt-6">
            <p className="text-sm text-gray-600 text-center">
              Staff member?{' '}
              <Link href="/staff/login" className="text-blue-600 hover:underline">
                Staff login here
              </Link>
            </p>
          </div>
        </div>
      </div>
    </>
  )
}