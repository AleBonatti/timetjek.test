import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import ts from '@typescript-eslint/eslint-plugin';
import parser from '@typescript-eslint/parser';
import vueParser from 'vue-eslint-parser';
import globals from 'globals';

export default [
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    {
        files: ['**/*.{ts,tsx,vue}'],
        languageOptions: {
            parser: vueParser,
            parserOptions: {
                parser: parser,
                ecmaVersion: 'latest',
                sourceType: 'module',
            },
            globals: {
                ...globals.browser,
                ...globals.es2021,
            },
        },
        plugins: {
            '@typescript-eslint': ts,
        },
        rules: {
            ...ts.configs.recommended.rules,
            'vue/multi-word-component-names': 'off',
            'vue/no-v-html': 'warn',
            '@typescript-eslint/no-explicit-any': 'warn',
            '@typescript-eslint/explicit-module-boundary-types': 'off',
        },
    },
    {
        ignores: [
            'node_modules/**',
            'vendor/**',
            'public/**',
            'storage/**',
            'bootstrap/cache/**',
        ],
    },
];
