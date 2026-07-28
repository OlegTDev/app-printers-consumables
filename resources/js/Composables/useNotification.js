import { useToast } from "primevue/usetoast";
import { useConfig } from "./useConfig";

export function useNotification() {

  const toast = useToast();
  const { config } = useConfig();

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

