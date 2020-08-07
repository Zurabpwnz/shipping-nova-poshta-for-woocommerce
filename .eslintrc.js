module.exports = {
	env: {
		node: true,
		browser: true,
		jquery: true,
		jest: true,
		es6: true,
	},
	plugins: [
		'react',
		'jsdoc',
	],
	extends: [
		'eslint:recommended',
		'plugin:react/recommended',
		'wordpress',
	],
	parser: 'babel-eslint',
	parserOptions: {
		sourceType: 'script',
		sourceType: 'module',
		ecmaFeatures: {
			jsx: true,
		},
	},
	settings: {
		react: {
			createClass: 'createReactClass',
			pragma: 'React',
			version: '^16.0 ',
			flowVersion: '0.53',
		},
		propWrapperFunctions: [
			'forbidExtraProps',
			{
				property: 'freeze',
				'object': 'Object',
			},
			{
				property: 'myFavoriteWrapper',
			},
		],
	},
	globals: {
		_: false,
		Backbone: false,
		jQuery: false,
		JSON: false,
		wp: false,
	},
	rules: {
		'strict': [
			'error',
			'global',
		],
		'no-use-before-define': 'error',
		'new-cap': 'error',
		'space-in-parens': [
			'error',
			'always',
		],
		'camelcase': [
			'warn',
			{
				properties: 'always',
				allow: [
					'^wpforms_',
				],
			},
		],
		'comma-dangle': [
			'error',
			'always-multiline',
		],
		'func-call-spacing': 'error',
		'key-spacing': 'off',
		'vars-on-top': 'off',
		'yoda': 'off',
		'valid-jsdoc': 'off',
		'require-jsdoc': 'off',
		'func-style': 'off',
		'eqeqeq': 'error',
		'no-eq-null': 'error',
		'radix': 'error',
		'no-unused-vars': [
			'error',
			{
				args: 'none',
			},
		],
		'no-useless-escape': 'warn',
		"complexity": [
			'warn',
			{
				"max": 6,
			},
		],
		"max-lines-per-function": [
			'warn',
			{
				"max": 50,
				"skipBlankLines": true,
				"skipComments": true,
			},
		],
		"max-depth": [
			'error',
			3,
		],
		"indent": [
			'error',
			'tab',
			{
				"SwitchCase": 1,
			},
		],
		"jsdoc/check-alignment": 'error',
		"jsdoc/check-indentation": 'error',
		"jsdoc/check-param-names": 'error',
		"jsdoc/check-syntax": 'error',
		"jsdoc/check-tag-names": 'error',
		"jsdoc/check-types": 'error',
		"jsdoc/implements-on-classes": 'error',
		"jsdoc/match-description": 'error',
		"jsdoc/newline-after-description": 'error',
		"jsdoc/require-hyphen-before-param-description": [
			'error',
			'never',
		],
		'jsdoc/require-jsdoc': [
			'error',
			{
				'require': {
					ArrowFunctionExpression: false,
					ClassDeclaration: true,
					FunctionDeclaration: true,
					FunctionExpression: false,
				},
			},
		],
		"jsdoc/require-param": 'error',
		"jsdoc/require-param-description": 'error',
		"jsdoc/require-param-name": 'error',
		"jsdoc/require-param-type": 'error',
		"jsdoc/require-returns": 'error',
		"jsdoc/require-returns-check": 'error',
		"jsdoc/require-returns-description": 'error',
		"jsdoc/require-returns-type": 'error',
		"jsdoc/valid-types": 'error',
	},
	overrides: [
		{
			files: [
				'wpforms/assets/js/components/admin/gutenberg/formselector.js',
			],
			rules: {
				'react/react-in-jsx-scope': 'off',
				'react/jsx-uses-react': 'error',
				'react/jsx-uses-vars': 'error',
				'react/jsx-filename-extension': 'off',
				'react/jsx-indent': [
					'error',
					'tab',
				],
				'react/jsx-indent-props': [
					'error',
					'tab',
				],
			},
		},
	],
};
