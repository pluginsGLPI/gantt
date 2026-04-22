/**
 * -------------------------------------------------------------------------
 * gantt plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of gantt.
 *
 * gantt is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * any later version.
 *
 * gantt is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with gantt. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2013-2023 by gantt plugin team.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/gantt
 * -------------------------------------------------------------------------
 */

const webpack = require('webpack');
const path = require('path');

module.exports = [
    {
        mode: 'production',
        entry: {
            'libs': path.resolve(__dirname, 'public/js/libs.js'),
        },
        output: {
            filename: 'libs.js',
            path: path.resolve(__dirname, 'public/lib'),
        },
        module: {
            rules: [
                {
                    test: /\.css$/,
                    use: ['style-loader', 'css-loader'],
                },
                {
                    test: /\.(ttf|woff|woff2|eot)$/,
                    type: 'asset/resource', // Plus moderne que file-loader pour Webpack 5
                    generator: {
                        filename: 'fonts/[name][ext]'
                    }
                },
            ],
        },
        plugins: [
            // Force tout le code dans un seul fichier pour GLPI
            new webpack.optimize.LimitChunkCountPlugin({
                maxChunks: 1,
            }),
        ],
        resolve: {
            mainFields: ['main'],
        },
        // Désactive les avertissements de performance (taille de libs.js)
        performance: {
            hints: false,
            maxEntrypointSize: 1024000,
            maxAssetSize: 1024000
        },
        devtool: 'source-map',
        stats: {
            all: false,
            errors: true,
            errorDetails: true,
            warnings: true,
            entrypoints: true,
            timings: true,
        }
    }
];
