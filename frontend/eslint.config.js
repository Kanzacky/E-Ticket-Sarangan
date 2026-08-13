import pluginVue from 'eslint-plugin-vue'
import tseslint from 'typescript-eslint'

import { globalIgnores } from 'eslint/config'
import js from '@eslint/js'
import prettier from 'eslint-config-prettier'
import vueParser from 'vue-eslint-parser'

export default tseslint.config(
  globalIgnores(['dist/**', 'node_modules/**']),
  js.configs.recommended,
  ...tseslint.configs.recommended,
  ...pluginVue.configs['flat/recommended'],
  {
    files: ['**/*.vue'],
    languageOptions: {
      parser: vueParser,
      parserOptions: {
        parser: tseslint.parser,
        ecmaVersion: 'latest',
        sourceType: 'module',
      },
    },
  },
  {
    files: ['**/*.{ts,vue}'],
    rules: {
      'no-undef': 'off',
    },
  },
  prettier,
)
