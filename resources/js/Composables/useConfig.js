import { urls } from '@/config/urls';
import { config } from '@/config/config';

export const useConfig =() => ({
  urls: urls || {},
  config: config || {},
});
