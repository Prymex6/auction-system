/* eslint-env node */

/**
 * The project already depended on ESLint, the Vue plugin and the Prettier
 * config, and package.json already had a `lint` script — but there was no
 * configuration file, so the script did nothing but fail. This is it.
 *
 * Prettier comes last on purpose: it switches off every rule that would
 * argue with the formatter, so formatting is settled in .prettierrc and
 * nowhere else.
 */
module.exports = {
  root: true,
  env: {
    browser: true,
    es2022: true,
    node: true,
  },
  extends: ['eslint:recommended', 'plugin:vue/vue3-recommended', '@vue/eslint-config-prettier'],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module',
  },
  rules: {
    // Leaving a console call behind is fine while developing and worth
    // noticing before it ships.
    'no-console': ['warn', { allow: ['warn', 'error'] }],
    'no-debugger': 'error',

    // Single-file components are named by their file; requiring a second
    // multi-word name inside them adds nothing here.
    'vue/multi-word-component-names': 'off',

    // Reported, not hidden. What is left is dead code inherited from earlier
    // work; it is removed as each component is next touched, rather than in
    // one sweep through screens that no test exercises.
    'no-unused-vars': 'warn',
  },
  overrides: [
    {
      files: ['**/*.spec.js', '**/tests/**/*.js'],
      env: { node: true },
      globals: {
        describe: 'readonly',
        it: 'readonly',
        expect: 'readonly',
        beforeEach: 'readonly',
        afterEach: 'readonly',
        vi: 'readonly',
      },
    },
  ],
}
