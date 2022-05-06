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
 * @copyright Copyright (C) 2013-2022 by gantt plugin team.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/gantt
 * -------------------------------------------------------------------------
 */

const webpack = require('webpack');
const path = require('path');

/*
 * External libs build configuration.
 */
module.exports = [
   {
      entry: {
         'libs': path.resolve(__dirname, 'js/libs.js'),
      },
      output: {
         filename: 'libs.js',
         path: path.resolve(__dirname, 'public/build'),
      },
      module: {
         rules: [
            {
                // Load scripts with no compilation for packages that are directly providing "dist" files.
                // This prevents useless compilation pass and can also
                // prevents incompatibility issues with the webpack require feature.
                test: /\.js$/,
                include: [
                    path.resolve(__dirname, 'node_modules/dhtmlx-gantt'),
                ],
                use: ['script-loader'],
            },
            {
               test: /\.css$/,
               use: ['style-loader', 'css-loader'],
            },
            {
                test: /\.ttf$/,
                use: ['file-loader']
            },
         ],
      },
      plugins: [
         new webpack.optimize.LimitChunkCountPlugin({
            maxChunks: 1,
         }),
      ],
      resolve: {
         // Use only main file in requirement resolution as we do not yet handle modules correctly
         mainFields: [
            'main',
         ],
      },
      devtool: 'source-map', // Add sourcemap to files
      // Limit verbosity to only usefull informations
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
