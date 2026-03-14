import antfu from '@antfu/eslint-config'

export default antfu({
  rules: {
    'no-alert': 'off',
    'no-console': 'off',
    'n/prefer-global/process': 'off',
  },
})
