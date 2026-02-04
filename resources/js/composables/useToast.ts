
import { toast } from 'sonner'

export const useToast = () => {
    const success = (message: string, description?: string) => {
        toast.success(message, {
            description,
        })
    }

    const error = (message: string, description?: string) => {
        toast.error(message, {
            description,
        })
    }

    const info = (message: string, description?: string) => {
        toast.info(message, {
            description,
        })
    }

    const loading = (message: string) => {
        toast.loading(message)
    }

    return {
        success,
        error,
        info,
        loading,
    }
}
