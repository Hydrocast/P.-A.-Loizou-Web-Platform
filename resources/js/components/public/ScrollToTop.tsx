import { usePage } from '@inertiajs/react'
import { useEffect } from 'react'

export default function ScrollToTop() {
  const { url } = usePage()

  useEffect(() => {
    window.scrollTo(0, 0)
  }, [url])

  return null
}