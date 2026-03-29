import { Head, Link, useForm, usePage } from '@inertiajs/react'
import { Eye, EyeOff } from 'lucide-react'
import { useState } from 'react'
import type { FormEventHandler } from 'react'

type PageProps = {
  flash?: {
    status?: string
    success?: string
    error?: string
  }
  redirect?: string | null
}

export default function CustomerLogin() {
  const { flash, redirect } = usePage<PageProps>().props
  const [showPassword, setShowPassword] = useState(false)

  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    redirect: redirect ?? '',
  })

  const submit: FormEventHandler = (e) => {
    e.preventDefault()
    post('/login')
  }

  return (
    <>
      <Head title="Customer Login" />

      <div className="mx-auto max-w-md px-4 py-10 sm:px-6 sm:py-14 lg:px-8 lg:py-16">
        <div className="rounded-lg bg-white p-5 shadow-md sm:p-6 md:p-8">
          <h1 className="mb-5 text-center text-xl font-bold sm:text-2xl md:mb-6">Customer Login</h1>

          {flash?.status && (
            <div className="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-800">
              {flash.status}
            </div>
          )}

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
                Email Address
              </label>
              <input
                type="email"
                value={data.email}
                onChange={(e) => setData('email', e.target.value)}
                className="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                placeholder="your@email.com"
                maxLength={100}
              />
              {errors.email && (
                <p className="mt-1 text-sm text-red-600">{errors.email}</p>
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

            <button
              type="submit"
              disabled={processing}
              className="cursor-pointer w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 font-medium disabled:opacity-60"
            >
              {processing ? 'Signing in...' : 'Login'}
            </button>
          </form>

          <div className="mt-6 space-y-2 text-center">
            <p className="text-sm text-gray-600">
              Don&apos;t have an account?{' '}
              <Link href="/register" className="text-blue-600 hover:underline">
                Register here
              </Link>
            </p>

            <p className="text-sm text-gray-600">
              <Link href="/forgot-password" className="text-blue-600 hover:underline">
                Forgot your password?
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