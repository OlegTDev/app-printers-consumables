import { authUrls } from './urls/auth';
import { usersUrls } from './urls/users';
import { chartUrls } from './urls/chart';
import { printersUrls } from './urls/printersUrls';
import { consumablesUrls } from './urls/consumablesUrls';
import {
  dictionaryPrintersUrls,
  dictionaryConsumablesUrls,
  dictionaryOrganizationsUrls,
  dictionarySparePartsUrls,
} from './urls/dictionary';
import { reportsUrls } from './urls/reports';
import { ordersUrls } from './urls/orders';

const API_ROOT = '/';

export const urls = {

  home: API_ROOT,

  // аутентификация
  auth: authUrls(API_ROOT),
  // управление пользователями
  users: usersUrls(API_ROOT),
  // графики
  chart: chartUrls(API_ROOT),
  // принтеры на рабочих местах
  printers: printersUrls(API_ROOT),
  // расходные материалы
  consumables: consumablesUrls(API_ROOT),

  // справочники
  dictionary: {
    // справочник принтеров
    printers: dictionaryPrintersUrls(API_ROOT),
    // справочник расходных материалов
    consumables: dictionaryConsumablesUrls(API_ROOT),
    // справочник организаций
    organizations: dictionaryOrganizationsUrls(API_ROOT),
    // запчасти
    spareParts: dictionarySparePartsUrls(API_ROOT),
  },

  // отчеты
  reports: reportsUrls(API_ROOT),
  // заказы
  orders: ordersUrls(API_ROOT),

  // статистика
  logActions: {
    // сохранение
    save: 'http://86000-app045:4785/view/save',
  },

}

export const createUrlWithParams = (path, params) => {
  let url = new URL(path, window.location.origin);
  Object.entries(params).forEach(([key, value]) => {
    url.searchParams.set(key, value);
  })
  return url;
}