'use strict';

const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    entry: './app',
    mode: 'production',
    output: {
        filename: 'build.js',
        library: 'app',
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'babel-loader',
                query: {
                    // presets: ['es2015']
                    "plugins": [
                        "@babel/plugin-proposal-class-properties"
                    ]
                }
            },
            {
                test: /\.css$/, loader: 'style-loader!css-loader'
            },
        ]
    },
    watch: false,
    optimization: {
        minimizer: [
            new TerserPlugin({
                parallel: false,
            }),
        ],
    },

    devtool: 'source-map',
};
