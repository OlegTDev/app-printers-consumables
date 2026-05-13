import { useNotification } from "@/Composables/useNotification";
import { ref } from "vue";

export function useReportError() {
  const { showError } = useNotification();
  const displayErrors = ref([]);

  const handleError = async (error) => {
    if (error.response && error.response.status == 422) {
      const textErrors = await error.response.data.text();
      const json = JSON.parse(textErrors);
      if (!json['errors']) {
        return;
      }
      const { errors } = json;
      const arrErrors = Object.values(errors ?? {});
      if (Array.isArray(arrErrors)) {
        displayErrors.value = arrErrors;
      }
    } else {
      showError(error.response.statusText);
    }
  };

  return {
    handleError,
    displayErrors,
  };
};
