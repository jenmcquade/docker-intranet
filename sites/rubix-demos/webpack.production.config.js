const path = require('path');
// const merge = require('webpack-merge');
const Webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const ROOT_PATH = path.resolve(__dirname);
const MODULES_PATH = path.resolve(__dirname, 'node_modules');
const PUBLIC_PATH = path.resolve(__dirname, 'public');
const BUNDLE_PATH = path.join(PUBLIC_PATH, '');
const SRC_PATH = path.join(ROOT_PATH, 'src');

module.exports = {
  entry: {
    'app': [
      path.resolve(MODULES_PATH, 'babel-polyfill'),
      path.resolve(SRC_PATH, 'index.js')
    ]
  },

  resolve: {
    extensions: ['.js', '.jsx']
  },

  output: {
    path: BUNDLE_PATH,
    publicPath: './',
    filename: '[name].js',
  },

  plugins: [
    new HtmlWebpackPlugin({
      title: 'Open 3D Object Viewer',
      filename: 'index.html',
      showErrors: true,
      template: path.resolve(SRC_PATH, 'index.html'),
      inject: false,
      minify: {minifyCSS: true, minifyJS: true},
      buildAt: process.env.BUILD_TIME ? process.env.BUILD_TIME + ' UTC' : 'unavailable',
      buildVer: process.env.BUILD_VER ? process.env.BUILD_VER : 'unavailable',
    }),
    new Webpack.optimize.OccurrenceOrderPlugin(),
    new Webpack.optimize.UglifyJsPlugin(), //minify everything
    new Webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify(process.env.NODE_ENV),
        CLIENT: JSON.stringify(process.env.CLIENT),
      }
    }),
    new ExtractTextPlugin({ filename: './static/css/[name].css', allChunks: true}),
    new Webpack.optimize.UglifyJsPlugin({
      mangle: true,
      compress: {
        warnings: false, // Suppress uglification warnings
        pure_getters: true,
        unsafe: true,
        unsafe_comps: true,
        screw_ie8: true
      },
      output: {
        comments: false,
      },
      exclude: [/\.min\.js$/gi] // skip pre-minified libs
    }),
    new Webpack.IgnorePlugin(/^\.\/locale$/, [/moment$/]),
    new Webpack.NoEmitOnErrorsPlugin(),
    new ManifestPlugin({
      fileName: 'asset-manifest.json', // Not to confuse with manifest.json 
    }),
    new CompressionPlugin({
      asset: '[path].gz[query]',
      algorithm: 'gzip',
      test: /\.js$|\.css$|\.html$/,
      threshold: 10240,
      minRatio: 0,
      minimize: true,
      compress: {
          warnings: false,
      },
      sourceMap: false,
    }),
    new ManifestPlugin({
      fileName: 'asset-manifest.json', // Not to confuse with manifest.json 
    }),
    new CopyWebpackPlugin([
      { from: 'src/pwa' }, // define the path of the files to be copied
    ])
  ],

  module: {
    loaders: [
      { 
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
        loader: path.resolve(MODULES_PATH, 'file-loader?name=./[name].[ext]'),
      },
      {
        test: /\.jpe?g$|\.gif$|\.png$/,
        loader: path.resolve(MODULES_PATH, 'file-loader?name=./static/media/[name].[ext]'),
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
        loader: path.resolve(MODULES_PATH, 'file-loader?name=./static/media/[name].[ext]'),
      }
    ]
  }
};