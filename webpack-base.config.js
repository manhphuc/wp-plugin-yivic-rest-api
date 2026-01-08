/**
 * Base Webpack configuration for the Yivic Lite theme.
 *
 * This file defines the shared build pipeline, including:
 * - ES5-compatible JS bundling
 * - SCSS → CSS compilation
 * - Source maps during development
 * - CSS/JS minification in production
 * - TypeScript support (optional)
 *
 * The final config is generated through buildConfig() and extended
 * by webpack.config.js with the appropriate mode value.
 */

const path = require('path');
const WebpackBuildNotifierPlugin = require('webpack-build-notifier');
const TerserPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");

module.exports.buildConfig = function (webpackVariables, mode) {
    const { webpackParams } = webpackVariables;
    const isProduction = mode === 'production';

    return {
        /**
         * Target modern browsers while maintaining ES5 compatibility
         * to avoid Webpack's “no default script chunk format available” error.
         */
        target: ['web', 'es5'],

        /** Generate source maps in development mode only */
        devtool: isProduction ? false : 'inline-source-map',

        /** Entry points for JavaScript and SCSS assets */
        entry: webpackParams.entryPath,

        /** Output configuration */
        output: {
            filename: webpackParams.jsOutputPath,
            path: path.resolve(__dirname),

            /**
             * Explicit chunk format to resolve compatibility issues
             * when building for non-module environments.
             */
            chunkFormat: 'array-push',

            // Prevent Webpack from cleaning the entire theme directory.
            clean: false,
        },

        module: {
            rules: [
                /**
                 * TypeScript support (optional).
                 * Safe to remove if TypeScript is not used.
                 */
                {
                    test: /\.tsx?$/,
                    use: 'ts-loader',
                    exclude: /node_modules/,
                },

                /** JavaScript transpilation via Babel */
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: [
                                ['@babel/preset-env', { targets: 'defaults' }],
                            ],
                        },
                    },
                },

                /** Load source maps for JS files */
                {
                    test: /\.js$/,
                    enforce: 'pre',
                    use: ['source-map-loader'],
                },

                /**
                 * Plain CSS loader chain
                 * Extracted into standalone CSS files.
                 */
                {
                    test: /\.css$/i,
                    use: [MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader'],
                },

                /**
                 * SCSS loader chain:
                 * SCSS → CSS → PostCSS → extracted CSS file.
                 */
                {
                    test: /\.(sass|scss)$/i,
                    use: [
                        MiniCssExtractPlugin.loader,
                        {
                            loader: 'css-loader',
                            options: {
                                sourceMap: true,
                                url: false,
                            },
                        },
                        {
                            loader: 'resolve-url-loader',
                            options: { sourceMap: true },
                        },
                        {
                            loader: 'postcss-loader',
                            options: { sourceMap: true },
                        },
                        {
                            loader: 'sass-loader',
                            options: { sourceMap: true },
                        },
                    ],
                },
            ],
        },

        plugins: [
            /** Extract compiled CSS into separate output files */
            new MiniCssExtractPlugin({
                filename: webpackParams.cssOutputPath,
            }),

            /** Desktop notifications for build results */
            new WebpackBuildNotifierPlugin({
                title: "Yivic Lite Webpack Build",
                suppressSuccess: true,
            }),
        ],

        /** Allow importing JS/TS without specifying file extensions */
        resolve: {
            extensions: ['.js', '.jsx', '.ts', '.tsx'],
        },

        /** Production-level optimizations */
        optimization: {
            minimize: isProduction,
            minimizer: [
                /** JavaScript minifier */
                new TerserPlugin({
                    parallel: true,
                    terserOptions: { compress: true },
                }),

                /** CSS minifier */
                new CssMinimizerPlugin(),
            ],
        },

        /** Cleaner CLI logging */
        stats: 'minimal',
    };
};