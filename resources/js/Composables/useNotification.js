import { useToast } from "primevue/usetoast";
import { inject } from "vue";

export function useNotification() {

  const toast = useToast();
  /** @type {typeof import('@/config/config').config} */
  const config = inject('config');

  const showError = (errorMessage, timeLifeParam = null) => {
    const timeLife = timeLifeParam || config.toast.timeLife || 3000;

    toast.add({
      severity: 'error',
      summary: 'Ошибка',
      detail: errorMessage,
      life: timeLife,
    });
  };

  return { showError };
};

