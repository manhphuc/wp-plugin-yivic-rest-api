/**
 * Main Webpack configuration loader.
 *
 * Delegates all logic to the base configuration and passes down
 * the selected build mode (development or production).
 */

const baseConfig = require('./webpack-base.config');
const pluginVariables = require('./webpack.var.config');

module.exports = function (env, argv) {
    const mode = argv.mode || 'development';
    return baseConfig.buildConfig(pluginVariables, mode);
};