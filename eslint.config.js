import { defineConfig } from "eslint/config";
import globals from "globals";

export default defineConfig({
  files: ["**/*.{js,mjs,cjs,vue}"],
  languageOptions: {
    globals: globals.browser,
  },
  plugins: {
    vue: "eslint-plugin-vue",
  },
  extends: [
    "plugin:vue/vue3-essential",   // або 'vue3-recommended'
    "eslint:recommended",
  ],
  rules: {
    // тут можна додати свої правила
  },
});
