import { Head, Link, useForm, usePage } from '@inertiajs/react'
import { Eye, EyeOff, Shield } from 'lucide-react'
import type { FormEventHandler, ReactNode } from 'react'
import { useState } from 'react'
import { useTimedFlash } from '@/hooks/useTimedFlash'

type PageProps = {
  flash?: {
    success?: string
    error?: string
  }
}

function StaffLogin() {
  const { flash } = usePage<PageProps>().props
  const [showPassword, setShowPassword] = useState(false)
  const { visibleSuccess, visibleError } = useTimedFlash({
    success: flash?.success,
    error: flash?.error,
  })
  const togglePassword = () => setShowPassword((prev) => !prev)

  const { data, setData, post, processing, errors } = useForm({
    username: '',
    password: '',
  })

  const submit: FormEventHandler = (e) => {
    e.preventDefault()
    post('/staff/login')
  }

  return (
    <>
      <Head title="Staff Login" />

      <div className="min-h-screen bg-gray-100 flex items-center justify-center px-4">
        <div className="w-full max-w-md rounded-lg bg-white p-5 shadow-md sm:p-6 md:p-8">
          <div className="mb-6 text-center sm:mb-8">
            <div className="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 sm:h-16 sm:w-16">
              <Shield className="h-7 w-7 text-blue-600 sm:h-8 sm:w-8" />
            </div>
            <h1 className="text-xl font-bold sm:text-2xl">Staff Login</h1>
            <p className="text-gray-600 mt-2">Access the management area</p>
          </div>

          {visibleSuccess && (
            <div className="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-800">
              {visibleSuccess}
            </div>
          )}

          {visibleError && (
            <div className="mb-4 rounded-md bg-red-100 px-4 py-3 text-sm text-red-800">
              {visibleError}
            </div>
          )}

          <form onSubmit={submit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Username
              </label>
              <input
                type="text"
                value={data.username}
                onChange={(e) => setData('username', e.target.value)}
                className="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                placeholder="Enter username"
              />
              {errors.username && (
                <p className="mt-1 text-sm text-red-600">{errors.username}</p>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Password
              </label>

              <div className="relative">
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  className="w-full rounded-md border border-gray-300 px-4 py-2 pr-12 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                  placeholder="••••••••"
                />

                <button
                  type="button"
                  onClick={togglePassword}
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

            <button
              type="submit"
              disabled={processing}
              className="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 font-medium disabled:opacity-60"
            >
              {processing ? 'Signing in...' : 'Sign In'}
            </button>
          </form>

          <div className="mt-6 pt-6 border-t border-gray-200 text-center">
            <Link href="/" className="text-blue-600 hover:underline text-sm">
              ← Back to main site
            </Link>
          </div>
        </div>
      </div>
    </>
  )
}

StaffLogin.layout = (page: ReactNode) => page

export default StaffLogin