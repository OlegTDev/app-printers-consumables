import pluginVue from 'eslint-plugin-vue';
import js from '@eslint/js';
import pluginImport from 'eslint-plugin-import';
import globals from 'globals';

export default [
  js.configs.recommended,
  ...pluginVue.configs['flat/recommended'],
  {
    plugins: {
      import: pluginImport,
    },
    languageOptions: {
      globals: {
        ...globals.browser,
        route: 'readonly',
      },
    },
    settings: {
      'import/resolver': {
        alias: {
          map: [
            ['@', './resources/js']
          ],
          extensions: ['.js', '.vue']
        }
      },
    },
    rules: {
      'semi': ['warn', 'always'],
      'vue/multi-word-component-names': 'off',
      'vue/require-default-prop': 'off',
      'vue/max-attributes-per-line': [
        'error',
        {
          singleline: { max: 5 },
          multiline: { max: 1 },
        },
      ],
      'import/extensions': [
        'warn',
        'always',
        {
          js: 'never',
          ts: 'never',
          mjs: 'never',
          vue: 'always',
        }
      ],
    },
  },
];
