import pluginVue from 'eslint-plugin-vue';
export default [
  {
    ignores: ['storage/**', 'node_modules/**', 'dist/**'],
  },
  ...pluginVue.configs['flat/recommended'],
  {
    rules: {
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
