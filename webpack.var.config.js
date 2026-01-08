/**
 * Build-time variables for the Webpack pipeline.
 *
 * This file defines which files will be processed and where
 * output CSS/JS files should be written.
 *
 * Keeping paths here ensures all build logic remains configurable
 * without modifying the base Webpack config.
 */

const basePath = '.';

const webpackParams = {
    /** Entry points for JavaScript + SCSS build */
    entryPath: {
        main: [
            basePath + '/public-assets/src/scss/main.scss',
            basePath + '/public-assets/src/js/main.js',
        ],
        admin: [
            basePath + '/public-assets/src/scss/admin.scss',
            basePath + '/public-assets/src/js/admin.js',
        ],
    },

    /** Output file locations (relative to the theme root) */
    jsOutputPath: basePath + '/public-assets/dist/js/[name].js',
    cssOutputPath: basePath + '/public-assets/dist/css/[name].css',
};

module.exports = { webpackParams };