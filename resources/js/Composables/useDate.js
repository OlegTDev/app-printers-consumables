import moment from 'moment';
import 'moment/dist/locale/ru';

moment.locale('ru');

export function useDate() {
  const formatDate = (date, format = 'LLLL') => {
    return date ? moment(date).format(format) : null;
  };

  const fromNow = (date) => {
    return date ? moment(date).fromNow() : null;
  };

  return {
    moment,
    formatDate,
    fromNow,
  };
}
