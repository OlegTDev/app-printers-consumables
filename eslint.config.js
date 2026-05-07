import pluginVue from 'eslint-plugin-vue';
import js from '@eslint/js';

export default [
  js.configs.recommended,
  {
    ignores: ['storage/**', 'node_modules/**', 'dist/**'],
  },
  ...pluginVue.configs['flat/recommended'],
  {
    rules: {
      'semi': ['warn', 'always'],
      'vue/multi-word-component-names': 'off',
      'vue/require-default-prop': 'off',
      'vue/max-attributes-per-line': [
        'error',
        {
          singleline: {
            max: 5,
          },
          multiline: {
            max: 1,
          },
        },
      ],
    },
  },
];
