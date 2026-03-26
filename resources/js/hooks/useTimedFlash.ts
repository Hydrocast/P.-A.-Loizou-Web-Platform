import { useEffect, useState } from 'react';

interface UseTimedFlashProps {
  success?: string;
  error?: string;
}

/**
 * Hook for managing temporary flash messages with auto-dismiss behavior.
 * 
 * Syncs visible state when incoming flash values change and automatically
 * clears them after 4000ms. Timers are cleaned up properly on unmount.
 * 
 * @param success - Success message from server/props
 * @param error - Error message from server/props
 * @returns Object with visible success/error values and optional setters
 */
export function useTimedFlash({ success, error }: UseTimedFlashProps) {
  const [visibleSuccess, setVisibleSuccess] = useState<string | undefined>(undefined);
  const [visibleError, setVisibleError] = useState<string | undefined>(undefined);

  // Handle success message: sync when prop changes, then clear after 4000ms
  useEffect(() => {
    if (success) {
      setVisibleSuccess(success);

      const timer = setTimeout(() => {
        setVisibleSuccess(undefined);
      }, 4000);

      return () => clearTimeout(timer);
    }
  }, [success]);

  // Handle error message: sync when prop changes, then clear after 4000ms
  useEffect(() => {
    if (error) {
      setVisibleError(error);

      const timer = setTimeout(() => {
        setVisibleError(undefined);
      }, 4000);

      return () => clearTimeout(timer);
    }
  }, [error]);

  return {
    visibleSuccess,
    visibleError,
    // Optional setters for manual control if needed
    setVisibleSuccess,
    setVisibleError,
  };
}
