import glpiEslintConfig from '../../eslint.config.mjs';

const filteredConfig = glpiEslintConfig.filter(config => !config.ignores);

export default [
    ...filteredConfig,
    {
        ignores: [
            'node_modules/*',
            "public/lib/*",
            'vendor/*',
        ],
    },
    {
        // Modules
        files: ["public/js/libs.js"],
        languageOptions: {
            sourceType: "module"
        }
    }
];
