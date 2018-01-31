const path = require('path');

// Minify JS
const MinifyPlugin = require('babel-minify-webpack-plugin');

// This plugin extracts the CSS into its own file instead of tying it with the JS.
// It prevents:
//   - not having styles due to a JS error
//   - the flash page without styles during JS loading
const ExtractTextPlugin = require("extract-text-webpack-plugin");

const extractSass = new ExtractTextPlugin({
    filename: "../css/[name].css",
    publicPath: 'tpl/default/css/',
    //disable: process.env.NODE_ENV === "development"
});

module.exports = {
    entry: {
        shaarli: './assets/default/js/base.js',
        picwall: './assets/default/js/picwall.js',
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'tpl/default/js/')
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            'babel-preset-env',
                        ]
                    }
                }
            },
            {
                test: /\.scss/,
                use: extractSass.extract({
                    use: [{
                        loader: "css-loader",
                        options: {
                            minimize: true,
                        }
                    }, {
                        loader: "sass-loader"
                    }],
                })
            },
            {
                test: /\.(gif|png|jpe?g|svg)$/i,
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: '../img/[name].[ext]',
                            publicPath: 'tpl/default/img/',
                        }
                    },
                    {
                        // Minify images at compilation
                        loader: 'image-webpack-loader',
                        options: {
                            optipng: {
                                optimizationLevel: 7,
                            },
                        },
                    },
                ],
            },
            {
                test: /\.(eot|ttf|woff|woff2)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
                loader: 'file-loader',
                options: {
                    name: '../fonts/[name].[ext]',
                    // do not add a publicPath here because it's already handled by CSS's publicPath
                    publicPath: '',
                }
            },
        ],
    },
    plugins: [
        new MinifyPlugin(),
        extractSass,
    ]
};


