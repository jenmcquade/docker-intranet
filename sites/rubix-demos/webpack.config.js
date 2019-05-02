const path = require('path');
const Webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const SWPrecacheWebpackPlugin = require('sw-precache-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const ROOT_PATH = path.resolve(__dirname);
const MODULES_PATH = path.resolve(__dirname, 'node_modules');
const PUBLIC_PATH = path.resolve(__dirname, 'public');
const SRC_PATH = path.join(ROOT_PATH, 'src');

module.exports = {
    devtool: 'inline-source-map',
    entry: {
        'app': [
            path.resolve(MODULES_PATH, 'babel-polyfill'),
            path.resolve(MODULES_PATH, 'webpack-hot-middleware/client?path=/__hmr'),
            path.resolve(SRC_PATH, 'index.js')
        ]
    },

    resolve: {
        extensions: ['.js', '.jsx']
    },

    output: {
        path: PUBLIC_PATH,
        publicPath: '/',
        filename: '[name].js',
    },

    plugins: [
        new HtmlWebpackPlugin({
            title: 'Open 3D Object Viewer',
            filename: 'index.html',
            showErrors: true,
            template: path.resolve(SRC_PATH, 'index.html'),
            buildAt: process.env.BUILD_TIME + ' UTC',
            buildVer: process.env.BUILD_VER,
        }),
        new Webpack.optimize.OccurrenceOrderPlugin(),
        new Webpack.HotModuleReplacementPlugin(),
        new Webpack.NamedModulesPlugin(),
        new Webpack.NoEmitOnErrorsPlugin(),
        new Webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify(process.env.NODE_ENV),
                CLIENT: JSON.stringify(process.env.CLIENT),
            }
        }),
        new ExtractTextPlugin({ filename: 'css/[name].css', allChunks: true }),
        new ManifestPlugin({
            fileName: 'asset-manifest.json', // Not to confuse with manifest.json 
        }),
        new SWPrecacheWebpackPlugin({
            // By default, a cache-busting query parameter is appended to requests
            // used to populate the caches, to ensure the responses are fresh.
            // If a URL is already hashed by Webpack, then there is no concern
            // about it being stale, and the cache-busting can be skipped.
            dontCacheBustUrlsMatching: /\.\w{8}\./,
            filename: 'service-worker.js',
            logger(message) {
                if (message.indexOf('Total precache size is') === 0) {
                    // This message occurs for every build and is a bit too noisy.
                    return;
                }
                console.log(message);
            },
            minify: true, // minify and uglify the script
            navigateFallback: '/index.html',
            staticFileGlobsIgnorePatterns: [/\.map$/, /asset-manifest\.json$/],
        }),
        new CopyWebpackPlugin([
            { from: 'src/pwa' }, // define the path of the files to be copied
        ])
    ],

    module: {
        loaders: [{
                test: /\.js$|\.jsx$/,
                loaders: [path.resolve(MODULES_PATH, 'babel-loader')],
                include: [
                    SRC_PATH
                ],
                exclude: MODULES_PATH
            },
            {
                test: /\.css$/,
                loaders: [
                    path.resolve(MODULES_PATH, 'babel-loader'),
                    path.resolve(MODULES_PATH, 'style-loader'),
                    path.resolve(MODULES_PATH, 'css-loader?importLoaders=1'),
                    path.resolve(MODULES_PATH, 'font-loader?format[]=truetype&format[]=woff&format[]=embedded-opentype'),
                ]
            },
            {
                test: /\.ico$/,
                loader: 'file-loader?./name=/[name].[ext]',
            },
            {
                test: /\.jpe?g$|\.gif$|\.png$/,
                loader: 'file-loader?name=.//static/media/[name].[ext]',
            },
            {
                test: /\.svg/,
                use: {
                    loader: 'svg-url-loader',
                    options: {
                        encoding: 'base64',
                        limit: 1024
                    }
                }
            },
            {
                test: /\.(eot|svg|ttf|woff|woff2)$/,
                loader: 'file-loader?name=./static/media/[name].[ext]',
            }
        ]
    }
};