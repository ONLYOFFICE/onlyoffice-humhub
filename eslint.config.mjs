import js from "@eslint/js";
import stylistic from "@stylistic/eslint-plugin";
import globals from "globals";

export default [
    {
        ignores: ["deploy/**", "resources/formats/**", "resources/templates/**"],
    },
    js.configs.recommended,
    {
        files: ["resources/**/*.js"],
        languageOptions: {
            ecmaVersion: "latest",
            sourceType: "script",
            globals: {
                ...globals.browser,
                ...globals.jquery,
                humhub: "readonly",
                DocsAPI: "readonly",
                clipboard: "readonly",
            },
        },
        plugins: {
            "@stylistic": stylistic,
        },
        rules: {
            "@stylistic/indent": ["error", 4],
            "@stylistic/semi": ["error", "always"],
            "no-var": "error",
            "prefer-const": "error",
        },
    },
];
