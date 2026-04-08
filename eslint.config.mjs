import js from "@eslint/js";
import globals from "globals";

export default [
	js.configs.recommended,
	{
		languageOptions: {
			globals: globals.browser,
			ecmaVersion: 2022,
			sourceType: "script"
		},
		rules: {
			"no-unused-vars": [
				"error",
				{
					caughtErrors: "none"
				}
			]
		}
	}
];
