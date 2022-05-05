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
