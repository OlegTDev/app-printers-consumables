import { urls } from '@/config/urls';
import { config } from '@/config/config';

export function useConfig() {
  return {
    urls: urls || {},
    config: config || {},
  };
};
