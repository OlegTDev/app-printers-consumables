import { useDialog, useConfirm } from 'primevue';
import { createUrlWithParams } from '@/config/urls';
import { toValue } from 'vue';
import { router } from '@inertiajs/vue3';

export function useActions(orderContext, ConfirmDialog, orderId, comment) {
  const dialog = useDialog();
  const confirm = useConfirm();

  const openConfirmDialog = (params) => {
    const {
      url,
      idOrder,
      message,
      header,
      buttonLabel,
      btnSeverity = null,
      requestContext = { context: orderContext },
      width = '50vw',
      breakpoints = { '960px': '75vw', '640px': '90vw' },
    } = params ?? {};
    dialog.open(ConfirmDialog, {
      props: {
        header,
        style: {
          width,
        },
        breakpoints,
        modal: true,
      },
      data: {
        idOrder: toValue(idOrder),
        message: toValue(message),
        url: createUrlWithParams(url, requestContext),
        buttonLabel,
        btnSeverity,
      },
    });
  };


  const remove = (url) => {
    confirm.require({
      message: 'Вы уверены, что хотите удалить заказ?',
      header: 'Удаление заказа',
      accept: () => {
        const finalUrl = createUrlWithParams(url, { context: orderContext });
        router.delete(finalUrl);
      },
    });
  };

  const cancel = (url) => {
    confirm.require({
      message: 'Вы уверены, что хотите отменить заказ?',
      header: 'Отмена заказа',
      accept: () => {
        const finalUrl = createUrlWithParams(url, { context: orderContext });
        router.put(finalUrl);
      },
    });
  };

  const agree = (url) => {
    openConfirmDialog({
      url,
      idOrder: orderId,
      message: comment,
      header: 'Согласование',
      buttonLabel: 'Согласовать',
    });
  };

  const reject = (url) => {
    openConfirmDialog({
      url,
      idOrder: orderId,
      message: comment,
      header: 'Отказать в согласовании',
      buttonLabel: 'Отказать',
      btnSeverity: 'danger',
    });
  };

  const ordered = (url) => {
    openConfirmDialog({
      url,
      idOrder: orderId,
      message: comment,
      header: 'Заказан',
      buttonLabel: 'Перевести в состояние "Заказан"',
    });
  };

  const receive = (url) => {
    openConfirmDialog({
      url,
      idOrder: orderId,
      message: comment,
      header: 'Получен',
      buttonLabel: 'Перевести в состояние "Получен"',
    });
  };

  const complete = (url) => {
    openConfirmDialog({
      url,
      idOrder: orderId,
      message: comment,
      header: 'Исполнено',
      buttonLabel: 'Исполнено',
    });
  };

  return {
    remove,
    cancel,
    agree,
    reject,
    ordered,
    receive,
    complete,
  };
}
